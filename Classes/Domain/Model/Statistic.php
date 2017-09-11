<?php
namespace LumIT\Typo3bb\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Statistic
 * @package LumIT\Typo3bb\Domain\Model
 */
class Statistic extends AbstractEntity {

    /**
     * @var \DateTime
     */
    protected $date = null;
    /**
     * @var int
     */
    protected $topics = 0;
    /**
     * @var int
     */
    protected $posts = 0;
    /**
     * @var int
     */
    protected $registers = 0;
    /**
     * @var int
     */
    protected $mostOn = 0;

    public function __construct() {
        $this->date = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date) {
        $this->date = $date;
    }

    /**
     * @return int
     */
    public function getTopics(): int {
        return $this->topics;
    }

    /**
     * @param int $topics
     */
    public function setTopics(int $topics) {
        $this->topics = $topics;
    }

    /**
     * @return int
     */
    public function getPosts(): int {
        return $this->posts;
    }

    /**
     * @param int $posts
     */
    public function setPosts(int $posts) {
        $this->posts = $posts;
    }

    /**
     * @return int
     */
    public function getRegisters(): int {
        return $this->registers;
    }

    /**
     * @param int $registers
     */
    public function setRegisters(int $registers) {
        $this->registers = $registers;
    }

    /**
     * @return int
     */
    public function getMostOn(): int {
        return $this->mostOn;
    }

    /**
     * @param int $mostOn
     */
    public function setMostOn(int $mostOn) {
        $this->mostOn = $mostOn;
    }
}