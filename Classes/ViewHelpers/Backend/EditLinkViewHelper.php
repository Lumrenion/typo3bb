<?php
namespace LumIT\Typo3bb\ViewHelpers\Backend;


/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use LumIT\Typo3bb\Domain\Model\Board;
use LumIT\Typo3bb\Domain\Model\ForumCategory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Backend\Utility\BackendUtility as BackendUtilityCore;

/**
 * Based on the tx_examples VH from Francois Suter (Cobweb) <typo3@cobweb.ch>
 *
 * Inspired by a Blog Post from http://www.npostnik.de
 *
 */
class EditLinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {

    /**
     * @var string
     */
    protected $tagName = 'a';

    /**
     * Initialize arguments
     *
     * @return void
     * @api
     */
    public function initializeArguments() {
        $this->registerUniversalTagAttributes();
        $this->registerTagAttribute('name', 'string', 'Specifies the name of an anchor');
        $this->registerTagAttribute('target', 'string', 'Specifies where to open the linked document');
    }

    /**
     * Build Links to TCA Forms
     *
     * @param string $table
     * @param mixed $record
     * @param string $context either edit or new
     * @param string $returnUrl URL to return to
     * @param mixed $parent
     * @return string The <a> tag
     * @see \TYPO3\CMS\Backend\Utility::editOnClick()
     */
    public function render($table, $record = null, $context = 'edit', $returnUrl = '', $parent = null) {
        if ($context != 'edit' && $context != 'new') {
            throw new \InvalidArgumentException('Context must be either edit or new');
        }
        if ($record == null && $context != 'new') {
            throw new \InvalidArgumentException('Context edit requires a record');
        }
        if ($record instanceof AbstractDomainObject) {
            $record = $record->getUid();
        }
        if ($record == null && $context == 'new') {
            $record = GeneralUtility::_GET('id');
        }

        if (!empty($returnUrl)) {
            $returnUrl = rawurlencode($returnUrl);
        } else {
            $returnUrl = 'index.php?M=web_Typo3bbTxTypo3bbM1&id=' . (int)GeneralUtility::_GET('id') . '&moduleToken=' . GeneralUtility::_GET('moduleToken');
        }
        $uriParams = [
            'edit' => [$table => [$record => $context]],
            'returnUrl' => $returnUrl
        ];

        if ($context == 'new' && $parent != null && ($parent instanceof ForumCategory || $parent instanceof Board)) {
            $parentField = $parent instanceof ForumCategory ? 'forum_category' : 'parent_board';
            $uriParams['defVals'] = [$table => [$parentField => $parent->getUid()]];
        }

        $uri = BackendUtilityCore::getModuleUrl('record_edit', $uriParams);

        $this->tag->addAttribute('href', $uri);
        $this->tag->setContent($this->renderChildren());
        $this->tag->forceClosingTag(TRUE);
        return $this->tag->render();
    }

}