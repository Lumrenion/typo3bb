<?php
return [
    'ctrl' => [
        'title' => 'Reader',
        'label' => 'uid_local',
        'dividers2tabs' => true,
        'searchFields' => '',
        'iconfile' => 'EXT:typo3bb/Resources/Public/Icons/tx_typo3bb_domain_model_reader.gif',
        'hideTable' => 1,
    ],
    'interface' => [
        'showRecordFieldList' => ''
    ],
    'types' => [
        '1' => ['showitem' => '']
    ],
    'palettes' => [
        '1' => ['showitem' => '']
    ],
    'columns' => [
        'user' => [
            'label' => 'User',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'fe_users',
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
            ]
        ],
        'topic' => [
            'label' => 'Topic',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_typo3bb_domain_model_topic',
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
            ]
        ],
        'post' => [
            'label' => 'Post',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_typo3bb_domain_model_post',
                'size' => 1,
                'minitems' => 1,
                'maxitems' => 1,
            ]
        ]
    ]
];