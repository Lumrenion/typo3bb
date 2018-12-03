<?php
return [
    'ctrl' => [
        'title'    => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_topic',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => TRUE,
        'hideTable' => 1,

        'delete' => 'deleted',
        'enablecolumns' => [],
        'searchFields' => 'title,sticky,closed,posts,poll,author,author_name,subscribers,latest_post,',
        'iconfile' => 'EXT:typo3bb/Resources/Public/Icons/tx_typo3bb_domain_model_topic.gif'
    ],
    'interface' => [
        'showRecordFieldList' => 'title, sticky, closed, posts, poll, author, author_name, subscribers, readers, latest_post',
    ],
    'types' => [
        '1' => ['showitem' => '--palette--;;1, title, sticky, closed, author, author_name, poll, posts'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [
        'title' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_topic.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
        'sticky' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_topic.sticky',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
        'closed' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_topic.closed',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
        'posts' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_topic.posts',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_typo3bb_domain_model_post',
                'foreign_field' => 'topic',
                'maxitems' => 9999,
                'appearance' => [
                    'collapseAll' => 0,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'showAllLocalizationLink' => 1
                ],
            ],
        ],
        'poll' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_topic.poll',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_typo3bb_domain_model_poll',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'author' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_topic.author',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'fe_users',
                'minitems' => 1,
                'maxitems' => 1,
            ],
        ],
        'author_name' => [
            'exclude' => 1,
            'label' => '',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ]
        ],
        'subscribers' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_topic.subscribers',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'fe_users',
                'MM' => 'tx_typo3bb_topic_subscribers_mm',
                'size' => 10,
                'autoSizeMax' => 30,
                'maxitems' => 9999,
                'multiple' => 0,
            ],
        ],
        'board' => [
            'exclude' => 1,
            'label' => '',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_typo3bb_domain_model_board',
                'minitems' => 0,
                'maxitems' => 1
            ],
        ],
        'readers' => [
            'exclude' => 1,
            'label' => 'Readers',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_typo3bb_domain_model_reader',
                'foreign_field' => 'topic',
                'foreign_label' => 'user'
            ]
        ],
        'views' => [
            'exclude' => 1,
            'label' => 'Views',
            'config' => [
                'type' => 'passthrough'
            ]
        ]
    ],
];