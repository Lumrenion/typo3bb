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

use LumIT\Typo3bb\Domain\Model\Attachment;
use LumIT\Typo3bb\Utility\SecurityUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * AttachmentController
 */
class AttachmentController extends AbstractController {

    /**
     * postRepository
     *
     * @var \LumIT\Typo3bb\Domain\Repository\PostRepository
     * @inject
     */
    protected $postRepository = NULL;

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Attachment $attachment
     * @param string $returnTo
     */
    public function removeAction(Attachment $attachment, string $returnTo = '') {
        $post = $attachment->getPost();

        SecurityUtility::assertAccessPermission('Post.edit', $post);

        $attachment->getFile()->getOriginalResource()->getOriginalFile()->delete();
        $post->removeAttachment($attachment);

        $this->postRepository->update($post);

        switch ($returnTo) {
            case 'editTopic':
                $this->redirect('edit', 'Topic', null, ['topic' => $post->getTopic()]);
                break;
            case 'editPost':
            default:
                $this->redirect('edit', 'Post', null, ['post' => $post]);
                break;
        }
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Attachment $attachment
     */
    public function downloadAction(Attachment $attachment) {
        SecurityUtility::assertAccessPermission('Topic.show', $attachment->getPost()->getTopic());
        $attachment->increaseDownloadCount();
        $this->postRepository->update($attachment->getPost());

        ob_start();
        $file = $attachment->getFile()->getOriginalResource()->getOriginalFile();
        $file->getStorage()->dumpFileContents($file, true, $attachment->getOriginalFileName());

        /** @var PersistenceManager $persistenceManager */
        $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
        $persistenceManager->persistAll();

        exit();
    }
}