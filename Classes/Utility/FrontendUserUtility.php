<?php

namespace LumIT\Typo3bb\Utility;

use LumIT\Typo3bb\Domain\Model\FrontendUser;
use LumIT\Typo3bb\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use LumIT\Typo3bb\Domain\Model\FrontendUserGroup;
use TYPO3\CMS\Extbase\Object\ObjectManager;


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
class FrontendUserUtility
{
    /**
     * @var \LumIT\Typo3bb\Domain\Model\FrontendUser
     */
    protected static $currentFrontendUser = null;

    /**
     * @return \LumIT\Typo3bb\Domain\Model\FrontendUser
     */
    public static function getCurrentUser()
    {
        if ($GLOBALS['TSFE']->loginUser && is_null(self::$currentFrontendUser)) {
            self::$currentFrontendUser = self::getUser($GLOBALS['TSFE']->fe_user->user['uid']);
        }
        return self::$currentFrontendUser;
    }

    /**
     * @param int $uid
     * @return FrontendUser
     */
    public static function getUser(int $uid)
    {
        /** @var FrontendUser $frontendUser */
        $frontendUser = self::getFrontendUserRepositoryInstance()->findByUid($uid);
        return $frontendUser;
    }

    /**
     * @return FrontendUserRepository
     */
    protected static function getFrontendUserRepositoryInstance()
    {
        /** @var ObjectManager $objectManager */
        $objectManager = self::getObjectManagerInstance();
        return $objectManager->get(FrontendUserRepository::class);
    }

    /**
     * @return ObjectManager
     */
    protected static function getObjectManagerInstance()
    {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }

    /**
     * Returns the Usergroup comma separated list of the current or a specific user. Includes 0 and -2 in list.
     *
     * @param FrontendUser $frontendUser
     * @return string
     */
    public static function getUsergroupList($frontendUser = null)
    {
        if ($frontendUser === null) {
            return $GLOBALS['TSFE']->gr_list;
        } else {
            $usergroups = $frontendUser->getUsergroup()->toArray();
            $usergroups = array_map(function ($usergroup) {
                /** @var FrontendUserGroup $usergroup */
                return $usergroup->getUid();
            }, $usergroups);
            // 0: matches 'logged in and not logged in users'
            $usergroups[] = 0;
            // -2: matches 'any login user'
            $usergroups[] = -2;
            return implode(',', $usergroups);
        }
    }
}