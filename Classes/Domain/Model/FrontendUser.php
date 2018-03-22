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
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * FrontendUser
 */
class FrontendUser extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser
{

    /**
     * @var string
     */
    protected $displayName = '';
    /**
     * Creation date
     *
     * @var \DateTime
     */
    protected $crdate = null;

    /**
     * Personal text of the frontend user appended to each post of the user.
     *
     * @var string
     */
    protected $signature = '';

    /**
     * The topics created by this frontend user
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Topic>
     * @lazy
     */
    protected $createdTopics = null;

    /**
     * The topics subscribed by this user
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Topic>
     * @lazy
     */
    protected $subscribedTopics = null;

    /**
     * The boards subscribed by this user
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Board>
     * @lazy
     */
    protected $subscribedBoards = null;

    /**
     * The posts created by this user
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Post>
     * @lazy
     */
    protected $createdPosts = null;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Post>
     * @lazy
     */
    protected $editedPosts = null;

    /**
     * Count of posts, added for performance
     *
     * @var int
     */
    protected $postsCount = 0;

    /**
     * The poll choices the frontend user selected
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\PollChoice>
     * @lazy
     */
    protected $selectedPollChoices = null;

    /**
     * The polls, the user has selected votes for
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Poll>
     * @lazy
     */
    protected $votedPolls = null;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\MessageParticipant>
     * @lazy
     */
    protected $sentMessages = null;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\MessageParticipant>
     * @lazy
     */
    protected $receivedMessages = null;

    /**
     * @var bool
     */
    protected $hideSensitiveData = true;

    /**
     * @var bool
     */
    protected $showOnline = true;

    /**
     * @var bool
     */
    protected $messageNotification = true;

    /**
     * @var int
     */
    protected $loginTime = 0;

    /**
     * @var \DateTime
     */
    protected $onlineTime = null;

    /**
     * @var \LumIT\Typo3bb\Domain\Model\Post
     */
    protected $lastReadPost = null;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Reader>
     * @lazy
     */
    protected $readTopics = null;

    /**
     * __construct
     * @param string $username
     * @param string $password
     */
    public function __construct($username = '', $password = '')
    {
        parent::__construct($username, $password);
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
        $this->createdTopics = new ObjectStorage();
        $this->subscribedBoards = new ObjectStorage();
        $this->subscribedTopics = new ObjectStorage();
        $this->createdPosts = new ObjectStorage();
        $this->selectedPollChoices = new ObjectStorage();
        $this->votedPolls = new ObjectStorage();
        $this->sentMessages = new ObjectStorage();
        $this->receivedMessages = new ObjectStorage();
        $this->editedPosts = new ObjectStorage();
        $this->readTopics = new ObjectStorage();
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return empty($this->displayName) ? $this->username : $this->displayName;
    }

    /**
     * @param string $displayName
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
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
    public function setCrdate($crdate)
    {
        $this->crdate = $crdate;
    }

    /**
     * Returns the signature
     *
     * @return string $signature
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Sets the signature
     *
     * @param string $signature
     * @return void
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;
    }

    /**
     * Adds a Topic
     *
     * @param \LumIT\Typo3bb\Domain\Model\Topic $createdTopic
     * @return void
     */
    public function addCreatedTopic(Topic $createdTopic)
    {
        $this->createdTopics->attach($createdTopic);
    }

    /**
     * Removes a Topic
     *
     * @param \LumIT\Typo3bb\Domain\Model\Topic $createdTopicToRemove The Topic to be removed
     * @return void
     */
    public function removeCreatedTopic(Topic $createdTopicToRemove)
    {
        $this->createdTopics->detach($createdTopicToRemove);
    }

    /**
     * Returns the createdTopics
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Topic> $createdTopics
     */
    public function getCreatedTopics()
    {
        return $this->createdTopics;
    }

    /**
     * Sets the createdTopics
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Topic> $createdTopics
     * @return void
     */
    public function setCreatedTopics(ObjectStorage $createdTopics)
    {
        $this->createdTopics = $createdTopics;
    }

    /**
     * Returns the postsCount
     *
     * @return int
     */
    public function getPostsCount()
    {
        return $this->postsCount;
    }

    /**
     * Sets the postsCount
     *
     * @param $postsCount
     */
    public function setPostsCount($postsCount)
    {
        $this->postsCount = $postsCount;
    }

    /**
     * Adds a Topic
     *
     * @param \LumIT\Typo3bb\Domain\Model\Topic $subscribedTopic
     * @return void
     */
    public function addSubscribedTopic(Topic $subscribedTopic)
    {
        $this->subscribedTopics->attach($subscribedTopic);
    }

    /**
     * Removes a Topic
     *
     * @param \LumIT\Typo3bb\Domain\Model\Topic $subscribedTopicToRemove The Topic to be removed
     * @return void
     */
    public function removeSubscribedTopic(Topic $subscribedTopicToRemove)
    {
        $this->subscribedTopics->detach($subscribedTopicToRemove);
    }

    /**
     * Returns the subscribedTopics
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Topic> $subscribedTopics
     */
    public function getSubscribedTopics()
    {
        return $this->subscribedTopics;
    }

    /**
     * Sets the subscribedTopics
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Topic> $subscribedTopics
     * @return void
     */
    public function setSubscribedTopics(ObjectStorage $subscribedTopics)
    {
        $this->subscribedTopics = $subscribedTopics;
    }

    /**
     * Adds a Board
     *
     * @param \LumIT\Typo3bb\Domain\Model\Board $subscribedBoard
     * @return void
     */
    public function addSubscribedBoard(Board $subscribedBoard)
    {
        $this->subscribedBoards->attach($subscribedBoard);
    }

    /**
     * Removes a Board
     *
     * @param \LumIT\Typo3bb\Domain\Model\Board $subscribedBoardToRemove The Board to be removed
     * @return void
     */
    public function removeSubscribedBoard(Board $subscribedBoardToRemove)
    {
        $this->subscribedBoards->detach($subscribedBoardToRemove);
    }

    /**
     * Returns the subscribedBoards
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Board> $subscribedBoards
     */
    public function getSubscribedBoards()
    {
        return $this->subscribedBoards;
    }

    /**
     * Sets the subscribedBoards
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Board> $subscribedBoards
     * @return void
     */
    public function setSubscribedBoards(ObjectStorage $subscribedBoards)
    {
        $this->subscribedBoards = $subscribedBoards;
    }

    /**
     * Adds a Post
     *
     * @param \LumIT\Typo3bb\Domain\Model\Post $createdPost
     * @return void
     */
    public function addCreatedPost(Post $createdPost)
    {
        $this->createdPosts->attach($createdPost);
        $this->_increasePostsCount();
    }

    /**
     * @param int $amount
     */
    public function _increasePostsCount($amount = 1)
    {
        $this->postsCount += $amount;
    }

    /**
     * Removes a Post
     *
     * @param \LumIT\Typo3bb\Domain\Model\Post $createdPostToRemove The Post to be removed
     * @return void
     */
    public function removeCreatedPost(Post $createdPostToRemove)
    {
        $this->createdPosts->detach($createdPostToRemove);
        $this->_increasePostsCount(-1);
    }

    /**
     * Returns the createdPosts
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Post> $createdPosts
     */
    public function getCreatedPosts()
    {
        return $this->createdPosts;
    }

    /**
     * Sets the createdPosts
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Post> $createdPosts
     * @return void
     */
    public function setCreatedPosts(ObjectStorage $createdPosts)
    {
        $this->createdPosts = $createdPosts;
    }

    /**
     * Adds a Post
     *
     * @param \LumIT\Typo3bb\Domain\Model\Post $editedPost
     * @return void
     */
    public function addEditedPost(Post $editedPost)
    {
        $this->editedPosts->attach($editedPost);
    }

    /**
     * Removes a Post
     *
     * @param \LumIT\Typo3bb\Domain\Model\Post $editedPostToRemove The Post to be removed
     * @return void
     */
    public function removeEditedPost(Post $editedPostToRemove)
    {
        $this->editedPosts->detach($editedPostToRemove);
    }

    /**
     * Returns the editedPosts
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Post> $editedPosts
     */
    public function getEditedPosts()
    {
        return $this->editedPosts;
    }

    /**
     * Sets the editedPosts
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Post> $editedPosts
     * @return void
     */
    public function setEditedPosts(ObjectStorage $editedPosts)
    {
        $this->editedPosts = $editedPosts;
    }

    /**
     * Adds a PollChoice
     *
     * @param \LumIT\Typo3bb\Domain\Model\PollChoice $selectedPollChoice
     * @return void
     */
    public function addSelectedPollChoice(PollChoice $selectedPollChoice)
    {
        $this->selectedPollChoices->attach($selectedPollChoice);
    }

    /**
     * Removes a PollChoice
     *
     * @param \LumIT\Typo3bb\Domain\Model\PollChoice $selectedPollChoiceToRemove The PollChoice to be removed
     * @return void
     */
    public function removeSelectedPollChoice(PollChoice $selectedPollChoiceToRemove)
    {
        $this->selectedPollChoices->detach($selectedPollChoiceToRemove);
    }

    /**
     * Returns the selectedPollChoices
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\PollChoice> $selectedPollChoices
     */
    public function getSelectedPollChoices()
    {
        return $this->selectedPollChoices;
    }

    /**
     * Sets the selectedPollChoices
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\PollChoice> $selectedPollChoices
     * @return void
     */
    public function setSelectedPollChoices(ObjectStorage $selectedPollChoices)
    {
        $this->selectedPollChoices = $selectedPollChoices;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Poll>
     */
    public function getVotedPolls()
    {
        return $this->votedPolls;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Poll> $votedPolls
     */
    public function setVotedPolls(ObjectStorage $votedPolls)
    {
        $this->votedPolls = $votedPolls;
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Poll $poll
     */
    public function addVotedPoll(Poll $poll)
    {
        $this->votedPolls->attach($poll);
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Poll $poll
     */
    public function removeVotedPoll(Poll $poll)
    {
        $this->votedPolls->detach($poll);
    }

    /**
     * @return ObjectStorage
     */
    public function getSentMessages()
    {
        return $this->sentMessages;
    }

    /**
     * @param ObjectStorage $sentMessages
     */
    public function setSentMessages(ObjectStorage $sentMessages)
    {
        $this->sentMessages = $sentMessages;
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\MessageParticipant $message
     */
    public function addSentMessage(MessageParticipant $message)
    {
        $this->sentMessages->attach($message);
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\MessageParticipant $message
     */
    public function removeSentMessage(MessageParticipant $message)
    {
        $this->sentMessages->detach($message);
    }

    /**
     * @return ObjectStorage
     */
    public function getReceivedMessages()
    {
        return $this->receivedMessages;
    }

    /**
     * @param ObjectStorage $receivedMessages
     */
    public function setReceivedMessages(ObjectStorage $receivedMessages)
    {
        $this->receivedMessages = $receivedMessages;
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\MessageParticipant $message
     */
    public function addReceivedMessage(MessageParticipant $message)
    {
        $this->receivedMessages->attach($message);
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\MessageParticipant $message
     */
    public function removeReceivedMessage(MessageParticipant $message)
    {
        $this->receivedMessages->detach($message);
    }

    /**
     * @return boolean
     */
    public function isHideSensitiveData()
    {
        return $this->hideSensitiveData;
    }

    /**
     * @param boolean $hideSensitiveData
     */
    public function setHideSensitiveData(bool $hideSensitiveData)
    {
        $this->hideSensitiveData = $hideSensitiveData;
    }

    /**
     * @return boolean
     */
    public function isShowOnline()
    {
        return $this->showOnline;
    }

    /**
     * @param boolean $showOnline
     */
    public function setShowOnline(bool $showOnline)
    {
        $this->showOnline = $showOnline;
    }

    /**
     * @return bool
     */
    public function getMessageNotification()
    {
        return $this->isMessageNotification();
    }

    /**
     * @return boolean
     */
    public function isMessageNotification()
    {
        return $this->messageNotification;
    }

    /**
     * @param boolean $messageNotification
     */
    public function setMessageNotification(bool $messageNotification)
    {
        $this->messageNotification = $messageNotification;
    }

    /**
     * @return int
     */
    public function getLoginTime()
    {
        return $this->loginTime;
    }

    /**
     * @param int $loginTime
     */
    public function setLoginTime(int $loginTime)
    {
        $this->loginTime = $loginTime;
    }

    /**
     * Returns the number of posts created per day since registration
     *
     * @return float
     */
    public function getPostsCountPerDay()
    {
        $now = time();
        $secondsRegistered = $now - $this->crdate->getTimestamp();
        $daysRegistered = $secondsRegistered / (60 * 60 * 24);
        return $this->postsCount / $daysRegistered;
    }

    /**
     * @return \LumIT\Typo3bb\Domain\Model\Post
     */
    public function getLastReadPost()
    {
        return $this->lastReadPost;
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Post $lastReadPost
     */
    public function setLastReadPost($lastReadPost)
    {
        $this->lastReadPost = $lastReadPost;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Reader>
     */
    public function getReadTopics()
    {
        return $this->readTopics;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Reader> $readTopics
     */
    public function setReadTopics($readTopics)
    {
        $this->readTopics = $readTopics;
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Reader $reader
     */
    public function addReadTopic($reader)
    {
        $this->readTopics->attach($reader);
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Reader $reader
     */
    public function removeReadTopic($reader)
    {
        $this->readTopics->detach($reader);
    }

    /**
     * @return \DateTime
     */
    public function getOnlineTime()
    {
        return $this->onlineTime;
    }

    /**
     * @param \DateTime $onlineTime
     */
    public function setOnlineTime(\DateTime $onlineTime)
    {
        $this->onlineTime = $onlineTime;
    }
}