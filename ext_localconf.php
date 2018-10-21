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
        'Message' => 'inbox, outbox, new, send, delete, getAjaxReceivers'
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
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'LumIT.' . $_EXTKEY,
    'Unread',
    [
        'Post' => 'listUnread',
    ],
    //non-cacheable actions
    [
        'Post' => 'listUnread',
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

$signalSlotDispatcher->connect(
    \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper::class, 'afterMappingSingleRow',
    \LumIT\Typo3bb\Slot\ObjectCreationSlot::class, 'afterMappingSingleRow'
);

/******************************************
 *
 * HOOKS
 *
 ******************************************/
// Hook after deletion of FrontendUsers in the backend
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = \LumIT\Typo3bb\Hook\ProcessFrontendUsersHook::class;
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \LumIT\Typo3bb\Hook\ProcessFrontendUsersHook::class;

// Hook after deletion of Boards in the backend */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = \LumIT\Typo3bb\Hook\ProcessBoardsHook::class;
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \LumIT\Typo3bb\Hook\ProcessBoardsHook::class;

// Hook for calculating fe users total time logged in */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['checkDataSubmission'][$_EXTKEY] = \LumIT\Typo3bb\Hook\ProcessFrontendUsersHook::class;

// Hook for registering ke_search indexer for forum posts
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['registerIndexerConfiguration'][] = \LumIT\Typo3bb\Extensions\KeSearch\Indexer\ForumIndexer::class;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['customIndexer'][] = \LumIT\Typo3bb\Extensions\KeSearch\Indexer\ForumIndexer::class;

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
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['typo3bb']['groups'] = [ 'system' ];
}


if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('sr_feuser_register')) {
    // this sr_feuser_register hook implements uniqueness validation of username and tx_typo3bb_display_name among each other
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sr_feuser_register']['tx_srfeuserregister_pi1']['model'][] =
        \LumIT\Typo3bb\Extensions\SrFeuserRegister\Hook\EvaluationHook::class;

    // Before a frontendUser is deleted, all their topics and posts need to have their authorNames and editorNames set
    // When a user profile is edited, the html signature needs to be sanitized
    // When a new user is registered, the statistics need to be updated for daily registrations
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sr_feuser_register']['tx_srfeuserregister_pi1']['registrationProcess'][] =
        \LumIT\Typo3bb\Extensions\SrFeuserRegister\Hook\RegistrationProcessHook::class;
}

// TYPO3 Queries lack the ability of counting queries with a statement set
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbBackend::class] = [
    'className' => \LumIT\Typo3bb\Xclass\Extbase\Persistence\Generic\Storage\Typo3DbBackend::class,
];

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['getQueryParts']['typo3bb'] = \LumIT\Typo3bb\Extensions\KeSearch\Hook\QueryPartsHook::class;