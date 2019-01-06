<?php

namespace LumIT\Typo3bb\Domain\Repository;


/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Philipp Seßner <philipp.sessner@gmail.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use LumIT\Typo3bb\Domain\Model\Board;
use LumIT\Typo3bb\Domain\Model\Post;
use LumIT\Typo3bb\Domain\Model\Topic;
use LumIT\Typo3bb\Utility\FrontendUserUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * The repository for Posts
 */
class PostRepository extends AbstractRepository
{
    /**
     * topicRepository
     *
     * @var \LumIT\Typo3bb\Domain\Repository\TopicRepository
     *
     */
    protected $topicRepository = null;

    protected $softCache = [
        'unreadPosts' => []
    ];

    public function injectTopicRepository(\LumIT\Typo3bb\Domain\Repository\TopicRepository $topicRepository)
    {
        $this->topicRepository = $topicRepository;
    }

    /**
     * Returns the previous posts of specified post. If no post is specified, it returns the last posts of specified topic.
     *
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     * @param \LumIT\Typo3bb\Domain\Model\Post $post
     * @param int $limit
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findPrevious(Topic $topic, Post $post = null, $limit = 5)
    {
        $query = $this->createQuery();
        $constraints[] = $query->equals('topic', $topic);
        if ($post != null) {
            $constraints[] = $query->lessThan('crdate', $post->getCrdate());
        }
        return $query->matching($query->logicalAnd($constraints))
            ->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING])
            ->setLimit($limit)
            ->execute();
    }

    /**
     * Returns all posts that were created after the specified post in posts topic.
     *
     * @param \LumIT\Typo3bb\Domain\Model\Post $post
     * @return QueryResultInterface
     */
    public function findFollowing(Post $post)
    {
        $query = $this->createQuery();
        return $query->matching($query->logicalAnd(
            $query->equals('topic', $post->getTopic()),
            $query->greaterThanOrEqual('crdate', $post->getCrdate())
        ))->execute();
    }

    public function countUnread($frontendUser, $board = null, $topic = null) {
        if ($frontendUser instanceof FrontendUser) $frontendUser = $frontendUser->getUid();
        if ($board instanceof Board) $board = $board->getUid();
        if ($topic instanceof Topic) $topic = $topic->getUid();

        if (!isset($this->softCache['unreadPosts'][$frontendUser])) {
            $this->softCache['unreadPosts'][$frontendUser] = [];
            $queryBuilder = $this->getUnreadQueryBuilder($frontendUser);
            $topicUnreadCounts = $queryBuilder->execute()->fetchAll();

            $this->softCache['unreadPosts'][$frontendUser] = $topicUnreadCounts;
        }

        if ($topic !== null) {
            $unreadCount = 0;
            foreach ($this->softCache['unreadPosts'][$frontendUser] as $row) {
                if ($row['topic'] == $topic) {
                    $unreadCount += $row['unreadCount'];
                }
            }
            return $unreadCount;
        }

        // when $board is set, we count unread topics, not unread posts
        $unreadCount = 0;
        foreach ($this->softCache['unreadPosts'][$frontendUser] as $row) {
            if ($board === null || $row['board'] == $board) {
                $unreadCount++;
            }
        }
        return $unreadCount;
    }



    /**
     * Returns the first unread post of each readable topic.
     * If $board is specified, only posts in the specified board are returned.
     * If $topic is specified, only the first unread post in the topic is returned.
     *
     * @param \LumIT\Typo3bb\Domain\Model\FrontendUser|int $frontendUser
     * @param Board|int $board
     * @param Topic|int $topic
     * @return Post|Post[]
     */
    public function findUnread($frontendUser, $board = null, $topic = null)
    {
        $queryBuilder = $this->getUnreadQueryBuilder($frontendUser);

        $unreadPosts = $queryBuilder->execute()->fetchAll();
        $unreadFirstPosts = [];
        foreach ($unreadPosts as $unreadPost) {
            if (!isset($unreadFirstPosts[$unreadPost['topic']])) {
                $unreadFirstPosts[$unreadPost['topic']] = $unreadPost['post'];
            } else {
                if ($unreadFirstPosts[$unreadPost['topic']] > $unreadPost['post']) {
                    $unreadFirstPosts[$unreadPost['topic']] = $unreadPost['post'];
                }
            }
        }

        if ($topic !== null) {
            $topic = $topic instanceof Topic ? $topic->getUid() : $topic;
            return $this->findByUid($unreadFirstPosts[$topic]);
        }

        $unreadPosts = [];
        foreach ($unreadFirstPosts as $unreadFirstPost) {
            $unreadPosts[] = $this->findByUid($unreadFirstPost);
        }

        return $unreadPosts;
    }

    /**
     * Find the latest post of a board recursively. Considers the read_permission
     * @param $usergroups
     * @param $board
     * @return null
     */
    public function findLatestRecursive($usergroups, $board) {
        if ($board instanceof Board) {
            $board = $board->getUid();
        }
        if (is_array($usergroups)) {
            $usergroups = implode(',', $usergroups);
        }
        $usergroups = implode(',', GeneralUtility::intExplode(',', $usergroups, true));

        $recursiveInformationQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_typo3bb_domain_model_post');
        $recursiveInformationQueryBuilder->select('*')
            ->from('view_tx_typo3bb_board_latest_post', 'latest_post')
            ->join('latest_post',
                'view_tx_typo3bb_board_recursive_information', 'recursive_information',
                $recursiveInformationQueryBuilder->expr()->eq('latest_post.board', $recursiveInformationQueryBuilder->quoteIdentifier('recursive_information.uid'))
            );

        $latestPostRecursiveQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_typo3bb_domain_model_post');
        $latestPostRecursiveQueryBuilder->select('children.uid as board')
            ->addSelectLiteral($latestPostRecursiveQueryBuilder->expr()->max('latest_post.post', 'post'))
            ->from('view_tx_typo3bb_board_children', 'children');
        $latestPostRecursiveQueryBuilder->getConcreteQueryBuilder()->join($latestPostRecursiveQueryBuilder->quoteIdentifier('children'),
            sprintf('(%s)', $recursiveInformationQueryBuilder->getSQL()), $latestPostRecursiveQueryBuilder->quoteIdentifier('latest_post'),
            $latestPostRecursiveQueryBuilder->expr()->inSet('latest_post.rootline', $latestPostRecursiveQueryBuilder->quoteIdentifier('children.uid'))
        );
        $latestPostRecursiveQueryBuilder->where($latestPostRecursiveQueryBuilder->expr()->eq('children.uid', $board));

        $latestPostRecursiveQueryBuilder->getSQL();
        if (!empty($usergroups)) {
            $latestPostRecursiveQueryBuilder->andWhere($latestPostRecursiveQueryBuilder->expr()->comparison(
                'findMultipleInAndSet(\'' . $usergroups . '\', ' . $latestPostRecursiveQueryBuilder->quoteIdentifier('latest_post.read_permissions') . ')',
                '=',
                'TRUE'
            ));
        }
        $latestPostRecursiveQueryBuilder->groupBy('children.uid');
        $latestPostRecursiveQueryBuilder->setMaxResults(1);

        $postQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_typo3bb_domain_model_post');
        $postQueryBuilder->select('post.*')
            ->from('tx_typo3bb_domain_model_post', 'post');
        $postQueryBuilder->getConcreteQueryBuilder()
            ->innerJoin($postQueryBuilder->quoteIdentifier('post'),
                sprintf('(%s)', $latestPostRecursiveQueryBuilder->getSQL()), $postQueryBuilder->quoteIdentifier('latest_post_recursive'),
                $postQueryBuilder->expr()->eq('post.uid', $postQueryBuilder->quoteIdentifier('latest_post_recursive.post'))
            );

        $postsArray = $postQueryBuilder->execute()->fetchAll();
        $dataMapper = $this->objectManager->get(DataMapper::class);
        $postObjects = $dataMapper->map($this->objectType, $postsArray);
        return $postObjects[0] ?? null;
    }

    /**
     * @param array|string $usergroups
     * @param \LumIT\Typo3bb\Domain\Model\Board|int|array $boards
     * @param int $limit
     *
     * @return QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findLatest($usergroups, $boards = null, $limit = 0)
    {
        if (!is_array($usergroups)) {
            $usergroups = explode(',', $usergroups);
        }

        $query = $this->createQuery();
        $constraints = [];

        $groupConstraints = [];
        foreach ($usergroups as $usergroup) {
            $groupConstraints[] = $query->contains('topic.board.readPermissions', $usergroup);
        }
        $constraints[] = $query->logicalOr($groupConstraints);

        if ($boards != null) {
            $boardConstraints = [];
            if (!is_array($boards)) {
                $boards = [$boards];
            }
            foreach ($boards as $board) {
                if ($board instanceof Board) {
                    $board = $board->getUid();
                }
                $boardConstraints[] = $query->equals('topic.board.uid', $board);
            }
            $constraints[] = $query->logicalOr($boardConstraints);
        }

        $query->matching($query->logicalAnd($constraints));

        if ($limit > 0) {
            $query->setLimit($limit);
        }

        $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);

        return $query->execute();
    }

    public function findLatestInTopic($topic)
    {
        $query = $this->createQuery();
        $query->matching($query->equals('topic', $topic->getUid()));
        $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);

        return $query->execute()->getFirst();
    }

    public function findLatestInBoard($board)
    {
        $query = $this->createQuery();
        $query->matching($query->equals('topic.board', $board->getUid()));
        $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);

        return $query->execute()->getFirst();
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Post $currentPost
     *
     * @return \LumIT\Typo3bb\Domain\Model\Post
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findNext($currentPost)
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('topic', $currentPost->getTopic()),
                $query->greaterThan('uid', $currentPost->getUid())
            )
        );
        $query->setOrderings([
            'uid' => QueryInterface::ORDER_ASCENDING
        ]);
        return $query->execute()->getFirst();
    }


    /**
     * Returns ['board' => $boardUid, 'topic' => $topicUid, 'post' => $postUid] of all unread posts
     *
     * @param \LumIT\Typo3bb\Domain\Model\FrontendUser|int $frontendUser $frontendUser
     * @return QueryBuilder
     *
     * //TODO it might be possible to simplify the resulting query (replace sub queries with joins)
     */
    protected function getUnreadQueryBuilder($frontendUser) {
        if (!($frontendUser instanceof FrontendUser)) {
            $frontendUser = $this->objectManager->get(FrontendUserRepository::class)->findByUid($frontendUser);
        }
        $usergroups = FrontendUserUtility::getUsergroupList($frontendUser);


        $maxReaderPostSelect = $this->objectManager->get(ConnectionPool::class)->getQueryBuilderForTable('tx_typo3bb_domain_model_reader');
        $maxReaderPostSelect->addSelectLiteral($maxReaderPostSelect->expr()->max('reader.post'))
            ->from('tx_typo3bb_domain_model_reader', 'reader')
            ->where(
                $maxReaderPostSelect->expr()->eq('reader.user', $frontendUser->getUid()),
                $maxReaderPostSelect->expr()->eq('reader.topic', 'topic.uid')
            );
        $readerTopicsSelect = $this->objectManager->get(ConnectionPool::class)->getQueryBuilderForTable('tx_typo3bb_domain_model_reader');
        $readerTopicsSelect->select('reader.topic')
            ->from('tx_typo3bb_domain_model_reader', 'reader')
            ->where($readerTopicsSelect->expr()->eq('reader.user', $frontendUser->getUid()));
        $lastReadPostSelect = $this->objectManager->get(ConnectionPool::class)->getQueryBuilderForTable('fe_users');
        $lastReadPostSelect->select('feuser.last_read_post')
            ->from('fe_users', 'feuser')
            ->where($lastReadPostSelect->expr()->eq('feuser.uid', $frontendUser->getUid()));

        $innerQueryBuilder = $this->objectManager->get(ConnectionPool::class)->getQueryBuilderForTable('tx_typo3bb_domain_model_post');
        $innerQueryBuilder->addSelect('innerpost.uid');
        $innerQueryBuilder->from('tx_typo3bb_domain_model_post', 'innerpost')
            ->where(
                $innerQueryBuilder->expr()->eq('innerpost.topic', 'topic.uid'),
                $innerQueryBuilder->expr()->orX(
                    $innerQueryBuilder->expr()->gt('innerpost.uid', '(' . $maxReaderPostSelect->getSQL() . ')'),
                    $innerQueryBuilder->expr()->notIn('innerpost.topic', $readerTopicsSelect->getSQL())
                ),
                $innerQueryBuilder->expr()->gt('innerpost.uid', '(' . $lastReadPostSelect->getSQL() . ')')
            );

        $queryBuilder = $this->objectManager->get(ConnectionPool::class)->getQueryBuilderForTable('tx_typo3bb_domain_model_post');
        $queryBuilder->select('topic.uid as topic', 'topic.board as board', 'post.uid as post')
            ->from('tx_typo3bb_domain_model_post', 'post')
            ->rightJoin('post', 'tx_typo3bb_domain_model_topic', 'topic', $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('topic.uid', 'post.topic'),
                $queryBuilder->expr()->in('post.uid', $innerQueryBuilder->getSQL())
            ))->leftJoin('post', 'tx_typo3bb_domain_model_board', 'board', $queryBuilder->expr()->eq('topic.board', 'board.uid'));
        $whereClauses = [
            $queryBuilder->expr()->isNotNull('post.uid'),
            $queryBuilder->expr()->comparison('hasAccess(board.uid, \'' . $usergroups . '\')', '=', 'TRUE')
        ];
        if ($frontendUser->getLastReadPost() != null) {
            $whereClauses[] = $queryBuilder->expr()->gt('post.uid', $frontendUser->getLastReadPost()->getUid());
        }
        $queryBuilder->where(...$whereClauses);

        return $queryBuilder;
    }
}