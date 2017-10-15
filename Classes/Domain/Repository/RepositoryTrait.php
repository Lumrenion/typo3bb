<?php
namespace LumIT\Typo3bb\Domain\Repository;

use LumIT\Typo3bb\Utility\PluginUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

trait RepositoryTrait {

    /**
     * This is a workaround
     * FIXME solution for storage page id's outside of plugin
     * The Problem:
     * StoragePageId's get set automatically from configuration. But when the repository get's called from outside the
     * plugin, the storagePageId is always 0. This is especially crucial for Hooks and Slots.
     * For now, with this workaround the storagePageId's MUST be set in TypoScript Persistence Settings!
     *
     * AbstractRepository constructor.
     * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
     */
    public function __construct(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager)
    {
        parent::__construct($objectManager);
        $this->setStoragePageIdsFromPluginSettings();
    }

    /**
     * @param Typo3QuerySettings $defaultQuerySettings
     */
    public function setStoragePageIdsFromPluginSettings($defaultQuerySettings = null) {
        if($this->defaultQuerySettings == null) {
            if($defaultQuerySettings instanceof  Typo3QuerySettings) {
                $this->setDefaultQuerySettings($defaultQuerySettings);
            } else {
                $this->setDefaultQuerySettings($this->objectManager->get(Typo3QuerySettings::class));
            }
        }
        $this->defaultQuerySettings->setStoragePageIds(GeneralUtility::intExplode(',', PluginUtility::_getPluginConfiguration()['persistence']['storagePid']));
    }

    /**
     * Extends magic method by adding findOrderedBy${propertyName}${ASC|DESC}
     *
     * @param string $methodName
     * @param string $arguments
     * @return array|mixed|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function __call($methodName, $arguments) {
        if (substr($methodName, 0, 13) === 'findOrderedBy' && strlen($methodName) > 14) {
            $propertyName = lcfirst(substr($methodName, 13));
            if (StringUtility::endsWith($propertyName, 'ASC')) {
                $propertyName = substr($propertyName, 0, -3);
                $ordering = QueryInterface::ORDER_ASCENDING;
            } elseif (StringUtility::endsWith($propertyName, 'DESC')) {
                $propertyName = substr($propertyName, 0,  -4);
                $ordering = QueryInterface::ORDER_DESCENDING;
            } else {
                $ordering = null;
            }

            if ($ordering != null) {
                $query = $this->createQuery();
                $query->setOrderings([lcfirst($propertyName) => $ordering]);
                if ($arguments[0] > 0) {
                    $query->setLimit($arguments[0]);
                }
                return $query->execute();
            }
        }

        if (substr($methodName, 0, 7) === 'findMax' && strlen($methodName) > 8) {
            $propertyName = lcfirst(substr($methodName, 7));
            $query = $this->createQuery();
            $query->setOrderings([
                $propertyName => QueryInterface::ORDER_DESCENDING
            ]);
            $query->setLimit(1);

            $result = $query->execute()->getFirst();
            return $result;
        }

        return parent::__call($methodName, $arguments);
    }
}