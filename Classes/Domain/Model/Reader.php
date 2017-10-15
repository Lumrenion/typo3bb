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

/**
 * A Reader
 */
class Reader extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

    /**
     * @var \LumIT\Typo3bb\Domain\Model\FrontendUser
     * @lazy
     */
    protected $user = null;

    /**
     * file
     *
     * @var \LumIT\Typo3bb\Domain\Model\Topic
     * @lazy
     */
    protected $topic = null;

    /**
     * @var \LumIT\Typo3bb\Domain\Model\Post
     */
    protected $post = null;

    /**
     * @return \LumIT\Typo3bb\Domain\Model\FrontendUser
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\FrontendUser $user
     */
    public function setUser($user) {
        $this->user = $user;
    }

    /**
     * @return \LumIT\Typo3bb\Domain\Model\Topic
     */
    public function getTopic() {
        return $this->topic;
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     */
    public function setTopic($topic) {
        $this->topic = $topic;
    }

    /**
     * @return \LumIT\Typo3bb\Domain\Model\Post
     */
    public function getPost() {
        return $this->post;
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Post $post
     */
    public function setPost($post) {
        $this->post = $post;
    }
}