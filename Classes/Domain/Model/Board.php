<?php

namespace LumIT\Typo3bb\Domain\Model;


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
use LumIT\Typo3bb\Domain\Repository\BoardRepository;
use LumIT\Typo3bb\Domain\Repository\FrontendUserGroupRepository;
use LumIT\Typo3bb\Domain\Repository\FrontendUserRepository;
use LumIT\Typo3bb\Domain\Repository\PostRepository;
use LumIT\Typo3bb\Utility\FrontendUserUtility;
use LumIT\Typo3bb\Utility\SecurityUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * A board can contain subboards and posts.
 * Boards without parent board need a forumCategory
 */
class Board extends AbstractCachableModel
{

    /**
     * title
     *
     * @var string
     * @validate NotEmpty
     */
    protected $title = '';

    /**
     * description
     *
     * @var string
     */
    protected $description = '';

    /**
     * redirect
     *
     * @var string
     */
    protected $redirect = '';

    /**
     * redirectCount
     *
     * @var int
     */
    protected $redirectCount = 0;

    /**
     * Topics of this board
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Topic>
     * @cascade remove
     * @lazy
     */
    protected $topics = null;

    /**
     * sub boards of the board
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Board>
     * @cascade remove
     * @lazy
     */
    protected $subBoards = null;

    /**
     * @var string
     */
    protected $readPermissions = '';

    /**
     * @var string
     */
    protected $writePermissions = '';

    /**
     * @var string
     */
    protected $moderatorGroups = '';

    /**
     * The users subscribed this topic
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\FrontendUser>
     * @cascade remove
     * @lazy
     */
    protected $subscribers = null;

    /**
     * Parent board of the board
     *
     * @var \LumIT\Typo3bb\Domain\Model\Board
     * @lazy
     */
    protected $parentBoard = null;

    /**
     * The category the board is in
     *
     * @var \LumIT\Typo3bb\Domain\Model\ForumCategory
     */
    protected $forumCategory = null;

    /**
     * @var bool
     */
    protected $txKesearchIndex = true;

    /**
     * @var array
     */
    protected $moderatorGroupsArray = null;

    /**
     * @var array
     */
    protected $moderatorsArray = null;


    /********************************************
     *                                          *
     *                                          *
     *             META INFORMATION             *
     *  Information not persisted in database   *
     *     collected at runtime and cached      *
     *                                          *
     *                                          *
     ********************************************/

    /**
     * Not persisted attribute, will be determined on demand and cached here
     *
     * @var \LumIT\Typo3bb\Domain\Model\Post
     */
    protected $latestPost = null;

    /**
     * Not persisted attribute, a performance shortcut for latestPost->getCrdate
     *
     * @var \DateTime
     */
    protected $latestPostCrdate = null;

    /**
     * Not persisted attribute, count of posts in board
     *
     * @var int
     */
    protected $postsCount = null;

    /**
     * Not persisted attribute, so the viewable latest post needs only to get detected once per Board-Object
     *
     * @var Post
     */
    protected $viewableLatestPost = null;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    protected $allowedSubBoards = null;

    /**
     * __construct
     */
    public function __construct()
    {
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }

    /**
     * Initializes all ObjectStorage properties
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->topics = new ObjectStorage();
        $this->subBoards = new ObjectStorage();
        $this->subscribers = new ObjectStorage();
    }

    /**
     * Returns the title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns the description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the description
     *
     * @param string $description
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Returns the redirect
     *
     * @return string $redirect
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * Sets the redirect
     *
     * @param string $redirect
     * @return void
     */
    public function setRedirect($redirect)
    {
        $this->redirect = $redirect;
    }

    /**
     * @return int
     */
    public function getRedirectCount()
    {
        return $this->redirectCount;
    }

    /**
     * @param int $redirectCount
     */
    public function setRedirectCount($redirectCount)
    {
        $this->redirectCount = $redirectCount;
    }

    /**
     * Increase the redirect count by one
     */
    public function increaseRedirectCount()
    {
        $this->redirectCount++;
    }

    /**
     * Adds a Topic
     *
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     * @return void
     */
    public function addTopic(Topic $topic)
    {
        $this->topics->attach($topic);
    }

    /**
     * Removes a Topic
     *
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topicToRemove The Topic to be removed
     * @return void
     */
    public function removeTopic(Topic $topicToRemove)
    {
        $this->topics->detach($topicToRemove);
    }

    /**
     * Returns the topics
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Topic> $topics
     */
    public function getTopics()
    {
        return $this->topics;
    }

    /**
     * Sets the topics
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Topic> $topics
     * @return void
     */
    public function setTopics(ObjectStorage $topics)
    {
        $this->topics = $topics;
    }

    /**
     * Adds a Board
     *
     * @param \LumIT\Typo3bb\Domain\Model\Board $subBoard
     * @return void
     */
    public function addSubBoard(Board $subBoard)
    {
        $this->subBoards->attach($subBoard);
        $subBoard->setParentBoard($this);
    }

    /**
     * Removes a Board
     *
     * @param \LumIT\Typo3bb\Domain\Model\Board $subBoardToRemove The Board to be removed
     * @return void
     */
    public function removeSubBoard(Board $subBoardToRemove)
    {
        $this->subBoards->detach($subBoardToRemove);
    }

    /**
     * Returns an array containing subboards and their subboards recursively
     *
     * @return Board[]
     */
    public function getAllSubBoards()
    {
        $allSubBoards = [];
        /** @var Board $subBoard */
        foreach ($this->subBoards as $subBoard) {
            $allSubBoards = array_merge($allSubBoards, $subBoard->getAllSubBoards());
        }
        $allSubBoards = array_merge($allSubBoards, $this->getSubBoards()->toArray());

        return $allSubBoards;
    }

    /**
     * Returns the subBoards
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Board> $subBoards
     */
    public function getSubBoards()
    {
        return $this->subBoards;
    }

    /**
     * Sets the subBoards
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Board> $subBoards
     * @return void
     */
    public function setSubBoards(ObjectStorage $subBoards)
    {
        $this->subBoards = $subBoards;
    }

    /**
     * Returns the readPermissions
     *
     * @return string $readPermissions
     */
    public function getReadPermissions()
    {
        return $this->readPermissions;
    }

    /**
     * Sets the readPermissions
     *
     * @param string $readPermissions
     * @return void
     */
    public function setReadPermissions(string $readPermissions)
    {
        $this->readPermissions = $readPermissions;
    }

    /**
     * Returns the writePermissions
     *
     * @return string $writePermissions
     */
    public function getWritePermissions()
    {
        return $this->writePermissions;
    }

    /**
     * Sets the writePermissions
     *
     * @param string $writePermissions
     * @return void
     */
    public function setWritePermissions(string $writePermissions)
    {
        $this->writePermissions = $writePermissions;
    }

    /**
     * Returns the moderatorgroups as comma separated list of UIDs
     *
     * @return string
     */
    public function getModeratorGroups()
    {
        return $this->moderatorGroups;
    }

    /**
     * Set a new comma separated list of UIDs for moderatorgroups
     *
     * @param string $moderatorGroups
     */
    public function setModeratorGroups(string $moderatorGroups)
    {
        $this->moderatorGroups = $moderatorGroups;
        $this->moderatorGroupsArray = null;
    }

    public function getModeratorGroupsArray()
    {
        if ($this->moderatorGroupsArray === null) {
            $moderatorGroups = explode(',', $this->moderatorGroups);
            if (empty($moderatorGroups)) {
                $this->moderatorGroupsArray = [];
            } else {
                $frontendUserGroupRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(FrontendUserGroupRepository::class);
                $this->moderatorGroupsArray = $frontendUserGroupRepository->findByUids($moderatorGroups)->toArray();
            }
        }
        return $this->moderatorGroupsArray;
    }

    public function getModeratorsArray()
    {
        if ($this->moderatorsArray === null) {
            $moderatorGroups = explode(',', $this->moderatorGroups);
            if (empty($moderatorGroups)) {
                $this->moderatorsArray = [];
                $this->moderatorGroupsArray = [];
            } else {
                $frontendUserRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(FrontendUserRepository::class);
                $this->moderatorsArray = $frontendUserRepository->findAllByUsergroups($moderatorGroups)->toArray();
            }
        }
        return $this->moderatorsArray;
    }

    /**
     * Adds a FrontendUser
     *
     * @param \LumIT\Typo3bb\Domain\Model\FrontendUser $subscriber
     * @return void
     */
    public function addSubscriber(FrontendUser $subscriber)
    {
        $this->subscribers->attach($subscriber);
    }

    /**
     * Removes a FrontendUser
     *
     * @param \LumIT\Typo3bb\Domain\Model\FrontendUser $subscriberToRemove The FrontendUser to be removed
     * @return void
     */
    public function removeSubscriber(FrontendUser $subscriberToRemove)
    {
        $this->subscribers->detach($subscriberToRemove);
    }

    /**
     * Returns the subscribers
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\FrontendUser> $subscribers
     */
    public function getSubscribers()
    {
        return $this->subscribers;
    }

    /**
     * Sets the subscribers
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\FrontendUser> $subscribers
     * @return void
     */
    public function setSubscribers(ObjectStorage $subscribers)
    {
        $this->subscribers = $subscribers;
    }

    /**
     * Whether the current user has subscribed the board
     * Alias for {@link isSubscribed()}
     *
     * @return bool
     */
    public function getSubscribed()
    {
        return $this->isSubscribed();
    }

    /**
     * Whether the current user has subscribed the board
     *
     * @return bool
     */
    public function isSubscribed()
    {
        $user = FrontendUserUtility::getCurrentUser();
        if (is_null($user)) {
            return false;
        } else {
            return $this->subscribers->contains($user);
        }
    }

    /**
     * Returns the parentBoard
     *
     * @return \LumIT\Typo3bb\Domain\Model\Board $parentBoard
     */
    public function getParentBoard()
    {
        return $this->parentBoard;
    }

    /**
     * Sets the parentBoard
     *
     * @param \LumIT\Typo3bb\Domain\Model\Board $parentBoard
     * @return void
     */
    public function setParentBoard($parentBoard)
    {
        $this->parentBoard = $parentBoard;
    }

    /**
     * Returns the forumCategory
     *
     * @return \LumIT\Typo3bb\Domain\Model\ForumCategory $forumCategory
     */
    public function getForumCategory()
    {
        return $this->forumCategory;
    }

    /**
     * Sets the forumCategory
     *
     * @param \LumIT\Typo3bb\Domain\Model\ForumCategory $forumCategory
     * @return void
     */
    public function setForumCategory($forumCategory)
    {
        $this->forumCategory = $forumCategory;
    }

    /**
     * @return bool
     */
    public function getRead()
    {
        return $this->isRead();
    }

    /**
     * @return bool
     */
    public function isRead()
    {
        $frontendUser = FrontendUserUtility::getCurrentUser();
        if (is_null($frontendUser)) {
            return true;
        }

        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var PostRepository $postRepository */
        $postRepository = $objectManager->get(PostRepository::class);
        if ($postRepository->countUnread($frontendUser, $this) > 0) {
            return false;
        }

        /** @var Board $board */
        foreach ($this->getSubBoards() as $board) {
            if (!$board->isRead()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function isTxKesearchIndex()
    {
        return $this->txKesearchIndex;
    }

    /**
     * @param bool $txKesearchIndex
     */
    public function setTxKesearchIndex($txKesearchIndex)
    {
        $this->txKesearchIndex = $txKesearchIndex;
    }



    /******************************************************************************************************************/



    /**
     * @return int
     */
    public function getPostsCount()
    {
        if ($this->postsCount === null) {
            $postsCount = $this->cacheInstance->getAttribute('postsCount');
            if ($postsCount !== null) {
                $this->postsCount = $postsCount;
            } else {
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_typo3bb_domain_model_post');
                $queryBuilder->count('post.uid')
                    ->from('tx_typo3bb_domain_model_post', 'post')
                    ->leftJoin(
                        'post',
                        'tx_typo3bb_domain_model_topic',
                        'topic',
                        $queryBuilder->expr()->eq('topic.uid', $queryBuilder->quoteIdentifier('post.topic'))
                    )->where($queryBuilder->expr()->eq('topic.board', $queryBuilder->createNamedParameter($this->getUid(), \PDO::PARAM_INT)));
                $postsCount = $queryBuilder->execute()->fetchColumn(0);

                $this->postsCount = is_numeric($postsCount) ? $postsCount : 0;
                $this->cacheInstance->setAttribute('postsCount', $this->postsCount);
            }
        }

        return $this->postsCount;
    }

    /**
     * @return \LumIT\Typo3bb\Domain\Model\Post
     */
    public function getLatestPost()
    {
        if ($this->latestPost === null) {
            $latestPost = $this->cacheInstance->getAttribute('latestPost');
            $postRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(PostRepository::class);
            if (!empty($latestPost)) {
                $this->latestPost = $postRepository->findByUid($latestPost);
            } else {
                $this->latestPost = $postRepository->findLatestInBoard($this);
                $this->cacheInstance->setAttribute('latestPost', $this->latestPost->getUid());
            }
        }

        return $this->latestPost;
    }

    public function getLatestPostCrdate()
    {
        if ($this->latestPostCrdate === null) {
            $latestPostCrdate = $this->cacheInstance->getAttribute('latestPostCrdate');
            if (!empty($latestPostCrdate)) {
                $this->latestPostCrdate = (new \DateTime())->setTimestamp($latestPostCrdate);
            } else {
                $latestPost = $this->getLatestPost();
                $this->latestPostCrdate = $latestPost ? $latestPost->getCrdate() : (new \DateTime())->setTimestamp(0);
                $this->cacheInstance->setAttribute('latestPostCrdate', $this->latestPostCrdate->getTimestamp());
            }
        }

        return $this->latestPostCrdate;
    }

    /**
     * @return \LumIT\Typo3bb\Domain\Model\Post|null
     */
    public function getViewableLatestPost()
    {
        if ($this->viewableLatestPost === null) {
            $viewableLatestPost = $this->cacheInstance->getUsergroupAttribute('viewableLatestPost');
            $postRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(PostRepository::class);
            if (!empty($viewableLatestPost)) {
                $this->viewableLatestPost = $postRepository->findByUid($viewableLatestPost);
            } else {
                $this->viewableLatestPost = $postRepository->findLatestRecursive($GLOBALS['TSFE']->gr_list, $this);
                $this->cacheInstance->setUsergroupAttribute('viewableLatestPost', ($this->viewableLatestPost ? $this->viewableLatestPost->getUid() : 0));
            }
        }

        return $this->viewableLatestPost;
    }

    /**
     * Returns the boards the current user is allowed to see
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function getAllowedSubBoards()
    {
        if ($this->allowedSubBoards === null) {
            $this->allowedSubBoards = [];
            $allowedSubBoards = $this->cacheInstance->getUsergroupAttribute('allowedSubBoards');
            $boardRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(BoardRepository::class);
            if ($allowedSubBoards !== null) {
                foreach ($allowedSubBoards as $allowedSubBoard) {
                    $this->allowedSubBoards[] = $boardRepository->findByUid($allowedSubBoard);
                }
            } else {
                $this->allowedSubBoards = $boardRepository->getAllowedBoards($this);
                $allowedSubBoardIds = [];
                /** @var \LumIT\Typo3bb\Domain\Model\Board $allowedSubBoard */
                foreach ($allowedSubBoards as $allowedSubBoard) {
                    $allowedSubBoardIds[] = $allowedSubBoard->getUid();
                }
                $this->cacheInstance->setUsergroupAttribute('allowedSubBoards', $allowedSubBoardIds);
            }
        }

        return $this->allowedSubBoards;
    }


    /******************************************************************************************************************/


    /**
     * @return array
     */
    public function getRootline()
    {
        if ($this->parentBoard !== null) {
            $rootline = $this->parentBoard->getRootline();
        } else {
            $rootline[] = $this->getForumCategory();
        }

        $rootline[] = $this;
        return $rootline;
    }

    /**
     * @param string $delimiter
     * @return string
     */
    public function getRootlineString($delimiter = ' > ')
    {
        return implode($delimiter, array_map(function ($board) {
            return $board->getTitle();
        }, $this->getRootline()));
    }


    /******************************************************************************************************************/



    public function flushCache()
    {
        parent::flushCache();
        if (!empty($this->parentBoard)) {
            $this->parentBoard->flushCache();
        }
        if (!empty($this->forumCategory)) {
            $this->forumCategory->flushCache();
        }
    }
}