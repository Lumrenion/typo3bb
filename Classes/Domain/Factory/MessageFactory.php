<?php
namespace LumIT\Typo3bb\Domain\Factory;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use LumIT\Typo3bb\Domain\Model\MessageParticipant;
use LumIT\Typo3bb\Domain\Repository\FrontendUserRepository;
use LumIT\Typo3bb\Utility\FrontendUserUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;


/**
 * Class MessageFactory
 * @package LumIT\Typo3bb\Domain\Factory
 */
class MessageFactory {
    /** @var FrontendUserRepository */
    protected static $frontendUserRepository = NULL;

    /**
     * @param \TYPO3\CMS\Extbase\Mvc\Controller\Argument $messageArgument
     * @param array $message
     * @param string|array $receivers
     */
    public static function prepareMessage($messageArgument, &$message, $receivers) {
        if(empty(trim($message['subject']))) {
            $message['subject'] = LocalizationUtility::translate('messages.subject.empty', 'typo3bb');
        }
        $message['sender'] = new MessageParticipant();
        $message['sender']->setUser(FrontendUserUtility::getCurrentUser());
        $message['sender']->setViewed(true);
        $messageArgument->getPropertyMappingConfiguration()->allowAllProperties('sender');

        if (!is_array($receivers)) {
            $receivers = str_getcsv($receivers);
        }
        $receivingUsers = self::getFrontendUserRepository()->findByUsernames($receivers);

        $i = 0;
        $message['receivers'] = [];
        foreach ($receivingUsers as $receivingUser) {
            $message['receivers'][$i] = new MessageParticipant();
            $message['receivers'][$i]->setUser($receivingUser);
            $messageArgument->getPropertyMappingConfiguration()->forProperty('receivers')->allowProperties($i);
            $i++;
        }
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Message $message
     */
    public static function createMessage($message) {
        $message->getSender()->setSentMessage($message);
        $message->getSender()->getUser()->addSentMessage($message->getSender());
        /** @var MessageParticipant $receiver */
        foreach ($message->getReceivers() as $receiver) {
            $receiver->setReceivedMessage($message);
            $receiver->getUser()->addReceivedMessage($receiver);
        }
    }

    /**
     * @return FrontendUserRepository
     */
    protected static function getFrontendUserRepository() {
        if (self::$frontendUserRepository == NULL) {
            /** @var FrontendUserRepository $frontendUserRepository */
            self::$frontendUserRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(FrontendUserRepository::class);
        }
        return self::$frontendUserRepository;
    }
}