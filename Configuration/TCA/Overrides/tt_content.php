<?php
defined('TYPO3_MODE') || die();


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'LumIT.typo3bb',
    'Forum',
    'Bulletin-Board (Typo3BB)'
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'LumIT.typo3bb',
    'Messages',
    'Messages (Typo3BB)'
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'LumIT.typo3bb',
    'Statistics',
    'Statistics (Typo3BB)'
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'LumIT.typo3bb',
    'Unread',
    'List unread posts (Typo3BB)'
);

$pluginSignature = 'typo3bb_statistics';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:typo3bb/Configuration/FlexForms/StatisticsFlexform.xml');
unset($pluginSignature);