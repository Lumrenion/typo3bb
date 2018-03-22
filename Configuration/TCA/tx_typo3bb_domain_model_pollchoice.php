<?php
return [
    'ctrl' => [
        'title'    => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_pollchoice',
        'label' => 'text',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => TRUE,
        'hideTable' => 1,

        'enablecolumns' => [

        ],
        'searchFields' => 'text,vote_count,poll,',
        'iconfile' => 'EXT:typo3bb/Resources/Public/Icons/tx_typo3bb_domain_model_pollchoice.gif'
    ],
    'interface' => [
        'showRecordFieldList' => 'text,',
    ],
    'types' => [
        '1' => ['showitem' => 'text,'],
    ],
    'palettes' => [
        '1' => ['showitem' => ''],
    ],
    'columns' => [

        'text' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_pollchoice.text',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
        'vote_count' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:typo3bb/Resources/Private/Language/locallang_db.xlf:tx_typo3bb_domain_model_pollchoice.vote_count',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'eval' => 'int'
            ]
        ],
        'poll' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ],
];