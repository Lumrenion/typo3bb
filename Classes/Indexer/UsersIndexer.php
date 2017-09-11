<?php
namespace LumIT\Typo3bb\Indexer;

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
class UsersIndexer {

    var $startMicrotime = 0;

    function registerIndexerConfiguration(&$params, $pObj) {

        // add item to "type" field
        $newArray = array(
        'Typo3bb Users indexer',
        'typo3bb_users',
        ExtensionManagementUtility::extRelPath('typo3bb') . 'ext_icon.gif'
        );
        $params['items'][] = $newArray;
    }
    /**
     * Custom indexer for ke_search
     *
     * @param array                 $indexerConfig Configuration from TYPO3 Backend
     * @param \tx_kesearch_indexer  $indexerObject Reference to indexer class.
     * @return string Output.
     */
    public function customIndexer(&$indexerConfig, &$indexerObject) {
        $this->startMicrotime = microtime(true);
        if($indexerConfig['type'] == 'typo3bb_users') {
            $content = '';

            // get all the entries to index
            // don't index hidden or deleted elements, BUT
            // get the elements with frontend user group access restrictions
            // or time (start / stop) restrictions.
            // Copy those restrictions to the index.
            $fields = '*';
            $table = 'fe_users';
            $where  = 'pid IN (' . $indexerConfig['sysfolder'] . ')';
            $groupBy = '';
            $orderBy =  '';
            $limit = '';
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$groupBy,$orderBy,$limit);

            $resCount = $GLOBALS['TYPO3_DB']->sql_num_rows($res);

            // Loop through the records and write them to the index.
            if($resCount) {
                while ( ($record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) ) {
                    // compile the information which should go into the index
                    // the field names depend on the table you want to index!
                    $title = strip_tags($record['name'] ?: $record['username']);
                    $abstract = '';
                    $fullContent = '';
                    $params = '&tx_typo3bb_user-profile[action]=show&tx_typo3bb_user-profile[controller]=FrontendUser&tx_typo3bb_user-profile[user]='. $record['uid'];
                    $tags = '#typo3bbUsersTag';
                    $additionalFields = array(
                        'sortdate' => $record['crdate'],
                        'orig_uid' => $record['uid'],
                        'orig_pid' => $record['pid'],
                    );

                    // add something to the title, just to identify the entries
                    // in the frontend

                    // ... and store the information in the index
                    $indexerObject->storeInIndex(
                        $indexerConfig['storagepid'], // storage PID
                        $title, // record title
                        'typo3bb_users', // content type
                        $indexerConfig['targetpid'], // target PID: where is the single view?
                        $fullContent, // indexed content, includes the title (linebreak after title)
                        $tags, // tags for faceted search
                        $params, // typolink params for singleview
                        $abstract, // abstract; shown in result list if not empty
                        0, // language uid
                        0, // starttime
                        0, // endtime
                        '-2', // fe_group
                        false, // debug only?
                        $additionalFields // additionalFields
                    );
                }
                $content = '<p><b>Indexer "' . $indexerConfig['title'] . '": </b><br>' . $resCount . ' Forum Elements have been indexed.</p>';

                $content .= $this->showTime();
            }
            return $content;
        }
    }

    /**
     * shows time used
     *
     * @return  string
    */
    public function showTime() {
        // calculate duration of indexing process
        $endMicrotime = microtime(true);
        $duration = ceil(($endMicrotime - $this->startMicrotime) * 1000);

        // show sec or ms?
        if ($duration > 10000) {
            $duration /= 1000;
            $duration = intval($duration);
            return '<p><i>Indexing process for "Typo3bb Users" took '.$duration.' s.</i> </p>'."\n\n";
        } else {
            return '<p><i>Indexing process for "Typo3bb Users" took '.$duration.' ms.</i> </p>'."\n\n";
        }
    }
}