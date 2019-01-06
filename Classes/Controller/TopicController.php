<?php

namespace LumIT\Typo3bb\Controller;


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

use LumIT\Typo3bb\Domain\Factory\PostFactory;
use LumIT\Typo3bb\Domain\Factory\TopicFactory;
use LumIT\Typo3bb\Domain\Model\Board;
use LumIT\Typo3bb\Domain\Model\Post;
use LumIT\Typo3bb\Domain\Model\Topic;
use LumIT\Typo3bb\Domain\Repository\BoardRepository;
use LumIT\Typo3bb\Domain\Repository\PollRepository;
use LumIT\Typo3bb\Domain\Repository\PostRepository;
use LumIT\Typo3bb\Domain\Repository\ReaderRepository;
use LumIT\Typo3bb\Domain\Repository\TopicRepository;
use LumIT\Typo3bb\Exception\AccessValidationException;
use LumIT\Typo3bb\Utility\CreationUtility;
use LumIT\Typo3bb\Utility\RteUtility;
use LumIT\Typo3bb\Utility\SecurityUtility;
use LumIT\Typo3bb\Utility\StatisticUtility;
use LumIT\Typo3bb\Utility\UrlUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * TopicController
 */
class TopicController extends AbstractController
{

    /**
     * topicRepository
     *
     * @var \LumIT\Typo3bb\Domain\Repository\TopicRepository
     */
    protected $topicRepository = null;

    /**
     * @var \LumIT\Typo3bb\Domain\Repository\PollRepository
     */
    protected $pollRepository = null;

    /**
     * @var \LumIT\Typo3bb\Domain\Repository\BoardRepository
     */
    protected $boardRepository = null;

    /**
     * @var \LumIT\Typo3bb\Domain\Repository\PostRepository
     */
    protected $postRepository = null;

    /**
     * @var \LumIT\Typo3bb\Domain\Repository\ReaderRepository
     */
    protected $readerRepository = null;

    public function __construct(TopicRepository $topicRepository, PollRepository $pollRepository, BoardRepository $boardRepository, PostRepository $postRepository, ReaderRepository $readerRepository)
    {
        parent::__construct();
        $this->topicRepository = $topicRepository;
        $this->pollRepository = $pollRepository;
        $this->boardRepository = $boardRepository;
        $this->postRepository = $postRepository;
        $this->readerRepository = $readerRepository;
    }

    /**
     * action show
     *
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     * @param \LumIT\Typo3bb\Domain\Model\Post $post
     * @return void
     */
    public function showAction(Topic $topic, Post $post = null)
    {
        SecurityUtility::assertAccessPermission('Topic.show', $topic);

        $topic->addView();
        $this->topicRepository->update($topic);

        $this->view->assign('topic', $topic);
        $this->view->assign('currentPost', $post);
        $poll = $topic->getPoll();
        if ($poll != null) {
            $this->view->assign('hidePollResult', !$poll->hasEnded() && $poll->isResultHidden());
            $this->view->assign('hidePollForm',
                $poll->hasEnded() || (!$poll->isChangeVoteAllowed() && $poll->hasFrontendUserVoted()));
        }

        //TODO cache
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     */
    public function showNewPostAction(Topic $topic)
    {
        /** @var Post $post */
        $post = null;
        if ($this->frontendUser !== null) {
            $post = $this->postRepository->findUnread($this->frontendUser, null, $topic);
        }

        if (is_null($post) || $post->getTopic()->getUid() != $topic->getUid()) {
            $post = $topic->getLatestPost();
        }

        $this->redirectToUri(UrlUtility::getPostUrl($this->uriBuilder, $post, $topic));
    }

    /**
     * action new
     *
     * @param \LumIT\Typo3bb\Domain\Model\Board $board
     * @return void
     */
    public function newAction(Board $board)
    {
        SecurityUtility::assertAccessPermission('Topic.create', $board);
        $this->view->assign('board', $board);
        $this->view->assign('frontendUser', $this->frontendUser);
    }

    public function initializeCreateAction()
    {
        $newTopic = $this->request->getArgument('topic');

        if ($this->request->hasArgument('poll')) {
            $poll = $this->request->getArgument('poll');
            CreationUtility::preparePollForValidation($this->arguments->getArgument('poll'), $poll);
            $this->request->setArgument('poll', $poll);
        }

        $firstPost = $this->request->getArgument('post');
        CreationUtility::preparePostForValidation($this->arguments->getArgument('post'), $firstPost);
        $firstPost['text'] = RteUtility::sanitizeHtml($firstPost['text']);
        $this->request->setArgument('post', $firstPost);

        $this->request->setArgument('topic', $newTopic);
    }

    /**
     * action create
     *
     * @param \LumIT\Typo3bb\Domain\Model\Board $board
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     * @param \LumIT\Typo3bb\Domain\Model\Post $post
     * @param \LumIT\Typo3bb\Domain\Model\Poll $poll
     * @param array $attachments
     * @return void
     */
    public function createAction(Board $board, Topic $topic, Post $post, $poll = null, $attachments = [])
    {
        SecurityUtility::assertAccessPermission('Topic.create', $board);
        if ($topic->isSticky()) {
            SecurityUtility::assertAccessPermission('Topic.pin', $board);
        }

        TopicFactory::createTopic($board, $topic, $post, $poll, $attachments);

        $this->topicRepository->add($topic);
        StatisticUtility::addTopic();
        $this->persistenceManager->persistAll();

        $topic->flushCache();

        $this->signalSlotDispatcher->dispatch(Topic::class, 'afterCreation',
            ['topic' => $topic, 'controllerContext' => $this->controllerContext]);
        $this->redirect('show', 'Topic', $this->extensionName, ['topic' => $topic]);
    }

    /**
     * action edit
     *
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     * @ignorevalidation $topic
     * @return void
     */
    public function editAction(Topic $topic)
    {
        SecurityUtility::assertAccessPermission('Topic.edit', $topic);
        $firstPost = $topic->getPosts()->toArray()[0];
        $this->view->assignMultiple(['topic' => $topic, 'post' => $firstPost, 'board' => $topic->getBoard(), 'poll' => $topic->getPoll()]);
    }

    public function initializeUpdateAction()
    {
        $topic = $this->request->getArgument('topic');

        CreationUtility::prepareTopicForValidation($this->arguments->getArgument('topic'), $topic);

        if ($this->request->hasArgument('poll')) {
            $poll = $this->request->getArgument('poll');
            CreationUtility::preparePollForValidation($this->arguments->getArgument('poll'), $poll);
            $this->request->setArgument('poll', $poll);
        } else {
            CreationUtility::preparePollForValidation($this->arguments->getArgument('poll'), $topic['poll']);
        }

        $firstPost = $this->request->getArgument('post');
        $firstPost['text'] = RteUtility::sanitizeHtml($firstPost['text']);
        $this->request->setArgument('post', $firstPost);

        $this->request->setArgument('topic', $topic);
    }

    /**
     * action update
     *
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     * @param \LumIT\Typo3bb\Domain\Model\Post $post
     * @param \LumIT\Typo3bb\Domain\Model\Poll $poll
     * @param array $attachments
     * @return void
     */
    public function updateAction(Topic $topic, $post, $poll = null, $attachments = [])
    {
        SecurityUtility::assertAccessPermission('Topic.edit', $topic);
        if ($topic->_isDirty('sticky')) {
            SecurityUtility::assertAccessPermission('Topic.pin', $topic);
        }

        if ($post->_isDirty()) {
            $post->setEditor($this->frontendUser);
            $this->postRepository->update($post);
        }
        if (!empty($attachments)) {
            PostFactory::processAttachments($post, $attachments);
        }

        if (!empty($poll)) {
            $topic->setPoll($poll);
        }

        $this->topicRepository->update($topic);

        $this->redirect('show', null, null, ['topic' => $topic]);
    }

    /**
     * action delete
     *
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     * @return void
     */
    public function deleteAction(Topic $topic)
    {
        SecurityUtility::assertAccessPermission('Topic.delete', $topic);
        $this->topicRepository->remove($topic);
        $topic->flushCache();
        $this->redirect('show', 'Board', $this->extensionName, ['board' => $topic->getBoard()]);
    }

    /**
     * Action pins or unpins given topic
     *
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     */
    public function pinAction(Topic $topic)
    {
        SecurityUtility::assertAccessPermission('Topic.pin', $topic);
        $topic->setSticky(!$topic->isSticky());

        $this->topicRepository->update($topic);
        $this->redirect('show', 'Topic', null, ['topic' => $topic]);
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     */
    public function closeAction(Topic $topic)
    {
        if ($topic->isClosed()) {
            SecurityUtility::assertAccessPermission('Topic.reopen', $topic);
            $topic->setClosed(false);
        } else {
            SecurityUtility::assertAccessPermission('Topic.close', $topic);
            $topic->setClosed(true);
        }

        $this->topicRepository->update($topic);
        $this->redirect('show', 'Topic', null, ['topic' => $topic]);
    }

    /**
     * Renders the move view
     *
     * @param Topic $topic
     */
    public function moveAction(Topic $topic)
    {
        SecurityUtility::assertAccessPermission('Topic.move', $topic);
        $boards = $boards = $this->boardRepository->getAllowedBoards();
        $this->view->assignMultiple(['boards' => $boards, 'topic' => $topic]);
    }

    /**
     * Moves a topic to the destination board
     *
     * @param Topic $topic
     * @param Board $destinationBoard
     */
    public function executeMoveAction(Topic $topic, Board $destinationBoard)
    {
        SecurityUtility::assertAccessPermission('Topic.move', $topic);
        SecurityUtility::assertAccessPermission('Topic.move', $destinationBoard);

        $oldBoard = $topic->getBoard();
        $topic->setBoard($destinationBoard);
        $this->topicRepository->update($topic);

        $oldBoard->flushCache();
        $destinationBoard->flushCache();

        $this->redirect('show', 'Topic', null, ['topic' => $topic]);
    }

    /**
     * @param Post $post
     */
    public function splitAction(Post $post)
    {
        SecurityUtility::assertAccessPermission('Topic.split', $post->getTopic());
        $this->view->assignMultiple([
            'post' => $post,
            'followingPosts' => $this->postRepository->findFollowing($post),
            'user' => $this->frontendUser
        ]);
    }

    public function initializeExecuteSplitAction()
    {
        if ($this->request->hasArgument('posts')) {
            $posts = $this->request->getArgument('posts');
        } else {
            $posts = [];
        }
        if ($this->request->hasArgument('post')) {
            $posts[] = $this->request->getArgument('post');
        }
        $this->arguments->getArgument('posts')->setValue($posts);
        $this->request->setArgument('posts', $posts);

        $newTopic = $this->request->getArgument('newTopic');
        CreationUtility::prepareTopicForValidation($this->arguments->getArgument('newTopic'), $newTopic);
        $this->request->setArgument('newTopic', $newTopic);
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Topic $oldTopic
     * @param \LumIT\Typo3bb\Domain\Model\Topic $newTopic
     * @param array $posts
     * @throws \LumIT\Typo3bb\Exception\AccessValidationException
     */
    public function executeSplitAction(Topic $oldTopic, Topic $newTopic, array $posts)
    {
        SecurityUtility::assertAccessPermission('Topic.split', $oldTopic);
        asort($posts);
        /** @var Post $firstPost */
        $firstPost = $this->postRepository->findByUid(array_shift($posts));
        if (!$firstPost->getTopic() == $oldTopic) {
            throw new AccessValidationException(LocalizationUtility::translate('exception.accessValidation',
                'typo3bb'));
        }

        TopicFactory::createTopic($oldTopic->getBoard(), $newTopic, $firstPost);
        $this->topicRepository->add($newTopic);
        $this->postRepository->update($firstPost);
        foreach ($posts as $post) {
            /** @var Post $postObject */
            $postObject = $this->postRepository->findByUid($post);
            if ($postObject->getTopic()->getUid() == $oldTopic->getUid()) {
                $postObject->setTopic($newTopic);
                $this->postRepository->update($postObject);
            }
        }

        // for simplicity, post is persisted and the latestPostCrdate for both topics is determined afterwards
        $this->persistenceManager->persistAll();
        $allPosts = array_reverse($oldTopic->getPosts()->toArray());
        if (isset($allPosts[0])) {
            $oldTopic->setLatestPostCrdate($allPosts[0]->getCrdate());
        }
        $allPosts = array_reverse($newTopic->getPosts()->toArray());
        if (isset($allPosts[0])) {
            $newTopic->setLatestPostCrdate($allPosts[0]->getCrdate());
        }
        $this->topicRepository->update($oldTopic);
        $this->topicRepository->update($newTopic);

        $oldTopic->flushCache();
        $newTopic->flushCache();
        $this->redirect('show', 'Board', $this->extensionName, ['board' => $newTopic->getBoard()]);
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic2
     */
    public function joinAction(Topic $topic, Topic $topic2 = null)
    {
        SecurityUtility::assertAccessPermission('Topic.join', $topic);
        $this->view->assign('topic', $topic);
        if (is_null($topic2)) {
            $boards = $this->boardRepository->getAllowedBoards();

            $this->view->assign('boards', $boards);
        } else {
            SecurityUtility::assertAccessPermission('Topic.join', $topic2);
            $this->view->assignMultiple([
                'topic2' => $topic2,
                'options' => [
                    $topic->getTitle(),
                    $topic2->getTitle(),
                    LocalizationUtility::translate('topic.join.step2.type.newTitle', $this->extensionName)
                ]
            ]);
        }
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic1
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic2
     * @param integer $type
     * @param string $newTitle
     */
    public function executeJoinAction(Topic $topic1, Topic $topic2, int $type = null, string $newTitle = '')
    {
        SecurityUtility::assertAccessPermission('Topic.join', $topic1);
        SecurityUtility::assertAccessPermission('Topic.join', $topic2);
        switch ($type) {
            case 1:
                $topic1->setTitle($topic2->getTitle());
                break;
            case 2:
                $topic1->setTitle($newTitle);
                break;
        }
        /** @var Post $post */
        $posts = $topic2->getPosts()->toArray();
        foreach ($posts as $post) {
            $post->setTopic($topic1);
            $this->postRepository->update($post);
        }
        $this->topicRepository->add($topic1);
        $this->topicRepository->remove($topic2);

        // for simplicity, post is persisted and the latestPostCrdate for both topics is determined afterwards
        $this->persistenceManager->persistAll();
        $allPosts = array_reverse($topic1->getPosts()->toArray());
        if (isset($allPosts[0])) {
            $topic1->setLatestPostCrdate($allPosts[0]->getCrdate());
        }
        $this->topicRepository->update($topic1);

        $topic1->flushCache();
        $topic2->flushCache();

        $this->redirect('show', 'Board', $this->extensionName, ['board' => $topic1->getBoard()]);
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     * @param bool $unsubscribe
     */
    public function subscribeAction(Topic $topic, bool $unsubscribe = false)
    {
        SecurityUtility::assertAccessPermission('Topic.subscribe', $topic);

        if ($unsubscribe) {
            $topic->removeSubscriber($this->frontendUser);
        } else {
            $topic->addSubscriber($this->frontendUser);
        }
        $this->topicRepository->update($topic);
        //TODO cache
        $this->redirect('show', null, null, ['topic' => $topic]);
    }
}