<?php
namespace LumIT\Typo3bb\Extensions\SfRegister\Validation\Validator;


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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * UniqueValidator
 * Extends the SfRegister UniqueValidator by adding a check of the username or display name property for being unique among each others
 */
class UniqueValidator extends \Evoweb\SfRegister\Validation\Validator\UniqueValidator {
    /**
     * @var \LumIT\Typo3bb\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $userRepository = null;

    /**
     * @var array
     */
    protected $supportedOptions = array(
        'global'     => array(true, 'Whether to check uniqueness globally', 'boolean'),
        'context'   => array('create', 'create or edit. If edit and if the field was not changed, count = 1 and the validator would fail', 'string')
    );

    /**
     * If the given name or username is valid
     *
     * @param string $value The value
     *
     * @return boolean
     */
    public function isValid($value) {
        $result = true;

        if ($this->propertyName == 'username' || $this->propertyName == 'tx_typo3bb_display_name') {
            $propertyToCheck = [$this->propertyName, $this->getOtherField()];
        } else {
            $propertyToCheck = $this->propertyName;
        }

        if ($this->userRepository->countByFieldNotCurrentUser($propertyToCheck, $value, !$this->options['global'])) {
            $this->addError(
                LocalizationUtility::translate(
                    'validator.unique.notValid',
                    'typo3bb'
                ),
                1479323753
            );
            $result = false;
        }

        return $result;
    }

    /**
     * Returns the opposite field of the current propertyName. username <> name
     *
     * @return string
     */
    protected function getOtherField() {
        return $this->propertyName == 'username' ? 'tx_typo3bb_display_name' : 'username';
    }
}