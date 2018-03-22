<?php

namespace LumIT\Typo3bb\Extensions\SrFeuserRegister\Hook;

use LumIT\Typo3bb\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class EvaluationHook
{

    public function evalValues(
        $theTable,
        $dataArray,
        $evalField,
        $cmdKey,
        $cmdParts,
        $extensionname
    ) {
        if ($cmdParts[0] == 'uniqueDisplayName' && $evalField == 'username' || $evalField == 'tx_typo3bb_display_name') {
            $propertyToCheck = [$evalField, $this->getOtherField($evalField)];
            /** @var FrontendUserRepository $userRepository */
            $userRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(FrontendUserRepository::class);
            if ($userRepository->countByFieldNotCurrentUser($propertyToCheck, $dataArray[$evalField])) {
                // invalid
                // theField "telephone" is in error
                return $evalField;
            }
        }

        return '';
    }

    protected function getOtherField($field)
    {
        return $field == 'username' ? 'tx_typo3bb_display_name' : 'username';
    }
}