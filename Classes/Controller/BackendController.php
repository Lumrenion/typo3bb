<?php

namespace LumIT\Typo3bb\Controller;

use TYPO3\CMS\Backend\Clipboard\Clipboard;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Utility\BackendUtility as BackendUtilityCore;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\FormProtection\FormProtectionFactory;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Lang\LanguageService;

class BackendController extends ActionController
{

    /**
     * Page uid
     *
     * @var int
     */
    protected $pageUid = 0;

    /**
     * BackendTemplateContainer
     *
     * @var BackendTemplateView
     */
    protected $view;

    /**
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * Backend Template Container
     *
     * @var BackendTemplateView
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    protected $pageInformation = [];

    /**
     * @var \LumIT\Typo3bb\Domain\Repository\ForumCategoryRepository
     * @inject
     */
    protected $forumCategoryRepository = null;

    /**
     * Function will be called before every other action
     *
     */
    public function initializeAction()
    {
        $this->pageUid = (int)GeneralUtility::_GET('id');
        $this->pageInformation = BackendUtilityCore::readPageAccess($this->pageUid, '');
        parent::initializeAction();
    }

    public function indexAction()
    {
        $forumCategories = $this->forumCategoryRepository->findAll();

        $this->view->assignMultiple([
            'forumCategories' => $forumCategories,
            'pageUid' => $this->pageUid
        ]);
    }

    public function newForumCategoryAction()
    {
        $this->redirectToCreateNewRecord('tx_typo3bb_domain_model_forumcategory');
    }

    /**
     * Redirect to tceform creating a new record
     *
     * @param string $table table name
     */
    private function redirectToCreateNewRecord($table)
    {
        $pid = $this->pageUid;

        $returnUrl = 'index.php?M=web_Typo3bbTxTypo3bbM1&id=' . $this->pageUid . $this->getToken();
        $url = BackendUtilityCore::getModuleUrl('record_edit', [
            'edit[' . $table . '][' . $pid . ']' => 'new',
            'returnUrl' => $returnUrl
        ]);
        HttpUtility::redirect($url);
    }

    /**
     * Get a CSRF token
     *
     * @param bool $tokenOnly Set it to TRUE to get only the token, otherwise including the &moduleToken= as prefix
     * @return string
     */
    protected function getToken($tokenOnly = false)
    {
        $token = FormProtectionFactory::get()->generateToken('moduleCall', 'web_Typo3bbTxTypo3bbM1');
        if ($tokenOnly) {
            return $token;
        } else {
            return '&moduleToken=' . $token;
        }
    }

    public function newBoardAction()
    {
        $this->redirectToCreateNewRecord('tx_typo3bb_domain_model_board');
    }

    /**
     * Set up the doc header properly here
     *
     * @param ViewInterface $view
     */
    protected function initializeView(ViewInterface $view)
    {
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        /** @var BackendTemplateView $view */
        parent::initializeView($view);
        $view->getModuleTemplate()->getDocHeaderComponent()->setMetaInformation([]);

        $pageRenderer = $this->view->getModuleTemplate()->getPageRenderer();
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/DateTimePicker');
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8006000) {
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/ContextMenu');
        } else {
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/ClickMenu');
        }
//        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Typo3bb/BackendModule');
        $dateFormat = ($GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'] ? [
            'MM-DD-YYYY',
            'HH:mm MM-DD-YYYY'
        ] : ['DD-MM-YYYY', 'HH:mm DD-MM-YYYY']);
        $pageRenderer->addInlineSetting('DateTimePicker', 'DateFormat', $dateFormat);
        $pageRenderer->addCssFile(ExtensionManagementUtility::extRelPath('typo3bb') . 'Resources/Public/Css/backend.css');
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Typo3bb/Backend/NestedSortable');
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/Typo3bb/Backend/DragDrop');
        $this->createMenu();
        $this->createButtons();
    }

    /**
     * Create menu
     *
     */
    protected function createMenu()
    {
        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);

        $menu = $this->view->getModuleTemplate()->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('typo3bb');

        $actions = [
            ['action' => 'index', 'label' => 'typo3bbListing']
        ];

        foreach ($actions as $action) {
            $item = $menu->makeMenuItem()
                ->setTitle($this->getLanguageService()->sL('LLL:EXT:typo3bb/Resources/Private/Language/locallang_be.xlf:module.' . $action['label']))
                ->setHref($uriBuilder->reset()->uriFor($action['action'], [], 'Administration'))
                ->setActive($this->request->getControllerActionName() === $action['action']);
            $menu->addMenuItem($item);
        }

        $this->view->getModuleTemplate()->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
        if (is_array($this->pageInformation)) {
            $this->view->getModuleTemplate()->getDocHeaderComponent()->setMetaInformation($this->pageInformation);
        }
    }

    /**
     * Returns the LanguageService
     *
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Create the panel of buttons
     *
     */
    protected function createButtons()
    {
        $buttonBar = $this->view->getModuleTemplate()->getDocHeaderComponent()->getButtonBar();

        $uriBuilder = $this->objectManager->get(UriBuilder::class);
        $uriBuilder->setRequest($this->request);

        $buttons = [
            [
                'table' => 'tx_typo3bb_domain_model_forumcategory',
                'label' => 'module.createNewForumCategoryRecord',
                'action' => 'newForumCategory',
                'icon' => 'mimetypes-x-sys_category'
            ]
        ];
        foreach ($buttons as $key => $tableConfiguration) {
            if ($this->getBackendUser()->isAdmin() || GeneralUtility::inList($this->getBackendUser()->groupData['tables_modify'],
                    $tableConfiguration['table'])
            ) {
                $title = $this->getLanguageService()->sL('LLL:EXT:typo3bb/Resources/Private/Language/locallang_be.xlf:' . $tableConfiguration['label']);
                $viewButton = $buttonBar->makeLinkButton()
                    ->setHref($uriBuilder->reset()->setRequest($this->request)->uriFor($tableConfiguration['action'],
                        [], 'Backend'))
                    ->setDataAttributes([
                        'toggle' => 'tooltip',
                        'placement' => 'bottom',
                        'title' => $title
                    ])
                    ->setTitle($title)
                    ->setIcon($this->iconFactory->getIcon($tableConfiguration['icon'], Icon::SIZE_SMALL,
                        'overlay-new'));
                $buttonBar->addButton($viewButton, ButtonBar::BUTTON_POSITION_LEFT, 2);
            }
        }

        $clipBoard = GeneralUtility::makeInstance(Clipboard::class);
        $clipBoard->initializeClipboard();
        $elFromTable = $clipBoard->elFromTable('tx_typo3bb_domain_model_board');
        if (!empty($elFromTable)) {
            $viewButton = $buttonBar->makeLinkButton()
                ->setHref($clipBoard->pasteUrl('', $this->pageUid))
                ->setOnClick('return ' . $clipBoard->confirmMsg('pages',
                        BackendUtilityCore::getRecord('pages', $this->pageUid), 'into',
                        $elFromTable))
                ->setTitle($this->getLanguageService()->sL('LLL:EXT:lang/locallang_mod_web_list.xlf:clip_pasteInto'))
                ->setIcon($this->iconFactory->getIcon('actions-document-paste-into', Icon::SIZE_SMALL));
            $buttonBar->addButton($viewButton, ButtonBar::BUTTON_POSITION_LEFT, 4);
        }

        // Refresh
        $path = VersionNumberUtility::convertVersionNumberToInteger(TYPO3_branch) >= VersionNumberUtility::convertVersionNumberToInteger('8.6') ? 'Resources/Private/Language/' : '';
        $refreshButton = $buttonBar->makeLinkButton()
            ->setHref(GeneralUtility::getIndpEnv('REQUEST_URI'))
            ->setTitle($this->getLanguageService()->sL('LLL:EXT:lang/' . $path . 'locallang_core.xlf:labels.reload'))
            ->setIcon($this->iconFactory->getIcon('actions-refresh', Icon::SIZE_SMALL));
        $buttonBar->addButton($refreshButton, ButtonBar::BUTTON_POSITION_RIGHT);
    }

    /**
     * Get backend user
     *
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
}