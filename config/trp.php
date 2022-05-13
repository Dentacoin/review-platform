<?php

return [
	'social_network' => [
		'facebook' => 'facebook',
		'linkedin' => 'linkedin',
		'twitter' => 'twitter',
		'instagram' => 'instagram',
		'youtube' => 'youtube',
		'vkontakte' => 'vkontakte',
		'wechat' => 'wechat',
	],
	'accepted_payment' => [
		'cash',
		'check',
		'credit_card',
		'invoice',
		'paypal',
		'dentacoin',
	],
	'treatments' => [
		'most_popular' => [
			'check_up',
			'tooth_cleaning',
			'teeth_whitening',
			'fillings',
			'crowns',
			'porcelain_veneers',
			'tooth_extraction',
			'implant_placement',
			'braces',
			'root_canal_treatment',
		],
		'general_dentistry' => [
			'check_up',
			'tooth_cleaning',
			'fillings',
			'caries_infiltration',
			'dental_sealants_for_children',
			'root_canal_treatment',
			'periodontal_treatment',
			'tooth_extraction',
		],
		'cosmetic_dentistry' => [
			'teeth_whitening',
			'composite_bonding',
			'porcelain_veneers',
			'composite_veneers',
			'inlays_and_onlays',
			'crowns',
			'bridges',
			'dentures',
		],
		'implant_dentistry' => [
			'implant_placement',
			'bone_augmentation',
		],
		'orthodontics' => [
			'braces',
			'clear_aligners',
			'retainers',
			'orthognathic_surgery',
		],
		'other' => [
			'other',
		],
	],
	'lead_magnet' => [
		1 => [
			1 => 'To acquire new patients',
			2 => 'To keep existing patients',
			3 => 'Both',
		],
		2 => [
			1 => 'My website',
			2 => 'Google',
			3 => 'Facebook or other social media',
			4 => 'General review platform (e.g. Trustpilot)',
			5 => 'Specialized review platform (e.g. Dentacoin Trusted Reviews, Zocdoc.)',
			6 => 'I don’t use one',
		],
		3 => [
			1 => 'Yes, in person',
			2 => 'Yes, by email',
			3 => 'Yes, by SMS',
			4 => 'No',
		],
		4 => [
			1 => 'Every day',
			2 => 'Occasionally',
			3 => 'It happened a few times only',
		],
		5 => [
			1 => 'Yes, to all reviews',
			2 => 'Yes, only to negative reviews',
			3 => 'Yes, from time to time',
			4 => 'No'
		],
	],
	'team_jobs' => [
		'practice_manager' => 'Practice Manager',
		'dentist' => 'Dentist',
		'dental_technician' => 'Dental Technician',
		'dental_hygienist' => 'Dental Hygienist',
		'dental_assistant' => 'Dental Assistant',
		'marketing_specialist' => 'Marketing Specialist',
		'other' => 'Other',
	],
	'limits_days' => [
		'review' => 93, //3 months
		'ask_dentist' => 30, //1 month
	],
	'payment_methods' => [
		'cash', 'check', 'credit_card', 'invoice', 'paypal', 'dentacoin'
	]
];

?>