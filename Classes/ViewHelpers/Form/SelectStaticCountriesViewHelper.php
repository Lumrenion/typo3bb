<?php
namespace LumIT\Typo3bb\ViewHelpers\Form;


/***************************************************************
 * Copyright notice
 *
 * (c) 2016 Philipp Seßner <philipp.sessner@gmail.com>
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
 * Viewhelper to render a selectbox with values of static info tables countries
 * <code title="Usage">
 * {namespace register=Evoweb\SfRegister\ViewHelpers}
 * <register:form.SelectStaticCountries
 *    name="country" optionLabelField="cnShortDe"/>
 * </code>
 * <code title="Optional label field">
 * {namespace register=Evoweb\SfRegister\ViewHelpers}
 * <register:form.SelectStaticCountries
 *    name="country" optionLabelField="cnShortDe"/>
 * </code>
 */
class SelectStaticCountriesViewHelper extends \Evoweb\SfRegister\ViewHelpers\Form\SelectStaticCountriesViewHelper {
    public function initialize() {
        parent::initialize();
        $this->arguments['options'] = $this->arguments['options']->toArray();
        array_unshift($this->arguments['options'], [$this->arguments['optionValueField'] => '', $this->arguments['optionLabelField'] => '']);
    }
}