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
use LumIT\Typo3bb\Domain\Model\Board;
use LumIT\Typo3bb\Domain\Model\Topic;
use LumIT\Typo3bb\Domain\Repository\BoardRepository;
use LumIT\Typo3bb\Domain\Repository\TopicRepository;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 *
 */
class ProcessBoardsHook
{
    /**
     * Called before deletion, with complete record-info ($recordToDelete)
     * @param $table
     * @param $id
     * @param $recordToDelete
     * @param null $recordWasDeleted
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $pObj
     */
    public function processCmdmap_deleteAction(
        $table,
        $id,
        $recordToDelete,
        $recordWasDeleted = null,
        DataHandler &$pObj
    ) {
        if ($table == 'tx_typo3bb_domain_model_board') {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            /** @var BoardRepository $boardRepository */
            $boardRepository = $objectManager->get(BoardRepository::class);
            /** @var Board $board */
            $board = $boardRepository->findByUid($id);
            /** @var TopicRepository $topicRepository */
            $topicRepository = $objectManager->get(TopicRepository::class);
            $topics = clone $board->getTopics();
            /** @var Topic $topic */
            foreach ($topics as $topic) {
                $topicRepository->remove($topic);
            }

            /** @var PersistenceManager $persistenceManager */
            $persistenceManager = $objectManager->get(PersistenceManager::class);
            $persistenceManager->persistAll();
        }
    }

    /**
     * @param $status
     * @param $table
     * @param $id
     * @param array $fieldArray
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $pObj
     */
    public function processDatamap_afterDatabaseOperations($status, $table, $id, array $fieldArray, DataHandler &$pObj)
    {
        if ($table == 'tx_typo3bb_domain_model_board') {
            $board = null;

            $boardRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(BoardRepository::class);
            if ($status === 'new') {
                if (!empty($fieldArray['parent_board'])) {
                    $board = $boardRepository->findByUid($fieldArray['parent_board']);
                }
            } else {
                $board = $boardRepository->findByUid($id);
            }

            if ($board instanceof Board) {
                $board->flushCache();
            }
        }
    }
}