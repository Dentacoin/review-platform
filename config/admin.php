<?php

return [
    'pages' => array(
        'users' => array(
            'icon' => 'users',
        ),
        'scammers' => array(
            'icon' => 'transgender-alt',
        ),
        'blacklist' => array(
            'icon' => 'umbrella',
        ),
        'transactions' => array(
            'icon' => 'bitcoin',
        ),
        'spending' => array(
            'icon' => 'area-chart',
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
        'trp-faq' => array(
            'icon' => 'comments',
            'js' => array(
                'faq.js',
            ),
        ),
        'pages' => array(
            'icon' => 'copy',
            'js' => array(
                'pages.js'
            ),
        ),
        'emails' => array(
            'icon' => 'envelope',
            'subpages' => array(
                'trp' => 'trp',
                'vox' => 'vox',
                'common' => 'common',
            ),
        ),
        'vox' => array(
            'icon' => 'bullhorn',
            'subpages' => array(
                'list' => 'list',
                'add' => 'add',
                'categories' => 'categories',
                'scales' => 'scales',
                'faq' => 'faq',
                'badges' => 'badges',
            ),
            'js' => array(
                'vox.js',
                'faq.js',
            ),
        ),
        'rewards' => array(
            'icon' => 'bitcoin',
        ),
        'youtube' => array(
            'icon' => 'film',
        ),
        /*
        'secrets' => array(
            'icon' => 'key',
        ),
        */
        'registrations' => array(
            'icon' => 'plus-circle',
        ),
        'translations' => array(
            'icon' => 'globe',
            'js' => array(
                'translations.js'
            ),
            'subpages' => array(
                'admin' => 'admin',
                'front' => 'front',
                'trp' => 'trp',
                'vox' => 'vox',
                'validation' => 'validation',
            )
        ),
        'admins' => array(
            'icon' => 'user-plus',
        ),
    )
];
