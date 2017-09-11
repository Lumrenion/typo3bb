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
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
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
    protected $topicRepository = NULL;

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Post $post
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function remove($post) {
        parent::remove($post);
        if(!is_null($post->getAuthor())) {
            $post->getAuthor()->removeCreatedPost($post);
        }
        if (!is_null($post->getEditor())) {
            $post->getEditor()->removeEditedPost($post);
        }
        $post->getTopic()->removePost($post);
        $this->topicRepository->update($post->getTopic());
    }

    /**
     * Returns the previous posts of specified post. If no post is specified, it returns the last posts of specified topic.
     *
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     * @param \LumIT\Typo3bb\Domain\Model\Post  $post
     * @param int                               $limit
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findPrevious(Topic $topic, Post $post = null, $limit = 5) {
        $query = $this->createQuery();
        $constraints[] = $query->equals('topic', $topic);
        if($post != null) {
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
    public function findFollowing(Post $post) {
        $query = $this->createQuery();
        return $query->matching($query->logicalAnd(
            $query->equals('topic', $post->getTopic()),
            $query->greaterThanOrEqual('crdate', $post->getCrdate())
        ))->execute();
    }

    /**
     * There seems to be a bug in extbase: When calling the count()-Method of a Query that has only the statement set,
     * the statement is not respected.
     * Therefor this method extends the findUnread-Method by wrapping it into a count and returning the result.
     *
     * @param $frontendUser
     * @param string $usergroups
     * @param null $board
     * @param null $topic
     * @return array|QueryResultInterface
     */
    public function countUnread($frontendUser, $usergroups = '', $board = null, $topic = null) {
        $unread = $this->findUnread($frontendUser, $usergroups, $board, $topic, true);
        $unreadStatement = $unread->getStatement()->getStatement();
        $unreadStatement = "SELECT COUNT(postCount.uid) as 'count' FROM ($unreadStatement) as postCount";
        $count = $unread->statement($unreadStatement)->execute(true)[0]['count'];
        return $count;
    }

    /**
     * Returns the first unread post of each readable topic.
     * If $board is specified, only posts in the specified board are returned.
     *
     * @param FrontendUser|int  $frontendUser
     * @param string            $usergroups
     * @param Board|int         $board
     * @param Topic|int         $topic
     * @return array|QueryResultInterface|Query
     * //TODO if TYPO3v8 brings a better database abstraction through doctrine, refactor this query.
     */
    public function findUnread($frontendUser, $usergroups = '', $board = null, $topic = null, $returnQuery = false) {
        if ($frontendUser instanceof FrontendUser) {
            $usergroups = $frontendUser->getUsergroup()->toArray();
            $usergroups = array_map(function($usergroup) {
                return $usergroup->getUid();
            }, $usergroups);
            $usergroups[] = 0;
            $usergroups[] = -2;
            $frontendUser = $frontendUser->getUid();
        } else {
            $usergroups = explode(',', $usergroups);
        }

        /** @var Query $query */
        $query = $this->createQuery();

        $sqlQuery = "
SELECT post.* 
FROM tx_typo3bb_domain_model_post as post 
RIGHT JOIN tx_typo3bb_domain_model_topic as topic 
ON (
    topic.uid = post.topic 
    AND post.uid = (
        SELECT MIN(innerpost.uid) 
        FROM tx_typo3bb_domain_model_post as innerpost 
        WHERE innerpost.topic = topic.uid AND innerpost.uid NOT IN (
            SELECT reader.post FROM tx_typo3bb_domain_model_reader as reader WHERE reader.user = '$frontendUser'
        ) AND innerpost.uid > (
            SELECT feuser.last_read_post FROM fe_users as feuser WHERE feuser.uid = '$frontendUser'
        )
    ) 
)
LEFT JOIN tx_typo3bb_domain_model_board as board ON (topic.board = board.uid)
        ";

        //APPEND WHERE CLAUSE
        $sqlQuery .= ' WHERE post.uid IS NOT NULL';
        if ($board != null) {
            if ($board instanceof Board) {
                $board = $board->getUid();
            }
            $sqlQuery .= ' AND board.uid = ' . $board;
        }
        if ($topic != null) {
            if ($topic instanceof Topic) {
                $topic = $topic->getUid();
            }
            $sqlQuery .= ' AND topic.uid = ' . $topic;
        }
        //APPEND STORAGE PAGE CONSTRAINT
        $sqlQuery .= ' AND post.pid IN (' . implode(',', $query->getQuerySettings()->getStoragePageIds()) . ')';
        //APPEND READ PERMISSION CONSTRAINT
        $sqlQuery .= ' AND (';
        $usergroupQueries = [];
        foreach ($usergroups as $usergroup) {
            $usergroupQueries[] = 'FIND_IN_SET(\'' . intval($usergroup) . '\', board.read_permissions)';
        }
        $sqlQuery .= implode(' OR ', $usergroupQueries);
        $sqlQuery .= ')';

        $query->statement($sqlQuery);
        if ($returnQuery) {
            return $query;
        }
        return $query->execute();
    }

    /**
     * @param array|string $usergroups
     * @param \LumIT\Typo3bb\Domain\Model\Board|int|array $boards
     * @param int $limit
     *
     * @return QueryResultInterface
     */
    public function findLatest($usergroups, $boards = null, $limit = 0) {
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
}