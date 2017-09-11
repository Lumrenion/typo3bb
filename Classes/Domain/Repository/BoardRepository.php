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

    /**
     * @var array
     */
    protected $defaultOrderings = [
        'sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
    ];

    /**
     * @param ForumCategory|Board $parent
     * @return int
     */
    public function countAllowedBoards($parent) {
        $query = $this->getAllowedBoardsQuery($parent);
        return $query->count();
    }
    /**
     * @param ForumCategory|Board $parent
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function getAllowedBoards($parent) {
        $query = $this->getAllowedBoardsQuery($parent);
        return $query->execute();
    }

    /**
     * @param ForumCategory|Board $parent
     * @return \TYPO3\CMS\Extbase\Persistence\QueryInterface
     */
    protected function getAllowedBoardsQuery($parent) {
        $query = $this->createQuery();

        $groupConstraints = [];
        foreach (explode(',', $GLOBALS["TSFE"]->gr_list) as $group) {
            $groupConstraints [] = $query->contains('readPermissions', $group);
        }

        if ($parent instanceof ForumCategory) {
            $parentField = 'forumCategory';
        } else {
            $parentField = 'parentBoard';
        }

        $query->matching($query->logicalAnd(
            $query->equals($parentField, $parent->getUid()),
            $query->logicalOr($groupConstraints)
        ));

        return $query;
    }
}