<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "typo3bb"
 *
 * Auto generated by Extension Builder 2016-03-21
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'Typo3-BB',
    'description' => 'Bulletin Board for typo3',
    'category' => 'plugin',
    'author' => 'Philipp Seßner',
    'author_email' => 'philipp.sessner@gmail.com',
    'state' => 'alpha',
    'internal' => '',
    'uploadfolder' => '1',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            "typo3-ter/sr-feuser-register" => "5.0.0-5.0.99",
        ],
    ],
];