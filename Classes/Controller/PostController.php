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
use LumIT\Typo3bb\Domain\Model\Post;
use LumIT\Typo3bb\Domain\Model\Topic;
use LumIT\Typo3bb\Exception\AccessValidationException;
use LumIT\Typo3bb\Exception\ActionNotAllowedException;
use LumIT\Typo3bb\Utility\CreationUtility;
use LumIT\Typo3bb\Utility\RteUtility;
use LumIT\Typo3bb\Utility\SecurityUtility;
use LumIT\Typo3bb\Utility\StatisticUtility;
use LumIT\Typo3bb\Utility\UrlUtility;
use LumIT\Typo3bb\ViewHelpers\Format\CsvViewHelper;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * PostController
 */
class PostController extends AbstractController
{

    /**
     * postRepository
     *
     * @var \LumIT\Typo3bb\Domain\Repository\PostRepository
     * @inject
     */
    protected $postRepository = null;

    /**
     * @var \LumIT\Typo3bb\Domain\Repository\TopicRepository
     * @inject
     */
    protected $topicRepository = null;

    /**
     * action new
     *
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     * @param \LumIT\Typo3bb\Domain\Model\Post $quotedPost
     * @return void
     */
    public function newAction(Topic $topic, Post $quotedPost = null)
    {
        SecurityUtility::assertAccessPermission('Post.create', $topic);
        if ($quotedPost != null) {
            $url = UrlUtility::getPostUrl($this->uriBuilder, $quotedPost);
            $quotedText = RteUtility::getQuote($quotedPost->getText(), $quotedPost->getAuthorName(),
                $quotedPost->getCrdate(), $url);
            $this->view->assign('quotedText', $quotedText);
        }
        $this->view->assignMultiple([
            'topic' => $topic,
            'previousPosts' => $this->postRepository->findPrevious($topic),
            'frontendUser' => $this->frontendUser
        ]);
    }

    public function initializeCreateAction()
    {
        $newPost = $this->request->getArgument('newPost');

        CreationUtility::preparePostForValidation($this->arguments->getArgument('newPost'), $newPost);

        $this->request->setArgument('newPost', $newPost);
    }

    /**
     * action create
     *
     * @param \LumIT\Typo3bb\Domain\Model\Post $newPost
     * @param array $attachments
     * @return void
     */
    public function createAction(Post $newPost, array $attachments = [])
    {
        SecurityUtility::assertAccessPermission('Post.create', $newPost);
        PostFactory::createPost($newPost->getTopic(), $newPost, $attachments);
        $this->postRepository->add($newPost);
        StatisticUtility::addPost();
        $this->persistenceManager->persistAll();

        $this->signalSlotDispatcher->dispatch(Post::class, 'afterCreation',
            ['post' => $newPost, 'controllerContext' => $this->controllerContext]);

        //TODO cache
        $this->forward('showNewPost', 'Topic', null, ['topic' => $newPost->getTopic()]);
    }

    /**
     * action edit
     *
     * @param \LumIT\Typo3bb\Domain\Model\Post $post
     * @return void
     */
    public function editAction(Post $post)
    {
        SecurityUtility::assertAccessPermission('Post.edit', $post);
        $this->view->assignMultiple([
            'post' => $post,
            'previousPosts' => $this->postRepository->findPrevious($post->getTopic(), $post)
        ]);
    }

    /**
     * action update
     *
     * @param \LumIT\Typo3bb\Domain\Model\Post $post
     * @param array $attachments
     * @return void
     */
    public function updateAction(Post $post, array $attachments = [])
    {
        SecurityUtility::assertAccessPermission('Post.edit', $post);
        $post->setText(RteUtility::sanitizeHtml($post->getText()));
        $post->setEditor($this->frontendUser);
        $post->setEdited(true);
        if (!empty($attachments)) {
            PostFactory::processAttachments($post, $attachments);
        }
        $this->postRepository->update($post);

        //TODO cache
        $this->redirect('show', 'Topic', null, ['topic' => $post->getTopic(), 'post' => $post]);
    }

    /**
     * action delete
     *
     * @param \LumIT\Typo3bb\Domain\Model\Post $post
     * @throws ActionNotAllowedException
     * @throws \LumIT\Typo3bb\Exception\AccessValidationException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function deleteAction(Post $post)
    {
        SecurityUtility::assertAccessPermission('Post.delete', $post);
        $topic = $post->getTopic();
        if ($topic->getPosts()->toArray()[0] == $post) {
            if ($topic->getPostsCount() > 1) {
                throw new ActionNotAllowedException(LocalizationUtility::translate('exception.delete.firstPost',
                    $this->extensionName));
            } else {
                $this->redirect('delete', 'Topic', null, ['topic' => $post->getTopic()]);
            }
        }

        $this->postRepository->remove($post);

        //TODO cache
        $this->redirect('show', 'Topic', null, ['topic' => $post->getTopic()]);
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Post $post
     */
    public function moveAction(Post $post)
    {
        SecurityUtility::assertAccessPermission('Post.move', $post);
        $topics = [];
        /**
         * @var  $key
         * @var Topic $topic
         */
        foreach ($this->topicRepository->findAll() as $topic) {
            if (SecurityUtility::checkAccessPermission('Topic.join', $topic)) {
                $topics[$topic->getUid()] = CsvViewHelper::getCsv($topic->getRootline(), 'title', ' &raquo; ');
            }
        }
        asort($topics);
        $this->view->assignMultiple([
            'post' => $post,
            'topics' => $topics
        ]);
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Post $post
     * @param \LumIT\Typo3bb\Domain\Model\Topic $destination
     */
    public function executeMoveAction(Post $post, Topic $destination)
    {
        SecurityUtility::assertAccessPermission('Post.move', $post);
        SecurityUtility::assertAccessPermission('Post.move', $destination);

        $previousTopic = $post->getTopic();

        $previousTopic->removePost($post);
        $destination->addPost($post);
        $this->topicRepository->update($previousTopic);
        $this->topicRepository->update($destination);
        $this->postRepository->update($post);

        //TODO cache
        $this->redirect('show', 'Topic', null, ['topic' => $destination]);
    }

    /**
     * Lists unread posts.
     */
    public function listUnreadAction()
    {
        if ($this->frontendUser === null) {
            throw new AccessValidationException(LocalizationUtility::translate('exception.accessValidation',
                'typo3bb'));
        }
        $unreadPosts = $this->postRepository->findUnread($this->frontendUser);
        $this->view->assignMultiple([
            'posts' => $unreadPosts
        ]);
    }
}