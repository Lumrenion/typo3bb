<?php
namespace LumIT\Typo3bb\Domain\Model;

use LumIT\Typo3bb\Domain\Model\Cache\CacheInstance;
use LumIT\Typo3bb\Utility\CacheUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

abstract class AbstractCachableModel extends AbstractEntity
{
    /**
     * @var \LumIT\Typo3bb\Domain\Model\Cache\CacheInstance
     */
    protected $cacheInstance = null;

    public function __construct()
    {
        $this->initializeCache();
    }


    /**
     * @param bool $forceRenewal
     */
    public function initializeCache($forceRenewal = false)
    {
        if ($this->cacheInstance === null) {
            $this->cacheInstance = GeneralUtility::makeInstance(CacheInstance::class,
                $this->_getEncodedCacheIdentifier(),
                $this->_getEncodedCacheIdentifierPerUsergroup(),
                $this->_getUsergroupCacheTag()
            );
            if ($forceRenewal) {
                $this->cacheInstance->flush();
            }
        }
    }

    public function flushCache()
    {
        if ($this->cacheInstance !== null) {
            $this->cacheInstance->flush();
        }
    }

    /**
     * @return string
     */
    protected function _getEncodedCacheIdentifier()
    {
        return CacheUtility::encodeIdentifier(CacheUtility::getObjectCacheIdentifier($this));
    }

    /**
     * @return string
     */
    protected function _getEncodedCacheIdentifierPerUsergroup()
    {
        return CacheUtility::encodeIdentifier(
            CacheUtility::getObjectCacheIdentifier($this) . '_' . CacheUtility::getUsergroupIdentifier()
        );
    }

    protected function _getUsergroupCacheTag()
    {
        return str_replace('\\', '-', CacheUtility::getObjectCacheIdentifier($this)) . '_usergroup';
    }

    /**
     * @return array
     */
    protected function _getCacheableAttributes() {
        return [];
    }

    /**
     * @return array
     */
    protected function _getCacheableAttributesPerUsergroup() {
        return [];
    }

    /**
     * @param array $cachedAttributes
     * @return void
     */
    protected function _setCachedAttributes($cachedAttributes) {
        foreach ($cachedAttributes as $attributeKey => $attributeValue) {
            $this->$attributeKey = $attributeValue;
        }
    }
}