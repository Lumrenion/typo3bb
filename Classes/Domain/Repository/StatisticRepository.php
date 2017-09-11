<?php
namespace LumIT\Typo3bb\Domain\Repository;
use LumIT\Typo3bb\Domain\Model\Statistic;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\RepositoryException;

/**
 * Class StatisticRepository
 * @package LumIT\Typo3bb\Domain\Repository
 */
class StatisticRepository extends AbstractRepository {

    /**
     * @return object|\LumIT\Typo3bb\Domain\Model\Statistic
     */
    public function getToday() {
        return $this->findByDate(new \DateTime());
    }

    /**
     * @param \DateTime $date
     * @return object|\LumIT\Typo3bb\Domain\Model\Statistic
     */
    public function findByDate(\DateTime $date) {
        $query = $this->createQuery();
        $query->matching($query->equals('date', $date->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d')));

        if ($query->count() <= 0) {
            $newStatistic = new Statistic();
            $this->persistenceManager->add($newStatistic);
            $this->persistenceManager->persistAll();
        }

        return $query->execute()->getFirst();
    }

    /**
     * @param \DateTime|null $dateFrom
     * @param \DateTime|null $dateTo
     * @return array
     */
    public function getAverages(\DateTime $dateFrom = null, \DateTime $dateTo = null) {
        if ($dateFrom !== null && $dateTo !== null) {
            $whereClause = 'date BETWEEN(' . $dateFrom->format('Y-m-d') . ',' . $dateTo->format('Y-m-d') . ')';
        } else {
            $whereClause = '';
        }

        $row = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
            'SUM(posts) AS posts, SUM(topics) AS topics, SUM(registers) AS registers, SUM(most_on) AS most_on, MIN(date) AS date',
            'tx_typo3bb_domain_model_statistic',
            $whereClause
        );

        $total_days_up = ceil((time() - strtotime($row['date'])) / (60 * 60 * 24));
        return [
            'posts' => round($row['posts'] / $total_days_up, 2),
            'topics' => round($row['topics'] / $total_days_up, 2),
            'registers' => round($row['registers'] / $total_days_up, 2),
            'online' => round($row['most_on'] / $total_days_up, 2),
        ];
    }

    /**
     * The repository does not support the add method.
     * To create a statistics record for a non existent date, findByDate() should be used.
     *
     * @param object $object
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\RepositoryException
     */
    public function add($object) {
        throw new RepositoryException("This repository does not support the add method. Please use findByDate() to create a statistics entry for a non existent date.", 1487347390);
    }
}