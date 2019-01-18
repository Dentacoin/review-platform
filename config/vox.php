<?php

return array(
	'stats_scales' => [
		'gender' => 'Sex',
		'age' => 'Age Group',
		'country_id' => 'Location',
		'marital_status' => 'Marital Status',
		'children' => 'Children',
		'education' => 'Education',
		'employment' => 'Employment',
		'household_children' => 'Household Children',
		// 'job' => 'Area of employment',
		'job_title' => 'Job title',
		'income' => 'Monthly net income',
	],
	'age_groups' => [
		'24' => '18-24',
		'34' => '25-34',
		'44' => '35-44',
		'54' => '45-54',
		'64' => '55-64',
		'74' => '65-74',
		'more' => '75+'
	],
	'details_fields' => [
		'marital_status' => [
			'label' => 'What\'s your marital status?',
			'values' => [
				'single' => 'Single (never married)',
				'relationship' => 'Living with another',
				'married' => 'Married',
				'separated' => 'Separated',
				'widowed' => 'Widowed',
				'divorced' => 'Divorced'
			]
		],
		'children' => [
			'label' => 'Do you have biological children?',
			'values' => [
				'yes' => 'Yes',
				'no' => 'No'
			]
		],
		'household_children' => [
			'label' => 'Number of children under 18 years old living in your household:',
			'values' => [
				'0' => '0',
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4 or more'
			]
		],
		'education' => [
			'label' => 'Highest education level completed ("Education level"):',
			'values' => [
				'pre_high_school' => 'Elementary school',
				'high_school' => 'High school',
				'pre_college' => 'College',
				'bachelor' => 'Bachelor\'s degree',
				'pre_postgraduate' => 'Postgraduate',
				'master' => 'Master\'s degree',
				'phd' => 'PhD',
			]

		],
		'employment' => [
			'label' => 'Current employment status:',
			'values' => [
				'employed' => 'Salaried employee',
				'self_employed' => 'Self - employed',
				'looking_for' => 'Looking for work',
				'not_looking_for' => 'Not looking for work',
				'homemaker' => 'Homemaker',
				'student' => 'Student',
				'military' => 'Military',
				'retired' => 'Retired',
				'unable' => 'Unable to work'
			]
		],
		'job' => [
			'label' => 'Current area of employment:',
			'values' => [
				'agriculture' => 'Agriculture, Forestry, Fishing or Hunting',
				'arts' => 'Arts, Entertainment, or Recreation',
				'broadcasting' => 'Broadcasting',
				'education_college' => 'Education - College, University, or Adult',
				'education_primary' => 'Education - Primary/Secondary (K-12)',
				'education_other' => 'Education - Other',
				'construction' => 'Construction',
				'finance' => 'Finance and Insurance',
				'government' => 'Government and Public Administration',
				'healthcare' => 'Health Care and Social Assistance',
				'hotel' => 'Hotel and Food Services',
				'information_services' => 'Information - Services and Data',
				'information_other' => 'Information - Other',
				'processing' => 'Processing',
				'legal' => 'Legal Services',
				'manufacturing_computer' => 'Manufacturing - Computer and Electronics',
				'manufacturing_other' => 'Manufacturing - Other',
				'military' => 'Military',
				'mining' => 'Mining',
				'publishing' => 'Publishing',
				'real_estate' => 'Real Estate, Rental or Leasing',
				'religious' => 'Religious',
				'retail' => 'Retail',
				'scientific' => 'Scientific or Technical Services',
				'software' => 'Software',
				'telecommunications' => 'Telecommunications',
				'transportation' => 'Transportation and Warehousing',
				'utilities' => 'Utilities',
				'wholesale' => 'Wholesale',
				'other' => 'Other'
			],
			'trigger' => [
				'employment' => ['employed', 'self_employed']
			]
		],
		'job_title' => [
			'label' => 'Current job title:',
			'values' => [
				'intern' => 'Intern',
				'entry' => 'Entry Level',
				'associate' => 'Analyst / Associate',
				'manager' => 'Manager',
				'senior_manager' => 'Senior Manager',
				'director' => 'Director',
				'vice_president' => 'Vice President',
				'senior_vice_president' => 'Senior Vice President',
				'c_level' => 'C level executive',
				'ceo' => 'President or CEO',
				'owner' => 'Owner'
			],
			'trigger' => [
				'employment' => ['employed', 'self_employed']
			]
		],
		'income' => [
			'label' => 'Monthly net income:',
			'values' => [
				'under_1000' => 'Under $1,000',
				'under_2000' => '$1,000 - 1,999',
				'under_3000' => '$2,000 - 2,999',
				'under_4000' => '$3,000 - 3,999',
				'under_5000' => '$4,000 - 4,999',
				'over_5000' => 'Over $5,000'
			]
		],
	]
);

?>