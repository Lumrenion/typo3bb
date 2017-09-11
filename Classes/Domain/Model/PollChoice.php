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


/**
 * PollChoice
 */
class PollChoice extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * text
     *
     * @var string
     * @validate NotEmpty
     */
    protected $text = '';
    
    /**
     * The number of votes for this choice
     *
     * @var int
     */
    protected $voteCount = 0;

    /**
     * The poll the poll choice belongs to
     *
     * @var \LumIT\Typo3bb\Domain\Model\Poll
     */
    protected $poll;
    
    /**
     * Returns the text
     *
     * @return string $text
     */
    public function getText()
    {
        return $this->text;
    }
    
    /**
     * Sets the text
     *
     * @param string $text
     * @return void
     */
    public function setText($text)
    {
        $this->text = $text;
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
     * @return Poll
     */
    public function getPoll() {
        return $this->poll;
    }

    /**
     * @param Poll $poll
     */
    public function setPoll($poll) {
        $this->poll = $poll;
    }

    /**
     * Returns if the current FrontendUser has already voted for this poll choice
     *
     * @return bool
     */
    public function hasFrontendUserVoted() {
        $frontendUser = FrontendUserUtility::getCurrentUser();
        if (!is_null($frontendUser)) {
            return $frontendUser->getSelectedPollChoices()->contains($this);
        }

        return false;
    }
}