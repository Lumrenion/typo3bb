<?php
namespace LumIT\Typo3bb\Property\TypeConverter;

/***************************************************************
 * Copyright notice
 *
 * (c) 2017 Philipp Seßner <philipp.sessner@gmail.com>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


/**
 * Class DateConverter
 * @package LumIT\Typo3bb\Property\TypeConverter
 */
class DateConverter extends \TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter {

    /**
     * @var array<string>
     */
    protected $sourceTypes = array('date');

    /**
     * @var string
     */
    protected $targetType = \DateTime::class;

    /**
     * Actually convert from $source to $targetType, taking into account the fully
     * built $convertedChildProperties and $configuration.
     * The return value can be one of three types:
     * - an arbitrary object, or a simple type (which has been created while mapping)
     *   This is the normal case.
     * - NULL, indicating that this object should *not* be mapped
     *   (i.e. a "File Upload" Converter could return NULL if no file has been
     *   uploaded, and a silent failure should occur.
     * - An instance of \TYPO3\CMS\Extbase\Error\Error
     *   This will be a user-visible error message later on.
     * Furthermore, it should throw an Exception if an unexpected failure
     * (like a security error) occurred or a configuration issue happened.
     *
     * @param mixed $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration
     *
     * @return mixed|\TYPO3\CMS\Extbase\Error\Error target type, or an error object
     * @throws \TYPO3\CMS\Extbase\Property\Exception\TypeConverterException thrown in case a developer error occurred
     */
    public function convertFrom(
        $source,
        $targetType,
        array $convertedChildProperties = [],
        \TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface $configuration = null
    ) {
        $date = new \DateTime($source);
        $date->setTime(0,0,0);

        return $date;
    }

}