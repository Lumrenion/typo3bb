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
use LumIT\Typo3bb\Domain\Model\Post;


use TYPO3\CMS\Extbase\Persistence\QueryInterface;


/**
 * The repository for Posts
 */
class TopicRepository extends AbstractRepository {

    protected $defaultOrderings = [
        'sticky' => QueryInterface::ORDER_ASCENDING,
        'latestPostCrdate' => QueryInterface::ORDER_ASCENDING
    ];

    /**
     * @var \LumIT\Typo3bb\Domain\Repository\PostRepository
     * @inject
     */
    protected $postRepository = NULL;

    /**
     * @var \LumIT\Typo3bb\Domain\Repository\BoardRepository
     * @inject
     */
    protected $boardRepository = NULL;

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     */
    public function remove($topic) {
        parent::remove($topic);
        /** @var Post $post */
        foreach ($topic->getPosts() as $post) {
            $this->postRepository->remove($post);
        }
        if (!is_null($topic->getAuthor())) {
            $topic->getAuthor()->removeCreatedTopic($topic);
        }
        $topic->getBoard()->removeTopic($topic);
        $this->boardRepository->update($topic->getBoard());
    }
}

















