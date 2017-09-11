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
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * The repository for FrontendUsers
 */
class FrontendUserRepository extends \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository
{

    /**
     * @param array $usernames
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByUsernames(array $usernames) {
        $query = $this->createQuery();
        return $query->matching($query->in('username', $usernames))->execute();
    }

    /**
     * @param $search
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByNameOrUsername($search) {
        $query = $this->createQuery();
        return $query->matching($query->logicalOr(
            $query->like('username', '%' . $search . '%'),
            $query->like('name', '%' . $search . '%')
        ))->execute();
    }

    /**
     * Count users in storagefolder which have a field that contains the value
     *
     * @param string|array  $field
     * @param string  $value
     * @param boolean       $respectStoragePage
     *
     * @return integer
     */
    public function countByField($field, $value, $respectStoragePage = true) {
        if (is_string($field)) {
            return parent::countByField($field, $value, $respectStoragePage);
        }

        $query = $this->createQuery();

        $querySettings = $query->getQuerySettings();
        $querySettings->setRespectStoragePage($respectStoragePage);
        $querySettings->setIgnoreEnableFields(true);

        $constraints = [];
        foreach ($field as $singleField) {
            $constraints[] = $query->equals($singleField, $value);
        }

        return $query->matching($query->logicalOr($constraints))->setLimit(1)->count();
    }

    /**
     * Count users in storagefolder which have a field that contains the value.
     * If a the user is logged in, the user is excluded from count.
     * If multiple fields are specified, the value should be $field1 OR [..] OR $fieldN
     *
     * @param string|array  $field
     * @param string  $value
     * @param boolean       $respectStoragePage
     *
     * @return integer
     */
    public function countByFieldNotCurrentUser($field, $value, $respectStoragePage = true) {
        if (!$GLOBALS['TSFE']->loginUser) {
            $this->countByField($field, $value, $respectStoragePage);
        }

        $query = $this->createQuery();

        $querySettings = $query->getQuerySettings();
        $querySettings->setRespectStoragePage($respectStoragePage);
        $querySettings->setIgnoreEnableFields(true);

        if (is_string($field)) {
            $field = [$field];
        }
        $constraints = [];
        foreach ($field as $singleField) {
            $constraints[] = $query->equals($singleField, $value);
        }

        return $query->matching($query->logicalAnd(
            $query->logicalNot($query->equals('uid', $GLOBALS['TSFE']->fe_user->user['uid'])),
            $query->logicalOr($constraints)
        ))->setLimit(1)->count();
    }


    /**
     * @return object|\LumIT\Typo3bb\Domain\Model\FrontendUser
     */
    public function findSingleLatest() {
        $query = $this->createQuery();
        $query->setOrderings(['crdate' => QueryInterface::ORDER_DESCENDING]);
        return $query->execute()->getFirst();
    }

    /**
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findOnlineUsers() {
        $query = $this->createQuery();
        $query->matching($query->greaterThanOrEqual('isOnline', strtotime('-15 minutes')));
        return $query->execute();
    }

    /**
     * Extends magic method by adding findOrderedBy${propertyName}${ASC|DESC}
     *
     * @param string $methodName
     * @param string $arguments
     * @return array|mixed|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function __call($methodName, $arguments) {
        if (substr($methodName, 0, 13) === 'findOrderedBy' && strlen($methodName) > 14) {
            $propertyName = lcfirst(substr($methodName, 13));
            if (StringUtility::endsWith($propertyName, 'ASC')) {
                $propertyName = substr($propertyName, 0, -3);
                $ordering = QueryInterface::ORDER_ASCENDING;
            } elseif (StringUtility::endsWith($propertyName, 'DESC')) {
                $propertyName = substr($propertyName, 0,  -4);
                $ordering = QueryInterface::ORDER_DESCENDING;
            } else {
                $ordering = null;
            }

            if ($ordering != null) {
                $query = $this->createQuery();
                $query->setOrderings([lcfirst($propertyName) => $ordering]);
                if ($arguments[0] > 0) {
                    $query->setLimit($arguments[0]);
                }
                return $query->execute();
            }
        }

        return parent::__call($methodName, $arguments); // TODO: Change the autogenerated stub
    }
}