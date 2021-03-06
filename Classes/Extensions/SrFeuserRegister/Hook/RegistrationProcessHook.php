<?php

namespace LumIT\Typo3bb\Extensions\SrFeuserRegister\Hook;

use LumIT\Typo3bb\Slot\FrontendUserSlot;
use LumIT\Typo3bb\Utility\FrontendUserUtility;
use LumIT\Typo3bb\Utility\RteUtility;
use LumIT\Typo3bb\Utility\StatisticUtility;
use SJBR\SrFeuserRegister\Domain\Data;
use SJBR\SrFeuserRegister\Hooks\RegistrationProcessHooks;
use SJBR\SrFeuserRegister\Request\Parameters;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class RegistrationProcessHook extends RegistrationProcessHooks
{

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
        $queryBuilder = $this->getQuerBuilder();
        $queryBuilder->update('fe_users')
            ->where($queryBuilder->expr()->eq('uid', $dataArray['uid']))
            ->set('signature', RteUtility::sanitizeHtml($dataArray['signature']))
            ->execute();
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
        $queryBuilder = $this->getQuerBuilder();
        $latestPosts = $queryBuilder->select('uid')
            ->from('tx_typo3bb_domain_model_post')
            ->orderBy('uid', 'DESC')
            ->setMaxResults(1)
            ->execute()->fetchAll();

        $queryBuilder = $this->getQuerBuilder();
        $queryBuilder->update('fe_users')
            ->where($queryBuilder->expr()->eq('uid', $dataArray['uid']))
            ->set('tx_typo3bb_display_name', $dataArray['username'])
            ->set('last_read_post', $latestPosts[0]['uid'])
            ->execute();

        StatisticUtility::addRegister();
    }

    /**
     * @return \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    protected function getQuerBuilder()
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('fe_users');
    }
}