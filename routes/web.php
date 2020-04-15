<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('cms/login', 							'Auth\AuthenticateAdmin@showLoginForm');
Route::post('cms/login',							'Auth\AuthenticateAdmin@postLogin');
Route::get('cms/logout', 							'Auth\AuthenticateAdmin@getLogout');

Route::get('cities/{id}/{empty?}', 					'CitiesController@getCities');
Route::post('location', 							'CitiesController@getLocation');
Route::post('user-name', 							'CitiesController@getUsername');
Route::post('dentist-location', 					'CitiesController@getDentistLocation');
Route::get('question-count', 						'CitiesController@getQuestions');
Route::any('suggest-clinic/{id?}', 					'CitiesController@getClinic');
Route::any('suggest-dentist/{id?}', 				'CitiesController@getDentist');
Route::get('custom-cookie', 						'SSOController@manageCustomCookie')->name('custom-cookie');

Route::post('wait', 								'CitiesController@wait');

Route::group(['prefix' => 'cms', 'namespace' => 'Admin', 'middleware' => ['admin'] ], function () {
	Route::get('/', 								'HomeController@list');
	Route::get('home', 								'HomeController@list');

	Route::post('translations/{subpage?}/add', 									'TranslationsController@add');
	Route::post('translations/{subpage?}/update', 								'TranslationsController@update');
	Route::get('translations/{subpage?}/export/{source}', 					'TranslationsController@export');
	Route::post('translations/{subpage?}/import/{source}', 					'TranslationsController@import');
	Route::get('translations/{subpage?}/{source?}/{target?}', 					'TranslationsController@list');
	Route::get('translations/{subpage?}/{source?}/{target?}/del/{delkey?}', 	'TranslationsController@delete');

	Route::get('admins', 							'AdminsController@list');
	Route::get('admins/delete/{id}',				'AdminsController@delete');
	Route::get('admins/edit/{id}',					'AdminsController@edit');
	Route::post('admins/edit/{id}',					'AdminsController@update');
	Route::post('admins/add',						'AdminsController@add');

	Route::get('scammers', 							'ScammersController@list');

	Route::any('blacklist', 						'BlacklistController@list');
	Route::get('blacklist/delete/{id}', 			'BlacklistController@delete');

	Route::any('whitelist', 						'WhitelistIpsController@list');
	Route::get('whitelist/delete/{id}', 			'WhitelistIpsController@delete');

	Route::get('users', 							'UsersController@list');
	Route::post('users/mass-delete', 				'UsersController@massdelete');
	Route::get('users/byweek', 						'UsersController@byweek');
	Route::any('users/loginas/{id}/{platform?}', 	'UsersController@loginas');
	Route::any('users/user-data/{id}', 				'UsersController@personal_data');
	Route::any('users/import', 						'UsersController@import');
	Route::any('users/upload-temp', 				'UsersController@upload_temp');
	Route::any('users/add', 						'UsersController@add');
	Route::any('users/edit/{id}', 					'UsersController@edit');
	Route::any('users/edit/{id}/addavatar', 		'UsersController@add_avatar');
	Route::any('users/edit/{id}/deleteavatar', 		'UsersController@delete_avatar');
	Route::any('users/edit/{id}/deletephoto/{position}', 'UsersController@delete_photo');
	Route::any('users/edit/{id}/deleteban/{banid}', 'UsersController@delete_ban');
	Route::get('users/edit/{id}/delete-reward/{rewardid}', 	'UsersController@delete_vox');
	Route::get('users/edit/{id}/delete-unfinished/{vox_id}', 	'UsersController@delete_unfinished');
	Route::any('users/delete/{id}', 				'UsersController@delete');
	Route::any('users/restore/{id}', 				'UsersController@restore');
	Route::any('users/restore-self-deleted/{id}', 	'UsersController@restore_self_deleted');
	Route::any('users/reviews/delete/{id}',		 	'UsersController@delete_review');

	Route::get('users_stats', 						'UsersStatsController@list');

	Route::get('reviews', 							'ReviewsController@list');
	Route::post('reviews/mass-delete', 				'ReviewsController@massdelete');
	Route::any('reviews/add', 						'ReviewsController@add');
	Route::any('reviews/delete/{id}', 				'ReviewsController@delete');
	Route::any('reviews/restore/{id}', 				'ReviewsController@restore');
	Route::any('reviews/edit/{id}', 				'ReviewsController@edit');

	Route::any('transactions', 						'TransactionsController@list');
	Route::any('transactions/bump/{id}', 			'TransactionsController@bump');
	Route::any('transactions/stop/{id}', 			'TransactionsController@stop');
	Route::post('transactions/mass-bump', 			'TransactionsController@massbump');
	Route::post('transactions/mass-stop', 			'TransactionsController@massstop');

	Route::get('spending', 							'SpendingController@list');

	Route::get('questions', 						'QuestionsController@list');
	Route::any('questions/add', 					'QuestionsController@add');
	Route::any('questions/edit/{id}', 				'QuestionsController@edit');
	Route::any('questions/delete/{id}', 			'QuestionsController@delete');

	Route::any('youtube', 							'YoutubeController@list');
	Route::any('youtube/approve/{id}', 				'YoutubeController@approve');
	Route::any('youtube/delete/{id}', 				'YoutubeController@delete');

	Route::any('trp-faq', 							'FaqController@faq');

	Route::get('vox', 								'VoxesController@list');
	Route::get('vox/list', 							'VoxesController@list');
	Route::post('vox/list/reorder', 					'VoxesController@reorderVoxes');
	Route::any('vox/add', 							'VoxesController@add');
	Route::any('vox/edit-field/{id}/{field}/{value}', 					'VoxesController@edit_field');
	Route::any('vox/edit/{id}', 					'VoxesController@edit');
	Route::any('vox/edit/{id}/delpic', 				'VoxesController@delpic');
	Route::any('vox/edit/{id}/export', 				'VoxesController@export');
	Route::any('vox/edit/{id}/import', 				'VoxesController@import');
	Route::any('vox/edit/{id}/import-quick', 				'VoxesController@import_quick');
	Route::get('vox/delete/{id}', 					'VoxesController@delete');
	Route::post('vox/edit/{id}/question/add', 		'VoxesController@add_question');
	Route::any('vox/edit/{id}/question/{question_id}', 		'VoxesController@edit_question');
	Route::get('vox/edit/{id}/question-del/{question_id}', 		'VoxesController@delete_question');
	Route::any('vox/edit/{id}/change-all', 			'VoxesController@reorder');
	Route::any('vox/edit/{id}/change-number/{question_id}', 		'VoxesController@order_question');
	Route::any('vox/edit/{id}/change-question/{question_id}', 		'VoxesController@change_question_text');
	Route::get('vox/ideas', 						'VoxesController@ideas');
	Route::get('vox/categories', 					'VoxesController@categories');
	Route::any('vox/categories/add', 				'VoxesController@add_category');
	Route::any('vox/categories/edit/{id}', 			'VoxesController@edit_category');
	Route::any('vox/categories/edit/{id}/delpic',   'VoxesController@delete_cat_image');
	Route::any('vox/categories/delete/{id}', 		'VoxesController@delete_category');
	Route::get('vox/scales', 						'VoxesController@scales');
	Route::any('vox/scales/add', 					'VoxesController@add_scale');
	Route::any('vox/scales/edit/{id}', 				'VoxesController@edit_scale');
	Route::any('vox/faq', 							'VoxesController@faq');
	Route::any('vox/badges', 						'VoxesController@badges');
	Route::any('vox/badges/delete/{id}', 			'VoxesController@delbadge');
	Route::any('vox/explorer/{vox_id?}/{question_id?}', 	'VoxesController@explorer');
	Route::any('vox/export-survey-data', 			'VoxesController@export_survey_data');
	Route::any('vox/duplicate-question', 			'VoxesController@duplicate_question');

	Route::get('vox/polls', 						'PollsController@list');
	Route::any('vox/polls/add', 					'PollsController@add');
	Route::any('vox/polls/edit/{id}', 				'PollsController@edit');
	Route::any('vox/polls/edit/{id}/import', 		'PollsController@import');
	Route::get('vox/polls/delete/{id}', 			'PollsController@delete');	
	Route::any('vox/polls/change-date/{id}', 		'PollsController@change_poll_date');
	Route::any('vox/polls/change-question/{id}', 	'PollsController@change_poll_question');
	Route::any('vox/polls/duplicate/{id}', 			'PollsController@duplicate_poll');
	Route::any('vox/polls-explorer/{id?}', 			'PollsController@polls_explorer');

	Route::get('emails', 							'EmailsController@list');
	Route::get('emails/{what?}', 					'EmailsController@list');
	Route::get('emails/edit/{id}', 					'EmailsController@edit');
	Route::post('emails/edit/{id}', 				'EmailsController@save');
	Route::get('emails/trp/send-engagement-email',  'EmailsController@engagement_email');
	Route::get('emails/trp/send-monthly-email',  		'EmailsController@monthly_email');

	Route::any('rewards', 							'RewardsController@list');

	Route::any('registrations', 					'StatsController@registrations');

	Route::any('incomplete', 						'UsersController@incomplete');

	Route::any('claims/approve/{id}', 				'DentistClaimsController@approve');
	Route::any('claims/reject/{id}', 				'DentistClaimsController@reject');
	Route::any('claims/suspicious/{id}', 			'DentistClaimsController@suspicious');

	Route::any('scrape-google-dentists', 			'ScrapeGoogleDentistsController@list');
	Route::any('scrape-google-dentists/{id}', 		'ScrapeGoogleDentistsController@download');

	Route::get('testimonial-slider', 				'TestimonialSliderController@list');
	Route::post('testimonial-slider/add', 			'TestimonialSliderController@add');
	Route::any('testimonial-slider/edit/{id}', 		'TestimonialSliderController@edit');
	Route::any('testimonial-slider/edit/{id}/addavatar', 	'TestimonialSliderController@add_avatar');
	Route::any('testimonial-slider/delete/{id}', 	'TestimonialSliderController@delete');

	Route::get('vox/recommendations', 				'RecommendationsController@list');

	Route::get('pages/vox', 						'PagesSeoController@vox_list');
	Route::any('pages/{platform}/add', 				'PagesSeoController@add');
	Route::any('pages/edit/{id}', 					'PagesSeoController@edit');
	Route::any('pages/edit/{id}/removepic', 		'PagesSeoController@removepic');
	Route::get('pages/trp', 						'PagesSeoController@trp_list');

	Route::any('logs', 								'LogsController@list');
	
	Route::any('export-import', 					'ImportExportController@list');
});


//Empty route
$reviewRoutes = function () {
	//Route::any('test', 									'Front\YouTubeController@test');
	Route::post('civic', 								'CivicController@add');
	//Route::any('mobident', 								'MobidentController@reward');

	Route::get('sitemap-trusted-reviews.xml', 			'Front\SitemapController@links');
	Route::get('sitemap.xml', 							'Front\SitemapController@sitemap');
	Route::get('robots.txt', 							'Front\RobotsController@content');

	Route::get('user-logout',							'Auth\AuthenticateUser@getLogout');
	
	Route::group(['prefix' => '{locale?}'], function(){

		Route::get('login', 									[ 'as' => 'login', 'uses' => 'Auth\AuthenticateUser@showLoginForm'] );
		Route::post('login',									'Auth\AuthenticateUser@postLogin');
		Route::get('logout',									'Auth\AuthenticateUser@getLogout');

		Route::get('widget/{id}/{hash}/{mode}', 				'WidgetController@widget');
		Route::any('widget-new/{id}/{hash}', 					'WidgetController@widget_new');

		Route::group(['namespace' => 'Front'], function () {

			Route::any('/', 									'IndexController@home');
			Route::get('index-down', 							'IndexController@index_down');
			Route::get('welcome-dentist/unsubscribe/{session_id?}/{hash?}',	'IndexController@unsubscribe');
			Route::any('welcome-dentist/claim/{id}/',			'IndexController@claim');
			Route::get('welcome-dentist/{session_id?}/{hash?}',	'IndexController@dentist');
			Route::get('want-to-invite-dentist', 				'IndexController@want_to_invite_dentist');
			
			Route::post('lead-magnet-step1', 					'IndexController@lead_magnet_step1');
			Route::post('lead-magnet-step2', 					'IndexController@lead_magnet_step2');
			Route::get('lead-magnet-session', 					'IndexController@lead_magnet_session');

			Route::get('lead-magnet-results', 					'IndexController@lead_magnet_results');

			Route::get('unsubscribe/{user_id}/{hash}', 			'UnsubscribeController@unsubscribe');
			Route::get('unsubscription/{user_id}/{hash}', 		'UnsubscribeController@new_unsubscribe');
			Route::get('unsubscribe-incomplete/{id}/{hash}', 	'UnsubscribeController@unsubscribe_incomplete');

			Route::any('invite/', 								'RegisterController@invite_accept');

			Route::get('review/{id}', 							'DentistController@fullReview');
			Route::get('useful/{id}', 							'DentistController@useful');
			Route::get('unuseful/{id}', 						'DentistController@unuseful');

			Route::post('recommend-dentist', 					'DentistController@recommend_dentist');

			Route::get('register', 								'RegisterController@register');
			Route::post('register', 							'RegisterController@register_form');
			Route::get('forgot-password', 						'RegisterController@forgot');
			Route::post('forgot-password', 						'RegisterController@forgot_form');
			Route::get('recover/{id}/{hash}', 					'RegisterController@recover');
			Route::post('recover/{id}/{hash}', 					'RegisterController@recover_form');
			Route::post('verification-dentist', 				'RegisterController@verification_dentist');
			Route::post('clinic-add-team', 						'RegisterController@clinic_add_team');
			Route::post('add-working-hours',					'RegisterController@add_work_hours');
			Route::post('register-invite', 						'RegisterController@register_invite');
			Route::post('invite-clinic', 						'RegisterController@invite_clinic');
			Route::post('invite-dentist', 						'RegisterController@invite_dentist');

			Route::get('login/facebook', 						'LoginController@facebook_login');
			Route::post('login/civic', 							'LoginController@civic');
			Route::get('login/callback/facebook', 				'LoginController@facebook_callback');
			Route::get('register/facebook/{is_dentist?}', 		'LoginController@facebook_register');
			Route::get('register/callback/facebook', 			'LoginController@facebook_callback_register');

			Route::post('status', 								'LoginController@status');

			Route::post('register/step1', 						'RegisterController@check_step_one');
			Route::post('register/step2', 						'RegisterController@check_step_two');
			Route::post('register/step3', 						'RegisterController@check_step_three');
			Route::post('register/upload', 						'RegisterController@upload');

			Route::post('register/civic', 						'RegisterController@civic');

			//Route::get('vpn', 									'VpnController@list');

			Route::get('dentists/{query?}/{filter?}', 			'DentistsController@search');
			Route::get('dentist-listings-by-country', 			'DentistsController@country');
			Route::get('dentists-in-{country_slug}', 			'DentistsController@state');
			Route::get('dentists-in-{country_slug}/{state}', 	'DentistsController@city');

			Route::any('dentist/{slug}/claim/{id}/',			'DentistController@claim_dentist');

			Route::post('dentist/{slug}/reply/{review_id}', 	'DentistController@reply');
			Route::get('dentist/{slug}/ask/{verification?}',	'DentistController@ask');
			Route::any('dentist/{slug}/{review_id}', 			'DentistController@list');
			Route::any('dentist/{slug}', 						'DentistController@list');
			Route::any('youtube', 								'DentistController@youtube');
			Route::any('full-review/{id}',						'DentistController@fullReview');

			Route::get('page-not-found', 						'NotFoundController@home');

			Route::get('faq', 									'FaqController@home');

			Route::post('facebook-tab', 						'DentistController@dentist_fb_tab');
			Route::any('facebook-tab-reviews', 					'DentistController@dentist_fb_tab_reviews');

			Route::post('dentist-fb-tab', 						'DentistController@fb_tab');

			Route::post('profile/invite-new', 					'ProfileController@invite_team_member');
			Route::post('profile/add-existing-dentist-team', 	'ProfileController@invite_existing_team_member');

			Route::group(['middleware' => 'auth:web'], function () {

				Route::any('invite-new-dentist', 				'AddDentistController@invite_new_dentist');

				Route::get('profile/setGrace', 					'ProfileController@setGrace');
				Route::post('profile/info/upload', 				'ProfileController@upload');
				Route::post('profile/gallery', 					'ProfileController@gallery');				
				Route::any('profile/gallery/delete/{id}', 		'ProfileController@gallery_delete');
				Route::post('profile/info', 					'ProfileController@info');
				Route::get('profile/trp-iframe', 				'ProfileController@trp');

				Route::post('invite-patient-again',				'ProfileController@invite_patient_again');
				Route::post('profile/invite', 					'ProfileController@invite');
				Route::post('profile/invite-whatsapp', 			'ProfileController@invite_whatsapp');
				Route::post('profile/invite-copypaste', 		'ProfileController@invite_copypaste');
				Route::post('profile/invite-copypaste-emails', 	'ProfileController@invite_copypaste_emails');
				Route::post('profile/invite-copypaste-names', 	'ProfileController@invite_copypaste_names');
				Route::post('profile/invite-copypaste-final', 	'ProfileController@invite_copypaste_final');
				Route::post('profile/invite-file',			 	'ProfileController@invite_file');

				Route::get('profile/asks/accept/{id}', 			'ProfileController@asks_accept');
				Route::get('profile/asks/deny/{id}', 			'ProfileController@asks_deny');

				Route::any('profile/dentists/reject/{id}', 		'ProfileController@dentists_reject');
				Route::any('profile/dentists/delete/{id}', 		'ProfileController@dentists_delete');
				Route::any('profile/dentists/accept/{id}', 		'ProfileController@dentists_accept');
				Route::any('profile/dentists/invite', 			'ProfileController@inviteDentist');
				Route::any('profile/clinics/delete/{id}', 		'ProfileController@clinics_delete');
				Route::any('profile/clinics/invite', 			'ProfileController@inviteClinic');

				Route::any('profile/invites/delete/{id}', 		'ProfileController@invites_delete');

				Route::get('profile/check-assurance', 			'ProfileController@checkAssurance');
				Route::get('profile/check-dentacare', 			'ProfileController@checkDentacare');
				Route::get('profile/check-reviews', 			'ProfileController@checkReviews');
				Route::get('profile/first-guided-tour', 		'ProfileController@firstGuidedTour');

				Route::post('share', 							'MiscController@share');
				
			});

			Route::get('{query?}/{filter?}', 					'DentistsController@search')->where('locale', '(en)');

		});
	});
	//Route::any('/{any}', 								'Front\NotFoundController@home')->where('any', '.*');
	//Route::any('page-not-found', 								'Front\NotFoundController@home');
};
Route::domain('reviews.dentacoin.com')->group($reviewRoutes);
Route::domain('dev.reviews.dentacoin.com')->group($reviewRoutes);
Route::domain('urgent.reviews.dentacoin.com')->group($reviewRoutes);


$voxRoutes = function () {
	
	//Route::any('test', 									'Front\YouTubeController@test');
	Route::get('sitemap-dentavox.xml', 					'Vox\SitemapController@links');
	Route::get('sitemap.xml', 							'Vox\SitemapController@sitemap');
	Route::get('robots.txt', 							'Vox\RobotsController@content');

	Route::get('user-logout',									'Auth\AuthenticateUser@getLogout');

	Route::group(['prefix' => '{locale?}'], function(){

		Route::get('login', 									[ 'as' => 'login', 'uses' => 'Auth\AuthenticateUser@showLoginFormVox'] );
		Route::post('login',									'Auth\AuthenticateUser@postLoginVox');
		Route::get('logout',									'Auth\AuthenticateUser@getLogout');

		Route::group(['namespace' => 'Vox'], function () {

			Route::get('unsubscribe/{user_id}/{hash}', 			'UnsubscribeController@unsubscribe');
			Route::get('unsubscription/{user_id}/{hash}', 		'UnsubscribeController@new_unsubscribe');
			Route::get('unsubscribe-incomplete/{id}/{hash}', 	'UnsubscribeController@unsubscribe_incomplete');

			Route::get('faq', 									'FaqController@home');

			Route::get('banned', 								'BannedController@home');
			Route::get('profile-redirect', 						'BannedController@profile_redirect');

			Route::any('invite', 								'RegisterController@invite_accept');
			
			Route::any('registration', 							'RegisterController@register');
			Route::post('registration/step1', 					'RegisterController@check_step_one');
			Route::post('registration/step2', 					'RegisterController@check_step_two');
			Route::post('registration/step3', 					'RegisterController@check_step_three');
			Route::post('registration/step4', 					'RegisterController@check_step_four');
			Route::post('registration/upload', 					'RegisterController@upload');

			Route::get('recover-password', 						'RegisterController@forgot');
			Route::post('recover-password', 					'RegisterController@forgot_form');
			Route::get('recover/{id}/{hash}', 					'RegisterController@recover');
			Route::post('recover/{id}/{hash}', 					'RegisterController@recover_form');

			Route::any('welcome-to-dentavox', 					'RegisterController@register_success');

			Route::any('new-login/facebook', 					'LoginController@new_facebook_login');
			
			Route::get('login/facebook/{query?}', 				'LoginController@facebook_login')->where('query','.+');
			Route::get('login/callback/facebook/{query?}', 		'LoginController@facebook_callback')->where('query','.+');
			Route::any('login/civic', 							'LoginController@civic');

			Route::any('new-register/facebook', 				'LoginController@new_facebook_register');
			Route::post('new-register/civic', 					'RegisterController@new_civic_register');
			
			Route::any('status', 								'LoginController@status');

			Route::post('register/civic', 						'RegisterController@civic');
			Route::get('register/facebook', 					'LoginController@facebook_register');
			Route::get('register/callback/facebook', 			'LoginController@facebook_callback_register');

			Route::any('dental-survey-stats', 					'StatsController@home');
			Route::any('dental-survey-stats/{id}', 				'StatsController@stats');
			Route::post('create-stat-pdf', 						'StatsController@createPdf');
			Route::post('create-stat-png', 						'StatsController@createPng');
			Route::any('download-statistics', 					'StatsController@download');
			Route::any('download-pdf/{name}', 					'StatsController@download_file');
			Route::any('download-png/{name}', 					'StatsController@download_file_png');

			Route::any('questionnaire/{id}', 					'VoxController@home');
			Route::any('paid-dental-surveys', 					'IndexController@surveys_public');
			Route::any('paid-dental-surveys/{id}', 				'VoxController@home_slug');
			Route::any('get-started/{id}', 						'VoxController@home_slug');
			Route::post('start-over', 							'VoxController@start_over');

			Route::any('daily-polls', 							'PollsController@list');
			Route::any('daily-polls/{date}', 					'PollsController@show_popup_poll');
			Route::any('daily-polls/{date}/stats', 				'PollsController@show_popup_stats_poll');
			Route::post('get-polls', 							'PollsController@get_polls');
			Route::any('poll/{id}', 							'PollsController@dopoll');
			Route::post('get-poll-content/{id}', 				'PollsController@get_poll_content');
			Route::post('get-poll-stats/{id}', 					'PollsController@get_poll_stats');

			Route::any('profile/vox-iframe', 					'ProfileController@vox');

			//Route::any('vpn', 									'VpnController@list');

			Route::group(['middleware' => 'auth:web'], function () {
				
				Route::get('profile/setGrace', 					'ProfileController@setGrace');

			});

			Route::get('/', 									'IndexController@home');
			Route::post('request-survey', 						'IndexController@request_survey');
			Route::post('request-survey-patients', 				'IndexController@request_survey_patients');
			Route::post('recommend', 							'IndexController@recommend');
			Route::get('welcome-survey', 						'IndexController@welcome');
			Route::any('appeal', 								'IndexController@appeal');
			Route::any('accept-gdpr', 							'IndexController@gdpr');

			Route::get('page-not-found', 						'NotFoundController@home');
			Route::get('{catch?}', 								'NotFoundController@catch');

		});
	});
};

Route::domain('vox.dentacoin.com')->group($voxRoutes);
Route::domain('vox.dentavox.dentacoin.com')->group($voxRoutes);

Route::domain('dentavox.dentacoin.com')->group($voxRoutes);
Route::domain('dev.dentavox.dentacoin.com')->group($voxRoutes);
Route::domain('urgent.dentavox.dentacoin.com')->group($voxRoutes);
