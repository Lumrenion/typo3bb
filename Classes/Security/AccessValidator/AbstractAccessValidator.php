<?php
namespace LumIT\Typo3bb\Security\AccessValidator;
use LumIT\Typo3bb\Utility\FrontendUserUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

abstract class AbstractAccessValidator implements AccessValidator {

    /**
     * @var \LumIT\Typo3bb\Domain\Model\FrontendUser $frontendUser
     * @return true;
     */
    protected $frontendUser = NULL;

    /**
     * @return \LumIT\Typo3bb\Domain\Model\FrontendUser
     */
    public function _getCurrentLoginUser() {
        return FrontendUserUtility::getCurrentUser();
    }

    /**
     * Checks, if the comma separated list of userGroups contains a group where the current user is in.
     * Special cases:
     * -2: Any logged in user
     * -1: Only anonymous users
     *  0: Any user, no matter if logged in or not
     *
     * @param string $userGroups
     *
     * @return bool
     */
    protected function _checkUserGroup($userGroups) {
        // empty userGroups list means public access, just as the list would contain 0
        if ($userGroups == '') {
            return true;
        }



        foreach (array_filter(explode(',',$userGroups), 'strlen') as $userGroup) {
            if (GeneralUtility::inList($GLOBALS['TSFE']->gr_list, $userGroup)) {
                return true;
            }
        }

        return false;
    }
}