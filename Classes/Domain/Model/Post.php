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
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * A post is always inside a topic and is user created.
 */
class Post extends AbstractEntity
{

    /**
     * text
     *
     * @var string
     * @validate NotEmpty
     */
    protected $text = '';

    /**
     * The name of the author. Needed for anonymous posts
     *
     * @var string
     */
    protected $authorName = '';

    /**
     * Creation date
     *
     * @var \DateTime
     */
    protected $crdate = null;

    /**
     * Date of the last edit.
     *
     * @var \DateTime
     */
    protected $tstamp = null;

    /**
     * Single Attachment to the post
     *
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Attachment>
     * @cascade remove
     */
    protected $attachments = null;

    /**
     * The author of the post
     *
     * @var \LumIT\Typo3bb\Domain\Model\FrontendUser
     * @lazy
     */
    protected $author = null;

    /**
     * The parent topic
     * @var \LumIT\Typo3bb\Domain\Model\Topic
     */
    protected $topic = null;

    /**
     * The person edited the post
     *
     * @var \LumIT\Typo3bb\Domain\Model\FrontendUser
     * @lazy
     */
    protected $editor = null;

    /**
     * @var bool
     */
    protected $edited = false;

    /**
     * The name of the editor, needed in case the editor was deleted
     *
     * @var string
     */
    protected $editorName = '';

    public function __construct()
    {
        $this->initStorageObjects();
        $this->crdate = new \DateTime();
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
        $this->attachments = new ObjectStorage();
    }

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
     * Returns the authorName
     *
     * @return string $authorName
     */
    public function getAuthorName()
    {
        if (!is_null($this->getAuthor())) {
            return $this->getAuthor()->getDisplayName();
        }
        return $this->authorName;
    }

    /**
     * Sets the authorName
     *
     * @param string $authorName
     * @return void
     */
    public function setAuthorName($authorName)
    {
        $this->authorName = $authorName;
    }

    /**
     * Returns the author
     *
     * @return \LumIT\Typo3bb\Domain\Model\FrontendUser $author
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Sets the author
     *
     * @param \LumIT\Typo3bb\Domain\Model\FrontendUser $author
     * @return void
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * Returns the true author name instead of the displayName, if it's available
     *
     * @return string
     */
    public function getTrueAuthorName()
    {
        return $this->authorName;
    }

    /**
     * Returns the crdate
     *
     * @return \DateTime $crdate
     */
    public function getCrdate()
    {
        return $this->crdate;
    }

    /**
     * Sets the crdate
     *
     * @param \DateTime $crdate
     * @return void
     */
    public function setCrdate($crdate)
    {
        $this->crdate = $crdate;
    }

    /**
     * Returns the tstamp
     *
     * @return \DateTime $tstamp
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * Sets the tstamp
     *
     * @param \DateTime $tstamp
     * @return void
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;
    }

    /**
     * Returns the attachments
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Attachment> $attachments
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Sets the attachments
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\LumIT\Typo3bb\Domain\Model\Attachment> $attachments
     * @return void
     */
    public function setAttachments(ObjectStorage $attachments)
    {
        $this->attachments = $attachments;
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Attachment $attachment
     */
    public function addAttachment(Attachment $attachment)
    {
        $this->attachments->attach($attachment);
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Attachment $attachment
     */
    public function removeAttachment(Attachment $attachment)
    {
        $this->attachments->detach($attachment);
    }

    /**
     * @return \LumIT\Typo3bb\Domain\Model\Topic
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Topic $topic
     */
    public function setTopic($topic)
    {
        $this->topic = $topic;
    }

    /**
     * @return array
     */
    public function getRootline()
    {
        $rootline = $this->topic->getRootline();
        return $rootline;
    }

    /**
     * @return string
     */
    public function getEditorName()
    {
        if (!is_null($this->getEditor())) {
            return $this->getEditor()->getDisplayName();
        }
        return $this->editorName;
    }

    /**
     * @param string $editorName
     */
    public function setEditorName(string $editorName)
    {
        $this->editorName = $editorName;
    }

    /**
     * Returns the editor
     *
     * @return \LumIT\Typo3bb\Domain\Model\FrontendUser $editor
     */
    public function getEditor()
    {
        return $this->editor;
    }

    /**
     * Sets the editor
     *
     * @param \LumIT\Typo3bb\Domain\Model\FrontendUser $editor
     * @return void
     */
    public function setEditor($editor)
    {
        $this->editor = $editor;
    }

    /**
     * @return boolean
     */
    public function isEdited()
    {
        return $this->edited;
    }

    /**
     * @param boolean $edited
     */
    public function setEdited(bool $edited)
    {
        $this->edited = $edited;
    }
}