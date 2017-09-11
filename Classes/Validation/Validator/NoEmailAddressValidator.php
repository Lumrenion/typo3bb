<?php
namespace LumIT\Typo3bb\Validation\Validator;


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
use TYPO3\CMS\Extbase\Validation\Validator\EmailAddressValidator;

/**
 * NoEmailAddressValidator
 * Validates if a value is NOT an Email address
 */
class NoEmailAddressValidator extends EmailAddressValidator {

    /**
     * propertyName
     *
     * @var string
     */
    protected $propertyName = '';


    /**
     * Setter for propertyName
     *
     * @param string $propertyName
     *
     * @return void
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * If the given value is not an email address
     *
     * @param string $value The value
     *
     * @return boolean
     */
    public function isValid($value) {
        $result = true;

        if (!is_string($value) || $this->validEmail($value)) {
            $this->addError(
                $this->translateErrorMessage(
                    'validator.noEmailAddress.notvalid',
                    'typo3bb'
                ), 1479323755);
            $result = false;
        }

        return $result;
    }
}