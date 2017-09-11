<?php
$example = [];
$example['postVarSets'] = [
    '_DEFAULT' => [
        'forum' => [
            [
                'GETvar' => 'tx_typo3bb_forum[action]'
            ],
            [
                'GETvar' => 'tx_typo3bb_forum[controller]'
            ],
            [
                'cond' => [
                    'prevValueInList' => 'Board'
                ],
                'GETvar' => 'tx_typo3bb_forum[board]',
                'lookUpTable' => [
                    'table' => 'tx_typo3bb_domain_model_board',
                    'id_field' => 'uid',
                    'alias_field' => 'title',
                    'addWhereClause' => ' AND NOT deleted',
                    'useUniqueCache' => 1,
                    'useUniqueCache_conf' => [
                        'strtolower' => 1,
                        'spaceCharacter' => '-',
                    ],
                ],
            ],
            [
                'cond' => [
                    'prevValueInList' => 'Topic'
                ],
                'GETvar' => 'tx_typo3bb_forum[topic]',
                'lookUpTable' => [
                    'table' => 'tx_typo3bb_domain_model_topic',
                    'id_field' => 'uid',
                    'alias_field' => 'title',
                    'addWhereClause' => ' AND NOT deleted',
                    'useUniqueCache' => 1,
                    'useUniqueCache_conf' => [
                        'strtolower' => 1,
                        'spaceCharacter' => '-',
                    ],

                ]
            ],
            [
                'GETvar' => 'tx_typo3bb_forum[post]',
            ]
        ],
        'profile' => [
            [
                'GETvar' => 'tx_typo3bb_user-profile[action]'
            ],
            [
                'GETvar' => 'tx_typo3bb_user-profile[controller]',
                'valueMap' => [
                    'User' => 'FrontendUser',
                ]
            ],
            [
                'GETvar' => 'tx_typo3bb_user-profile[user]',
                'lookUpTable' => [
                    'table' => 'fe_users',
                    'id_field' => 'uid',
                    'alias_field' => 'name',
                    'addWhereClause' => ' AND NOT deleted',
                    'useUniqueCache' => 1,
                    'useUniqueCache_conf' => [
                        'strtolower' => 1,
                        'spaceCharacter' => '-',
                    ],

                ]
            ],
        ],
        'messages' => [
            [
                'GETvar' => 'tx_typo3bb_messages[action]'
            ],
            [
                'GETvar' => 'tx_typo3bb_messages[controller]'
            ],
        ]
    ]
];