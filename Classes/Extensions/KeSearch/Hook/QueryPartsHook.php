<?php
namespace LumIT\Typo3bb\Extensions\KeSearch\Hook;

use LumIT\IglarpTemplate\Xclass\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class QueryPartsHook
{
    /**
     * @param array $queryParts
     * @param \tx_kesearch_db $kesearchDb
     */
    public function getQueryParts($queryParts, $kesearchDb)
    {
        $where = $queryParts['WHERE'];

        $whereWithoutFeGroups = substr($where, 0, strrpos( $where, 'AND'));


        $field = $kesearchDb->table . '.' . $GLOBALS['TCA'][$kesearchDb->table]['ctrl']['enablecolumns']['fe_group'];

        $expressionBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($kesearchDb->table)
            ->expr();
        $memberGroups = GeneralUtility::intExplode(',', $this->getTypoScriptFrontendController()->gr_list);
        $orChecks = [];
        // If the field is empty, then OK
        $orChecks[] = $expressionBuilder->eq($field, $expressionBuilder->literal(''));
        // If the field is NULL, then OK
        $orChecks[] = $expressionBuilder->isNull($field);
        // If the field contains zero, then OK
        $orChecks[] = $expressionBuilder->eq($field, $expressionBuilder->literal('0'));

        $orChecks[] = 'findMultipleInAndSet(\'' . implode(',', $memberGroups) . '\', ' . $field . ')';

        $where = $whereWithoutFeGroups . ' AND (' . $expressionBuilder->orX(...$orChecks) . ')';


        $queryParts['WHERE'] = $where;
        return $queryParts;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}