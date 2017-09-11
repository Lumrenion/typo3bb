<?php
namespace LumIT\Typo3bb\Utility;
use LumIT\Typo3bb\Domain\Repository\FrontendUserRepository;
use LumIT\Typo3bb\Domain\Repository\StatisticRepository;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class StatisticUtility
 * @package LumIT\Typo3bb\Utility
 */
class StatisticUtility {
    protected static $databaseName = 'tx_typo3bb_domain_model_statistic';


    public static function addRegister() {
        $todayStatistic = self::getStatisticRepository()->getToday();
        $todayStatistic->setRegisters($todayStatistic->getRegisters() + 1);

        self::updateStatistic($todayStatistic, 'registers');
    }

    public static function addPost() {
        $todayStatistic = self::getStatisticRepository()->getToday();
        $todayStatistic->setPosts($todayStatistic->getPosts() + 1);

        self::updateStatistic($todayStatistic, 'posts');
    }

    public static function addTopic() {
        $todayStatistic = self::getStatisticRepository()->getToday();
        $todayStatistic->setTopics($todayStatistic->getTopics() + 1);
        $todayStatistic->setPosts($todayStatistic->getPosts() + 1);

        self::updateStatistic($todayStatistic, ['topics', 'posts']);
    }

    public static function updateOnlineUsers() {
        /** @var FrontendUserRepository $frontendUserRepository */
        $frontendUserRepository = self::getObjectManager()->get(FrontendUserRepository::class);
        $statisticRepository = self::getStatisticRepository();

        $onlineUsersCount = $frontendUserRepository->findOnlineUsers()->count();
        $todaysStatistic = $statisticRepository->getToday();
        if ($onlineUsersCount > $todaysStatistic->getMostOn()) {
            $todaysStatistic->setMostOn($onlineUsersCount);
            self::updateStatistic($todaysStatistic, 'mostOn');
        }
    }


    /**
     * @return DatabaseConnection;
     */
    protected static function getDatabaseConnection() {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Statistic $statistic  The updated statistic record
     * @param string|array                          $fields      The field(s) to update
     */
    protected static function updateStatistic($statistic, $fields) {
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        $fieldsToUpdate = [];
        foreach ($fields as $field) {
            $fieldGetter = 'get' . ucfirst($fields);
            $databaseField = GeneralUtility::camelCaseToLowerCaseUnderscored($field);

            $fieldsToUpdate[$databaseField] = $statistic->$fieldGetter();;
        }

        self::getDatabaseConnection()->exec_UPDATEquery(
            self::$databaseName,
            'uid = ' . $statistic->getUid(),
            $fieldsToUpdate
        );
    }

    /**
     * @return object|StatisticRepository
     */
    protected static function getStatisticRepository() {
        return self::getObjectManager()->get(StatisticRepository::class);
    }

    /**
     * @return object|ObjectManager
     */
    protected static function getObjectManager() {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }
}