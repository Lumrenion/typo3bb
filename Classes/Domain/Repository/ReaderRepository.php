<?php
namespace LumIT\Typo3bb\Domain\Repository;


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
use LumIT\Typo3bb\Domain\Model\FrontendUser;
use LumIT\Typo3bb\Domain\Model\Post;
use LumIT\Typo3bb\Domain\Model\Reader;

/**
 * The repository for Readers
 */
class ReaderRepository extends AbstractRepository  {
    protected $table = 'tx_typo3bb_domain_model_reader';

    /**
     * @param FrontendUser $frontendUser
     */
    public function removeAllByFrontendUser($frontendUser) {
        $query = $this->createQuery();
        $readersToRemove = $query->matching($query->equals('user', $frontendUser))->execute();
        foreach ($readersToRemove as $readerToRemove) {
            $this->remove($readerToRemove);
        }
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     * @param \LumIT\Typo3bb\Domain\Model\FrontendUser $frontendUser
     * @return \LumIT\Typo3bb\Domain\Model\Reader
     */
    public function findByTopicAndFrontendUser($topic, $frontendUser) {
        $query = $this->createQuery();
        /** @var Reader $reader */
        $reader =  $query->matching($query->logicalAnd(
            $query->equals('topic', $topic),
            $query->equals('user', $frontendUser)
        ))->execute()->getFirst();
        return $reader;
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Reader $reader
     */
    public function add($reader) {
        /** @var Reader $previousReader */
        $previousReader = $this->findByTopicAndFrontendUser($reader->getTopic(), $reader->getUser());
        if (empty($previousReader)) {
            parent::add($reader);
        } else {
            //update
            if (
                !($previousReader->getPost() instanceof Post) ||
                $previousReader->getPost()->getUid() < $reader->getPost()->getUid()
            ) {
                $previousReader->setPost($reader->getPost());
                parent::update($previousReader);
            }
        }
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Reader $reader
     */
    public function update($reader) {
        $this->add($reader);
    }
}