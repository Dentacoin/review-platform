<?php

return [
    'pages' => array(
        'users' => array(
            'icon' => 'users',
            'js' => array(
                'users.js',
                'address.js',
                '../jquery.multi-select.min.js',
            ),
            'jscdn' => array(
                'https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en',
            ),
        ),
        'anonymous_users' => array(
            'icon' => 'user-secret',
            'js' => array(
                'anonymous-users.js'
            ),
        ),
        'invites' => array(
            'icon' => 'paper-plane-o',
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
        'ban_appeals' => array(
            'icon' => 'ban',
            'js' => array(
                'ban-appeals.js',
            ),
        ),
        'spending' => array(
            'icon' => 'area-chart',
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
        'email_validations' => array(
            'icon' => 'lock',
            'subpages' => array(
                'email_validations' => 'email_validations',
                'invalid_emails' => 'invalid_emails',
            ),
        ),
        'trp' => array(
            'icon' => 'list-alt',
            'subpages' => array(
                'reviews' => 'reviews',
                'youtube' => 'youtube',
                'questions' => 'questions',
                'faq' => 'faq',
                'testimonials' => 'testimonials',
                'scrape-google-dentists' => 'scrape-google-dentists',
            ),
            'js' => array(
                'questions.js',
                'faq.js',
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
                'polls-monthly-description' => 'polls-monthly-description',
                'polls-explorer' => 'polls-explorer',
                'recommendations' => 'recommendations',
            ),
            'js' => array(
                'vox.js',
                'faq.js',
                'polls.js',
                'jquery.multisortable.js',
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
                //'front' => 'front',
                'trp' => 'trp',
                'vox' => 'vox',
                'validation' => 'validation',
                'translations' => 'translations',
                'login-register' => 'login-register',
                'api' => 'api',
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
            'subpages' => array(
                'incomplete' => 'incomplete',
                'leads' => 'leads',
            ),
        ),
        'logs' => array(
            'icon' => 'bug',
            'subpages' => array(
                'trp' => 'trp',
                'trp-urgent' => 'trp-urgent',
                'api' => 'api',
                'api-dev' => 'api-dev',
                'api_civic' => 'api_civic',
                'api_withdraw' => 'api_withdraw',
                'api-ban-appeals' => 'api-ban-appeals',
            ),
        ),
    )
];
