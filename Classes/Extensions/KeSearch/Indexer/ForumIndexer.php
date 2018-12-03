<?php

namespace LumIT\Typo3bb\Extensions\KeSearch\Indexer;

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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 *
 */
class ForumIndexer
{

    public static $indexerType = 'typo3bb_forum';
    var $startMicrotime = 0;

    function registerIndexerConfiguration(&$params, $pObj)
    {

        // add item to "type" field
        $newArray = array(
            'Typo3bb indexer',
            self::$indexerType,
            ExtensionManagementUtility::extRelPath('typo3bb') . 'ext_icon.svg'
        );
        $params['items'][] = $newArray;
    }


    /**
     * // TODO this indexer indexes all posts as separat records, but $indexerObject->storeInIndex() takes too long to process each record. Maybe an INSERT FROM SELECT could solve this
     * Custom indexer for ke_search
     *
     * @param array $indexerConfig Configuration from TYPO3 Backend
     * @param \tx_kesearch_indexer $indexerObject Reference to indexer class.
     * @return string Output.
     */
//    public function customIndexer(&$indexerConfig, &$indexerObject)
//    {
//        $this->startMicrotime = microtime(true);
//        if ($indexerConfig['type'] == self::$indexerType) {
//            $content = '';
//
//            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_typo3bb_domain_model_post');
//            $queryBuilder->select('post.uid', 'post.text', 'post.crdate', 'post.pid', 'topic.uid as topic', 'topic.title', 'board_recur.read_permissions as recursiveReadPermissions')
//                ->from('tx_typo3bb_domain_model_post', 'post')
//                ->join('post' , 'tx_typo3bb_domain_model_topic', 'topic', $queryBuilder->expr()->eq('post.topic', $queryBuilder->quoteIdentifier('topic.uid')))
//                ->join('topic' , 'tx_typo3bb_domain_model_board', 'board', $queryBuilder->expr()->eq('topic.board', $queryBuilder->quoteIdentifier('board.uid')))
//                ->join('board', 'view_tx_typo3bb_board_recursive_information', 'board_recur', $queryBuilder->expr()->eq('board.uid', $queryBuilder->quoteIdentifier('board_recur.uid')))
//                ->where(
//                    $queryBuilder->expr()->in('post.pid', GeneralUtility::intExplode(',', $indexerConfig['sysfolder'], true)),
//                    $queryBuilder->expr()->eq('board_recur.tree_tx_kesearch_index', 1)
//                );
//
//            $statement = $queryBuilder->execute();
//            $resCount = $statement->rowCount();
//
//            if ($resCount) {
//                while (($record = $statement->fetch())) {
//                    $title = strip_tags($record['title']); //Category Title
//                    $abstract = strip_tags($record['text']);
//                    $content = strip_tags($record['text']);
//                    $fullContent = $title . "\n" . $content;
//                    $params = '&tx_typo3bb_forum[action]=show&tx_typo3bb_forum[controller]=Topic&tx_typo3bb_forum[topic]=' . $record['topic'] . '&tx_typo3bb_forum[post]=' . $record['uid'] . '#post-' . $record['uid'];
//                    $tags = '#typo3bbForumTag';
//                    $additionalFields = array(
//                        'sortdate' => $record['crdate'],
//                        'orig_uid' => $record['uid'],
//                        'orig_pid' => $record['pid'],
//                    );
//                    $readPermissionsAndStack = $record['recursiveReadPermissions'];
//
//                    // ... and store the information in the index
//                    $indexerObject->storeInIndex(
//                        $indexerConfig['storagepid'], // storage PID
//                        $title, // record title
//                        self::$indexerType, // content type
//                        $indexerConfig['targetpid'], // target PID: where is the single view?
//                        $fullContent, // indexed content, includes the title (linebreak after title)
//                        $tags, // tags for faceted search
//                        $params, // typolink params for singleview
//                        $abstract, // abstract; shown in result list if not empty
//                        0, // language uid
//                        0, // starttime
//                        0, // endtime
//                        $readPermissionsAndStack, // fe_group
//                        false, // debug only?
//                        $additionalFields // additionalFields
//                    );
//                }
//
//                $content = '<p><b>Indexer "' . $indexerConfig['title'] . '": </b><br>' . $resCount . ' Forum Elements have been indexed.</p>';
//                $content .= $this->showTime();
//            }
//            return $content;
//        }
//
//        return null;
//    }

    /**
     * Custom indexer for ke_search
     *
     * @param array $indexerConfig Configuration from TYPO3 Backend
     * @param \tx_kesearch_indexer $indexerObject Reference to indexer class.
     * @return string Output.
     */
    public function customIndexer(&$indexerConfig, &$indexerObject)
    {
        $this->startMicrotime = microtime(true);
        if ($indexerConfig['type'] == self::$indexerType) {
            $content = '';

            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_typo3bb_domain_model_post');
            $queryBuilder->select('topic.uid', 'topic.title', 'board_recur.read_permissions as recursiveReadPermissions')
                ->addSelectLiteral('GROUP_CONCAT(post.text SEPARATOR " ") as text')
                ->from('tx_typo3bb_domain_model_post', 'post')
                ->join('post' , 'tx_typo3bb_domain_model_topic', 'topic', $queryBuilder->expr()->eq('post.topic', $queryBuilder->quoteIdentifier('topic.uid')))
                ->join('topic' , 'tx_typo3bb_domain_model_board', 'board', $queryBuilder->expr()->eq('topic.board', $queryBuilder->quoteIdentifier('board.uid')))
                ->join('board', 'view_tx_typo3bb_board_recursive_information', 'board_recur', $queryBuilder->expr()->eq('board.uid', $queryBuilder->quoteIdentifier('board_recur.uid')))
                ->where(
                    $queryBuilder->expr()->in('post.pid', GeneralUtility::intExplode(',', $indexerConfig['sysfolder'], true)),
                    $queryBuilder->expr()->eq('board_recur.tree_tx_kesearch_index', 1)
                )->groupBy('topic.uid');

            $statement = $queryBuilder->execute();
            $resCount = $statement->rowCount();

            if ($resCount) {
                while (($record = $statement->fetch())) {
                    $title = strip_tags($record['title']); //Category Title
                    $abstract = strip_tags($record['text']);
                    $content = strip_tags($record['text']);
                    $fullContent = $title . "\n" . $content;
                    $params = '&tx_typo3bb_forum[action]=show&tx_typo3bb_forum[controller]=Topic&tx_typo3bb_forum[topic]=' . $record['uid'];
                    $tags = '#typo3bbForumTag';
                    $additionalFields = array(
                        'sortdate' => $record['crdate'],
                        'orig_uid' => $record['uid'],
                        'orig_pid' => $record['pid'],
                    );
                    $readPermissionsAndStack = $record['recursiveReadPermissions'];

                    // ... and store the information in the index
                    $indexerObject->storeInIndex(
                        $indexerConfig['storagepid'], // storage PID
                        $title, // record title
                        self::$indexerType, // content type
                        $indexerConfig['targetpid'], // target PID: where is the single view?
                        $fullContent, // indexed content, includes the title (linebreak after title)
                        $tags, // tags for faceted search
                        $params, // typolink params for singleview
                        $abstract, // abstract; shown in result list if not empty
                        0, // language uid
                        0, // starttime
                        0, // endtime
                        $readPermissionsAndStack, // fe_group
                        false, // debug only?
                        $additionalFields // additionalFields
                    );
                }

                $content = '<p><b>Indexer "' . $indexerConfig['title'] . '": </b><br>' . $resCount . ' Forum Elements have been indexed.</p>';
                $content .= $this->showTime();
            }
            return $content;
        }

        return null;
    }

    /**
     * shows time used
     *
     * @return  string
     */
    public function showTime()
    {
        // calculate duration of indexing process
        $endMicrotime = microtime(true);
        $duration = ceil(($endMicrotime - $this->startMicrotime) * 1000);

        // show sec or ms?
        if ($duration > 10000) {
            $duration /= 1000;
            $duration = intval($duration);
            return '<p><i>Indexing process for "Typo3bb" took ' . $duration . ' s.</i> </p>' . "\n\n";
        } else {
            return '<p><i>Indexing process for "Typo3bb" took ' . $duration . ' ms.</i> </p>' . "\n\n";
        }
    }
}