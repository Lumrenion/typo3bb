<?php

namespace LumIT\Typo3bb\Domain\Model;


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
use LumIT\Typo3bb\Domain\Repository\BoardRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * A forum category is displayed on the index page of the bulletin board and
 * contains boards.
 */
class ForumCategory extends AbstractCachableModel
{

    /**
     * title
     *
     * @var string
     * @validate NotEmpty
     */
    protected $title = '';

    /**
     * Boards in this cateogry
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Board>
     * @cascade remove
     * @lazy
     */
    protected $boards = null;


    /********************************************
     *                                          *
     *                                          *
     *             META INFORMATION             *
     *  Information not persisted in database   *
     *     collected at runtime and cached      *
     *                                          *
     *                                          *
     ********************************************/
    
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    protected $allowedBoards = null;

    /**
     * __construct
     */
    public function __construct()
    {
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }

    /**
     * Initializes all ObjectStorage properties
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->boards = new ObjectStorage();
    }

    /**
     * Returns the title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the title
     *
     * @param string $title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Adds a Board
     *
     * @param \LumIT\Typo3bb\Domain\Model\Board $board
     * @return void
     */
    public function addBoard(Board $board)
    {
        $this->boards->attach($board);
    }

    /**
     * Removes a Board
     *
     * @param \LumIT\Typo3bb\Domain\Model\Board $boardToRemove The Board to be removed
     * @return void
     */
    public function removeBoard(Board $boardToRemove)
    {
        $this->boards->detach($boardToRemove);
    }

    /**
     * Returns the boards
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function getBoards()
    {
        return $this->boards;
    }

    /**
     * Sets the boards
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Board> $boards
     * @return void
     */
    public function setBoards(ObjectStorage $boards)
    {
        $this->boards = $boards;
    }

    
    
    /******************************************************************************************************************/
    
    
    
    /**
     * Returns the boards the current user is allowed to see
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function getAllowedBoards()
    {
        if ($this->allowedBoards === null) {
            $boardRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(BoardRepository::class);
            $this->allowedBoards = $boardRepository->getAllowedBoards($this);
        }
        if (is_array($this->allowedBoards)) {
            $boardRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(BoardRepository::class);
            $allowedBoards = [];
            foreach ($this->allowedBoards as $allowedBoard) {
                $subBoard = $boardRepository->findByUid($allowedBoard);
                if (!empty($subBoard)) {
                    $allowedBoards[] = $subBoard;
                }
            }
            $this->allowedBoards = $allowedBoards;
        }
        return $this->allowedBoards;
    }


    /******************************************************************************************************************/



    protected function _getCacheableAttributesPerUsergroup()
    {
        $allowedBoardObjects = $this->getAllowedBoards();
        $allowedBoards = [];
        foreach ($allowedBoardObjects as $allowedBoardObject) {
            $allowedBoards[] = $allowedBoardObject->getUid();
        }

        $cachedAttributes = [
            'allowedBoards' => $allowedBoards
        ];

        return $cachedAttributes;
    }
}