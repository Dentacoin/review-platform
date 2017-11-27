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

Route::group(['prefix' => 'cms', 'namespace' => 'Admin', 'middleware' => ['admin'] ], function () {
	Route::get('/', 								'HomeController@list');
	Route::get('home', 								'HomeController@list');

	Route::post('translations/{subpage?}/add', 									'TranslationsController@add');
	Route::post('translations/{subpage?}/update', 								'TranslationsController@update');
	Route::get('translations/{subpage?}/export/{source}', 					'TranslationsController@export');
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

	Route::get('users', 							'UsersController@list');
	Route::any('users/loginas/{id}', 				'UsersController@loginas');
	Route::any('users/edit/{id}', 					'UsersController@edit');
	Route::any('users/edit/{id}/deleteavatar', 		'UsersController@delete_avatar');
	Route::any('users/edit/{id}/deletephoto/{position}', 'UsersController@delete_photo');
	Route::any('users/delete/{id}', 				'UsersController@delete');
	Route::any('users/restore/{id}', 				'UsersController@restore');

	Route::get('reviews', 							'ReviewsController@list');
	Route::any('reviews/add', 						'ReviewsController@add');
	Route::any('reviews/delete/{id}', 				'ReviewsController@delete');
	Route::any('reviews/restore/{id}', 				'ReviewsController@restore');
	Route::any('reviews/edit/{id}', 				'ReviewsController@edit');
	
	Route::get('pages', 							'PagesController@list');
	Route::any('pages/add', 						'PagesController@add');
	Route::any('pages/edit/{id}', 					'PagesController@edit');
	Route::any('pages/edit/{id}/removepic', 		'PagesController@removepic');
	Route::any('pages/delete/{id}', 				'PagesController@delete');

	Route::get('questions', 						'QuestionsController@list');
	Route::any('questions/add', 					'QuestionsController@add');
	Route::any('questions/edit/{id}', 				'QuestionsController@edit');
	Route::any('questions/delete/{id}', 			'QuestionsController@delete');

	Route::get('vox', 								'VoxesController@list');
	Route::get('vox/list', 							'VoxesController@list');
	Route::any('vox/add', 							'VoxesController@add');
	Route::any('vox/edit/{id}', 					'VoxesController@edit');
	Route::get('vox/delete/{id}', 					'VoxesController@delete');
	Route::post('vox/edit/{id}/question/add', 		'VoxesController@add_question');
	Route::any('vox/edit/{id}/question/{question_id}', 		'VoxesController@edit_question');
	Route::get('vox/edit/{id}/question-del/{question_id}', 		'VoxesController@delete_question');
	Route::get('vox/ideas', 						'VoxesController@ideas');

	Route::get('emails', 							'EmailsController@list');
	Route::get('emails/edit/{id}', 					'EmailsController@edit');
	Route::post('emails/edit/{id}', 				'EmailsController@save');

	Route::any('rewards', 							'RewardsController@list');
});


//Empty route

Route::domain('reviews.dentacoin.com')->group(function () {

	Route::group(['prefix' => '{locale?}'], function(){

		Route::get('login', 									[ 'as' => 'login', 'uses' => 'Auth\AuthenticateUser@showLoginForm'] );
		Route::post('login',									'Auth\AuthenticateUser@postLogin');
		Route::get('logout',									'Auth\AuthenticateUser@getLogout');

		Route::group(['namespace' => 'Front'], function () {
			Route::get('/', 									'IndexController@home');

			Route::get('test', 									'DentistController@test');
			
			Route::any('invite/{id}/{hash}/{inv_id}', 			'RegisterController@invite_accept');
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

			Route::get('login/callback/facebook', 				'LoginController@facebook_callback');
			Route::get('login/callback/twitter', 				'LoginController@twitter_callback');
			Route::get('login/callback/gplus', 					'LoginController@gplus_callback');

			Route::get('register/facebook/{is_dentist}', 		'LoginController@facebook_register');
			Route::get('register/twitter/{is_dentist}', 		'LoginController@twitter_register');
			Route::get('register/gplus/{is_dentist}', 			'LoginController@gplus_register');

			Route::get('register/callback/facebook', 			'LoginController@facebook_callback_register');
			Route::get('register/callback/twitter', 			'LoginController@twitter_callback_register');
			Route::get('register/callback/gplus', 				'LoginController@gplus_callback_register');

			Route::get('dentists/p/{page?}', 					'DentistsController@paginate');
			Route::get('dentists/{country?}/{city?}', 			'DentistsController@list');

			Route::get('dentist/{slug}/confirm-review/{secret}', 	'DentistController@confirmReview');
			Route::post('dentist/{slug}/reply/{review_id}', 	'DentistController@reply');
			Route::post('dentist/{slug}/claim-phone', 			'DentistController@claim');
			Route::post('dentist/{slug}/claim-code', 			'DentistController@code');
			Route::post('dentist/{slug}/claim-password', 		'DentistController@password');
			Route::post('dentist/{slug}/claim-email', 			'DentistController@email');
			Route::any('dentist/{slug}/{review_id}', 			'DentistController@list');
			Route::any('dentist/{slug}', 						'DentistController@list');

			Route::any('add', 									'AddController@list');

			Route::group(['middleware' => 'auth:web'], function () {
				Route::get('profile', 							'ProfileController@home');
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

				Route::any('profile/reward', 					'ProfileController@reward');

				
			});

			Route::get('{slug}', 								'PagesController@home');
		});
	});
});

Route::domain('dentavox.dentacoin.com')->group(function () {

	Route::group(['prefix' => '{locale?}'], function(){

		Route::get('login', 									[ 'as' => 'login', 'uses' => 'Auth\AuthenticateUser@showLoginForm'] );
		Route::post('login',									'Auth\AuthenticateUser@postLoginVox');
		Route::get('logout',									'Auth\AuthenticateUser@getLogout');

		Route::group(['namespace' => 'Vox'], function () {

			Route::get('banned', 								'BannedController@home');

			Route::post('register', 							'RegisterController@register');
			Route::get('verify/{id}/{hash}', 					'RegisterController@register_verify');
			Route::get('forgot-password', 						'RegisterController@forgot');
			Route::post('forgot-password', 						'RegisterController@forgot_form');
			Route::get('recover/{id}/{hash}', 					'RegisterController@recover');
			Route::post('recover/{id}/{hash}', 					'RegisterController@recover_form');

			Route::get('login/facebook', 						'LoginController@facebook_login');
			Route::get('login/callback/facebook', 				'LoginController@facebook_callback');

			Route::get('register/facebook', 					'LoginController@facebook_register');
			Route::get('register/callback/facebook', 			'LoginController@facebook_callback_register');

			
			Route::group(['middleware' => 'auth:web'], function () {
				Route::any('questionnaire/{id}', 				'VoxController@home');

				Route::post('phone/save', 						'PhoneController@save');
				Route::post('phone/check', 						'PhoneController@check');

				Route::any('profile', 							'ProfileController@home');
				Route::any('profile/home', 						'ProfileController@home');
				Route::any('profile/info', 						'ProfileController@info');
				Route::get('profile/password', 					'ProfileController@password');
				Route::post('profile/password', 				'ProfileController@change_password');
				Route::any('profile/wallet', 					'ProfileController@wallet');
				Route::post('profile/balance', 					'ProfileController@balance');
				Route::post('profile/withdraw', 				'ProfileController@withdraw');
			});

			Route::get('/', 									'IndexController@home');
			Route::get('stats/{id}', 							'StatsController@home');
			Route::get('{slug}', 								'PagesController@home');

		});
	});
});
