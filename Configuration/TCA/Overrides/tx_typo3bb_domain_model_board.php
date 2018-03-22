<?php

$keSearchIndex = [
        'tx_kesearch_index' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_board.tx_kesearch_index',
        'config' => [
            'type' => 'check',
            'default' => 1
        ]
    ]
];

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('ke_search')) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tx_typo3bb_domain_model_board', $keSearchIndex);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'tx_typo3bb_domain_model_board',
        ',--div--;LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_board.tabs.search, '
        . 'tx_kesearch_index, '
    );
}