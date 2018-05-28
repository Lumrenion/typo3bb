<?php

namespace LumIT\Typo3bb\Utility;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Philipp Seßner <philipp.sessner@gmail.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 ***************************************************************/

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class CacheUtility
 * @package LumIT\Typo3bb\Utility
 */
class CacheUtility
{

    /**
     * @return VariableFrontend
     */
    public static function getCacheInstance()
    {
        return GeneralUtility::makeInstance(CacheManager::class)->getCache('typo3bb');
    }

    public static function encodeIdentifier($identifier)
    {
        return sha1($identifier);
    }

    /**
     * Returns the cache identifier for an object
     *
     * @param AbstractEntity $object
     * @return string
     */
    public static function getObjectCacheIdentifier($object)
    {
        $class = get_class($object);
        $uid = $object->getUid();
        return $class . '_' . $uid;
    }

    /**
     * Returns the sorted user groups so that it qualifies as an identifier
     *
     * @return string
     */
    public static function getUsergroupIdentifier()
    {
        $userGroups = explode(',', $GLOBALS['TSFE']->gr_list);
        $userGroups = array_unique($userGroups);
        sort($userGroups);
        return implode(',', $userGroups);
    }
}