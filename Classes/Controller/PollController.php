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

use LumIT\Typo3bb\Domain\Model\Topic;
use LumIT\Typo3bb\Exception\AccessValidationException;
use LumIT\Typo3bb\Utility\SecurityUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedMethodException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * TopicController
 */
class PollController extends AbstractController {

    /**
     * @var \LumIT\Typo3bb\Domain\Repository\TopicRepository
     * @inject
     */
    protected $topicRepository = null;

    /**
     * @var \LumIT\Typo3bb\Domain\Repository\PollRepository
     * @inject
     */
    protected $pollRepository = null;

    /**
     * @var \LumIT\Typo3bb\Domain\Repository\FrontendUserRepository
     * @inject
     */
    protected $frontendUserRepository = null;

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     * @param array $redirectTo
     */
    public function deleteAction(Topic $topic, $redirectTo) {
        SecurityUtility::assertAccessPermission('Topic.edit', $topic);

        $this->pollRepository->remove($topic->getPoll());
        $topic->setPoll(null);
        $this->topicRepository->update($topic);

        //TODO cache
        if(!empty($redirectTo)) {
            $this->redirect($redirectTo['action'], $redirectTo['controller'], $redirectTo['extension'], $redirectTo['arguments'], $redirectTo['pageUid']);
        } else {
            $this->redirect('show', 'Topic', ['topic' => $topic]);
        }
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     * @param array $selection
     * @throws AccessValidationException
     * @throws UnsupportedMethodException
     */
    public function voteAction(Topic $topic, array $selection) {
        SecurityUtility::assertAccessPermission('Poll.vote', $topic);

        $poll = $topic->getPoll();
        if (!$poll->isChangeVoteAllowed() && $poll->hasFrontendUserVoted()) {
            throw new AccessValidationException(LocalizationUtility::translate('exception.poll.alreadyVoted', $this->extensionName));
        }
        if ($poll->getMaxChoicesSelect() > 0 && $poll->getMaxChoicesSelect() < count($selection)) {
            throw new AccessValidationException(LocalizationUtility::translate('exception.poll.maxVotesExceeded', $this->extensionName, [0 => $poll->getMaxChoicesSelect()]));
        }
        $now = new \DateTime();
        if ($poll->getStarttime() > $now || $poll -> getEndtime() < $now) {
            throw new AccessValidationException(LocalizationUtility::translate('exception.poll.timeLimit', $this->extensionName, [0 => $poll->getStarttime()->format('d.m.Y'), 1 => $poll->getEndtime()->format('d.m.Y')]));
        }

        if ($poll->hasFrontendUserVoted()) {
            $previouslySelected = $this->frontendUser->getSelectedPollChoices();
            foreach ($poll->getChoices() as $choice) {
                if($previouslySelected->contains($choice)) {
                    $this->frontendUser->removeSelectedPollChoice($choice);
                    $choice->setVoteCount($choice->getVoteCount() - 1);
                }
            }

            $poll->setVoteCount($poll->getVoteCount() - 1);
            $this->frontendUser->removeVotedPoll($poll);
        }

        foreach ($poll->getChoices() as $choice) {
            if (in_array($choice->getUid(), $selection)) {
                $this->frontendUser->addSelectedPollChoice($choice);
                $choice->setVoteCount($choice->getVoteCount() + 1);
            }
        }

        $poll->setVoteCount($poll->getVoteCount() + 1);
        $this->frontendUser->addVotedPoll($poll);


        $this->topicRepository->update($topic);
        $this->frontendUserRepository->update($this->frontendUser);

        //TODO cache
        $this->redirect('show', 'Topic', null, ['topic' => $topic]);
    }
}