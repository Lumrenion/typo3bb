<?php
namespace LumIT\Typo3bb\Security\AccessValidator;
use LumIT\Typo3bb\Domain\Model\Board;
use LumIT\Typo3bb\Domain\Model\Post;
use LumIT\Typo3bb\Domain\Model\Topic;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

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

class IsBoardModeratorAccessValidator extends AbstractAccessValidator{

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Board|\LumIT\Typo3bb\Domain\Model\Topic|\LumIT\Typo3bb\Domain\Model\Post $objectToValidate
     * @return bool
     * @throws IllegalObjectTypeException
     */
    public function validate($objectToValidate) {
        if ($objectToValidate instanceof LazyLoadingProxy) {
            $objectToValidate = $objectToValidate->_loadRealInstance();
        }
        if($objectToValidate instanceof Board) {
            return $this->validateBoard($objectToValidate);
        } elseif($objectToValidate instanceof Topic) {
            return $this->validateTopic($objectToValidate);
        } elseif($objectToValidate instanceof Post) {
            return $this->validatePost($objectToValidate);
        } else {
            throw new IllegalObjectTypeException('Object to validate must be of type ' . Board::class . ', ' . Topic::class . ' or ' . Post::class . '!');
        }

        return false;
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Board $boardToValidate
     * @return bool
     */
    protected function validateBoard($boardToValidate) {
        if($boardToValidate->getParentBoard() != null) {
            if($this->validateBoard($boardToValidate->getParentBoard())) {
                return true;
            }
        }

        foreach (array_filter(explode(',', $boardToValidate->getModerators()), 'strlen') as $moderatorUid) {
            if (trim($moderatorUid) == $GLOBALS['TSFE']->fe_user->user['uid']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topicToValidate
     * @return bool
     */
    protected function validateTopic($topicToValidate) {
        return $this->validateBoard($topicToValidate->getBoard());
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Post $postToValidate
     * @return bool
     */
    protected function validatePost($postToValidate) {
        return $this->validateTopic($postToValidate->getTopic());
    }
}