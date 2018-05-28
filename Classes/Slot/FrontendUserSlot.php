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

use LumIT\Typo3bb\Domain\Repository\FrontendUserRepository;
use LumIT\Typo3bb\Utility\RteUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class FrontendUserSlot
 * @package LumIT\Typo3bb\Slot
 */
class FrontendUserSlot implements SingletonInterface
{

    /**
     * Sets authorNames and editorNames of topics and posts from the frontendUser to be deleted.
     * @param \LumIT\Typo3bb\Domain\Model\FrontendUser $forumUser
     */
    public static function processDeletedForumUser($forumUser)
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = $objectManager->get(FrontendUserRepository::class);

        $forumUser->setName($forumUser->getUsername());
        $frontendUserRepository->update($forumUser);
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\FrontendUser $user
     * @param array $settings
     */
    public static function sanitizeHtmlSignatureBeforeSave($user, $settings)
    {
        $user->setSignature(RteUtility::sanitizeHtml($user->getSignature()));
    }
}