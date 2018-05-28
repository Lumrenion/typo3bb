<?php

namespace LumIT\Typo3bb\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This ViewHelper extends the fluid ImageViewHelper by rendering child content when an error occurs
 * @package LumIT\Typo3bb\ViewHelpers
 */
class ImageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper
{


    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('as', 'string', 'Image tag passed to children. Empty if image could not be rendered', true);
    }
    /**
     * Resizes a given image (if required) and renders the respective img tag
     *
     * @see https://docs.typo3.org/typo3cms/TyposcriptReference/ContentObjects/Image/
     *
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
     * @return string Rendered tag
     */
    public function render()
    {

        try {
            $img = $this->imageService->getImage($this->arguments['src'], $this->arguments['image'],
                $this->arguments['treatIdAsReference']);
            $file = urldecode(GeneralUtility::getFileAbsFileName($img->getPublicUrl()));
            if (((file_exists($file) || file_exists(constant('PATH_site') . $file)) && is_file($file))) {
                $image = parent::render();
            } else {
                throw new \Exception('Render default content if image does not exist');
            }
        } catch (\Exception $e) {
            $image = "";
        }

        $this->renderingContext->getVariableProvider()->add($this->arguments['as'], $image);
        $content = $this->renderChildren();
        $this->renderingContext->getVariableProvider()->remove($this->arguments['as']);

        return $content;
    }
}
