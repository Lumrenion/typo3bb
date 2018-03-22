<?php
return [
    'ctrl' => [
        'title' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_statistic',
        'label' => 'file',
        'dividers2tabs' => true,
        'searchFields' => '',
        'iconfile' => 'EXT:typo3bb/Resources/Public/Icons/tx_typo3bb_domain_model_statistic.gif',
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
        'date' => [
            'exclue' => 1,
            'config' => [
                'dbType' => 'date',
                'type' => 'passthrough',
                'eval' => 'date'
            ]
        ],
        'topics' => [
            'exclude' => 1,
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'posts' => [
            'exclude' => 1,
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'registers' => [
            'exclude' => 1,
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'most_on' => [
            'exclude' => 1,
            'config' => [
                'type' => 'passthrough'
            ]
        ]
    ]
];