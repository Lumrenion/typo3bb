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
use LumIT\Typo3bb\Domain\Model\Board;
use LumIT\Typo3bb\Domain\Model\Reader;
use LumIT\Typo3bb\Domain\Model\Topic;
use LumIT\Typo3bb\Domain\Repository\BoardRepository;
use LumIT\Typo3bb\Domain\Repository\FrontendUserRepository;
use LumIT\Typo3bb\Domain\Repository\PostRepository;
use LumIT\Typo3bb\Domain\Repository\ReaderRepository;
use LumIT\Typo3bb\Exception\ActionNotAllowedException;
use LumIT\Typo3bb\Utility\SecurityUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;


/**
 * BoardController
 */
class BoardController extends AbstractController
{

    /**
     * boardRepository
     *
     * @var \LumIT\Typo3bb\Domain\Repository\BoardRepository
     */
    protected $boardRepository = null;

    /**
     * @var \LumIT\Typo3bb\Domain\Repository\ReaderRepository
     */
    protected $readerRepository = null;

    /**
     * @var \LumIT\Typo3bb\Domain\Repository\PostRepository
     */
    protected $postRepository = null;

    /**
     * @var \LumIT\Typo3bb\Domain\Repository\FrontendUserRepository
     */
    protected $frontendUserRepository = null;

    public function __construct(BoardRepository $boardRepository, ReaderRepository $readerRepository, PostRepository $postRepository, FrontendUserRepository $frontendUserRepository)
    {
        parent::__construct();
        $this->boardRepository = $boardRepository;
        $this->readerRepository = $readerRepository;
        $this->postRepository = $postRepository;
        $this->frontendUserRepository = $frontendUserRepository;
    }

    /**
     * action show
     *
     * @param \LumIT\Typo3bb\Domain\Model\Board $board
     * @return void
     */
    public function showAction(Board $board)
    {
        SecurityUtility::assertAccessPermission('Board.show', $board);
        if (!empty($board->getRedirect())) {
            $board->increaseRedirectCount();
            $this->boardRepository->update($board);
            $this->redirectToUri($board->getRedirect());
        }
        $this->view->assign('board', $board);

        //TODO cache
    }

    /**
     * @param bool $all
     * @param \LumIT\Typo3bb\Domain\Model\Board $board
     *
     * @throws ActionNotAllowedException
     */
    public function markAsReadAction(bool $all, Board $board = null)
    {
        SecurityUtility::assertAccessPermission('Board.markAsRead', $board);

        if ($all && !($board instanceof Board)) {
            $this->readerRepository->removeAllByFrontendUser($this->frontendUser);
            $this->frontendUser->setLastReadPost(
                $this->postRepository->findLatest($GLOBALS['TSFE']->gr_list, null, 1)->getFirst()
            );
            $this->frontendUserRepository->update($this->frontendUser);
        } elseif ($board instanceof Board) {
            $this->markAsReadRecursive($board);
        }

        //TODO cache

        if ($board instanceof Board) {
            $this->redirect('show', null, null, ['board' => $board]);
        } else {
            $this->redirect('list', 'ForumCategory');
        }
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Board $board
     */
    protected function markAsReadRecursive($board)
    {
        /** @var Topic $topic */
        foreach ($board->getTopics() as $topic) {
            $reader = new Reader();
            $reader->setUser($this->frontendUser);
            $reader->setTopic($topic);
            $latestPost = $topic->getLatestPost();
            if ($latestPost instanceof LazyLoadingProxy) {
                $latestPost = $latestPost->_loadRealInstance();
            }
            $reader->setPost($latestPost);
            $this->readerRepository->add($reader);
        }
        foreach ($board->getAllowedSubBoards() as $subBoard) {
            $this->markAsReadRecursive($subBoard);
        }
    }

    /**
     * @param \LumIT\Typo3bb\Domain\Model\Board $board
     * @param bool $unsubscribe
     */
    public function subscribeAction(Board $board, bool $unsubscribe = false)
    {
        SecurityUtility::assertAccessPermission('Board.subscribe', $board);

        if ($unsubscribe) {
            $board->removeSubscriber($this->frontendUser);
        } else {
            $board->addSubscriber($this->frontendUser);
        }
        $this->boardRepository->update($board);
        //TODO cache
        $this->redirect('show', null, null, ['board' => $board]);
    }

}