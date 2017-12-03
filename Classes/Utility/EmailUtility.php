<?php
namespace LumIT\Typo3bb\Utility;


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
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;


/**
 * EmailUtility
 */
class EmailUtility {

    /**
     * @return MailMessage
     */
    public static function getMailMessage() {
        $settings = PluginUtility::_getPluginSettings();
        /** @var MailMessage $mailMessage */
        $mailMessage = self::_getObjectManager()->get(MailMessage::class);

        $mailMessage->addTo($settings['email']['fromEmail'], $settings['email']['fromName']);
        $mailMessage->addFrom(
            $settings['email']['fromEmail'],
            $settings['email']['fromName']
        );

        return $mailMessage;
    }

    /**
     * @param String $templateName
     * @param array $variablesToAssign
     * @param \TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext $controllerContext
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager
     * @return String
     */
    public static function getEmailBody($templateName, array $variablesToAssign, $controllerContext) {
        /** @var StandaloneView $emailView */
        $emailView = GeneralUtility::makeInstance(StandaloneView::class);
        $emailView->setControllerContext($controllerContext);
        $emailView->setFormat('html');
        $extbaseFrameworkConfiguration = GeneralUtility::makeInstance(ConfigurationManager::class)->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, 'typo3bb');
        $emailView->setTemplateRootPaths($extbaseFrameworkConfiguration['view']['templateRootPaths']);
        $emailView->setLayoutRootPaths($extbaseFrameworkConfiguration['view']['layoutRootPaths']);
        $emailView->setPartialRootPaths($extbaseFrameworkConfiguration['view']['partialRootPaths']);
        $emailView->setTemplate('Email/' . $templateName);

        return $emailView->assignMultiple($variablesToAssign)->render();
    }

    /**
     * @return ObjectManager
     */
    protected static function _getObjectManager() {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }
}