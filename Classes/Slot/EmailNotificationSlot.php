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
use LumIT\Typo3bb\Utility\EmailUtility;
use LumIT\Typo3bb\Utility\PluginUtility;
use LumIT\Typo3bb\Utility\SecurityUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class EmailNotificationSlot
 * @package LumIT\Typo3bb\Slot
 */
class EmailNotificationSlot implements SingletonInterface
{


    /**
     * Sends email notifications to receivers of the sent message.
     * @param \TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext $controllerContext
     *
     * @param \LumIT\Typo3bb\Domain\Model\Message $message
     */
    public function onMessageCreation($message, $controllerContext)
    {
        $rawEmailBody = EmailUtility::getEmailBody(
            'OnMessageSent',
            ['message' => $message],
            $controllerContext
        );

        /** @var \LumIT\Typo3bb\Domain\Model\MessageParticipant $messageReceiver */
        foreach ($message->getReceivers() as $messageReceiver) {
            if ($messageReceiver->getUser()->isMessageNotification()) {
                $mailMessage = EmailUtility::getMailMessage();
                $mailMessage->setSubject(LocalizationUtility::translate('emailNotification.onMessageSent.subject',
                    'typo3bb'));
                $receiverUser = $messageReceiver->getUser();
                $mailMessage->setTo($receiverUser->getEmail(), $receiverUser->getDisplayName());
                $emailBody = EmailUtility::substituteMarkers($rawEmailBody, ['receiver' => $receiverUser]);
                $mailMessage->setBody($emailBody, 'text/html');
                if (false === (bool)PluginUtility::_getPluginSettings()['debug']) {
                    $mailMessage->send();
                }
            }
        }
    }

    /**
     * Sends email notifications to users that subscribed the parent board of the newly created topic.
     *
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     * @param \TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext $controllerContext
     */
    public function onTopicCreated($topic, $controllerContext)
    {
        $rawEmailBody = EmailUtility::getEmailBody(
            'OnTopicCreated',
            ['topic' => $topic],
            $controllerContext
        );

        /** @var \LumIT\Typo3bb\Domain\Model\FrontendUser $boardSubscriber */
        foreach ($this->getBoardSubscribers($topic->getBoard()) as $boardSubscriber) {
            if (
                $boardSubscriber->getUid() !== $topic->getAuthor()->getUid()
                && GeneralUtility::validEmail($boardSubscriber->getEmail())
                && SecurityUtility::checkAccessPermission('Board.show', $topic->getBoard(), $boardSubscriber)
            ) {
                $mailMessage = EmailUtility::getMailMessage();
                $mailMessage->setSubject(LocalizationUtility::translate('emailNotification.onTopicCreated.subject',
                    'typo3bb'));
                $mailMessage->setTo($boardSubscriber->getEmail(), $boardSubscriber->getDisplayName());
                $emailBody = EmailUtility::substituteMarkers($rawEmailBody, ['receiver' => $boardSubscriber]);
                $mailMessage->setBody($emailBody, 'text/html');
                if (false === (bool)PluginUtility::_getPluginSettings()['debug']) {
                    $mailMessage->send();
                }
            }
        }
    }

    /**
     * Returns subscribers of boards and it's parent boards and removes duplicates.
     *
     * @param \LumIT\Typo3bb\Domain\Model\Board $board
     *
     * @return array
     */
    protected function getBoardSubscribers($board)
    {
        $subscribers = [];
        if (!empty($board->getParentBoard())) {
            $subscribers = array_merge($subscribers, $this->getBoardSubscribers($board->getParentBoard()));
        }
        return array_unique(array_merge($subscribers, $board->getSubscribers()->toArray()));
    }

    /**
     * Sends email notifications to users that subscribed the parent topic of the newly created post.
     *
     * @param \LumIT\Typo3bb\Domain\Model\Post $post
     * @param \TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext $controllerContext
     */
    public function onPostCreated($post, $controllerContext)
    {
        $rawEmailBody = EmailUtility::getEmailBody(
            'OnPostCreated',
            ['post' => $post],
            $controllerContext
        );

        $subscribers = $this->getBoardSubscribers($post->getTopic()->getBoard());
        $subscribers = array_unique(array_merge($subscribers, $post->getTopic()->getSubscribers()->toArray()));

        /** @var \LumIT\Typo3bb\Domain\Model\FrontendUser $topicSubscriber */
        foreach ($subscribers as $topicSubscriber) {
            if (
                $topicSubscriber->getUid() !== $post->getAuthor()->getUid()
                && GeneralUtility::validEmail($topicSubscriber->getEmail())
                && SecurityUtility::checkAccessPermission('Board.show', $post->getTopic()->getBoard(), $topicSubscriber)
            ) {
                $mailMessage = EmailUtility::getMailMessage();
                $mailMessage->setSubject(LocalizationUtility::translate('emailNotification.onPostCreated.subject',
                    'typo3bb'));
                $mailMessage->setTo($topicSubscriber->getEmail(), $topicSubscriber->getDisplayName());
                $emailBody = EmailUtility::substituteMarkers($rawEmailBody, ['receiver' => $topicSubscriber]);
                $mailMessage->setBody($emailBody, 'text/html');
                if (false === (bool)PluginUtility::_getPluginSettings()['debug']) {
                    $mailMessage->send();
                }
            }
        }
    }
}