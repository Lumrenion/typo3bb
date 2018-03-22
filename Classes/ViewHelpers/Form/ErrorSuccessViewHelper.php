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
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ErrorSuccessViewHelper
 */
class ErrorSuccessViewHelper extends AbstractViewHelper
{
    /**
     * If the form form has errors, if
     *  the field has no errors: returns success-classname
     *  the field has errors:    returns error-classname
     *
     * @param string $for The name of the error name (e.g. argument name or property name). This can also be a property path (like blog.title), and will then only display the validation errors of that property.
     * @param string $success The class name to return if there is no error in field
     * @param string $error The class name to return if there are errors in field
     * @return string Rendered string
     * @api
     */
    public function render($for, $success = 'has-success', $error = 'has-error')
    {
        $validationResults = $this->controllerContext->getRequest()->getOriginalRequestMappingResults();
        if (!$validationResults->hasErrors()) {
            return '';
        }

        $validationResults = $validationResults->forProperty($for);

        return $validationResults->hasErrors() ? $error : $success;
    }
}