<?php
namespace LumIT\Typo3bb\Utility;
use LumIT\Typo3bb\Exception\AccessValidationException;
use LumIT\Typo3bb\Security\AccessValidator\AccessValidator;
use TYPO3\CMS\Core\Resource\Exception\InvalidConfigurationException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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

class SecurityUtility implements SingletonInterface {
    
    protected static $settings = [];

    /**
     * @param string $permissionKey
     * @param $objectToValidate
     * @throws AccessValidationException
     * @throws InvalidConfigurationException
     */
    public static function assertAccessPermission(string $permissionKey, $objectToValidate = null) {
        if (!self::checkAccessPermission($permissionKey, $objectToValidate)) {
            throw new AccessValidationException(LocalizationUtility::translate('exception.accessValidation', 'typo3bb'));
        }
    }

    /**
     * Evaluates if the current user passes one of the permission checks specified in TypoScript settings
     *
     * @param string $permissionKey The key of the permission stack. Might be "Board.view.XYZ"
     * @param object $objectToValidate The object to validate
     * @return bool
     * @throws InvalidConfigurationException
     */
    public static function checkAccessPermission(string $permissionKey, $objectToValidate = null) {
        $settings = PluginUtility::_getPluginSettings()['accessValidation'];
        $permissionValidators = ArrayUtility::getValueByPath($settings, $permissionKey);

        if(empty($permissionValidators)) {
            return true;
            throw new InvalidConfigurationException('No access validation rules exist for key ' . $permissionKey . '!');
        }

        return self::_evaluateStack($permissionValidators, $objectToValidate);
    }


    /**
     * @param array $permissionValidators The stack of validators that evaluate the object
     * @param object $objectToValidate The object to evaluate
     * @param bool $meetAllConditions If all validators in stack must be true (default: single true is enough)
     * @return bool
     * @throws IllegalObjectTypeException
     * @throws InvalidConfigurationException
     */
    public static function _evaluateStack($permissionValidators, $objectToValidate = null, $meetAllConditions = FALSE) {
        foreach ($permissionValidators as $permissionValidatorKey => $permissionValidator) {
            if(StringUtility::beginsWith($permissionValidatorKey, 'AND')) {
                if(!is_array($permissionValidator)) {
                    throw new InvalidConfigurationException('An AND-Stack must always contain an array!');
                }
                $result = self::_evaluateStack($permissionValidator, $objectToValidate, TRUE);
            } elseif(StringUtility::beginsWith($permissionValidatorKey, 'OR')) {
                if(!is_array($permissionValidator)) {
                    throw new InvalidConfigurationException('An OR-Stack must always contain an array!');
                }
                $result = self::_evaluateStack($permissionValidator, $objectToValidate);
            } else {
                $accessValidatorObject = self::_getValidatorObject($permissionValidator);
                $result = $accessValidatorObject->validate($objectToValidate);
            }

            if(!$meetAllConditions && $result) {
                return true;
            } elseif ($meetAllConditions && !$result) {
                return false;
            }
        }

        return $meetAllConditions;
    }

    /**
     * @param string $validatorString
     * @return AccessValidator
     * @throws IllegalObjectTypeException
     */
    public static function _getValidatorObject(string $validatorString) {
        if(!StringUtility::beginsWith($validatorString, '\\')) {
            //we assume a validator from this extension
            $validatorString = '\LumIT\Typo3bb\Security\AccessValidator\\' . $validatorString . 'AccessValidator';
        }
        $accessValidatorObject = self::_getObjectManagerInstance()->get(ltrim($validatorString, '\\'));
        if (!$accessValidatorObject instanceof AccessValidator) {
            throw new IllegalObjectTypeException('Access Validation classes must always implement interface \LumIT\Typo3bb\Security\AccessValidator\AccessValidator');
        }
        return $accessValidatorObject;
    }

    /**
     * @return ObjectManager
     */
    protected static function _getObjectManagerInstance() {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }
}