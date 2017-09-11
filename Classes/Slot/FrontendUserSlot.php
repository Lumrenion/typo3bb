<?php
namespace LumIT\Typo3bb\Slot;

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

use LumIT\Typo3bb\Domain\Model\FrontendUser;
use LumIT\Typo3bb\Domain\Model\MessageParticipant;
use LumIT\Typo3bb\Domain\Model\Post;
use LumIT\Typo3bb\Domain\Model\Topic;

use LumIT\Typo3bb\Domain\Repository\MessageRepository;
use LumIT\Typo3bb\Domain\Repository\PostRepository;
use LumIT\Typo3bb\Domain\Repository\TopicRepository;

use LumIT\Typo3bb\Utility\RteUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class FrontendUserSlot
 * @package LumIT\Typo3bb\Slot
 */
class FrontendUserSlot implements \TYPO3\CMS\Core\SingletonInterface {

    /**
     * @param \LumIT\Typo3bb\Domain\Model\FrontendUser $user
     * @param array $settings
     */
    public static function deleted(FrontendUser &$user, $settings) {
        self::processDeletedForumUser($user);

    }

    /**
     * Sets authorNames and editorNames of topics and posts from the frontendUser to be deleted.
     * TODO srfeuserregister
     * @param \LumIT\Typo3bb\Domain\Model\FrontendUser $forumUser
     */
    public static function processDeletedForumUser($forumUser) {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var PostRepository $postRepository */
        $postRepository = $objectManager->get(PostRepository::class);
        /** @var TopicRepository $topicRepository */
        $topicRepository = $objectManager->get(TopicRepository::class);
        /** @var MessageRepository $messageRepository */
        $messageRepository = $objectManager->get(MessageRepository::class);

        $createdPosts = $forumUser->getCreatedPosts();
        /** @var Post $createdPost */
        foreach ($createdPosts as $createdPost) {
            $createdPost->setAuthorName($forumUser->getUsername());
            $postRepository->update($createdPost);
        }
        $createdTopics = $forumUser->getCreatedTopics();
        /** @var Topic $createdTopic */
        foreach ($createdTopics as $createdTopic) {
            $createdTopic->setAuthorName($forumUser->getUsername());
            $topicRepository->update($createdTopic);
        }
        $editedPosts = $forumUser->getEditedPosts();
        /** @var Post $editedPost */
        foreach ($editedPosts as $editedPost) {
            $editedPost->setEditorName($forumUser->getUsername());
            $postRepository->update($editedPost);
        }
        $sentMessages = $forumUser->getSentMessages();
        /** @var MessageParticipant $sentMessage */
        foreach ($sentMessages as $sentMessage) {
            $sentMessage->setUserName($forumUser->getUsername());
            $sentMessage->setDeleted(true);
            $messageRepository->update($sentMessage->getSentMessage());
        }
        $receivedMessages = $forumUser->getReceivedMessages();
        /** @var MessageParticipant $receivedMessage */
        foreach ($receivedMessages as $receivedMessage) {
            $receivedMessage->setUserName($forumUser->getUsername());
            $receivedMessage->setDeleted(true);
            $messageRepository->update($receivedMessage->getReceivedMessage());
        }
        $forumUser->setName($forumUser->getUsername());
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\FrontendUser $user
     * @param array $settings
     */
    public static function sanitizeHtmlSignatureBeforeSave(&$user, $settings) {
        $user->setSignature(RteUtility::sanitizeHtml($user->getSignature()));
    }
}