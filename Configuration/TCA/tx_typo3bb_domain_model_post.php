<?php
return [
    'ctrl' => [
        'title'    => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_post',
        'label' => 'text',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => TRUE,
        'hideTable' => 1,

        'delete' => 'deleted',
        'enablecolumns' => [

        ],
        'searchFields' => 'text,author_name,editor_name,attachments,author,editor',
        'iconfile' => 'EXT:typo3bb/Resources/Public/Icons/tx_typo3bb_domain_model_post.gif'
    ],
    'interface' => [
        'showRecordFieldList' => 'text, author_name, attachments, author',
    ],
    'types' => [
        '1' => ['showitem' => 'text, author, author_name, attachments, '],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [

        'crdate' => [
            'exclude' => 1,
            'config' => [
                'type' => 'passthrough'
            ],
        ],
        'tstamp' => [
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        
        'text' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_post.text',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim,required',
                'default' => ''
            ]
        ],
        'author_name' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_post.author_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'attachments' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_post.attachments',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_typo3bb_domain_model_attachment',
                'foreign_field' => 'post',
                'maxitems' => 10,
                'appearance' => [
                    'collapseAll' => 0,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1
                ],
            ],
        ],
        'author' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_post.author',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'fe_users',
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
        'editor' => [
            'exclude' => 1,
            'label' => '',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'fe_users',
                'minitems' => 1,
                'maxitems' => 1,
            ]
        ],
        'editor_name' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_post.editor_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'edited' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_post.edited',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
        
        'topic' => [
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_typo3bb_domain_model_topic',
                'size' => 10,
                'autoSizeMax' => 30,
                'minitems' => 1,
                'maxitems' => 1,
                'multiple' => 0,
            ],
        ],
    ],
];