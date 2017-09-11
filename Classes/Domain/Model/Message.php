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

use LumIT\Typo3bb\Utility\FrontendUserUtility;

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Message
 */
class Message extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * @var \LumIT\Typo3bb\Domain\Model\MessageParticipant
     * @lazy
     */
    protected $sender = null;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\MessageParticipant>
     * @lazy
     * @cascade remove
     * @validate NotEmpty
     */
    protected $receivers = null;

    /**
     * subject
     *
     * @var string
     * @validate NotEmpty
     */
    protected $subject = '';

    /**
     * @var string
     */
    protected $text = '';

    /**
     * @var \DateTime
     */
    protected $crdate = null;

    /**
     * @var \LumIT\Typo3bb\Domain\Model\Message
     */
    protected $parent = null;

    /**
     * __construct
     */
    public function __construct() {
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
    protected function initStorageObjects() {
        $this->receivers = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
    }

    /**
     * @return MessageParticipant
     */
    public function getSender() {
        return $this->sender;
    }

    /**
     * @param MessageParticipant $sender
     */
    public function setSender(MessageParticipant $sender) {
        $this->sender = $sender;
    }

    /**
     * Adds a Receiver
     *
     * @param \LumIT\Typo3bb\Domain\Model\MessageParticipant $receiver
     * @return void
     */
    public function addReceiver(MessageParticipant $receiver)
    {
        $this->receivers->attach($receiver);
    }

    /**
     * Removes a Receiver
     *
     * @param \LumIT\Typo3bb\Domain\Model\MessageParticipant $receiverToRemove The Receiver to be removed
     * @return void
     */
    public function removeReceiver(MessageParticipant $receiverToRemove)
    {
        $this->receivers->detach($receiverToRemove);
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getReceivers() {
        return $this->receivers;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $receivers
     */
    public function setReceivers(ObjectStorage $receivers) {
        $this->receivers = $receivers;
    }

    /**
     * @return string
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject) {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text) {
        $this->text = $text;
    }

    /**
     * @return \DateTime
     */
    public function getCrdate() {
        return $this->crdate;
    }

    /**
     * @param \DateTime $crdate
     */
    public function setCrdate(\DateTime $crdate) {
        $this->crdate = $crdate;
    }

    /**
     * @return \LumIT\Typo3bb\Domain\Model\Message
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Message $parent
     */
    public function setParent(Message $parent) {
        $this->parent = $parent;
    }

    /**
     * @return bool
     */
    public function getViewed() {
        if($GLOBALS['TSFE']->loginUser) {
            /** @var FrontendUser $frontendUser */
            $frontendUser = FrontendUserUtility::getCurrentUser();
            return $this->getMessageParticipant($frontendUser)->isViewed();
        }
        return true;
    }

    /**
     * @param FrontendUser $frontendUser
     * @return MessageParticipant
     */
    public function getMessageParticipant(FrontendUser $frontendUser) {
        $messageParticipant = null;
        if($this->getSender()->getUser() == $frontendUser) {
            $messageParticipant = $this->getSender();
        } else {
            foreach ($this->getReceivers() as $receiver) {
                if($receiver->getUser() == $frontendUser) {
                    $messageParticipant = $receiver;
                    break;
                }
            }
        }
        return $messageParticipant;
    }
}