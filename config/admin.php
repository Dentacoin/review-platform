<?php

return [
    'pages' => array(
        'users' => array(
            'icon' => 'users',
        ),
        'questions' => array(
            'icon' => 'question-circle',
            'js' => array(
                'questions.js'
            ),
        ),
        'reviews' => array(
            'icon' => 'edit',
        ),
        'pages' => array(
            'icon' => 'copy',
            'js' => array(
                'pages.js'
            ),
        ),
        'emails' => array(
            'icon' => 'envelope',
        ),
        'vox' => array(
            'icon' => 'bullhorn',
            'subpages' => array(
                'list' => 'list',
                'add' => 'add',
                'ideas' => 'ideas',
            ),
            'js' => array(
                'vox.js'
            ),
        ),
        /*
        'rewards' => array(
            'icon' => 'bitcoin',
        ),
        'secrets' => array(
            'icon' => 'key',
        ),
        */
        'admins' => array(
            'icon' => 'user-plus',
        ),
        'translations' => array(
            'icon' => 'globe',
            'js' => array(
                'translations.js'
            ),
            'subpages' => array(
                'admin' => 'admin',
                'front' => 'front',
                'vox' => 'vox',
                'validation' => 'validation',
            )
        ),
    )
];
