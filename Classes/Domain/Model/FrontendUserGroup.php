<?php
namespace LumIT\Typo3bb\Domain\Model;

class FrontendUserGroup extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup
{

    /**
     * @var bool
     */
    protected $globalModeratorGroup = false;

    /**
     * @return bool
     */
    public function isGlobalModeratorGroup()
    {
        return $this->globalModeratorGroup;
    }

    /**
     * @param bool $globalModeratorGroup
     */
    public function setGlobalModeratorGroup($globalModeratorGroup)
    {
        $this->globalModeratorGroup = $globalModeratorGroup;
    }
}