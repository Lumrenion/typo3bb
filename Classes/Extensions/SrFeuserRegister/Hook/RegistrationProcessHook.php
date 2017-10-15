<?php
namespace LumIT\Typo3bb\Extensions\SrFeuserRegister\Hook;

use LumIT\Typo3bb\Slot\FrontendUserSlot;
use LumIT\Typo3bb\Utility\FrontendUserUtility;
use LumIT\Typo3bb\Utility\RteUtility;
use LumIT\Typo3bb\Utility\StatisticUtility;
use SJBR\SrFeuserRegister\Domain\Data;
use SJBR\SrFeuserRegister\Hooks\RegistrationProcessHooks;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class RegistrationProcessHook extends RegistrationProcessHooks {

    public function registrationProcess_beforeSaveDelete($recordArray, $invokingObj)
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $user = FrontendUserUtility::getUser($recordArray['uid']);
        FrontendUserSlot::processDeletedForumUser($user);
        /** @var PersistenceManager $persistenceManager */
        $persistenceManager = $objectManager->get(PersistenceManager::class);
        $persistenceManager->persistAll();
    }

    /**
     * Called after an existing user was edited. Sanitizes the html signature
     *
     * @param $theTable
     * @param array $dataArray
     * @param array $origArray
     * @param $token
     * @param array $newRow
     * @param $cmd
     * @param $cmdKey
     * @param $pid
     * @param $fieldList
     * @param Data $pObj
     */
    public function registrationProcess_afterSaveEdit(
        $theTable,
        array $dataArray,
        array $origArray,
        $token,
        array &$newRow,
        $cmd,
        $cmdKey,
        $pid,
        $fieldList,
        Data $pObj
    ) {
        $this->getDatabase()->exec_UPDATEquery($theTable, 'uid=' . (int)$dataArray['uid'], ['signature' => RteUtility::sanitizeHtml($dataArray['signature'])]);
    }

    /**
     * Called after a new user has registered. The number of today's registrations will be increased by 1.
     *
     * @param $theTable
     * @param array $dataArray
     * @param array $origArray
     * @param $token
     * @param array $newRow
     * @param $cmd
     * @param $cmdKey
     * @param $pid
     * @param $fieldList
     * @param Data $pObj
     */
    public function registrationProcess_afterSaveCreate(
        $theTable,
        array $dataArray,
        array $origArray,
        $token,
        array &$newRow,
        $cmd,
        $cmdKey,
        $pid,
        $fieldList,
        Data $pObj
    ) {
        StatisticUtility::addRegister();
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabase()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}