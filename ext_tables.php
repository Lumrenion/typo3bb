<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'LumIT.' . $_EXTKEY,
    'Forum',
    'Bulletin-Board (Typo3BB)'
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'LumIT.' . $_EXTKEY,
    'Messages',
    'Messages (Typo3BB)'
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'LumIT.' . $_EXTKEY,
    'Statistics',
    'Statistics (Typo3BB)'
);
$pluginSignature = 'typo3bb_statistics';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:'.$_EXTKEY . '/Configuration/FlexForms/StatisticsFlexform.xml');


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Typo3-BB');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_typo3bb_domain_model_forumcategory', 'EXT:typo3bb/Resources/Private/Language/locallang_csh_tx_typo3bb_domain_model_forumcategory.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_typo3bb_domain_model_forumcategory');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_typo3bb_domain_model_board', 'EXT:typo3bb/Resources/Private/Language/locallang_csh_tx_typo3bb_domain_model_board.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_typo3bb_domain_model_board');

//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_typo3bb_domain_model_topic', 'EXT:typo3bb/Resources/Private/Language/locallang_csh_tx_typo3bb_domain_model_topic.xlf');
//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_typo3bb_domain_model_topic');
//
//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_typo3bb_domain_model_post', 'EXT:typo3bb/Resources/Private/Language/locallang_csh_tx_typo3bb_domain_model_post.xlf');
//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_typo3bb_domain_model_post');
//
//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_typo3bb_domain_model_poll', 'EXT:typo3bb/Resources/Private/Language/locallang_csh_tx_typo3bb_domain_model_poll.xlf');
//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_typo3bb_domain_model_poll');
//
//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_typo3bb_domain_model_pollchoice', 'EXT:typo3bb/Resources/Private/Language/locallang_csh_tx_typo3bb_domain_model_pollchoice.xlf');
//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_typo3bb_domain_model_pollchoice');