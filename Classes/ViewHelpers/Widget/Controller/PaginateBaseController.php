<?php

namespace LumIT\Typo3bb\ViewHelpers\Widget\Controller;

/*                                                                        *
 * This script is backported from the TYPO3 Flow package "TYPO3.Fluid".   *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 *  of the License, or (at your option) any later version.                *
 *                                                                        *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */
use TYPO3\CMS\Fluid\ViewHelpers\Widget\Controller\PaginateController;

/**
 * Class PaginateBaseController
 */
class PaginateBaseController extends PaginateController
{

    protected $currentPage = -1;

    /**
     * @param int $currentPage
     * @return void
     */
    public function indexAction($currentPage = 1)
    {
        // set current page if it was not set yet
        if ($this->currentPage < 1) {
            $this->currentPage = $currentPage < 1 ? 1 : $currentPage;
        }
        if ($this->currentPage > $this->numberOfPages) {
            // set $modifiedObjects to NULL if the page does not exist
            $modifiedObjects = null;
        } else {
            // modify query
            $itemsPerPage = (int)$this->configuration['itemsPerPage'];
            $offset = 0;
            if ($this->currentPage > 1) {
                $offset = ((int)($itemsPerPage * ($this->currentPage - 1)));
            }
            $modifiedObjects = $this->prepareObjectsSlice($itemsPerPage, $offset);
        }
        $this->processModifiedObjects($modifiedObjects);
        $this->view->assign('contentArguments', [
            $this->widgetConfiguration['as'] => $modifiedObjects
        ]);
        $this->view->assign('configuration', $this->configuration);
        $this->view->assign('pagination', $this->buildPagination());
    }

    /**
     * @param $modifiedObjects
     */
    public function processModifiedObjects($modifiedObjects)
    {
    }
}