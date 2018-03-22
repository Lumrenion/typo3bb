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
            $file = GeneralUtility::getFileAbsFileName($img->getPublicUrl());
            if (((file_exists($file) || file_exists(constant('PATH_site') . $file)) && is_file($file))) {
                $content = parent::render();
            } else {
                throw new \Exception('Render default content if image does not exist');
            }
        } catch (\Exception $e) {
            $content = $this->renderChildren();
        }

        return $content;
    }
}
