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

/**
 * Message
 */
class MessageParticipant extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * @var \LumIT\Typo3bb\Domain\Model\Message
     * @lazy
     */
    protected $sentMessage = null;

    /**
     * @var \LumIT\Typo3bb\Domain\Model\Message
     * @lazy
     */
    protected $receivedMessage = null;

    /**
     * @var \LumIT\Typo3bb\Domain\Model\FrontendUser
     * @lazy
     */
    protected $user = null;

    /**
     * @var string
     */
    protected $userName = '';

    /**
     * @var bool
     */
    protected $viewed = false;

    /**
     * @var bool
     */
    protected $deleted = false;

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

    }

    /**
     * @return Message
     */
    public function getSentMessage() {
        return $this->sentMessage;
    }

    /**
     * @param Message $sentMessage
     */
    public function setSentMessage(Message $sentMessage) {
        $this->sentMessage = $sentMessage;
    }

    /**
     * @return Message
     */
    public function getReceivedMessage() {
        return $this->receivedMessage;
    }

    /**
     * @param Message $receivedMessage
     */
    public function setReceivedMessage(Message $receivedMessage) {
        $this->receivedMessage = $receivedMessage;
    }

    /**
     * @return \LumIT\Typo3bb\Domain\Model\FrontendUser
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\FrontendUser $user
     */
    public function setUser(FrontendUser $user) {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getUserName() {
        if (!is_null($this->getUser())) {
            return $this->getUser()->getDisplayName();
        }
        return $this->userName;
    }

    /**
     * @param string $userName
     */
    public function setUserName(string $userName) {
        $this->userName = $userName;
    }


    /**
     * @return bool
     */
    public function getViewed() {
        return $this->viewed;
    }

    /**
     * @return boolean
     */
    public function isViewed() {
        return $this->viewed;
    }

    /**
     * @param boolean $viewed
     */
    public function setViewed(bool $viewed) {
        $this->viewed = $viewed;
    }

    /**
     * @return boolean
     */
    public function isDeleted() {
        return $this->deleted;
    }

    /**
     * @param boolean $deleted
     */
    public function setDeleted(bool $deleted) {
        $this->deleted = $deleted;
    }


}