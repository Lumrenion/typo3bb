<?php

namespace LumIT\Typo3bb\Controller;

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
 * ForumCategoryController
 */
class ForumCategoryController extends AbstractController
{

    /**
     * forumCategoryRepository
     *
     * @var \LumIT\Typo3bb\Domain\Repository\ForumCategoryRepository
     * @inject
     */
    protected $forumCategoryRepository = null;

    /**
     * @var \LumIT\Typo3bb\Domain\Repository\BoardRepository
     * @inject
     */
    protected $boardRepository = null;

    /**
     * action list
     *
     * @return void
     */
    public function listAction()
    {
        $boards = $this->boardRepository->getAllowedBoards();

        $this->view->assign('allBoards', $boards);
    }

}