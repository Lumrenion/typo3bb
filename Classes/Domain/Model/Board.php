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
use LumIT\Typo3bb\Domain\Repository\FrontendUserRepository;
use LumIT\Typo3bb\Domain\Repository\PostRepository;
use LumIT\Typo3bb\Utility\FrontendUserUtility;
use LumIT\Typo3bb\Utility\SecurityUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * A board can contain subboards and posts.
 * Boards without parent board need a forumCategory
 */
class Board extends AbstractEntity
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
     * @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    protected $allowedSubBoards = null;

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
    protected $moderators = '';

    /**
     * The users subscribed this topic
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\FrontendUser>
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
     * @lazy
     */
    protected $forumCategory = null;

    /**
     * Count of subBoards in board. Added for performance
     *
     * @var int
     */
    protected $subBoardsCount = 0;
    /**
     * Count of topics in board. Added for performance
     *
     * @var int
     */
    protected $topicsCount = 0;

    /**
     * Count of posts in all topics of board. Added for performance
     *
     * @var int
     */
    protected $postsCount = 0;

    /**
     * Pointer to the latest post. Added for perfornace
     *
     * @var \LumIT\Typo3bb\Domain\Model\Post
     * @lazy
     */
    protected $latestPost = null;

    /**
     * The Crdate of the latest post. Enables sorting of topics without database join
     *
     * @var \DateTime
     */
    protected $latestPostCrdate = null;


    /**
     * Not persisted attribute, so the viewable latest post needs only to get detected once per Board-Object
     *
     * @var int
     */
    protected $viewableTopicsCount = null;
    /**
     * Not persisted attribute, so the viewable latest post needs only to get detected once per Board-Object
     *
     * @var int
     */
    protected $viewablePostsCount = null;
    /**
     * Not persisted attribute, so the viewable latest post needs only to get detected once per Board-Object
     *
     * @var Post
     */
    protected $viewableLatestPost = null;

    /**
     * @var bool
     */
    protected $txKesearchIndex = true;

    /**
     * @var array
     */
    protected $moderatorsArray = null;


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
    public function getRedirectCount() {
        return $this->redirectCount;
    }

    /**
     * @param int $redirectCount
     */
    public function setRedirectCount($redirectCount) {
        $this->redirectCount = $redirectCount;
    }

    /**
     * Increase the redirect count by one
     */
    public function increaseRedirectCount() {
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

        $topic->setBoard($this);
        $this->_increaseTopicsCount();
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
        $this->_resetLatestPost();
        $this->_resetPostsCount();
        $this->_resetTopicCount();
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
     * Returns the boards the current user is allowed to see
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function getAllowedSubBoards() {
        if (is_null($this->allowedSubBoards)) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            /** @var BoardRepository $boardRepository */
            $boardRepository = $objectManager->get(BoardRepository::class);
            $this->allowedSubBoards = $boardRepository->getAllowedBoards($this);
        }
        return $this->allowedSubBoards;
    }

    /**
     * Returns an array containing subboards and their subboards recursively
     *
     * @return Board[]
     */
    public function getAllSubBoards() {
        $allSubBoards = [];
        /** @var Board $subBoard */
        foreach ($this->subBoards as $subBoard) {
            $allSubBoards = array_merge($allSubBoards, $subBoard->getAllSubBoards());
        }
        $allSubBoards = array_merge($allSubBoards, $this->getSubBoards()->toArray());

        return $allSubBoards;
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
     * Returns the moderators as comma separated list of UIDs
     *
     * @return string
     */
    public function getModerators() {
        return $this->moderators;
    }

    /**
     * Set a new comma separated list of UIDs for moderators
     *
     * @param string $moderators
     */
    public function setModerators(string $moderators) {
        $this->moderators = $moderators;
        $this->moderatorsArray = null;
    }

    public function getModeratorsArray() {
        if ($this->moderatorsArray === null) {
            $moderators = explode(',', $this->moderators);
            if (empty($moderators)) {
                $this->moderatorsArray = [];
            } else {
                /** @var FrontendUserRepository $frontendUserRepository */
                $frontendUserRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(FrontendUserRepository::class);
                $this->moderatorsArray = $frontendUserRepository->findByUids($moderators)->toArray();
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
     *
     * @return bool
     */
    public function isSubscribed() {
        $user = FrontendUserUtility::getCurrentUser();
        if (is_null($user)) {
            return false;
        } else {
            return $this->subscribers->contains($user);
        }
    }

    /**
     * Whether the current user has subscribed the board
     * Alias for {@link isSubscribed()}
     *
     * @return bool
     */
    public function getSubscribed() {
        return $this->isSubscribed();
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
     * @return int
     */
    public function getTopicsCount() {
        return $this->topicsCount;
    }

    /**
     * @param int $topicsCount
     */
    public function setTopicsCount($topicsCount) {
        $this->topicsCount = $topicsCount;
    }

    /**
     * @return int
     */
    public function getPostsCount() {
        return $this->postsCount;
    }

    /**
     * @param int $postsCount
     */
    public function setPostsCount($postsCount) {
        $this->postsCount = $postsCount;
    }

    public function _increasePostsCount($amount = 1) {
        $this->postsCount += $amount;
    }

    public function _increaseTopicsCount($amount = 1) {
        $this->topicsCount += $amount;
    }

    /**
     * @return mixed
     */
    public function getSubBoardsCount() {
        return $this->subBoardsCount;
    }

    /**
     * @param mixed $subBoardsCount
     */
    public function setSubBoardsCount($subBoardsCount) {
        $this->subBoardsCount = $subBoardsCount;
    }

    /**
     * @return \LumIT\Typo3bb\Domain\Model\Post
     */
    public function getLatestPost() {
        return $this->latestPost;
    }

    /**
     * Sets the latestPost
     *
     * @param \LumIT\Typo3bb\Domain\Model\Post $latestPost
     * @return void
     */
    public function setLatestPost($latestPost)
    {
        $this->latestPost = $latestPost;
        $this->latestPostCrdate = $latestPost->getCrdate();
    }

    /**
     * Returns the latestPostCrdate
     *
     * @return \DateTime $latestPostCrdate
     */
    public function getLatestPostCrdate()
    {
        return $this->latestPostCrdate;
    }

    /**
     * Sets the latestPostCrdate
     *
     * @param \DateTime $latestPostCrdate
     * @return void
     */
    public function setLatestPostCrdate($latestPostCrdate)
    {
        $this->latestPostCrdate = $latestPostCrdate;
    }

    /**
     * @access private
     */
    public function _resetLatestPost() {
        /** @var $latestPost Post */
        $latestPost = NULL;
        foreach ($this->topics as $topic) {
            /** @var $topic Topic */
            /** @noinspection PhpUndefinedMethodInspection */
            if ($topic->getLatestPost() instanceof Post) {
                $latestTopicPostTimestamp = $topic->getLatestPost()->getCrdate();
                if ($latestPost === NULL || $latestTopicPostTimestamp > $latestPost->getCrdate()) {
                    $latestPost = $topic->getLatestPost();
                }
            }
        }

        $this->latestPost = $latestPost;
    }

    /**
     * @access private
     */
    public function _resetPostsCount() {
        $this->postsCount = 0;
        foreach ($this->getTopics() as $topic) {
            $this->postsCount += $topic->getPostsCount();
        }
    }

    /**
     * @access private
     */
    public function _resetTopicCount() {
        $this->topicsCount= $this->topics->count();
    }

    /**
     * @return array
     */
    public function getRootline() {
        if($this->parentBoard !== null) {
            $rootline = $this->parentBoard->getRootline();
        } else {
            $rootline[] = $this->getForumCategory();
        }
        
        $rootline[] = $this;
        return $rootline;
    }

    /**
     * @return string
     */
    public function getRootlineString() {
        return implode(' > ', array_map(function ($board) {
            return $board->getTitle();
        }, $this->getRootline()));
    }

    /**
     * @return int
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function getViewablePostsCount() {
        if($this->viewablePostsCount == null) {
            $count = 0;
            if(SecurityUtility::checkAccessPermission('Board.show', $this)) {
                foreach ($this->getSubBoards() as $subBoard) {
                    $count += $subBoard->getViewablePostsCount();
                }
                $count += $this->getPostsCount();
            }
            $this->viewablePostsCount = $count;
        }
        return $this->viewablePostsCount;
    }

    /**
     * @return \LumIT\Typo3bb\Domain\Model\Post|null
     */
    public function getViewableTopicsCount() {
        if($this->viewableTopicsCount == null) {
            $count = 0;
            if(SecurityUtility::checkAccessPermission('Board.show', $this)) {
                foreach ($this->getSubBoards() as $subBoard) {
                    $count += $subBoard->getViewableTopicsCount();
                }
                $count += $this->getTopicsCount();
            }
            $this->viewableTopicsCount = $count;
        }
        return $this->viewableTopicsCount;
    }

    /**
     * @return \LumIT\Typo3bb\Domain\Model\Post|null
     */
    public function getViewableLatestPost() {
        if($this->viewableLatestPost == NULL) {
            $boardWithLatestPost = $this->_getBoardWithTrueLatestPost($this);
            if($boardWithLatestPost != null) {
                $this->viewableLatestPost = $boardWithLatestPost->getLatestPost();
            }
        }

        return $this->viewableLatestPost;
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Board $board
     * @return \LumIT\Typo3bb\Domain\Model\Board|null
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function _getBoardWithTrueLatestPost($board) {
        $boardWithTrueLatestPost = NULL;
        if(SecurityUtility::checkAccessPermission('Board.show', $board)) {
            $boardWithTrueLatestPost = $board;
            foreach ($board->getSubBoards() as $subBoard) {
                $boardWithLatestPost = $this->_getBoardWithTrueLatestPost($subBoard);
                if($boardWithLatestPost != NULL && $boardWithTrueLatestPost->getLatestPostCrdate() < $boardWithLatestPost->getLatestPostCrdate()) {
                    $boardWithTrueLatestPost = $boardWithLatestPost;
                }
            }
        }

        return $boardWithTrueLatestPost;
    }

    /**
     * @return bool
     */
    public function isRead() {
        $frontendUser = FrontendUserUtility::getCurrentUser();
        if (is_null($frontendUser))
            return true;

        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var PostRepository $postRepository */
        $postRepository = $objectManager->get(PostRepository::class);
        if ($postRepository->findUnread($frontendUser, $this)->count() > 0) {
            return false;
        }

        /** @var Board $board */
        foreach ($this->getSubBoards() as $board) {
            if (! $board->isRead()) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function getRead() {
        return $this->isRead();
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
}