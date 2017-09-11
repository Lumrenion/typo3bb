<?php
return [
    'ctrl' => [
        'title'    => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_messageparticipant',
        'label' => 'subject',
        'dividers2tabs' => TRUE,
        'sortby' => 'uid',
        'hideTable' => 1,

        'enablecolumns' => [

        ],
        'searchFields' => '',
        'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('typo3bb') . 'Resources/Public/Icons/tx_typo3bb_domain_model_message.gif'
    ],
    'interface' => [
        'showRecordFieldList' => 'sent_message, received_message, user, viewed',
    ],
    'types' => [
        '1' => ['showitem' => 'sent_message, received_message, user, viewed, '],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [
        'sent_message' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_messageparticipant.sent_message',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_typo3bb_domain_model_message',
                'minitems' => 0,
                'maxitems' => 1
            ]
        ],

        'received_message' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_messageparticipant.received_message',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_typo3bb_domain_model_message',
                'minitems' => 0,
                'maxitems' => 1
            ]
        ],

        'viewed' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_messageparticipant.viewed',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
        'user' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_messageparticipant.user',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'fe_users',
                'minitems' => 1,
                'maxitems' => 1,
            ]
        ],
        'user_name' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_messageparticipant.user_name',
            'config' => [
                'type' => 'passthrough'
            ]
        ],

        'deleted' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_messageparticipant.deleted',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
    ],
];