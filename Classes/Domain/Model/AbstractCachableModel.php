<?php
namespace LumIT\Typo3bb\Domain\Model;

use LumIT\Typo3bb\Utility\CacheUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

abstract class AbstractCachableModel extends AbstractEntity
{
    /**
     * @param bool $forceRenewal
     */
    public function initializeCache($forceRenewal = false)
    {
        $cacheInstance = CacheUtility::getCacheInstance();
        $cacheIdentifier = $this->_getEncodedCacheIdentifier();
        $cacheIdentifierPerUsergroup = $this->_getEncodedCacheIdentifierPerUsergroup();

        if ($forceRenewal) {
            $this->flushCache();
        }
        if (($cachedAttributes = $cacheInstance->get($cacheIdentifier)) === false) {
            $cacheableAttributes = $this->_getCacheableAttributes();
            $cachedAttributes = $this->_renewCache($cacheIdentifier, $cacheableAttributes);
        }
        if (($cachedAttributesPerUsergroup = $cacheInstance->get($cacheIdentifierPerUsergroup)) === false) {
            $cacheableAttributesPerUsergroup = $this->_getCacheableAttributesPerUsergroup();
            $cacheTagsPerUsergroup = [
                $this->_getUsergroupCacheTag()
            ];
            $cachedAttributesPerUsergroup = $this->_renewCache($cacheIdentifierPerUsergroup, $cacheableAttributesPerUsergroup, $cacheTagsPerUsergroup);
        }

        $this->_setCachedAttributes(array_merge($cachedAttributes, $cachedAttributesPerUsergroup));
    }

    public function flushCache()
    {
        $cacheInstance = CacheUtility::getCacheInstance();
        $cacheIdentifier = $this->_getEncodedCacheIdentifier();
        $cacheInstance->remove($cacheIdentifier);
        $cacheInstance->flushByTag($this->_getUsergroupCacheTag());
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
     * @param string $cacheIdentifier
     * @param array $cacheableAttributes
     * @param array $tags
     * @return array
     */
    protected function _renewCache($cacheIdentifier, $cacheableAttributes, $tags = [])
    {
        $cacheInstance = CacheUtility::getCacheInstance();

        if (!empty($cacheableAttributes)) {
            $cacheInstance->set($cacheIdentifier, $cacheableAttributes, $tags);
        }

        return $cacheableAttributes;
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