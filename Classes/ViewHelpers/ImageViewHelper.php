<?php
namespace LumIT\Typo3bb\ViewHelpers;

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\AbstractFileFolder;

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
     * @param string $src a path to a file, a combined FAL identifier or an uid (int). If $treatIdAsReference is set, the integer is considered the uid of the sys_file_reference record. If you already got a FAL object, consider using the $image parameter instead
     * @param string $width width of the image. This can be a numeric value representing the fixed width of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
     * @param string $height height of the image. This can be a numeric value representing the fixed height of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
     * @param int $minWidth minimum width of the image
     * @param int $minHeight minimum height of the image
     * @param int $maxWidth maximum width of the image
     * @param int $maxHeight maximum height of the image
     * @param bool $treatIdAsReference given src argument is a sys_file_reference record
     * @param FileInterface|AbstractFileFolder $image a FAL object
     * @param string|bool $crop overrule cropping of image (setting to FALSE disables the cropping set in FileReference)
     * @param bool $absolute Force absolute URL
     *
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
     * @return string Rendered tag
     */
    public function render(
    $src = null,
    $width = null,
    $height = null,
    $minWidth = null,
    $minHeight = null,
    $maxWidth = null,
    $maxHeight = null,
    $treatIdAsReference = false,
    $image = null,
    $crop = null,
    $absolute = false
    ) {

        try {
            $img = $this->imageService->getImage($src, $image, $treatIdAsReference);
            $file = GeneralUtility::getFileAbsFileName($img->getPublicUrl());
            if (((file_exists($file) || file_exists(constant('PATH_site') . $file)) && is_file($file))) {
                $content = parent::render($src, $width, $height, $minWidth, $minHeight, $maxWidth, $maxHeight,
                    $treatIdAsReference, $image, $crop, $absolute);
            } else {
                throw new \Exception('Render default content if image does not exist');
            }
        } catch (\Exception $e) {
            $content =  $this->renderChildren();
        }

        return $content;
    }
}
