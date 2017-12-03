<?php
return [
    'ctrl' => [
        'title'    => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_board',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => TRUE,
        'sortby' => 'sorting',

        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',

        ],
        'searchFields' => 'title,description,redirect,topics,sub_boards,read_permissions,write_permissions,moderators,parent_board,forum_category,',
        'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('typo3bb') . 'Resources/Public/Icons/tx_typo3bb_domain_model_board.gif',
        'requestUpdate' => 'parent_board',
    ],
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, title, description, redirect, sub_boards, read_permissions, write_permissions, moderators, parent_board, forum_category',
    ],
    'types' => [
        '1' => ['showitem' =>
            'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, title, description, redirect, moderators, parent_board, forum_category, sub_boards, '
            . '--div--;LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_board.tabs.access, '
            . 'hidden;;1, read_permissions, write_permissions, '
            . '--div--;LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_board.tabs.search, '
            . 'tx_kesearch_index, '
        ],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [
    
        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => [
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1],
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0]
                ],
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_typo3bb_domain_model_board',
                'foreign_table_where' => 'AND tx_typo3bb_domain_model_board.pid=###CURRENT_PID### AND tx_typo3bb_domain_model_board.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],

        'title' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_board.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
        'description' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_board.description',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim'
            ]
        ],
        'redirect' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_board.redirect',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'wizards' => [
                    'link' => [
                        'type' => 'popup',
                        'title' => 'LLL:EXT:cms/locallang_ttc.xlf:header_link_formlabel',
                        'icon' => 'link_popup.gif',
                        'module' => [
                            'name' => 'wizard_element_browser',
                            'urlParameters' => [
                                'mode' => 'wizard'
                            ]
                        ],
                        'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
                    ]
                ],
                'softref' => 'typolink'
            ],
        ],
        'redirect_count' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_board.redirect_count',
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'topics' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_board.topics',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_typo3bb_domain_model_topic',
                'foreign_field' => 'board',
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
        'sub_boards' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_board.sub_boards',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_typo3bb_domain_model_board',
                'foreign_field' => 'parent_board',
                'foreign_sortby' => 'sorting',
                'maxitems' => 9999,
                'appearance' => [
                    'collapseAll' => 1,
                    'levelLinksPosition' => 'top',
                    'showSynchronizationLink' => 1,
                    'showPossibleLocalizationRecords' => 1,
                    'useSortable' => 1,
                    'showAllLocalizationLink' => 1
                ],
            ],

        ],
        'read_permissions' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_board.read_permissions',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'fe_groups',
                'foreign_table_where' => 'ORDER BY fe_groups.title',
                'size' => 10,
                'autoSizeMax' => 30,
                'maxitems' => 9999,
                'multiple' => 0,
                'items' => [
                    [
                        'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:LGL.public',
                        0
                    ],
                    [
                        'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:LGL.anonymous',
                        -1
                    ],
                    [
                        'LLL:EXT:lang/locallang_general.xlf:LGL.any_login',
                        -2
                    ],
                    [
                        'LLL:EXT:lang/locallang_general.xlf:LGL.usergroups',
                        '--div--'
                    ]
                ],
                'exclusiveKeys' => '0,-1,-2',
                'allowNonIdValues' => true,
                'enableMultiSelectFilterTextfield' => 1,
            ],
        ],
        'write_permissions' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_board.write_permissions',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'fe_groups',
                'foreign_table_where' => 'ORDER BY fe_groups.title',
                'size' => 10,
                'autoSizeMax' => 30,
                'maxitems' => 9999,
                'multiple' => 0,
                'items' => [
                    [
                        'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:LGL.public',
                        0
                    ],
                    [
                        'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:LGL.anonymous',
                        -1
                    ],
                    [
                        'LLL:EXT:lang/locallang_general.xlf:LGL.any_login',
                        -2
                    ],
                    [
                        'LLL:EXT:lang/locallang_general.xlf:LGL.usergroups',
                        '--div--'
                    ]
                ],
                'exclusiveKeys' => '0,-1,-2',
                'allowNonIdValues' => true,
                'enableMultiSelectFilterTextfield' => 1,
            ],
        ],
        'moderators' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_board.moderators',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'fe_users',
                'foreign_table_where' => 'ORDER BY fe_users.name',
                'size' => 10,
                'autoSizeMax' => 30,
                'maxitems' => 9999,
                'multiple' => 0,
                'enableMultiSelectFilterTextfield' => 1,
            ],
        ],
        'subscribers' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_board.subscribers',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'fe_users',
                'MM' => 'tx_typo3bb_board_subscribers_mm',
                'size' => 10,
                'autoSizeMax' => 30,
                'maxitems' => 9999,
                'multiple' => 0,
            ],
        ],
        'parent_board' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_board.parent_board',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_typo3bb_domain_model_board',
                'minitems' => 0,
                'maxitems' => 1,
                'default' => 0,
                'items' => [
                    ['', 0],
                ]
            ],
        ],
        'forum_category' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_board.forum_category',
            'displayCond' => 'FIELD:parent_board:=:0',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_typo3bb_domain_model_forumcategory',
                'minitems' => 0,
                'maxitems' => 1,
                'default' => 0,
                'items' => [
                    ['', 0],
                ],
            ],
        ],
        'topics_count' => [
            'config' => [
                'type' => 'passthrough'
            ],
        ],
        'posts_count' => [
            'config' => [
                'type' => 'passthrough'
            ],
        ],
        'latest_post' => [
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'latest_post_crdate' => [
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'tx_kesearch_index' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_board.tx_kesearch_index',
            'displayCond' => 'EXT:ke_search:LOADED:TRUE',
            'config' => [
                'type' => 'check',
                'default' => 1
            ]
        ]
    ],
];