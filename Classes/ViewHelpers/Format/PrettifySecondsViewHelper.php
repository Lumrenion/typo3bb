<?php
namespace LumIT\Typo3bb\ViewHelpers\Format;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;


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

class PrettifySecondsViewHelper extends AbstractViewHelper implements CompilableInterface {
    /**
     * @param int   $seconds
     * @param bool  $shortcut
     * @return string
     */
    public function render($seconds = null, $shortcut = false) {
        return static::renderStatic(
            [
                'seconds' => $seconds,
                'shortcut' => $shortcut
            ],
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, \TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface $renderingContext) {
        $time = self::secondsToTime($arguments['seconds']);

        $timeString = '';
        $separator = '';
        $unskip = false;
        foreach (['days','h','i'] as $timeUnit) {
            if ($time->$timeUnit == 0 && !$unskip) {
                continue;
            }

            $unskip = true;
            if ($arguments['shortcut']) {
                $localizedTimeUnit = LocalizationUtility::translate(
                    'viewHelpers.format.prettifySeconds.' . $timeUnit . '.shortcut',
                    'typo3bb',
                    [$time->$timeUnit]
                );
                $separator = ' ';
            } else {
                $pluralSingular = ($time->$timeUnit == 1 ? 'singular' : 'plural');
                $localizedTimeUnit = LocalizationUtility::translate(
                    'viewHelpers.format.prettifySeconds.' . $timeUnit . '.' . $pluralSingular,
                    'typo3bb',
                    [$time->$timeUnit]
                );
                $separator = ', ';
            }

            $timeString .= $localizedTimeUnit . $separator;
        }

        $timeString = substr($timeString, 0, strlen($timeString) - strlen($separator));
        return $timeString;
    }

    /**
     * @param int $seconds
     * @return bool|\DateInterval
     */
    protected static function secondsToTime($seconds) {
        $then = new \DateTime(date('Y-m-d H:i:s', 0));
        $now = new \DateTime(date('Y-m-d H:i:s', $seconds));
        $diff = $then->diff($now);

        return $diff;
    }
}