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
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

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

            // Indexing each post and creating direct links would be the more accurate approach
            // but indexing every single post can take a lot of time, depending on how active a forum is
            $fields = 'topic.uid, topic.title, GROUP_CONCAT( post.text SEPARATOR " ") as text, getReadpermissionsRecursive(board.uid) as recursiveReadPermissions, board.parent_board, board.tx_kesearch_index';
            $table = 'tx_typo3bb_domain_model_post post LEFT JOIN tx_typo3bb_domain_model_topic topic ON post.topic = topic.uid LEFT JOIN tx_typo3bb_domain_model_board board ON topic.board = board.uid';
            $where = 'post.pid IN (' . $indexerConfig['sysfolder'] . ') AND post.deleted = 0 AND topic.hidden = 0 AND topic.deleted = 0';
            $groupBy = 'topic.uid';

//            $fields = 'post.*, topic.title, board.read_permissions, board.parent_board';
//            $table = 'tx_typo3bb_domain_model_post as post
//	LEFT JOIN tx_typo3bb_domain_model_topic as topic
//	ON post.topic = topic.uid
//	LEFT JOIN tx_typo3bb_domain_model_board as board
//	ON topic.board = board.uid';
//            $where  = 'post.pid IN (' . $indexerConfig['sysfolder'] . ') AND post.deleted = 0 AND topic.hidden = 0 AND topic.deleted = 0';
//            $groupBy = '';
            $orderBy = '';
            $limit = '';
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, $where, $groupBy, $orderBy, $limit);

            $resCount = $GLOBALS['TYPO3_DB']->sql_num_rows($res);

            // Loop through the records and write them to the index.
            if ($resCount) {
                while (($record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
                    // compile the information which should go into the index
                    // the field names depend on the table you want to index!
                    if (!$record['tx_kesearch_index']) {
                        continue;
                    }

                    // We need the read permissions recursively for each parent board
                    $parentBoardId = $record['parent_board'];
                    while ($parentBoardId != 0) {
                        $parentBoard = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('tx_kesearch_index, parent_board',
                            'tx_typo3bb_domain_model_board', 'uid = ' . $parentBoardId);
                        if (empty($parentBoard)) {
                            break;
                        }
                        if (!$parentBoard['tx_kesearch_index']) {
                            continue 2;
                        }
                        $parentBoardId = $parentBoard['parent_board'];
                    }
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