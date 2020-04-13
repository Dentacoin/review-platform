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
        'whitelist' => array(
            'icon' => 'list-ul',
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
        'emails' => array(
            'icon' => 'envelope',
            'subpages' => array(
                'trp' => 'trp',
                'vox' => 'vox',
                'dentacare' => 'dentacare',
                'assurance' => 'assurance',
                'dentacoin' => 'dentacoin',
                'dentists' => 'dentists',
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
                'recommendations' => 'recommendations',
            ),
            'js' => array(
                'vox.js',
                'faq.js',
                'polls.js',
            ),
        ),
        'pages' => array(
            'icon' => 'image',
            'subpages' => array(
                'vox' => 'vox',
                'trp' => 'trp',
            )
        ),
        'rewards' => array(
            'icon' => 'bitcoin',
        ),
        'youtube' => array(
            'icon' => 'film',
        ),
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
        'export-import' => array(
            'icon' => 'globe',
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
        'testimonial-slider' => array(
            'icon' => 'film',
        ),
        'logs' => array(
            'icon' => 'bug',
        ),
    )
];
