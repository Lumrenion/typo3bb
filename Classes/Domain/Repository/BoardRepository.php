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
use LumIT\Typo3bb\Domain\Model\Board;
use LumIT\Typo3bb\Domain\Model\ForumCategory;
use LumIT\Typo3bb\Utility\StringUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;


/**
 * The repository for Boards
 */
class BoardRepository extends AbstractRepository
{

    protected $tableName = 'tx_typo3bb_domain_model_board';
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'sorting' => QueryInterface::ORDER_ASCENDING
    ];

    /**
     * @param ForumCategory|Board $parent
     * @return array
     */
    public function getAllowedBoards($parent = null)
    {
        $usergroups = $GLOBALS['TSFE']->gr_list;
        if ($parent === null) {
            $parentField = 'parent_board';
            $uid = 0;
        } elseif ($parent instanceof ForumCategory) {
            $parentField = 'forum_category';
            $uid = $parent->getUid();
        } else {
            $parentField = 'parent_board';
            $uid = $parent->getUid();
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_typo3bb_domain_model_board');
        $queryBuilder->select('board.*', 'board_recursive.read_permissions AS read_permissions_recursive')
            ->from('tx_typo3bb_domain_model_board', 'board')
            ->leftJoin('board', 'view_tx_typo3bb_board_recursive_information', 'board_recursive', 'board.uid = board_recursive.uid')
            ->where($queryBuilder->expr()->eq($parentField, $uid));
        foreach ($this->createQuery()->getOrderings() as $field => $ordering) {
            $queryBuilder->addOrderBy($field, $ordering);
        }
        $boardStatement = $queryBuilder->execute();

        $allowedBoards = [];
        while (($board = $boardStatement->fetch()) !== false) {
            if (StringUtility::findAnyInAndStack($usergroups, $board['read_permissions_recursive'])) {
                unset($board['read_permissions_recursive']);
                $allowedBoards[] = $board;
            }
        }

        $dataMapper = $this->objectManager->get(DataMapper::class);
        return $dataMapper->map($this->objectType, $allowedBoards);
    }
}