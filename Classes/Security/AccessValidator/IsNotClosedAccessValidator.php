<?php
namespace LumIT\Typo3bb\Security\AccessValidator;
use LumIT\Typo3bb\Domain\Model\Post;
use LumIT\Typo3bb\Domain\Model\Topic;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

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

class IsNotClosedAccessValidator extends AbstractAccessValidator {

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Topic|\LumIT\Typo3bb\Domain\Model\Post $objectToValidate
     * @return bool
     * @throws IllegalObjectTypeException
     */
    public function validate($objectToValidate) {
        if ($objectToValidate instanceof LazyLoadingProxy) {
            $objectToValidate = $objectToValidate->_loadRealInstance();
        }
        if($objectToValidate instanceof Topic) {
            return !$objectToValidate->isClosed();
        } elseif($objectToValidate instanceof Post) {
            return !$objectToValidate->getTopic()->isClosed();
        } else {
            throw new IllegalObjectTypeException('Object to validate must be of type ' . Topic::class . ' or ' . Post::class . '!');
        }
    }
}