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
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\View\StandaloneView;


/**
 * EmailUtility
 */
class EmailUtility
{

    /**
     * Returns a MailMessage with the sender set from typoscript settings.
     *
     * @return MailMessage
     */
    public static function getMailMessage()
    {
        $settings = PluginUtility::_getPluginSettings();
        /** @var MailMessage $mailMessage */
        $mailMessage = self::_getObjectManager()->get(MailMessage::class);

        $mailMessage->setFrom(
            $settings['email']['fromEmail'],
            $settings['email']['fromName']
        );

        return $mailMessage;
    }

    /**
     * @return ObjectManager
     */
    protected static function _getObjectManager()
    {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }

    /**
     * Returns the rendered email body by template name. The Template must be located in templateRootPath/Email/$templateName
     *
     * @param String $templateName
     * @param array $variablesToAssign
     * @param \TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext $controllerContext
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager
     * @return String
     */
    public static function getEmailBody($templateName, array $variablesToAssign, $controllerContext)
    {
        /** @var StandaloneView $emailView */
        $emailView = GeneralUtility::makeInstance(StandaloneView::class);
        $emailView->setControllerContext($controllerContext);
        $emailView->setFormat('html');
        $extbaseFrameworkConfiguration = GeneralUtility::makeInstance(ConfigurationManager::class)->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK,
            'typo3bb');
        $emailView->setTemplateRootPaths($extbaseFrameworkConfiguration['view']['templateRootPaths']);
        $emailView->setLayoutRootPaths($extbaseFrameworkConfiguration['view']['layoutRootPaths']);
        $emailView->setPartialRootPaths($extbaseFrameworkConfiguration['view']['partialRootPaths']);
        $variablesToAssign['settings'] = $extbaseFrameworkConfiguration['settings'];
        $emailView->setTemplate('Email/' . $templateName);

        return $emailView->assignMultiple($variablesToAssign)->render();
    }

    /**
     * The same email template is expected to differ in only some variables for each receiver.
     * The more complex a fluid template is, the more cost intensive it is to render it for each user anew.
     * Therefor this method replaces standard markers.
     * In your template, use markers just like you would use fluid variables, e.g. ###receiver.displayName###
     * The correct property will be inserted by the property path, so $variables = ['receiver' => FrontendUser]
     *
     * @param string $emailBody
     * @param mixed $variables
     *
     * @return string
     */
    public static function substituteMarkers($emailBody, $variables)
    {
        return preg_replace_callback('/###([A-Z0-9_\.]*)###/is', function ($match) use ($variables) {
            return ObjectAccess::getPropertyPath($variables, $match[1]);
        }, $emailBody);
    }
}