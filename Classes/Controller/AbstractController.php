<?php

namespace LumIT\Typo3bb\Controller;


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

use LumIT\Typo3bb\Exception\AbstractException;
use LumIT\Typo3bb\Utility\FrontendUserUtility;
use LumIT\Typo3bb\Utility\RteUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\NotFoundView;
use TYPO3\CMS\Extbase\Mvc\Web\Response;


/**
 * BoardController
 */
abstract class AbstractController extends ActionController
{

    /**
     * @var \LumIT\Typo3bb\Domain\Model\FrontendUser
     */
    protected $frontendUser = null;

    /**
     * @var \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend
     */
    protected $cacheManager = null;

    /**
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    protected $cObj = null;

    /**
     * persistence manager
     *
     * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
     * @inject
     */
    protected $persistenceManager;

    public function initializeAction()
    {
        $this->cacheManager = GeneralUtility::makeInstance(CacheManager::class)->getCache("typo3bb");
        $this->cObj = $this->configurationManager->getContentObject();
        $this->frontendUser = FrontendUserUtility::getCurrentUser();

        if ($this->response instanceof Response) {
            $ckeditorSettings = RteUtility::prepareSmilies($this->settings['emoticonPath']);
            $ckeditorSettings['language'] = $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
            )['config.']['tinymceLangKey'] ?: 'en_GB';

            $this->response->addAdditionalHeaderData('
<script>
window.LumIT = window.LumIT || {};
window.LumIT.Typo3bb = window.LumIT.Typo3bb || (window.LumIT.Typo3bb = {});
window.LumIT.Typo3bb.Constants = window.LumIT.Typo3bb.Constants || (window.LumIT.Typo3bb.Constants = {});
window.LumIT.Typo3bb.Constants["ckeditorSettings"] = ' . json_encode($ckeditorSettings) . ';
</script>'
            );
        }
    }

    /**
     *
     * Calls a controller action. This method wraps the callActionMethod method of
     * the parent \TYPO3\CMS\Extbase\Mvc\Controller\ActionController class. It catches all
     * Exceptions that might be thrown inside one of the action methods.
     * This method ONLY catches exceptions that belong to the typo3_forum extension.
     * All other exceptions are not caught.
     *
     * @return void
     *
     */
    protected function callActionMethod()
    {
        try {
            parent::callActionMethod();
        } catch (AbstractException $e) {
            $this->handleError($e);
        }
    }

    protected function handleError(AbstractException $e)
    {
        $this->request->setControllerName('Default');
        $this->request->setControllerActionName('error');
        if ($this->view instanceof NotFoundView) {
            $this->view = $this->resolveView();
        }
        $controllerContext = $this->buildControllerContext();
        $this->view->setControllerContext($controllerContext);

        $this->view->assign('exception', $e);
        $content = $this->view->render('error');
        $this->response->appendContent($content);
    }
}