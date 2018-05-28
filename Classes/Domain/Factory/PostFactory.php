<?php

namespace LumIT\Typo3bb\Domain\Factory;

/*
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


use LumIT\Typo3bb\Domain\Model\Attachment;
use LumIT\Typo3bb\Domain\Model\FileReference;
use LumIT\Typo3bb\Domain\Model\Post;
use LumIT\Typo3bb\Utility\RteUtility;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class PostFactory
 * @package LumIT\Typo3bb\Domain\Factory
 */
class PostFactory
{
    /**
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     * @param \LumIT\Typo3bb\Domain\Model\Post $post
     * @param array $attachments
     */
    public static function createPost($topic, $post, $attachments = [])
    {
        $topic->addPost($post);
        $post->setTopic($topic);
        $post->setText(RteUtility::sanitizeHtml($post->getText()));

        if (!empty($attachments)) {
            self::processAttachments($post, $attachments);
        }
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Post $post
     * @param array $attachments
     */
    public static function processAttachments(Post $post, $attachments)
    {
        $objectManager = self::_getObjectManagerInstance();
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = $objectManager->get(ConfigurationManager::class);
        $settings = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            'typo3bb')['settings'];

        self::_processAttachmentsInternal($post, $attachments, $settings['attachmentsPath']);
    }

    /**
     * @return ObjectManager
     */
    protected static function _getObjectManagerInstance()
    {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Post $post
     * @param array $attachments
     * @param string $attachmentsPath
     * @param string $duplicationBehaviour {@link \TYPO3\CMS\Core\Resource\DuplicationBehaviour}
     * @param bool $removeOriginal
     */
    public static function _processAttachmentsInternal(
        Post $post,
        $attachments,
        $attachmentsPath,
        string $duplicationBehaviour = DuplicationBehavior::RENAME,
        bool $removeOriginal = true
    ) {
        $objectManager = self::_getObjectManagerInstance();

        $attachmentsFolder = self::getAttachmentsFolder($attachmentsPath);
        $attachmentObjects = [];

        foreach ($attachments as $attachment) {
            if (is_array($attachment)) {
                if ($attachment['error'] == UPLOAD_ERR_OK) {
                    $movedFile = self::addFileToFolder($attachmentsFolder, $attachment['tmp_name'],
                        hash_file('md5', $attachment['tmp_name']), $duplicationBehaviour, $removeOriginal);

                    $attachmentObject = new Attachment();
                    $attachmentObject->setPost($post);
                    $attachmentObject->setOriginalFileName($attachment['name']);
                    if (!empty($attachment['downloadCount']) && $attachment['downloadCount'] > 0) {
                        $attachmentObject->setDownloadCount($attachment['downloadCount']);
                    }
                    if (!empty($attachment['pid'] && $attachment['pid'] >= 0)) {
                        $attachmentObject->setPid($attachment['pid']);
                    }

                    /** @var FileReference $fileReference */
                    $fileReference = $objectManager->get(FileReference::class);
                    $fileReference->setFile($movedFile);
                    $attachmentObject->setFile($fileReference);
                    $post->addAttachment($attachmentObject);
                    $attachmentObjects[] = $attachmentObject;
                }
            }
        }
    }

    /**
     * @param string $attachmentsPath
     * @return \TYPO3\CMS\Core\Resource\Folder
     */
    public static function getAttachmentsFolder($attachmentsPath)
    {
        $absoluteAttachmentsPath = GeneralUtility::getFileAbsFileName($attachmentsPath);
        if (!is_dir($absoluteAttachmentsPath)) {
            mkdir($absoluteAttachmentsPath, 0777, true);
        }
        /** @var Folder $attachmentsFolder */
        return ResourceFactory::getInstance()->retrieveFileOrFolderObject($attachmentsPath);
    }

    /**
     * @param Folder $folder
     * @param string $filePath
     * @param string $newName
     * @param string $duplicationBehavior
     * @param bool $removeOriginal
     * @return \TYPO3\CMS\Core\Resource\File|\TYPO3\CMS\Core\Resource\FileInterface
     */
    public static function addFileToFolder(
        Folder $folder,
        string $filePath,
        string $newName = '',
        string $duplicationBehavior = DuplicationBehavior::RENAME,
        bool $removeOriginal = true
    ) {
        if ($removeOriginal) {
            return $folder->addFile($filePath, $newName, DuplicationBehavior::RENAME);
        } else {
            return $folder->getStorage()->addFile($filePath, $folder, $newName, $duplicationBehavior, $removeOriginal);
        }
    }
}