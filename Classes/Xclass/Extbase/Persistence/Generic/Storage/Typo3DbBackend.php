<?php

namespace LumIT\Typo3bb\Xclass\Extbase\Persistence\Generic\Storage;

use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Exception;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * A Storage backend
 * TYPO3 lacks the ability to count queries with statement. This wraps statements into a COUNT-Select if a statement is set
 * TODO This must probably be refactored for TYPO3 8
 */
class Typo3DbBackend extends \TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbBackend
{
    /**
     * Returns the number of tuples matching the query.
     *
     * @param QueryInterface $query
     * @throws Exception\BadConstraintException
     * @return int The number of matching tuples
     */
    public function getObjectCountByQuery(QueryInterface $query)
    {
        if (empty($query->getStatement()) || empty($query->getStatement()->getStatement())) {
            return parent::getObjectCountByQuery($query);
        } else {
            $originalStatement = $query->getStatement()->getStatement();
            $statement = "SELECT COUNT(*) as 'count' FROM ($originalStatement) as statement";
            $count = $query->statement($statement)->execute(true)[0]['count'];
            $query->statement($originalStatement);

            return (int)$count;
        }
    }
}
