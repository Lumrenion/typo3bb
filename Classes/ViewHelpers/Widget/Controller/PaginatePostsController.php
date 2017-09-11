<?php
namespace LumIT\Typo3bb\ViewHelpers\Widget\Controller;

/*                                                                        *
 * This script is backported from the TYPO3 Flow package "TYPO3.Fluid".   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use LumIT\Typo3bb\Domain\Model\Post;
use LumIT\Typo3bb\Domain\Model\Reader;

use LumIT\Typo3bb\Domain\Repository\ReaderRepository;

use LumIT\Typo3bb\Utility\FrontendUserUtility;


/**
 * Class PaginatePostsController
 */
class PaginatePostsController extends PaginateBaseController {
    /**
     * @var array
     */
    protected $configuration = array(
        'itemsPerPage' => 10,
        'insertAbove' => false,
        'insertBelow' => true,
        'maximumNumberOfLinks' => 99,
        'addQueryStringMethod' => '',
        'section' => '',
        'currentPost' => ''
    );

    public function initializeIndexAction() {
        if (!empty($this->configuration['currentPost'])) {
            $itemsPerPage = (int)$this->configuration['itemsPerPage'];
            $positionOfPost = $this->objects->getPosition($this->configuration['currentPost']);
            $this->currentPage = (floor($positionOfPost / $itemsPerPage)) + 1;
        }
    }

    /**
     * @param $modifiedObjects
     */
    public function processModifiedObjects($modifiedObjects) {
        if($GLOBALS['TSFE']->loginUser) {
            $lastNotNullIndex = 0;
            foreach ($modifiedObjects as $index => $modifiedObject) {
                if (is_null($modifiedObject)) {
                    break;
                }
                $lastNotNullIndex = $index;
            }
            /** @var Post $lastSeenPost */
            $lastSeenPost = $modifiedObjects[$lastNotNullIndex];

            $user = FrontendUserUtility::getCurrentUser();
            $reader = new Reader();
            $reader->setUser($user);
            $reader->setTopic($lastSeenPost->getTopic());
            $reader->setPost($lastSeenPost);
            /** @var ReaderRepository $readerRepository */
            $readerRepository = $this->objectManager->get(ReaderRepository::class);
            $readerRepository->add($reader);
            //TODO clear cache of board and all its parent boards show-Action and ForumCategory list-Action
        }
    }
}