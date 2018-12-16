<?php
namespace LumIT\Typo3bb\Slot;

use LumIT\Typo3bb\Domain\Model\AbstractCachableModel;

class ObjectCreationSlot
{
    public function afterMappingSingleRow($object)
    {
        if ($object instanceof AbstractCachableModel) {
            $object->initializeCache();
        }
    }
}