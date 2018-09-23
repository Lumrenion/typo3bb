<?php
namespace LumIT\Typo3bb\Slot;

use LumIT\Typo3bb\Domain\Model\AbstractCachableModel;

class ObjectCreationSlot
{
    public function afterMappingSingleRow($object)
    {
        if (TYPO3_MODE !== 'FE') {
            return;
        }
        if ($object instanceof AbstractCachableModel) {
            $object->initializeCache();
        }
    }
}