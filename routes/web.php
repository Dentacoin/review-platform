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
Route::get('question-count', 						'CitiesController@getQuestions');
Route::any('suggest-clinic/{id}', 					'CitiesController@getClinic');
Route::any('suggest-dentist/{id}', 					'CitiesController@getDentist');


Route::post('wait', 									'CitiesController@wait');

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

	Route::get('secrets', 							'SecretsController@list');
	Route::post('secrets', 							'SecretsController@add');
	Route::get('secrets/delete/{id}',				'SecretsController@delete');

	Route::get('scammers', 							'ScammersController@list');

	Route::any('blacklist', 						'BlacklistController@list');
	Route::get('blacklist/delete/{id}', 			'BlacklistController@delete');

	Route::get('users', 							'UsersController@list');
	Route::post('users/mass-delete', 				'UsersController@massdelete');
	Route::get('users/byweek', 						'UsersController@byweek');
	Route::any('users/loginas/{id}', 				'UsersController@loginas');
	Route::any('users/user-data/{id}', 				'UsersController@personal_data');
	Route::any('users/edit/{id}', 					'UsersController@edit');
	Route::any('users/edit/{id}/deleteavatar', 		'UsersController@delete_avatar');
	Route::any('users/edit/{id}/deletephoto/{position}', 'UsersController@delete_photo');
	Route::any('users/edit/{id}/deleteban/{banid}', 'UsersController@delete_ban');
	Route::get('users/edit/{id}/delete-reward/{rewardid}', 	'UsersController@delete_vox');
	Route::get('users/edit/{id}/delete-unfinished/{vox_id}', 	'UsersController@delete_unfinished');
	Route::any('users/delete/{id}', 				'UsersController@delete');
	Route::any('users/restore/{id}', 				'UsersController@restore');
	Route::any('users/reviews/delete/{id}', 		'UsersController@delete_review');

	Route::get('reviews', 							'ReviewsController@list');
	Route::post('reviews/mass-delete', 							'ReviewsController@massdelete');
	Route::any('reviews/add', 						'ReviewsController@add');
	Route::any('reviews/delete/{id}', 				'ReviewsController@delete');
	Route::any('reviews/restore/{id}', 				'ReviewsController@restore');
	Route::any('reviews/edit/{id}', 				'ReviewsController@edit');
	
	Route::get('pages', 							'PagesController@list');
	Route::any('pages/add', 						'PagesController@add');
	Route::any('pages/edit/{id}', 					'PagesController@edit');
	Route::any('pages/edit/{id}/removepic', 		'PagesController@removepic');
	Route::any('pages/delete/{id}', 				'PagesController@delete');

	Route::get('transactions', 						'TransactionsController@list');

	Route::get('spending', 							'SpendingController@list');

	Route::get('questions', 						'QuestionsController@list');
	Route::any('questions/add', 					'QuestionsController@add');
	Route::any('questions/edit/{id}', 				'QuestionsController@edit');
	Route::any('questions/delete/{id}', 			'QuestionsController@delete');

	Route::any('youtube', 							'YoutubeController@list');
	Route::any('youtube/approve/{id}', 				'YoutubeController@approve');
	Route::any('youtube/delete/{id}', 				'YoutubeController@delete');

	Route::get('vox', 								'VoxesController@list');
	Route::get('vox/list', 							'VoxesController@list');
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
	Route::any('vox/categories/delete/{id}', 		'VoxesController@delete_category');
	Route::get('vox/scales', 						'VoxesController@scales');
	Route::any('vox/scales/add', 					'VoxesController@add_scale');
	Route::any('vox/scales/edit/{id}', 				'VoxesController@edit_scale');
	Route::any('vox/faq', 							'VoxesController@faq');
	Route::any('vox/badges', 							'VoxesController@badges');

	Route::get('emails', 							'EmailsController@list');
	Route::get('emails/{what?}', 					'EmailsController@list');
	Route::get('emails/edit/{id}', 					'EmailsController@edit');
	Route::post('emails/edit/{id}', 				'EmailsController@save');

	Route::any('rewards', 							'RewardsController@list');

	Route::any('registrations', 					'StatsController@registrations');
});


//Empty route
$reviewRoutes = function () {
	
	Route::any('test', 									'Front\YouTubeController@test');
	Route::any('civic', 								'CivicController@add');
	Route::any('mobident', 								'MobidentController@reward');

	Route::group(['prefix' => '{locale?}'], function(){

		Route::get('login', 									[ 'as' => 'login', 'uses' => 'Auth\AuthenticateUser@showLoginForm'] );
		Route::post('login',									'Auth\AuthenticateUser@postLogin');
		Route::get('logout',									'Auth\AuthenticateUser@getLogout');

		Route::get('widget/{id}/{hash}/{mode}', 				'WidgetController@widget');

		Route::group(['namespace' => 'Front'], function () {
			Route::get('/', 									'IndexController@home');
			Route::any('accept-gdpr', 							'IndexController@gdpr');
			
			Route::any('invite/{id}/{hash}/{inv_id?}', 			'RegisterController@invite_accept');
			Route::any('claim/{id}/{hash}', 					'RegisterController@claim');

			Route::post('phone/save', 							'PhoneController@save');
			Route::post('phone/check', 							'PhoneController@check');
			Route::get('useful/{id}', 							'DentistController@useful');

			Route::get('register', 								'RegisterController@register');
			Route::post('register', 							'RegisterController@register_form');
			Route::get('verify/{id}/{hash}', 					'RegisterController@register_verify');
			Route::get('forgot-password', 						'RegisterController@forgot');
			Route::post('forgot-password', 						'RegisterController@forgot_form');
			Route::get('recover/{id}/{hash}', 					'RegisterController@recover');
			Route::post('recover/{id}/{hash}', 					'RegisterController@recover_form');

			Route::get('login/facebook', 						'LoginController@facebook_login');
			Route::get('login/twitter', 						'LoginController@twitter_login');
			Route::get('login/gplus', 							'LoginController@gplus_login');
			Route::post('login/civic', 							'LoginController@civic');

			Route::get('login/callback/facebook', 				'LoginController@facebook_callback');
			Route::get('login/callback/twitter', 				'LoginController@twitter_callback');
			Route::get('login/callback/gplus', 					'LoginController@gplus_callback');

			Route::get('register/facebook/{is_dentist}', 		'LoginController@facebook_register');
			Route::get('register/twitter/{is_dentist}', 		'LoginController@twitter_register');
			Route::get('register/gplus/{is_dentist}', 			'LoginController@gplus_register');

			Route::get('register/callback/facebook', 			'LoginController@facebook_callback_register');
			Route::get('register/callback/twitter', 			'LoginController@twitter_callback_register');
			Route::get('register/callback/gplus', 				'LoginController@gplus_callback_register');

			Route::post('register/step1', 						'RegisterController@check_step_one');
			Route::post('register/upload', 						'RegisterController@upload');

			Route::post('register/civic', 						'RegisterController@civic');

			Route::get('dentists/p/{page?}', 					'DentistsController@paginate');
			Route::get('dentists/{country?}/{city?}', 			'DentistsController@list');

			Route::get('dentist/{slug}/confirm-review/{secret}', 	'DentistController@confirmReview');
			Route::post('dentist/{slug}/reply/{review_id}', 	'DentistController@reply');
			Route::post('dentist/{slug}/claim-phone', 			'DentistController@claim');
			Route::post('dentist/{slug}/claim-code', 			'DentistController@code');
			Route::post('dentist/{slug}/claim-password', 		'DentistController@password');
			Route::post('dentist/{slug}/claim-email', 			'DentistController@email');
			Route::get('dentist/{slug}/ask', 					'DentistController@ask');
			Route::any('dentist/{slug}/{review_id}', 			'DentistController@list');
			Route::any('dentist/{slug}', 						'DentistController@list');
			Route::any('youtube', 								'DentistController@youtube');
			Route::any('full-review/{id}',						'DentistController@fullReview');

			Route::group(['middleware' => 'auth:web'], function () {
				Route::get('profile', 							'ProfileController@home');
				Route::get('profile/setGrace', 					'ProfileController@setGrace');
				Route::get('profile/home', 						'ProfileController@home');
				Route::post('profile/avatar', 					'ProfileController@avatar');
				Route::any('profile/info', 						'ProfileController@info');
				Route::get('profile/gallery', 					'ProfileController@gallery');
				Route::get('profile/gallery/delete/{position}', 'ProfileController@gallery_delete');
				Route::post('profile/gallery/{position}', 		'ProfileController@gallery');
				Route::get('profile/password', 					'ProfileController@password');
				Route::post('profile/password', 				'ProfileController@change_password');
				Route::get('profile/reviews', 					'ProfileController@reviews');
				Route::get('profile/wallet', 					'ProfileController@wallet');
				Route::any('profile/invite', 					'ProfileController@invite');
				Route::any('profile/remove-avatar', 			'ProfileController@remove_avatar');
				Route::post('profile/balance', 					'ProfileController@balance');
				Route::get('profile/resend', 					'ProfileController@resend');
				Route::get('profile/widget', 					'ProfileController@widget');
				Route::get('profile/history', 					'ProfileController@history');
				Route::get('profile/asks', 						'ProfileController@asks');
				Route::get('profile/asks/accept/{id}', 			'ProfileController@asks_accept');
				Route::get('profile/asks/deny/{id}', 			'ProfileController@asks_deny');
				Route::any('profile/dentists', 					'ProfileController@dentists');
				Route::any('profile/dentists/reject/{id}', 		'ProfileController@dentists_reject');
				Route::any('profile/dentists/delete/{id}', 		'ProfileController@dentists_delete');
				Route::any('profile/dentists/accept/{id}', 		'ProfileController@dentists_accept');
				Route::any('profile/dentists/invite', 			'ProfileController@inviteDentist');
				Route::any('profile/clinics', 					'ProfileController@clinics');
				Route::any('profile/clinics/delete/{id}', 		'ProfileController@clinics_delete');
				Route::any('profile/clinics/invite', 			'ProfileController@inviteClinic');

				Route::any('profile/reward', 					'ProfileController@reward');
				Route::any('profile/setEmail', 					'ProfileController@setEmail');
				Route::post('profile/jwt', 						'ProfileController@jwt');
				Route::post('profile/withdraw', 				'ProfileController@withdraw');
				Route::any('profile/privacy', 					'ProfileController@privacy');
				Route::any('profile/privacy-download', 			'ProfileController@privacy_download');

				Route::get('gdpr', 								'ProfileController@gdpr');

				
			});

			Route::get('{slug}', 								'PagesController@home');
		});
	});
};
Route::domain('reviews.dentacoin.com')->group($reviewRoutes);
Route::domain('dev-reviews.dentacoin.com')->group($reviewRoutes);


$voxRoutes = function () {
	
	Route::any('test', 									'Front\YouTubeController@test');

	Route::group(['prefix' => '{locale?}'], function(){

		Route::get('login', 									[ 'as' => 'login', 'uses' => 'Auth\AuthenticateUser@showLoginFormVox'] );
		Route::post('login',									'Auth\AuthenticateUser@postLoginVox');
		Route::get('logout',									'Auth\AuthenticateUser@getLogout');

		Route::group(['namespace' => 'Vox'], function () {

			Route::get('faq', 									'FaqController@home');

			Route::get('banned', 								'BannedController@home');

			Route::any('invite/{id}/{hash}/{inv_id?}', 			'RegisterController@invite_accept');
			
			Route::any('registration', 								'RegisterController@register');
			Route::post('registration/step1', 						'RegisterController@check_step_one');
			Route::post('registration/upload', 						'RegisterController@upload');

			Route::get('verify/{id}/{hash}', 					'RegisterController@register_verify');
			Route::get('recover-password', 						'RegisterController@forgot');
			Route::post('recover-password', 						'RegisterController@forgot_form');
			Route::get('recover/{id}/{hash}', 					'RegisterController@recover');
			Route::post('recover/{id}/{hash}', 					'RegisterController@recover_form');

			Route::get('login/facebook', 						'LoginController@facebook_login');
			Route::get('login/callback/facebook', 				'LoginController@facebook_callback');
			Route::post('login/civic', 							'LoginController@civic');

			Route::post('register/civic', 						'RegisterController@civic');
			Route::get('register/facebook', 					'LoginController@facebook_register');
			Route::get('register/callback/facebook', 			'LoginController@facebook_callback_register');

			Route::any('dental-survey-stats', 					'StatsController@home');
			Route::any('dental-survey-stats/{id}', 				'StatsController@stats');

			Route::any('questionnaire/{id}', 				'VoxController@home');
			Route::any('paid-dental-surveys/{id}', 				'VoxController@home_slug');

			Route::group(['middleware' => 'auth:web'], function () {
				Route::any('welcome-to-dentavox', 					'RegisterController@register_success');

				Route::post('phone/save', 						'PhoneController@save');
				Route::post('phone/check', 						'PhoneController@check');

				Route::any('profile', 							'ProfileController@home');
				Route::get('profile/setGrace', 					'ProfileController@setGrace');
				Route::post('profile/address', 					'ProfileController@address');
				Route::any('profile/vox', 						'ProfileController@vox');
				Route::any('profile/home', 						'ProfileController@home');
				Route::any('profile/info', 						'ProfileController@info');
				Route::post('profile/info/upload', 				'ProfileController@upload');
				Route::post('profile/password', 				'ProfileController@change_password');
				Route::post('profile/balance', 					'ProfileController@balance');
				Route::post('profile/withdraw', 				'ProfileController@withdraw');
				Route::any('profile/invite', 					'ProfileController@invite');
				Route::any('profile/setEmail', 					'ProfileController@setEmail');
				Route::post('profile/jwt', 						'ProfileController@jwt');
				Route::any('profile/privacy', 					'ProfileController@privacy');
				Route::any('profile/privacy-download', 			'ProfileController@privacy_download');
				
				Route::get('gdpr', 								'ProfileController@gdpr');
			});

			Route::get('/', 									'IndexController@home');
			Route::get('welcome-survey', 								'IndexController@welcome');
			Route::any('appeal', 								'IndexController@appeal');
			Route::any('accept-gdpr', 							'IndexController@gdpr');
			Route::get('{slug}', 								'PagesController@home');

		});
	});
};
Route::domain('dentavox.dentacoin.com')->group($voxRoutes);
Route::domain('dev-dentavox.dentacoin.com')->group($voxRoutes);
