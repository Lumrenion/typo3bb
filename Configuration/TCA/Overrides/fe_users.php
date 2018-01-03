<?php

$tmp_typo3bb_columns = [
    'crdate' => [
        'config' => [
            'type' => 'passthrough',
        ],
    ],

    'tx_typo3bb_display_name' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_frontenduser.display_name',
        'config' => [
            'type' => 'text',
            'size' => 30,
            'eval' => 'trim'
        ]
    ],
    'tx_typo3bb_global_moderator' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_frontenduser.global_moderator',
        'config' => [
            'type' => 'check',
            'default' => 0
        ]
    ],
    
    'signature' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_frontenduser.signature',
        'config' => [
            'type' => 'text',
            'size' => 30,
            'eval' => 'trim'
        ],
    ],
    'created_topics' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_frontenduser.created_topics',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'tx_typo3bb_domain_model_topic',
            'foreign_field' => 'author',
            'maxitems' => 9999,
            'appearance' => [
                'collapseAll' => 0,
                'levelLinksPosition' => 'top',
                'showSynchronizationLink' => 1,
                'showPossibleLocalizationRecords' => 1,
                'showAllLocalizationLink' => 1
            ],
            'behaviour' => [
                'enableCascadingDelete' => false
            ]
        ],
    ],
    'subscribed_topics' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_frontenduser.subscribed_topics',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'tx_typo3bb_domain_model_topic',
            'MM' => 'tx_typo3bb_topic_subscribers_mm',
            'MM_opposite_field' => 'subscribers',
            'size' => 10,
            'autoSizeMax' => 30,
            'maxitems' => 9999,
            'multiple' => 0,
        ],
    ],
    'subscribed_boards' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_frontenduser.subscribed_boards',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'tx_typo3bb_domain_model_board',
            'MM' => 'tx_typo3bb_board_subscribers_mm',
            'MM_opposite_field' => 'subscribers',
            'size' => 10,
            'autoSizeMax' => 30,
            'maxitems' => 9999,
            'multiple' => 0,
        ],
    ],
    'created_posts' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_frontenduser.created_posts',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'tx_typo3bb_domain_model_post',
            'foreign_field' => 'author',
            'maxitems' => 9999,
            'appearance' => [
                'collapseAll' => 0,
                'levelLinksPosition' => 'top',
                'showSynchronizationLink' => 1,
                'showPossibleLocalizationRecords' => 1,
                'showAllLocalizationLink' => 1
            ],
            'behaviour' => [
                'enableCascadingDelete' => false
            ]
        ],

    ],
    'edited_posts' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_frontenduser.edited_posts',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'tx_typo3bb_domain_model_post',
            'foreign_field' => 'editor',
            'maxitems' => 9999,
            'appearance' => [
                'collapseAll' => 0,
                'levelLinksPosition' => 'top',
                'showSynchronizationLink' => 1,
                'showPossibleLocalizationRecords' => 1,
                'showAllLocalizationLink' => 1
            ],
            'behaviour' => [
                'enableCascadingDelete' => false
            ]
        ],

    ],
    'posts_count' => [
        'config' => [
            'type' => 'passthrough'
        ]
    ],
    'selected_poll_choices' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_frontenduser.selected_poll_choices',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'tx_typo3bb_domain_model_pollchoice',
            'size' => 10,
            'autoSizeMax' => 30,
            'maxitems' => 9999,
            'multiple' => 0,
        ],
    ],
    'voted_polls' => [
        'config' => [
            'type' => 'passthrough'
        ]
    ],
    'sent_messages' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_frontendUser.sent_messages',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'tx_typo3bb_domain_model_messageparticipant',
            'foreign_field' => 'user',
            'foreign_label' => 'sent_message',
            'behaviour' => [
                'enableCascadingDelete' => false
            ]
        ],
    ],
    'received_messages' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_frontendUser.received_messages',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'tx_typo3bb_domain_model_messageparticipant',
            'foreign_field' => 'user',
            'foreign_label' => 'received_message',
            'behaviour' => [
                'enableCascadingDelete' => false
            ]
        ]
    ],
    'hide_sensitive_data' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_frontendUser.hide_sensitive_data',
        'config' => [
            'type' => 'check',
            'default' => 0
        ]
    ],
    'show_online' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_frontendUser.show_online',
        'config' => [
            'type' => 'check',
            'default' => 1
        ]
    ],
    'message_notification' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_frontendUser.message_notification',
        'config' => [
            'type' => 'check',
            'default' => 1
        ]
    ],
    'login_time' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_frontendUser.login_time',
        'config' => [
            'type' => 'input',
            'default' => 0,
            'size' => 30,
            'eval' => 'trim, int'
        ]
    ],
    'read_topics' => [
        'exclude' => 1,
        'label' => 'Read Topics',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'tx_typo3bb_domain_model_reader',
            'foreign_field' => 'user',
            'foreign_label' => 'topic'
        ]
    ],
    'last_read_post' => [
        'exclude' => 1,
        'label' => 'Last read post',
        'config' => [
            'type' => 'select',
            'foreign_table' => 'tx_typo3bb_domain_model_post',
            'size' => 1,
            'minitems' => 0,
            'maxitems' => 1,
        ]
    ]
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'fe_users',
    $tmp_typo3bb_columns
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    ',--div--;LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:fe_user.tab.typo3bb_settings.label,'
    . 'tx_typo3bb_global_moderator, signature, hide_sensitive_data, show_online, message_notification, '
);