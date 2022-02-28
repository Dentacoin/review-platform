<?php

return [
    'pages' => array(
        'users' => array(
            'icon' => 'users',
            'subpages' => array(
                'users' => 'users',
                'anonymous_users' => 'anonymous_users',
                'users_stats' => 'users_stats',
                'registrations' => 'registrations',
                'incomplete-registrations' => 'incomplete-registrations',
                'lead-magnet' => 'lead-magnet',
                'rewards' => 'rewards',
                'bans' => 'bans',
                'lost_users' => 'lost_users'
            ),
            'js' => array(
                'users.js',
                'address.js',
                'anonymous-users.js',
                '../jquery.multi-select.min.js',
            ),
            'jscdn' => array(
                'https://maps.googleapis.com/maps/api/js?key=AIzaSyCaVeHq_LOhQndssbmw-aDnlMwUG73yCdk&libraries=places&callback=initMap&language=en',
            ),
        ),
        'ips' => array(
            'icon' => 'wifi',
            'subpages' => array(
                'bad' => 'bad',
                'vpn' => 'vpn',
            ),
        ),
        'whitelist' => array(
            'icon' => 'list-ul',
            'subpages' => array(
                'ips' => 'ips',
                'vpn-ips' => 'vpn-ips',
            ),
        ),
        'blacklist' => array(
            'icon' => 'umbrella',
        ),
        'invites' => array(
            'icon' => 'paper-plane-o',
        ),
        'transactions' => array(
            'icon' => 'bitcoin',
            'js' => array(
                'transactions.js',
            ),
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
        'rewards' => array(
            'icon' => 'bitcoin',
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
                'support' => 'support',
            ),
        ),
        'email_validations' => array(
            'icon' => 'lock',
            'subpages' => array(
                'email_validations' => 'email_validations',
                'invalid_emails' => 'invalid_emails',
                'old_emails' => 'old_emails',
            ),
        ),
        'orders' => array(
            'icon' => 'shopping-cart',
            'js' => array(
                'orders.js',
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
                'clinic-branches' => 'clinic-branches',
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
                'faq-ios' => 'faq-ios',
                'explorer' => 'explorer',
                'export-survey-data' => 'export-survey-data',
                'polls' => 'polls',
                'polls-monthly-description' => 'polls-monthly-description',
                'polls-explorer' => 'polls-explorer',
                'recommendations' => 'recommendations',
                'paid-reports' => 'paid-reports',
                'history' => 'history',
                'tests' => 'tests',
            ),
            'js' => array(
                'vox.js',
                'faq.js',
                'polls.js',
                'paid-reports.js',
                'jquery.multisortable.js',
                '../jquery.multi-select.min.js',
            ),
        ),
        'support' => array(
            'icon' => 'question-circle',
            'subpages' => array(
                'contact' => 'contact',
                'content' => 'content',
                'categories' => 'categories',
            ),
            'js' => array(
                'support.js',
                '../ckeditor/ckeditor.js',
                '../../plugins/html5lightbox/html5lightbox.js'
            )
        ),
        'meetings' => array(
            'icon' => 'headphones',
            'js' => array(
                'meetings.js',
            )
        ),
        'pages' => array(
            'icon' => 'image',
            'subpages' => array(
                'vox' => 'vox',
                'trp' => 'trp',
            ),
        ),
        'translations' => array(
            'icon' => 'globe',
            'js' => array(
                'translations.js'
            ),
            'subpages' => array(
                'admin' => 'admin',
                // 'front' => 'front',
                'trp' => 'trp',
                'vox' => 'vox',
                'vox-ios' => 'vox-ios',
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
            'subpages' => array(
                'admins' => 'admins',
                'ips' => 'ips',
                'actions-history' => 'actions-history',
                'messages' => 'messages',
            ),
        ),
        'logs' => array(
            'icon' => 'bug',
            'subpages' => array(
                'trp' => 'trp',
                'api' => 'api',
                'api_withdraw' => 'api_withdraw',
                'api-ban-appeals' => 'api-ban-appeals',
                // 'too-fast-bans' => 'too-fast-bans',
            ),
        ),
    )
];
