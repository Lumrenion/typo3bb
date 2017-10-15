<?php
namespace LumIT\Typo3bb\Hook;

    /***************************************************************
     * Copyright notice
     *
     * (c) 2016 Philipp Seßner <philipp.sessner@gmail.com>
     * All rights reserved
     *
     * This script is part of the TYPO3 project. The TYPO3 project is
     * free software; you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation; either version 2 of the License, or
     * (at your option) any later version.
     *
     * The GNU General Public License can be found at
     * http://www.gnu.org/copyleft/gpl.html.
     *
     * This script is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU General Public License for more details.
     *
     * This copyright notice MUST APPEAR in all copies of the script!
     ***************************************************************/


use LumIT\Typo3bb\Domain\Model\FrontendUser;
use LumIT\Typo3bb\Slot\FrontendUserSlot;
use LumIT\Typo3bb\Utility\FrontendUserUtility;
use LumIT\Typo3bb\Utility\StatisticUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 *
 */
class ProcessFrontendUsersHook {
    /**
     * Called before deletion, with complete record-info ($recordToDelete)
     * Sets authorNames and editorNames of topics and posts from the frontendUser to be deleted.
     *
     * @param $table
     * @param $id
     * @param $recordToDelete
     * @param null $recordWasDeleted
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $pObj
     */
    public function processCmdmap_deleteAction($table, $id, $recordToDelete, $recordWasDeleted=NULL, \TYPO3\CMS\Core\DataHandling\DataHandler &$pObj) {
        if ($table == 'fe_users') {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $user = FrontendUserUtility::getUser($id);
            if ($user instanceof FrontendUser) {
                FrontendUserSlot::processDeletedForumUser($user);
                /** @var PersistenceManager $persistenceManager */
                $persistenceManager = $objectManager->get(PersistenceManager::class);
                $persistenceManager->persistAll();
            }
        }
    }

    /**
     * Raises the login_time of a frontend user.
     * Also determines the maximum users logged in today.
     *
     * @param TypoScriptFrontendController $tsfe
     */
    public function checkDataSubmission(TypoScriptFrontendController $tsfe) {
        if ($tsfe->loginUser) {
            $this->processLoginTime($tsfe);
            StatisticUtility::updateOnlineUsers();
        }
    }

    /**
     * Adapted SMF way to calculate fe_users total_time_logged_in.
     * Logs the frontend user login time and raises the login_time of the frontend user if necessary.
     * If there was no activity during the last 15 minutes, the login time is not updated.
     *
     * @param TypoScriptFrontendController $tsfe
     */
    protected function processLoginTime(TypoScriptFrontendController $tsfe) {
        $loginSessionData = $GLOBALS['TSFE']->fe_user->getKey('ses', 'tx_typo3bb_logintime');
        $loginSessionData['log_time'] = time();

        if(empty($loginSessionData['timeOnlineUpdated']))
            $loginSessionData['timeOnlineUpdated'] = time();

        if(!empty($tsfe->fe_user->user['is_online']) && $tsfe->fe_user->user['is_online'] < time()-60) {
            //Don't count longer than 15 minutes
            if (time()-$loginSessionData['timeOnlineUpdated'] > 60*15)
                $loginSessionData['timeOnlineUpdated'] = time();

            $previousLoginTime = $tsfe->fe_user->user['login_time'];
            $tsfe->fe_user->user['login_time'] += time() - $loginSessionData['timeOnlineUpdated'];
            if ($previousLoginTime < $tsfe->fe_user->user['login_time']) {
                /** @var DatabaseConnection $typo3Db */
                $typo3Db = $GLOBALS['TYPO3_DB'];
                $typo3Db->exec_UPDATEquery(
                    'fe_users',
                    'uid = ' . $tsfe->fe_user->user['uid'],
                    ['login_time' => $tsfe->fe_user->user['login_time']]
                );
            }

            $tsfe->fe_user->user['login_time'] += time() - $loginSessionData['timeOnlineUpdated'];
            $loginSessionData['timeOnlineUpdated'] = time();
        }
        $GLOBALS['TSFE']->fe_user->setKey('ses', 'tx_typo3bb_logintime', $loginSessionData);
    }
}