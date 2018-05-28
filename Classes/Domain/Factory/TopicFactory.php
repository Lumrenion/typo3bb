<?php

namespace LumIT\Typo3bb\Domain\Factory;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Class TopicFactory
 * @package LumIT\Typo3bb\Domain\Factory
 */
class TopicFactory
{
    /**
     * @param \LumIT\Typo3bb\Domain\Model\Board $board
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     * @param \LumIT\Typo3bb\Domain\Model\Post $post
     * @param \LumIT\Typo3bb\Domain\Model\Poll $poll
     * @param array $attachments
     */
    public static function createTopic($board, $topic, $post, $poll = null, $attachments = [])
    {
        $topic->setBoard($board);
        if (!empty($poll)) {
            $poll->getEndtime()->add(new \DateInterval('PT23H59M'));
            $topic->setPoll($poll);
        }
        $topic->setLatestPostCrdate($post->getCrdate());

        PostFactory::createPost($topic, $post, $attachments);

        $topic->setAuthorName($post->getTrueAuthorName());
        $topic->setAuthor($post->getAuthor());
    }
}