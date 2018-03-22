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

use LumIT\Typo3bb\Domain\Repository\PostRepository;
use LumIT\Typo3bb\Utility\FrontendUserUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * A topic contains posts and always has a parent board.
 * Topics are user created.
 * It might be a poll.
 */
class Topic extends AbstractEntity
{

    /**
     * title
     *
     * @var string
     * @validate NotEmpty
     */
    protected $title = '';

    /**
     * If the topic is pinned
     *
     * @var bool
     */
    protected $sticky = false;

    /**
     * If new posts can be added to the topic
     *
     * @var bool
     */
    protected $closed = false;

    /**
     * crdate
     *
     * @var \DateTime
     */
    protected $crdate = null;

    /**
     * Number of posts, added for performance reasons
     *
     * @var int
     */
    protected $postsCount = 0;

    /**
     * posts
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Post>
     * @lazy
     */
    protected $posts = null;

    /**
     * poll
     *
     * @var \LumIT\Typo3bb\Domain\Model\Poll
     * @lazy
     */
    protected $poll = null;

    /**
     * The creator of the topic
     *
     * @var \LumIT\Typo3bb\Domain\Model\FrontendUser
     * @lazy
     */
    protected $author = null;

    /**
     * Author name needed for topics created by guests and deleted users
     *
     * @var string
     */
    protected $authorName = '';

    /**
     * The users subscribed this topic
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\FrontendUser>
     * @lazy
     */
    protected $subscribers = null;

    /**
     * The readers of this topic
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Reader>
     * @lazy
     * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
     */
    protected $readers = null;

    /**
     * Pointer to the latest post of the topic.
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
     * The Board the Topic is in
     * @var \LumIT\Typo3bb\Domain\Model\Board
     * @lazy
     */
    protected $board = null;

    /**
     * @var int
     */
    protected $views = 0;

    /**
     * __construct
     */
    public function __construct()
    {
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
        $this->crdate = new \DateTime();
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
        $this->posts = new ObjectStorage();
        $this->subscribers = new ObjectStorage();
        $this->readers = new ObjectStorage();
    }

    /**
     * Returns the title
     *
     * @return string $title
     * @validate NotEmpty
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
     * Returns the sticky
     *
     * @return bool $sticky
     */
    public function getSticky()
    {
        return $this->sticky;
    }

    /**
     * Returns the boolean state of sticky
     *
     * @return bool
     */
    public function isSticky()
    {
        return $this->sticky;
    }

    /**
     * Sets the sticky
     *
     * @param bool $sticky
     * @return void
     */
    public function setSticky($sticky)
    {
        $this->sticky = $sticky;
    }

    /**
     * Returns the closed
     *
     * @return bool $closed
     */
    public function getClosed()
    {
        return $this->closed;
    }

    /**
     * Returns the boolean state of closed
     *
     * @return bool
     */
    public function isClosed()
    {
        return $this->closed;
    }

    /**
     * Sets the closed
     *
     * @param bool $closed
     * @return void
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;
    }

    /**
     * Returns the crdate
     *
     * @return \DateTime $crdate
     */
    public function getCrdate()
    {
        return $this->crdate;
    }

    /**
     * Sets the crdate
     *
     * @param \DateTime $crdate
     * @return void
     */
    public function setCrdate(\DateTime $crdate)
    {
        $this->crdate = $crdate;
    }

    /**
     * Returns the postsCount
     *
     * @return int $postsCount
     */
    public function getPostsCount()
    {
        return $this->postsCount;
    }

    /**
     * Sets the postsCount
     *
     * @param int $postsCount
     * @return void
     */
    public function setPostsCount($postsCount)
    {
        $this->postsCount = $postsCount;
    }


    /**
     * Adds a Post
     *
     * @param \LumIT\Typo3bb\Domain\Model\Post $post
     * @return void
     */
    public function addPost(Post $post)
    {
        $this->posts->attach($post);

        $post->setTopic($this);
        $this->postsCount++;
        // if there are no posts in this topic yet, set the needed data
        if ($this->posts->count() == 0) {
            $this->setCrdate($post->getCrdate());
            $this->setAuthor($post->getAuthor());
            $this->setAuthorName($post->getTrueAuthorName());
        }
        // if the added post is newer than the latestPost, change latestPost
        if ($this->latestPost === null || $this->latestPost->getCrdate() < $post->getCrdate()) {
            $this->setLatestPost($post);
        }

        $this->board->_increasePostsCount();
        // if the added post is newer than the boards latestPost, change latestPost
        if ($this->board->getLatestPost() === null || $this->board->getLatestPost()->getCrdate() < $post->getCrdate()) {
            $this->board->setLatestPost($post);
        }
    }

    /**
     * Removes a Post
     *
     * @param \LumIT\Typo3bb\Domain\Model\Post $postToRemove The Post to be removed
     * @return void
     */
    public function removePost(Post $postToRemove)
    {
        $this->posts->detach($postToRemove);

        $this->postsCount--;
        if ($this->latestPost->getUid() == $postToRemove->getUid()) {
            $postsArray = $this->posts->toArray();
            if (count($postsArray) > 0) {
                $this->setLatestPost(array_pop($postsArray));
            }
        }

        $this->board->_increasePostsCount(-1);
        if ($this->board->getLatestPost() === $postToRemove) {
            $this->board->_resetLatestPost();
        }
    }

    /**
     * Returns the posts
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Post> $posts
     * @validate NotEmpty
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * Sets the posts
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Post> $posts
     * @return void
     */
    public function setPosts(ObjectStorage $posts)
    {
        $this->posts = $posts;
    }

    /**
     * Returns the poll
     *
     * @return \LumIT\Typo3bb\Domain\Model\Poll $poll
     */
    public function getPoll()
    {
        return $this->poll;
    }

    /**
     * Sets the poll
     *
     * @param \LumIT\Typo3bb\Domain\Model\Poll $poll
     * @return void
     */
    public function setPoll($poll)
    {
        $this->poll = $poll;
    }

    /**
     * @return string
     */
    public function getAuthorName()
    {
        if (!is_null($this->getAuthor())) {
            return $this->getAuthor()->getDisplayName();
        }
        return $this->authorName;
    }

    /**
     * @param string $authorName
     */
    public function setAuthorName($authorName)
    {
        $this->authorName = $authorName;
    }

    /**
     * Returns the author
     *
     * @return \LumIT\Typo3bb\Domain\Model\FrontendUser $author
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Sets the author
     *
     * @param \LumIT\Typo3bb\Domain\Model\FrontendUser $author
     * @return void
     */
    public function setAuthor($author)
    {
        $this->author = $author;
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
        // TODO there seems to be an inconsistency when getting database records via Repository or via ObjectStorage
        // TODO when getting via Repository, a Typo3bb-FrontendUser is returned
        // TODO when getting via ObjectStorage, IglarpTemplate-FrontendUser is returned
        $user = FrontendUserUtility::getCurrentUser();
        if (is_null($user)) {
            return false;
        } else {
            return $this->subscribers->contains($user);
        }
    }

    /**
     * Adds a FrontendUser
     *
     * @param \LumIT\Typo3bb\Domain\Model\Reader $reader
     * @return void
     */
    public function addReader($reader)
    {
        $this->readers->attach($reader);
    }

    /**
     * Removes a FrontendUser
     *
     * @param \LumIT\Typo3bb\Domain\Model\Reader $reader
     * @return void
     */
    public function removeReader($reader)
    {
        $this->readers->detach($reader);
    }

    /**
     * Returns the readers
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage $readers
     */
    public function getReaders()
    {
        return $this->readers;
    }

    /**
     * Sets the readers
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Reader> $readers
     * @return void
     */
    public function setReaders($readers)
    {
        $this->readers = $readers;
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
        $unreadCount = $postRepository->findUnread($frontendUser, null, $this)->count();
        return $unreadCount == 0;
    }

    /**
     * Returns the latestPost
     *
     * @return \LumIT\Typo3bb\Domain\Model\Post $latestPost
     */
    public function getLatestPost()
    {
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
     * @return \LumIT\Typo3bb\Domain\Model\Board
     */
    public function getBoard()
    {
        return $this->board;
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Board $board
     */
    public function setBoard($board)
    {
        $this->board = $board;
    }


    /**
     * @return array
     */
    public function getRootline()
    {
        $rootline = $this->board->getRootline();
        $rootline[] = $this;
        return $rootline;
    }

    /**
     * @return int
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * @param int $views
     */
    public function setViews($views)
    {
        $this->views = $views;
    }

    public function addView()
    {
        $this->views++;
    }
}