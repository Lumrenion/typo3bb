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
        'sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
    ];

    /**
     * @param ForumCategory|Board $parent
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function getAllowedBoards($parent = null) {
        $usergroups = $GLOBALS['TSFE']->gr_list;
        if ($parent === NULL) {
            $parentField = 'parent_board';
            $uid = 0;
        } elseif ($parent instanceof ForumCategory) {
            $parentField = 'forum_category';
            $uid = $parent->getUid();
        } else {
            $parentField = 'parent_board';
            $uid = $parent->getUid();
        }

        $query = $this->createQuery();

        $sql = 'SELECT tx_typo3bb_domain_model_board.* FROM tx_typo3bb_domain_model_board';
        $sql .= ' WHERE 1 = 1 ' . $GLOBALS['TSFE']->sys_page->enableFields($this->tableName);
        $sql .= ' AND hasAccess(tx_typo3bb_domain_model_board.uid, \'' . $usergroups . '\') = TRUE';
        $sql .= ' AND ' . $parentField . ' = '. $uid;

        // ordering
        $sql .= ' ORDER BY ';
        foreach ($query->getOrderings() as $orderField => $ordering) {
            $sql .= ' ' . $orderField . ' ' . $ordering;
        }

        $query->statement($sql);

        return $query->execute();
    }
}