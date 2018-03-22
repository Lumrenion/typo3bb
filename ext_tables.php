<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_typo3bb_domain_model_forumcategory', 'EXT:typo3bb/Resources/Private/Language/locallang_csh_tx_typo3bb_domain_model_forumcategory.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_typo3bb_domain_model_forumcategory');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_typo3bb_domain_model_board', 'EXT:typo3bb/Resources/Private/Language/locallang_csh_tx_typo3bb_domain_model_board.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_typo3bb_domain_model_board');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'LumIT.typo3bb',
    'web',
    'tx_typo3bb_m1',
    '',
    ['Backend' => 'index, newForumCategory, newBoard'],
    [
        'access' => 'user,group',
        'icon' => 'EXT:typo3bb/Resources/Public/Icons/module_m1.svg',
        'labels' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_m1.xlf',
        'navigationComponentId' => 'typo3-pagetree'
    ]
);