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

use LumIT\Typo3bb\Domain\Model\Message;

use LumIT\Typo3bb\Domain\Repository\MessageRepository;
use LumIT\Typo3bb\Utility\FrontendUserUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Class PaginateMessagesController
 */
class PaginateMessagesController extends PaginateBaseController {

    /**
     * @param $modifiedObjects
     */
    public function processModifiedObjects($modifiedObjects) {
        $frontendUser = FrontendUserUtility::getCurrentUser();
        if (!empty($frontendUser)) {
            /** @var MessageRepository $messageRepository */
            $messageRepository = $this->objectManager->get(MessageRepository::class);
            $changedMessageParticipants = [];
            foreach ($modifiedObjects as $modifiedObject) {
                if ($modifiedObject instanceof Message) {
                    // Senders do not need to update their 'viewed' status, so we only iterate through receivers
                    $messageParticipant = $modifiedObject->getMessageReceiver($frontendUser);
                    if ($messageParticipant !== null && !$messageParticipant->getViewed()) {
                        $messageParticipant->setViewed(true);
                        $changedMessageParticipants[] = $messageParticipant;
                        $messageRepository->update($modifiedObject);
                    }
                }
            }
            /** @var PersistenceManager $persistenceManager */
            $persistenceManager = $this->objectManager->get(PersistenceManager::class);
            $persistenceManager->persistAll();
            //TODO Cache Check if page is cachable and clear cache if it is.
            //previously not viewed records should be displayed as "not viewed" for first time viewing, so restore changes
            foreach ($changedMessageParticipants as $changedMessageParticipant) {
                $changedMessageParticipant->setViewed(false);
            }
        }
    }
}