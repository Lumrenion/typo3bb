<?php

namespace LumIT\Typo3bb\Domain\Repository;


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
use LumIT\Typo3bb\Domain\Model\FrontendUser;
use LumIT\Typo3bb\Utility\PluginUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * The repository for Messages
 */
class MessageRepository extends AbstractRepository
{

    /**
     * @var array
     */
    protected $defaultOrderings = [
        'crdate' => QueryInterface::ORDER_DESCENDING
    ];

    /**
     * @param Typo3QuerySettings $defaultQuerySettings
     */
    public function setStoragePageIdsFromPluginSettings($defaultQuerySettings = null)
    {
        if ($this->defaultQuerySettings == null) {
            if ($defaultQuerySettings instanceof Typo3QuerySettings) {
                $this->setDefaultQuerySettings($defaultQuerySettings);
            } else {
                $this->setDefaultQuerySettings($this->objectManager->get(Typo3QuerySettings::class));
            }
        }
        $this->defaultQuerySettings->setStoragePageIds(GeneralUtility::intExplode(',',
            PluginUtility::_getPluginConfiguration()['persistence']['storagePid']));
    }

    /**
     * @param FrontendUser $frontendUser
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findInbox(FrontendUser $frontendUser)
    {
        $query = $this->createQuery();
        return $query->matching($query->logicalAnd(
            $query->equals('receivers.user', $frontendUser),
            $query->equals('receivers.deleted', 0)
        ))->execute();
    }

    /**
     * @param FrontendUser $frontendUser
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findOutbox(FrontendUser $frontendUser)
    {
        $query = $this->createQuery();
        return $query->matching($query->logicalAnd(
            $query->equals('sender.user', $frontendUser),
            $query->equals('sender.deleted', 0)
        ))->execute();
    }

    /**
     * @param FrontendUser|integer $frontendUser
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findUnviewed($frontendUser)
    {
        $query = $this->createQuery();
        return $query->matching($query->logicalAnd(
            $query->equals('receivers.user', $frontendUser),
            $query->equals('receivers.viewed', 0)
        ))->execute();
    }
}