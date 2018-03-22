<?php

namespace LumIT\Typo3bb\ViewHelpers;

use LumIT\Typo3bb\Domain\Model\Board;
use LumIT\Typo3bb\Domain\Model\ForumCategory;
use LumIT\Typo3bb\Domain\Model\Topic;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;


/**
 * This Viewhelper returns a rendered breadcrumb navigation
 *
 * @package TYPO3
 * @subpackage Fluid
 * @version
 */
class BreadcrumbViewHelper extends AbstractTagBasedViewHelper
{

    /**
     * @var string $tagName
     */
    protected $tagName = 'ol';

    /**
     * @var array $settings
     */
    protected $settings = null;

    /**
     * @var \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder $uriBuilder
     */
    protected $uriBuilder = null;

    public function initialize()
    {
        parent::initialize();
        $this->settings = $this->templateVariableContainer->get('settings');
        $this->uriBuilder = $this->controllerContext->getUriBuilder();
    }

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        $this->registerArgument('rootline', 'array', 'The rootline to render', true);
    }

    /**
     * @return string|bool
     */
    public function render()
    {
        $class = 'breadcrumb';
        if ($this->arguments['class']) {
            $class .= ' ' . $this->arguments['class'];
        }
        $this->tag->addAttribute('class', $class);

        $rootline = $this->arguments['rootline'];

        $uri = $this->uriBuilder->reset()->setTargetPageUid($this->settings['PID']['Forum']['homepage'])->build();
        $tagContent = $this->renderBreadcrumbItem($uri,
            LocalizationUtility::translate('forum.breadcrumb.home', 'typo3bb', []),
            $this->settings['breadcrumb']['homeIconClass']);
        foreach ($rootline as $item) {
            $tagContent .= $this->processBreadcrumbItem($item);
        }
        $this->tag->setContent($tagContent);
        return $this->tag->render();
    }

    protected function renderBreadcrumbItem($uri, $fullTitle, $iconClass)
    {
        $titleMaxChars = (int)$this->settings['breadcrumb']['itemMaxChars'];
        if ($titleMaxChars == 0 || strlen($fullTitle) < $titleMaxChars) {
            $title = $fullTitle;
        } else {
            $title = substr($fullTitle, 0, $titleMaxChars) . "&hellip;";
        }

        return '<li><a href="' . $uri . '" title="' . $fullTitle . '"><span class="' . $iconClass . '" aria-hidden="true"></span>' . $title . '</a></li>';
    }

    protected function processBreadcrumbItem($item)
    {
        if ($item instanceof LazyLoadingProxy) {
            $item = $item->_loadRealInstance();
        }
        $extensionName = 'typo3bb';
        $pluginName = 'forum';
        if ($item instanceof Board) {
            $section = '';
            $controller = 'Board';
            $action = 'show';
            $arguments = ['board' => $item];
            $fullTitle = $item->getTitle();
            $iconClass = $this->settings['breadcrumb']['boardIconClass'];
        } elseif ($item instanceof Topic) {
            $section = '';
            $controller = 'Topic';
            $action = 'show';
            $arguments = ['topic' => $item];
            $fullTitle = $item->getTitle();
            $iconClass = $this->settings['breadcrumb']['topicIconClass'];
        } elseif ($item instanceof ForumCategory) {
            $section = 'forum-category-' . $item->getUid();
            $controller = 'ForumCategory';
            $action = 'list';
            $arguments = [];
            $fullTitle = $item->getTitle();
            $iconClass = $this->settings['breadcrumb']['forumCategoryIconClass'];
        } else {
            throw new \InvalidArgumentException('Only objects of type \LumIT\Typo3bb\Domain\Model\Board and \LumIT\Typo3bb\Domain\Model\Topic are allowed in breadcrumb.');
        }

        $uri = $this->uriBuilder->reset()->setTargetPageUid($this->settings['PID']['Forum']['homepage'])->setSection($section)
            ->uriFor($action, $arguments, $controller, $extensionName, $pluginName);

        return $this->renderBreadcrumbItem($uri, $fullTitle, $iconClass);
    }
}