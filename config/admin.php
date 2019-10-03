<?php

return [
    'pages' => array(
        'users' => array(
            'icon' => 'users',
            'js' => array(
                'users.js',
                'address.js',
            ),
            'jscdn' => array(
                'https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en',
            ),
        ),
        'users_stats' => array(
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
                'dentacare' => 'dentacare',
                'assurance' => 'assurance',
                'dentacoin' => 'dentacoin',
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
                'explorer' => 'explorer',
                'export-survey-data' => 'export-survey-data',
                'polls' => 'polls',
                'polls-explorer' => 'polls-explorer',
            ),
            'js' => array(
                'vox.js',
                'faq.js',
                'polls.js',
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
        'incomplete' => array(
            'icon' => 'heartbeat',
        ),
        'scrape-google-dentists' => array(
            'icon' => 'download',
        ),
    )
];
