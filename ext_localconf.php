<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'LumIT.' . $_EXTKEY,
    'Forum',
    [
        'ForumCategory' => 'list',
        'Board' => 'show, markAsRead, subscribe',
        'Topic' => 'show, showNewPost, new, create, edit, update, delete, pin, close, move, executeMove, split, executeSplit, join, executeJoin, subscribe',
        'Post' => 'new, create, edit, update, delete, move, executeMove',
        'Poll' => 'delete, vote',
        'Attachment' => 'remove, download',

    ],
    // non-cacheable actions
    [
        'ForumCategory' => 'list',
        'Board' => 'show, markAsRead, subscribe',
        'Topic' => 'show, showNewPost, new, create, edit, update, delete, pin, close, move, executeMove, split, executeSplit, join, executeJoin, subscribe',
        'Post' => 'new, create, edit, update, delete, move, executeMove',
        'Poll' => 'delete, vote',
        'Attachment' => 'remove, download',
    ]
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'LumIT.' . $_EXTKEY,
    'Messages',
    [
        'Message' => 'inbox, outbox, new, send, delete, getAjaxReceivers'
    ],
    //non-cacheable actions
    [
        'Message' => 'inbox, outbox, send, delete, getAjaxReceivers'
    ]
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'LumIT.' . $_EXTKEY,
    'Statistics',
    [
        'Statistic' => 'infoCenter, statistics',
    ],
    //non-cacheable actions
    [
        'Statistic' => 'infoCenter, statistics',
    ]
);


/**********************************
 *
 * SLOTS
 *
 **********************************/
/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
/** Signal to send notifications to subscribers after topic creation */
$signalSlotDispatcher->connect(
    \LumIT\Typo3bb\Domain\Model\Topic::class, 'afterCreation',
    \LumIT\Typo3bb\Slot\EmailNotificationSlot::class, 'onTopicCreated'
);
/** Signal to send notifications to subscribers after post creation */
$signalSlotDispatcher->connect(
    \LumIT\Typo3bb\Domain\Model\Post::class, 'afterCreation',
    \LumIT\Typo3bb\Slot\EmailNotificationSlot::class, 'onPostCreated'
);
/** Signal to send notifications to message receivers after message creation */
$signalSlotDispatcher->connect(
    \LumIT\Typo3bb\Domain\Model\Message::class, 'afterCreation',
    \LumIT\Typo3bb\Slot\EmailNotificationSlot::class, 'onMessageCreation'
);
/**
 * Before a frontendUser is deleted, all its Topics and posts need to have their authorNames and editorNames set.
 * This Slot is also called, when a user is deleted in the backend {@link \LumIT\Typo3bb\Hook\ProcessFrontendUserHook::processCmdmap_deleteAction()}
 * That is why the code is outsourced into a slot instead of writing it right into the delete action.
 * TODO srfeuserregister
 */
//$signalSlotDispatcher->connect(
//    \LumIT\Typo3bb\Controller\FrontendUserController::class, 'deleteAction',
//    \LumIT\Typo3bb\Slot\FrontendUserSlot::class, 'deleted'
//);
/** Hook before frontend users are saved (in frontend) to sanitize HTML */
// TODO srfeuserregister
//$signalSlotDispatcher->connect(
//    \Evoweb\SfRegister\Controller\FeuserEditController::class, 'saveAction',
//    \LumIT\Typo3bb\Slot\FrontendUserSlot::class, 'sanitizeHtmlSignatureBeforeSave'
//);
/** Hook when frontend users are added - increase todays registrations by 1 */
// TODO srfeuserregister
//$signalSlotDispatcher->connect(
//    \Evoweb\SfRegister\Controller\FeuserCreateController::class, 'saveAction',
//    \LumIT\Typo3bb\Utility\StatisticUtility::class, 'addRegister'
//);

/******************************************
 *
 * HOOKS
 *
 ******************************************/
// Hook after deletion of FrontendUsers
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = \LumIT\Typo3bb\Hook\ProcessFrontendUsersHook::class;
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \LumIT\Typo3bb\Hook\ProcessFrontendUsersHook::class;

// Hook after deletion of Boards */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = \LumIT\Typo3bb\Hook\ProcessBoardsHook::class;
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \LumIT\Typo3bb\Hook\ProcessBoardsHook::class;

// Hook for calculating fe users total time logged in */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['checkDataSubmission'][$_EXTKEY] = \LumIT\Typo3bb\Hook\ProcessFrontendUsersHook::class;

// Hook for registering ke_search indexer for forum posts
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['registerIndexerConfiguration'][] = \LumIT\Typo3bb\Indexer\ForumIndexer::class;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['customIndexer'][] = \LumIT\Typo3bb\Indexer\ForumIndexer::class;
// Hook for registering ke_search indexer for forum frontend users
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['registerIndexerConfiguration'][] = \LumIT\Typo3bb\Indexer\UsersIndexer::class;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['customIndexer'][] = \LumIT\Typo3bb\Indexer\UsersIndexer::class;

/******************************************
 *
 * CACHING FRAMEWORK
 *
 ******************************************/
//Caching Framework
if( !is_array( $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['typo3bb'] ) ) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['typo3bb'] = [];
}

if( !isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['typo3bb']['frontend'] ) ) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['typo3bb']['frontend'] = \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class;
}

if( !isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['typo3bb']['backend'] ) ) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['typo3bb']['backend'] = \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class;
}

if( !isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['typo3bb']['options'] ) ) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['typo3bb']['options'] = [ 'defaultLifetime' => 0 ];
}

if( !isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['typo3bb']['groups'] ) ) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['typo3bb']['groups'] = [ 'pages' ];
}