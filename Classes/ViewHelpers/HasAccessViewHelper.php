<?php

namespace LumIT\Typo3bb\ViewHelpers;

use LumIT\Typo3bb\Utility\SecurityUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper;


/**
 * This Viewhelper returns the value of the key of an array
 *
 * @package TYPO3
 * @subpackage Fluid
 * @version
 */
class HasAccessViewHelper extends AbstractConditionViewHelper
{

    /**
     * This method decides if the condition is TRUE or FALSE. It can be overriden in extending viewhelpers to adjust functionality.
     *
     * @param array $arguments ViewHelper arguments to evaluate the condition for this ViewHelper, allows for flexiblity in overriding this method.
     * @return bool
     */
    static protected function evaluateCondition($arguments = null)
    {
        return (SecurityUtility::checkAccessPermission($arguments['key'],
                $arguments['object']) || parent::evaluateCondition($arguments));
    }

    public function initializeArguments()
    {
        $this->registerArgument('condition', 'boolean', 'Condition to avaluate', false, false);
        $this->registerArgument('key', 'string', 'The key of the permission stack. Might be "Board.view.XYZ"', true,
            '');
        $this->registerArgument('object', 'mixed', 'The object to validate', false, null);
    }
}