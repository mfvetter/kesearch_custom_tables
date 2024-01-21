<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Kesearch Custom Tables Indexer',
    'description' => 'ke_search Indexer For Custom Tables',
    'category' => 'backend',
    'version' => '1.0',
    'dependencies' => 'ke_search',
    'state' => 'stable',
    'author' => 'Marcelo Vetter',
    'author_email' => 'mvetter@lasierra.edu',
    'constraints' => [
        'depends' => [
            'typo3' => '11.0.0-11.5.99',
            'flux' => '9.0.0-9.9.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
