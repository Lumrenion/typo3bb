<?php
return [
    'ctrl' => [
        'title'    => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_message',
        'label' => 'subject',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => TRUE,
        'sortby' => 'crdate',
        'hideTable' => 1,

        'enablecolumns' => [

        ],
        'searchFields' => '',
        'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('typo3bb') . 'Resources/Public/Icons/tx_typo3bb_domain_model_message.gif'
    ],
    'interface' => [
        'showRecordFieldList' => 'subject, sender, receivers',
    ],
    'types' => [
        '1' => ['showitem' => 'subject, sender, receivers, text, '],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [
        'crdate' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.crdate',
            'config' => [
                'type' => 'passthrough'
            ],
        ],
        'subject' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_message.subject',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
        'text' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_message.text',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim,required'
            ]
        ],
        'sender' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_message.sender',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_typo3bb_domain_model_messageparticipant',
                'foreign_field' => 'sent_message',
                'foreign_label' => 'user',
                'minitems' => 0,
                'maxitems' => 1,
            ]
        ],
        'receivers' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_message.receivers',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_typo3bb_domain_model_messageparticipant',
                'foreign_field' => 'received_message',
                'foreign_label' => 'user',
                'minitems' => 0,
                'maxitems' => 9999,
            ]
        ],

    ],
];