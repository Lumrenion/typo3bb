<?php
namespace LumIT\Typo3bb;

/*
 *  Copyright notice
 *
 *  (c) 2013-2015 Stanislas Rolland <typo3(arobas)sjbr.ca>
 *  All rights reserved
 *
 *  This script is part of the Typo3 project. The Typo3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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
 */

use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class for updating the db
 */
class ext_update
{
    /**
     * Main function, returning the HTML content
     *
     * @return string HTML
     */
    public function main()
    {
        $content = '';

        $sql = file_get_contents(GeneralUtility::getFileAbsFileName('EXT:typo3bb/ext_tables_static+adt.sql'));

        $statements = $this->getStatementArray($sql, true);
        foreach ($statements as $statement) {
            if (!empty($statement) && !StringUtility::beginsWith($statement, '--')) {
                self::getDb()->admin_query($statement);
            }
        }

        $content.= '<p>Required MySQL functions and procedures were created</p>';
        return $content;
    }

    public function access()
    {
        return true;
    }

    /**
     * @return DatabaseConnection
     */
    protected static function getDb()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @return ObjectManager
     */
    protected static function getObjectManager()
    {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }

    /**
     * The SqlSchemaMigrationService SQL-Parser does not take delimiters into account
     * @see \TYPO3\CMS\Install\Service\SqlSchemaMigrationService::getStatementArray
     *
     * @param string $sqlcode The SQL-file content. Provided that 1) every query in the input is ended with ';' and that a line in the file contains only one query or a part of a query.
     * @param bool $removeNonSQL If set, non-SQL content (like comments and blank lines) is not included in the final output
     * @param string $query_regex Regex to filter SQL lines to include
     * @return array Array of SQL statements
     */
    protected function getStatementArray($sqlcode, $removeNonSQL = false, $query_regex = '')
    {
        $sqlcodeArr = explode(LF, $sqlcode);
        // Based on the assumption that the sql-dump has
        $statementArray = [];
        $statementArrayPointer = 0;
        $delimiter = ';';
        foreach ($sqlcodeArr as $line => $lineContent) {
            $lineContent = trim($lineContent);
            $is_set = 0;
            // Auto_increment fields cannot have a default value!
            if (stristr($lineContent, 'auto_increment')) {
                $lineContent = preg_replace('/ default \'0\'/i', '', $lineContent);
            }
            if (substr($lineContent, 0, 9) === 'DELIMITER') {
                $delimiter = substr($lineContent, strlen('DELIMITER '));
                continue;
            }
            if (!$removeNonSQL || $lineContent !== '' && $lineContent[0] !== '#' && substr($lineContent, 0, 2) !== '--') {
                // '--' is seen as mysqldump comments from server version 3.23.49
                $statementArray[$statementArrayPointer] .= $lineContent;
                $is_set = 1;
            }
            if (substr($lineContent, strlen($delimiter) * -1) === $delimiter) {
                if (isset($statementArray[$statementArrayPointer])) {
                    if (!trim($statementArray[$statementArrayPointer]) || $query_regex && !preg_match(('/' . $query_regex . '/i'), trim($statementArray[$statementArrayPointer]))) {
                        unset($statementArray[$statementArrayPointer]);
                    }
                }
                $statementArray[$statementArrayPointer] = substr(
                    $statementArray[$statementArrayPointer],
                    0,
                    strlen($statementArray[$statementArrayPointer]) - strlen($delimiter)
                );
                $statementArrayPointer++;
            } elseif ($is_set) {
                $statementArray[$statementArrayPointer] .= LF;
            }
        }
        return $statementArray;
    }
}