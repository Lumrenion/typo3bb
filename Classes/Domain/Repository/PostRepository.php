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
     * @inject
     */
    protected $topicRepository = null;

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

    /**
     * Returns the first unread post of each readable topic.
     * If $board is specified, only posts in the specified board are returned.
     *
     * @param \LumIT\Typo3bb\Domain\Model\FrontendUser|int $frontendUser
     * @param Board|int $board
     * @param Topic|int $topic
     * @param bool      $all
     * @param bool      $returnQueryBuilder
     * @return Post[]|QueryBuilder
     * //TODO it might be possible to simplify the resulting query (replace sub queries with joins)
     */
    public function findUnread($frontendUser, $board = null, $topic = null, $all = false, $returnQueryBuilder = false)
    {
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
        if ($all) {
            $innerQueryBuilder->addSelect('innerpost.uid');
        } else {
            $innerQueryBuilder->addSelectLiteral($innerQueryBuilder->expr()->min('innerpost.uid'));
        }
        $innerQueryBuilder->from('tx_typo3bb_domain_model_post', 'innerpost')
            ->where(
                $innerQueryBuilder->expr()->eq('innerpost.topic', 'topic.uid'),
                $innerQueryBuilder->expr()->orX(
                    $innerQueryBuilder->expr()->gt('innerpost.uid', '(' . $maxReaderPostSelect->getSQL() . ')'),
                    $innerQueryBuilder->expr()->notIn('innerpost.topic', '(' . $readerTopicsSelect->getSQL() . ')')
                ),
                $innerQueryBuilder->expr()->gt('innerpost.uid', '(' . $lastReadPostSelect->getSQL() . ')')
            );

        $queryBuilder = $this->objectManager->get(ConnectionPool::class)->getQueryBuilderForTable('tx_typo3bb_domain_model_post');
        $queryBuilder->select('post.*')
            ->from('tx_typo3bb_domain_model_post', 'post')
            ->rightJoin('post', 'tx_typo3bb_domain_model_topic', 'topic', $queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('topic.uid', 'post.topic'),
                $queryBuilder->expr()->{$all ? 'in' : 'eq'}('post.uid', '(' . $innerQueryBuilder->getSQL() . ')')
            ))->leftJoin('post', 'tx_typo3bb_domain_model_board', 'board', $queryBuilder->expr()->eq('topic.board', 'board.uid'));
        $whereClauses = [
            $queryBuilder->expr()->isNotNull('post.uid'),
            $queryBuilder->expr()->comparison('hasAccess(board.uid, \'' . $usergroups . '\')', '=', 'TRUE')
        ];
        if ($frontendUser->getLastReadPost() != null) {
            $whereClauses[] = $queryBuilder->expr()->gt('post.uid', $frontendUser->getLastReadPost()->getUid());
        }
        if ($board != null) {
            $whereClauses[] = $queryBuilder->expr()->eq('board.uid', $board instanceof Board ? $board->getUid() : $board);
        }
        if ($topic != null) {
            $whereClauses[] = $queryBuilder->expr()->eq('topic.uid', $topic instanceof Topic ? $topic->getUid() : $topic);
        }
        $queryBuilder->where(...$whereClauses);

        $queryBuilder->orderBy('post.crdate', 'ASC');

        if ($returnQueryBuilder) {
            return $queryBuilder;
        }

        $postsArray = $queryBuilder->execute()->fetchAll();
        $dataMapper = $this->objectManager->get(DataMapper::class);
        return $dataMapper->map($this->objectType, $postsArray);
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\FrontendUser|int $frontendUser
     * @param Board|int $board
     * @param Topic|int $topic
     * @param bool      $all
     * @return int
     */
    public function countUnread($frontendUser, $board = null, $topic = null, $all = false)
    {
        $queryBuilder = $this->findUnread($frontendUser, $board, $topic, $all, true);
        $queryBuilder->count('post.uid');
        return (int)$queryBuilder->execute()->fetchColumn(0);
    }

    /**
     * @param array|string $usergroups
     * @param \LumIT\Typo3bb\Domain\Model\Board|int|array $boards
     * @param int $limit
     *
     * @return QueryResultInterface
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
}