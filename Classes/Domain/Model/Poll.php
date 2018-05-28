<?php

namespace LumIT\Typo3bb\Domain\Model;


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
use LumIT\Typo3bb\Utility\FrontendUserUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * A poll consists of a question and a number of options to select.
 * It can has a maximum number of poll choices.
 */
class Poll extends AbstractEntity
{

    /**
     * The question of the poll
     *
     * @var string
     * @validate NotEmpty
     */
    protected $question = '';

    /**
     * The maximum number of choices a user can select
     *
     * @var int
     */
    protected $maxChoicesSelect = 0;

    /**
     * If the user is allowed to change his selection afterwards
     *
     * @var bool
     */
    protected $changeVoteAllowed = false;

    /**
     * Number of votes, added for performance reasons
     *
     * @var int
     */
    protected $voteCount = 0;

    /**
     * If the results should be hidden until the poll ended
     *
     * @var bool
     */
    protected $resultHidden = false;

    /**
     * The choices the poll contains
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\PollChoice>
     * @cascade remove
     * @validate NotEmpty
     * @lazy
     */
    protected $choices = null;

    /**
     * Endtime of the poll
     *
     * @var \DateTime null
     */
    protected $endtime = null;

    /**
     * __construct
     */
    public function __construct()
    {
        //Do not remove the next line: It would break the functionality
        $this->initStorageObjects();
    }

    /**
     * Initializes all ObjectStorage properties
     * Do not modify this method!
     * It will be rewritten on each save in the extension builder
     * You may modify the constructor of this class instead
     *
     * @return void
     */
    protected function initStorageObjects()
    {
        $this->choices = new ObjectStorage();
    }

    /**
     * Returns the question
     *
     * @return string $question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Sets the question
     *
     * @param string $question
     * @return void
     */
    public function setQuestion($question)
    {
        $this->question = $question;
    }

    /**
     * Returns the maxChoicesSelect
     *
     * @return int $maxChoicesSelect
     */
    public function getMaxChoicesSelect()
    {
        return $this->maxChoicesSelect;
    }

    /**
     * Sets the maxChoicesSelect
     *
     * @param int $maxChoicesSelect
     * @return void
     */
    public function setMaxChoicesSelect($maxChoicesSelect)
    {
        $this->maxChoicesSelect = $maxChoicesSelect;
    }

    /**
     * Returns the changeVoteAllowed
     *
     * @return bool $changeVoteAllowed
     */
    public function getChangeVoteAllowed()
    {
        return $this->changeVoteAllowed;
    }

    /**
     * Returns the boolean state of changeVoteAllowed
     *
     * @return bool
     */
    public function isChangeVoteAllowed()
    {
        return $this->changeVoteAllowed;
    }

    /**
     * Sets the changeVoteAllowed
     *
     * @param bool $changeVoteAllowed
     * @return void
     */
    public function setChangeVoteAllowed($changeVoteAllowed)
    {
        $this->changeVoteAllowed = $changeVoteAllowed;
    }

    /**
     * Returns the voteCount
     *
     * @return int $voteCount
     */
    public function getVoteCount()
    {
        return $this->voteCount;
    }

    /**
     * Sets the voteCount
     *
     * @param int $voteCount
     * @return void
     */
    public function setVoteCount($voteCount)
    {
        $this->voteCount = $voteCount;
    }

    /**
     * Returns the resultHidden
     *
     * @return bool $resultHidden
     */
    public function getResultHidden()
    {
        return $this->resultHidden;
    }

    /**
     * Returns the boolean state of resultHidden
     *
     * @return bool
     */
    public function isResultHidden()
    {
        return $this->resultHidden;
    }

    /**
     * Sets the resultHidden
     *
     * @param bool $resultHidden
     * @return void
     */
    public function setResultHidden($resultHidden)
    {
        $this->resultHidden = $resultHidden;
    }

    /**
     * Adds a PollChoice
     *
     * @param \LumIT\Typo3bb\Domain\Model\PollChoice $choice
     * @return void
     */
    public function addChoice(PollChoice $choice)
    {
        $this->choices->attach($choice);
    }

    /**
     * Removes a PollChoice
     *
     * @param \LumIT\Typo3bb\Domain\Model\PollChoice $choiceToRemove The PollChoice to be removed
     * @return void
     */
    public function removeChoice(PollChoice $choiceToRemove)
    {
        $this->choices->detach($choiceToRemove);
    }

    /**
     * Returns the choices
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\PollChoice> $choices
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * Sets the choices
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\PollChoice> $choices
     * @return void
     */
    public function setChoices(ObjectStorage $choices)
    {
        $this->choices = $choices;
    }

    /**
     * Returns if the current FrontendUser has already voted for the poll
     *
     * @return bool
     */
    public function hasFrontendUserVoted()
    {
        if ($GLOBALS['TSFE']->loginUser) {
            /** @var FrontendUser $frontendUser */
            $frontendUser = FrontendUserUtility::getCurrentUser();
            return $frontendUser->getVotedPolls()->contains($this);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function hasEnded()
    {
        return (new \DateTime()) > $this->getEndtime();
    }

    /**
     * @return \DateTime
     */
    public function getEndtime()
    {
        return $this->endtime;
    }

    /**
     * @param \DateTime $endtime
     */
    public function setEndtime($endtime)
    {
        $this->endtime = $endtime;
    }
}