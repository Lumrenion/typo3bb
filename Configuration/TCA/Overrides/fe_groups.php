<?php
$tmp_typo3bb_columns = [
    'tx_typo3bb_global_moderator_group' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_frontendusergroup.global_moderator_group',
        'config' => [
            'type' => 'check',
            'default' => 0
        ]
    ],
];


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'fe_groups',
    $tmp_typo3bb_columns
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_groups',
    ',--div--;LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:fe_user.tab.typo3bb_settings.label,'
    . 'tx_typo3bb_global_moderator_group, '
);

unset($tmp_typo3bb_columns);
