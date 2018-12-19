<?php

namespace LumIT\Typo3bb\Controller;


/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2017 Philipp Seßner <philipp.sessner@gmail.com>
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

use LumIT\Typo3bb\Domain\Repository\BoardRepository;
use LumIT\Typo3bb\Domain\Repository\ForumCategoryRepository;
use LumIT\Typo3bb\Domain\Repository\FrontendUserRepository;
use LumIT\Typo3bb\Domain\Repository\PostRepository;
use LumIT\Typo3bb\Domain\Repository\StatisticRepository;
use LumIT\Typo3bb\Domain\Repository\TopicRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * UtilityController
 */
class StatisticController extends ActionController
{

    /**
     * postRepository
     *
     * @var \LumIT\Typo3bb\Domain\Repository\PostRepository
     */
    protected $postRepository = null;

    /**
     * @var \LumIT\Typo3bb\Domain\Repository\TopicRepository
     */
    protected $topicRepository = null;

    /**
     * @var \LumIT\Typo3bb\Domain\Repository\BoardRepository
     */
    protected $boardRepository = null;

    /**
     * @var \LumIT\Typo3bb\Domain\Repository\ForumCategoryRepository
     */
    protected $forumCategoryRepository = null;

    /**
     * frontendUserRepository
     *
     * @var \LumIT\Typo3bb\Domain\Repository\FrontendUserRepository
     */
    protected $frontendUserRepository = null;

    /**
     * @var \LumIT\Typo3bb\Domain\Repository\StatisticRepository
     */
    protected $statisticRepository = null;

    public function __construct(PostRepository $postRepository, TopicRepository $topicRepository, BoardRepository $boardRepository, ForumCategoryRepository $forumCategoryRepository, FrontendUserRepository $frontendUserRepository, StatisticRepository $statisticRepository)
    {
        $this->postRepository = $postRepository;
        $this->topicRepository = $topicRepository;
        $this->boardRepository = $boardRepository;
        $this->forumCategoryRepository = $forumCategoryRepository;
        $this->frontendUserRepository = $frontendUserRepository;
        $this->statisticRepository = $statisticRepository;
    }


    /**
     * infoCenterAction
     */
    public function infoCenterAction()
    {
        $latestPosts = $this->postRepository->findLatest($GLOBALS['TSFE']->gr_list, null, 5);
        $newestMember = $this->frontendUserRepository->findSingleLatest();
        $onlineUsers = $this->frontendUserRepository->findOnlineUsers();

        $this->view->assignMultiple([
            'latestPosts' => $latestPosts,
            'onlineUsers' => $onlineUsers,
            'newestMember' => $newestMember,
            'postsCount' => $this->postRepository->countAll(),
            'topicsCount' => $this->topicRepository->countAll(),
            'membersCount' => $this->frontendUserRepository->countAll()
        ]);
    }

    public function statisticsAction()
    {
//        $dailyRegistrations;
//        $dailyPosts;
//        $dailyTopics;
//        $topBoards;
//        $topAuthors;
//        $topTopicStarters;
//        $topTopicsViews;
//        $topTopicsPosts;
//        $longestTimeOnline;


        $this->view->assignMultiple([
            'postsCount' => $this->postRepository->countAll(),
            'topicsCount' => $this->topicRepository->countAll(),
            'boardsCount' => $this->boardRepository->countAll(),
            'forumCategoriesCount' => $this->forumCategoryRepository->countAll(),
            'membersCount' => $this->frontendUserRepository->countAll(),
            'membersOnlineCount' => $this->frontendUserRepository->findOnlineUsers()->count(),
            'averages' => $this->statisticRepository->getAverages(),
            'totalMostOn' => $this->statisticRepository->findMaxMostOn(),
            'todayStatistics' => $this->statisticRepository->getToday(),

            'topAuthors' => $this->frontendUserRepository->findOrderedByCreatedPostsDESC(10),
            'topTopicStarters' => $this->frontendUserRepository->findOrderedByCreatedTopicsDESC(10),
            'longestTimeOnlineUsers' => $this->frontendUserRepository->findOrderedByLoginTimeDESC(10),
            'topTopicsByViews' => $this->topicRepository->findOrderedByViewsDESC(10),
            'topTopicsByPosts' => $this->topicRepository->findOrderedByPostsDESC(10)
        ]);
    }
}