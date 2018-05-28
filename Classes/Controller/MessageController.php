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
use LumIT\Typo3bb\Domain\Factory\MessageFactory;
use LumIT\Typo3bb\Domain\Model\FrontendUser;
use LumIT\Typo3bb\Domain\Model\Message;
use LumIT\Typo3bb\Domain\Model\MessageParticipant;
use LumIT\Typo3bb\Utility\RteUtility;
use LumIT\Typo3bb\Utility\SecurityUtility;


/**
 * MessageController
 */
class MessageController extends AbstractController
{

    /**
     * messageRepository
     *
     * @var \LumIT\Typo3bb\Domain\Repository\MessageRepository
     * @inject
     */
    protected $messageRepository = null;

    /**
     * @var \LumIT\Typo3bb\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository = null;

    public function inboxAction()
    {
        SecurityUtility::assertAccessPermission('Message.inbox');
        $receivedMessages = $this->messageRepository->findInbox($this->frontendUser);
        $this->view->assign('messages', $receivedMessages);
    }

    public function outboxAction()
    {
        SecurityUtility::assertAccessPermission('Message.outbox');
        $sentMessages = $this->messageRepository->findOutbox($this->frontendUser);
        $sentMessages->toArray();
        $this->view->assign('messages', $sentMessages);
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Message $parentMessage
     * @param \LumIT\Typo3bb\Domain\Model\FrontendUser $receiver
     */
    public function newAction($parentMessage = null, FrontendUser $receiver = null)
    {
        SecurityUtility::assertAccessPermission('Message.send');
        $receiverText = '';
        $subject = '';
        $body = '';
        $receiversArray = [];
        if (!empty($parentMessage)) {
            $message = new Message();
            $message->setSubject($parentMessage->getSubject());
            $body = RteUtility::getQuote($parentMessage->getText(), $parentMessage->getSender()->getUserName(),
                $parentMessage->getCrdate());
            $senderUser = $parentMessage->getSender()->getUser();
            if (!empty($senderUser)) {
                $receiverText .= $parentMessage->getSender()->getUser()->getUsername() . ',';
                $receiversArray[] = [
                    'id' => $parentMessage->getSender()->getUser()->getUsername(),
                    'text' => $parentMessage->getSender()->getUser()->getDisplayName()
                ];
            }
            /** @var MessageParticipant $messageReceiver */
            foreach ($parentMessage->getReceivers() as $messageReceiver) {
                if (!empty($messageReceiver->getUser()) && $messageReceiver->getUser()->getUid() != $this->frontendUser->getUid()) {
                    $receiverText .= $messageReceiver->getUser()->getUsername() . ',';
                    $receiversArray[] = [
                        'id' => $messageReceiver->getUser()->getUsername(),
                        'text' => $messageReceiver->getUser()->getDisplayName()
                    ];
                }
            }
            $subject = $parentMessage->getSubject();
        } elseif (!empty($receiver)) {
            $receiverText = $receiver->getUsername() . ',';
            $receiversArray[] = [
                'id' => $receiver->getUsername(),
                'text' => $receiver->getDisplayName()
            ];
        }
        $this->view->assignMultiple([
            'user' => $this->frontendUser,
            'receiver' => $receiverText,
            'receiverArray' => $receiversArray,
            'subject' => $subject,
            'body' => $body,
        ]);
    }

    public function initializeSendAction()
    {
        if (!$this->request->hasArgument('receivers')) {
            $this->request->setArgument('receivers', '');
        }
        $message = $this->request->getArgument('message');
        MessageFactory::prepareMessage($this->arguments->getArgument('message'), $message,
            $this->request->getArgument('receivers'));
        $this->request->setArgument('message', $message);
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Message $message
     */
    public function sendAction(Message $message)
    {
        SecurityUtility::assertAccessPermission('Message.send');
        $message->setText(RteUtility::sanitizeHtml($message->getText()));

        MessageFactory::createMessage($message);

        $this->messageRepository->add($message);
        $this->persistenceManager->persistAll();
        $this->signalSlotDispatcher->dispatch(Message::class, 'afterCreation',
            ['message' => $message, 'controllerContext' => $this->controllerContext]);
        $this->redirect('inbox');
    }

    /**
     * @param Message $message
     * @param string $from Can be inbox or outbox
     */
    public function deleteAction(Message $message, $from = 'inbox')
    {
        SecurityUtility::assertAccessPermission('Message.delete');
        if ($from == 'inbox') {
            $messageParticipant = $message->getMessageReceiver($this->frontendUser);
        } else {
            $messageParticipant = $message->getSender();
            if ($messageParticipant->getUser()->getUid() != $this->frontendUser->getUid()) {
                $messageParticipant = null;
            }
        }

        if (!empty($messageParticipant)) {
            $messageParticipant->setDeleted(true);
        }

        $deleteOriginalMessage = true;
        /** @var MessageParticipant $receiver */
        foreach ($message->getReceivers() as $receiver) {
            if (!$receiver->isDeleted()) {
                $deleteOriginalMessage = false;
                break;
            }
        }
        if ($deleteOriginalMessage) {
            $deleteOriginalMessage = $message->getSender()->isDeleted();
        }

        if ($deleteOriginalMessage) {
            $this->messageRepository->remove($message);
        } else {
            $this->messageRepository->update($message);
        }

        switch ($from) {
            case 'outbox':
                $this->redirect($from);
                break;
            default:
                $this->redirect('inbox');
                break;
        }
    }

    /**
     * Returns a filtered list of possible receivers by search value
     * @param string $search The search value
     *
     * @return string
     */
    public function getAjaxReceiversAction($search)
    {
        SecurityUtility::assertAccessPermission("Message.send");

        $possibleUsers = $this->frontendUserRepository->findByNameOrUsername($search);

        $possibleUsersArray = ['results' => []];
        /** @var FrontendUser $possibleUser */
        foreach ($possibleUsers as $possibleUser) {
            $possibleUsersArray['results'][] = [
                'id' => $possibleUser->getUsername(),
                'text' => $possibleUser->getDisplayName()
            ];
        }

        return json_encode($possibleUsersArray);
    }

}