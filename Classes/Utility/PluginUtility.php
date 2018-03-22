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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * PluginUtility
 */
class PluginUtility
{
    static $configuration = null;

    /**
     * @return array The Plugin Settings
     */
    public static function _getPluginSettings()
    {
        return self::_getPluginConfiguration()['settings'];
    }

    /**
     * @return array    The Plugin Configuration
     */
    public static function _getPluginConfiguration()
    {
        if (empty(self::$configuration)) {
            $objectManager = self::_getObjectManagerInstance();
            /** @var ConfigurationManager $configurationManager */
            $configurationManager = $objectManager->get(ConfigurationManager::class);

            self::$configuration = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK,
                'typo3bb');
        }
        return self::$configuration;
    }

    /**
     * @return ObjectManager
     */
    protected static function _getObjectManagerInstance()
    {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }
}