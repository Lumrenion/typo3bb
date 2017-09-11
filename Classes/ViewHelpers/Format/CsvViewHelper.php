<?php
namespace LumIT\Typo3bb\ViewHelpers\Format;

use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;


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

class CsvViewHelper extends AbstractViewHelper {
    /**
     * @param array|\ArrayAccess $subject
     * @param string    $property
     * @param string    $glue
     * @param bool      $endingAnd
     * @param string    $as
     * @return string
     */
    public function render($subject, $property = null, string $glue = ', ', bool $endingAnd = false, $as = null) {
        return static::renderStatic(
            $this->arguments,
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param \TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
     * @return string
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, \TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface $renderingContext) {
        $subject = $arguments['subject'];
        $property = $arguments['property'];
        $glue = $arguments['glue'];
        $endingAnd = $arguments['endingAnd'];
        $as = $arguments['as'];

        if ($property != null) {
            $subject = static::getProcessedSubject($subject, $property);
        } else {
            if ($subject instanceof \ArrayAccess) {
                $subject = $subject->toArray();
            }
            if ($as != null) {
                $templateVariableContainer = $renderingContext->getTemplateVariableContainer();
                $subject = array_map(function($singleSubject) use ($as, $templateVariableContainer, $renderChildrenClosure) {
                    $templateVariableContainer->add($as, $singleSubject);
                    $output = $renderChildrenClosure();
                    $templateVariableContainer->remove($as);
                    return $output;
                }, $subject);
            }
        }

        return static::getCsvString($subject, $glue, $endingAnd);
    }

    /**
     * @param array|\ArrayAccess    $subject
     * @param string                $property
     * @param string                $glue
     * @param bool                  $endingAnd
     * @return string
     */
    public static function getCsv($subject, $property = null, string $glue = ', ', bool $endingAnd = false) {
        $subject = static::getProcessedSubject($subject, $property);
        return static::getCsvString($subject, $glue, $endingAnd);
    }

    /**
     * @param array|\ArrayAccess    $subject
     * @param string                $property
     * @return array
     */
    protected static function getProcessedSubject($subject, $property) {
        if ($subject instanceof \ArrayAccess || !is_null($property)) {
            $arraySubject = [];
            foreach ($subject as $subjectItem) {
                $arraySubject[] = ObjectAccess::getPropertyPath($subjectItem, $property);
            }
            $subject = $arraySubject;
        }
        return preg_grep('/^\s*\z/', $subject, PREG_GREP_INVERT);
    }

    /**
     * @param array     $subject
     * @param string    $glue
     * @param boolean   $endingAnd
     * @return string
     */
    protected static function getCsvString($subject, $glue, $endingAnd) {
        $subject = array_filter(array_map('trim', $subject));
        $last = array_pop($subject);
        $subject = implode($glue, $subject);

        $lastGlue = $endingAnd ? ' ' . LocalizationUtility::translate('viewHelpers.format.csv.endingAnd', 'typo3bb') . ' ' : $glue;
        return implode($lastGlue, array_filter(array_map('trim', [$subject, $last])));
    }
}