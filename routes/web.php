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

Route::get('cms/login', 											'Auth\AuthenticateAdmin@showLoginForm');
Route::post('cms/login',											'Auth\AuthenticateAdmin@postLogin');
Route::any('cms/password-expired',									'Auth\AuthenticateAdmin@passwordExpired');
Route::get('cms/logout', 											'Auth\AuthenticateAdmin@getLogout');
// Route::get('cms/admin-authentication', 							'Auth\AuthenticateAdmin@authentication');

Route::get('cities/{id}/{empty?}', 									'CitiesController@getCities');
Route::post('location', 											'CitiesController@getLocation');
Route::post('search-dentists', 										'CitiesController@searchDentists');
Route::post('dentist-location', 									'CitiesController@getDentistLocation');
Route::post('dentist-city', 										'CitiesController@getDentistsCities');
Route::any('suggest-clinic/{id?}', 									'CitiesController@getClinic');
Route::any('suggest-dentist/{id?}', 								'CitiesController@getDentist');
Route::get('custom-cookie', 										'SSOController@manageCustomCookie')->name('custom-cookie');
Route::post('get-unseen-notifications-count', 						'SSOController@getUnseenNotificationsCount');

Route::group(['prefix' => 'cms', 'namespace' => 'Admin', 'middleware' => ['admin'] ], function () {
	Route::get('/', 												'HomeController@list');
	Route::get('home', 												'HomeController@list');
	Route::any('admin-authentication', 								'HomeController@authentication');

	Route::post('translations/{subpage?}/add', 								'TranslationsController@add');
	Route::post('translations/{subpage?}/update', 							'TranslationsController@update');
	Route::get('translations/{subpage?}/export/{source}/{target}', 			'TranslationsController@export');
	Route::get('translations/{subpage?}/export-missing/{source}/{target}', 	'TranslationsController@export_missing');
	Route::post('translations/{subpage?}/import/{source}/{target}', 		'TranslationsController@import');
	Route::get('translations/{subpage?}/{source?}/{target?}', 				'TranslationsController@list');
	Route::get('translations/{subpage?}/{source?}/{target?}/del/{delkey?}', 'TranslationsController@delete');

	Route::get('admins/admins', 									'AdminsController@list');
	Route::get('admins/admins/delete/{id}',							'AdminsController@delete');
	Route::get('admins/admins/edit/{id}',							'AdminsController@edit');
	Route::post('admins/admins/edit/{id}',							'AdminsController@update');
	Route::post('admins/admins/add',								'AdminsController@add');
	Route::any('admins/ips',										'AdminsController@listIps');
	Route::get('admins/ips/delete/{id}',							'AdminsController@deleteIp');
	Route::get('admins/actions-history',							'AdminsController@actionsHistory');
	Route::get('admins/reset-auth/{id}',							'AdminsController@resetAuth');
	Route::get('admins/messages',									'AdminsController@messagesList');
	Route::post('admins/add-message',								'AdminsController@addMessage');
	Route::get('admins/profile', 									'AdminsController@profile');
	Route::any('read-admin-message/{id}', 							'AdminsController@readMessage');

	Route::any('blacklist', 										'BlacklistController@list');
	Route::get('blacklist/delete/{id}', 							'BlacklistController@delete');

	Route::any('whitelist/ips', 									'WhitelistIpsController@list');
	Route::get('whitelist/ips/delete/{id}', 						'WhitelistIpsController@delete');
	Route::any('whitelist/vpn-ips', 								'WhitelistIpsController@vpnList');
	Route::get('whitelist/vpn-ips/delete/{id}', 					'WhitelistIpsController@vpnDelete');

	Route::get('users', 											'UsersController@list'); //for old links
	Route::get('users/users', 										'UsersController@list');
	Route::post('users/users/mass-delete', 							'UsersController@massdelete');
	Route::post('users/users/mass-reject', 							'UsersController@massReject');
	Route::post('users/users/mass-approve', 						'UsersController@massApprove');
	Route::get('users/users/byweek', 								'UsersController@byweek');
	Route::any('users/users/loginas/{id}/{platform?}', 				'UsersController@loginas');
	Route::any('users/users/user-data/{id}', 						'UsersController@personal_data');
	Route::any('users/users/import', 								'UsersController@import');
	Route::any('users/users/upload-temp', 							'UsersController@upload_temp');
	Route::any('users/users/add', 									'UsersController@add');
	Route::any('users/edit/{id}', 									'UsersController@edit'); //for old links
	Route::any('users/users/edit/{id}', 							'UsersController@edit');
	Route::any('users/users/edit/{id}/deleteavatar', 				'UsersController@delete_avatar');
	Route::any('users/users/edit/{id}/deletephoto/{position}', 		'UsersController@delete_photo');
	Route::any('users/users/edit/{id}/deleteban/{banid}', 			'UsersController@delete_ban');
	Route::any('users/users/edit/{id}/restoreban/{banid}', 			'UsersController@restore_ban');
	Route::get('users/users/edit/{id}/delete-reward/{rewardid}', 	'UsersController@delete_vox');
	Route::get('users/users/edit/{id}/delete-unfinished/{vox_id}', 	'UsersController@delete_unfinished');
	Route::any('users/users/edit/{id}/add-edit-highlight/{h_id?}',	'UsersController@addOrEditHighlight');
	Route::get('users/users/edit/{user_id}/remove-highlight/{id}',	'UsersController@removeHighlight');
	Route::any('users/users/edit/{user_id}/highlights-reorder',		'UsersController@reorderHighlight');
	Route::any('users/users/delete/{id}', 							'UsersController@delete');
	Route::any('users/users/delete-database/{id}', 					'UsersController@deleteDatabase');
	Route::any('users/users/restore/{id}', 							'UsersController@restore');
	Route::any('users/users/restore-self-deleted/{id}', 			'UsersController@restore_self_deleted');
	Route::any('users/users/reviews/delete/{id}',		 			'UsersController@delete_review');
	Route::any('users/users/reset-first-guided-tour/{id}',			'UsersController@resetFirstGudedTour');
	Route::get('users/users/convert-to-dentist/{id}',				'UsersController@convertToDentist');
	Route::get('users/users/convert-to-patient/{id}',				'UsersController@convertToPatient');
	Route::get('users/users/info/{id}',								'UsersController@userInfo');
	Route::get('users/rewards',										'UsersController@rewards');
	Route::get('users/bans',										'UsersController@bans');
	Route::get('users/lost_users',									'UsersController@lostUsers');
	Route::get('users/users_stats', 								'UsersController@usersStats');
	Route::post('users/answered-questions-count',					'UsersController@answeredQuestionsCount');
	Route::any('users/registrations', 								'UsersController@registrations');
	Route::any('users/incomplete-registrations', 					'UsersController@incompleteRegs');
	Route::any('users/lead-magnet', 								'UsersController@leadMagnet');
	Route::any('users/make-partners', 								'UsersController@makePartners');

	Route::get('users/anonymous_users', 							'AnonymousUsersController@anonymous_list');
	Route::get('users/anonymous_users/delete/{id}',					'AnonymousUsersController@anonymousDelete');

	Route::get('invites',											'InvitesController@list');
	Route::get('invites/delete/{id}',								'InvitesController@delete');

	Route::get('trp/reviews', 										'ReviewsController@list');
	Route::post('trp/reviews/mass-delete', 							'ReviewsController@massdelete');
	Route::any('trp/reviews/add', 									'ReviewsController@add');
	Route::any('trp/reviews/delete/{id}', 							'ReviewsController@delete');
	Route::any('trp/reviews/restore/{id}', 							'ReviewsController@restore');
	Route::any('trp/reviews/edit/{id}', 							'ReviewsController@edit');

	Route::get('trp/questions', 									'QuestionsController@list');
	Route::any('trp/questions/add', 								'QuestionsController@add');
	Route::any('trp/questions/edit/{id}', 							'QuestionsController@edit');
	Route::post('trp/questions/reorder', 							'QuestionsController@reorderQuestions');

	Route::any('trp/faq/{locale?}', 								'FaqController@faq');

	Route::any('trp/youtube', 										'YoutubeController@list');
	Route::any('trp/youtube/approve/{id}', 							'YoutubeController@approve');
	Route::any('trp/youtube/delete/{id}', 							'YoutubeController@delete');
	Route::any('trp/youtube/new-token', 							'YoutubeController@generateNewAccessToken');

	Route::get('trp/testimonials', 									'TestimonialSliderController@list');
	Route::post('trp/testimonials/add', 							'TestimonialSliderController@add');
	Route::any('trp/testimonials/edit/{id}', 						'TestimonialSliderController@edit');
	Route::any('trp/testimonials/edit/{id}/addavatar', 				'TestimonialSliderController@add_avatar');
	Route::any('trp/testimonials/delete/{id}', 						'TestimonialSliderController@delete');
	Route::post('trp/testimonials/export', 							'TestimonialSliderController@export');
	Route::post('trp/testimonials/import', 							'TestimonialSliderController@import');
	Route::post('trp/testimonials/reorder', 						'TestimonialSliderController@reorderTestimonials');

	Route::any('trp/scrape-google-dentists', 						'ScrapeGoogleDentistsController@list');
	Route::any('trp/scrape-google-dentists/{id}', 					'ScrapeGoogleDentistsController@download');

	Route::get('trp/clinic-branches', 								'BranchesController@clinicBranches');
	Route::any('trp/add-clinic-branch', 							'BranchesController@addClinicBranch');

	Route::any('transactions', 										'TransactionsController@list');
	Route::any('transactions/edit/{id}', 							'TransactionsController@edit');
	Route::any('transactions/bump/{id}', 							'TransactionsController@bump');
	Route::any('transactions/stop/{id}', 							'TransactionsController@stop');
	Route::any('transactions/delete/{id}', 							'TransactionsController@delete');
	Route::any('transactions/pending/{id}', 						'TransactionsController@pending');
	Route::post('transactions/mass-bump', 							'TransactionsController@massbump');
	Route::post('transactions/mass-stop', 							'TransactionsController@massstop');
	Route::post('transactions/mass-pending', 						'TransactionsController@massPending');
	Route::get('transactions/bump-dont-retry', 						'TransactionsController@bumpDontRetry');
	Route::get('transactions/start', 								'TransactionsController@allowWithdraw');
	Route::get('transactions/stop', 								'TransactionsController@disallowWithdraw');
	Route::get('transactions/start-hash-check', 					'TransactionsController@startHashCheck');
	Route::get('transactions/stop-hash-check', 						'TransactionsController@stopHashCheck');
	Route::get('transactions/remove-message', 						'TransactionsController@removeMessage');
	Route::get('transactions/add-message', 							'TransactionsController@addMessage');
	Route::post('transactions/conditions', 							'TransactionsController@withdrawalConditions');
	Route::get('transactions/scammers', 							'TransactionsController@scammers');
	Route::get('transactions/scammers/{id}', 						'TransactionsController@scammersChecked');
	Route::get('transactions/scammers-balance', 					'TransactionsController@scammersBalance');
	Route::get('transactions/scammers-balance/{id}',				'TransactionsController@scammersBalanceChecked');
	Route::get('transactions/disable-retry', 						'TransactionsController@disableRetry');
	Route::get('transactions/enable-retry', 						'TransactionsController@enableRetry');
	Route::post('transactions/user-suspicious/{id}',				'TransactionsController@makeUserSuspicious');
	Route::any('transactions/checked-by-admin/{id}',				'TransactionsController@checkedByAdmin');
	Route::post('check-pending-trans', 								'TransactionsController@checkPendingTransactions');
	Route::post('check-nodes', 										'TransactionsController@checkConnectedNodes');

	Route::get('spending', 											'SpendingController@list');

	Route::get('vox', 												'VoxesController@list');
	Route::any('vox/list', 											'VoxesController@list');
	Route::get('vox/list/show-all-results', 						'VoxesController@showAllResults');
	Route::get('vox/list/show-individual-results', 					'VoxesController@showIndividualResults');
	Route::post('vox/list/reorder', 								'VoxesController@reorderVoxes');
	Route::any('vox/add', 											'VoxesController@add');
	Route::any('vox/edit-field/{id}/{field}/{value}',				'VoxesController@edit_field');
	Route::any('vox/edit/{id}', 									'VoxesController@edit');
	Route::any('vox/edit/{id}/delpic', 								'VoxesController@delpic');
	Route::any('vox/edit/{id}/export', 								'VoxesController@export');
	Route::any('vox/edit/{id}/import', 								'VoxesController@import');
	Route::any('vox/edit/{id}/import-quick', 						'VoxesController@import_quick');
	Route::get('vox/delete/{id}', 									'VoxesController@delete');
	Route::post('vox/edit/{id}/question/add', 						'VoxesController@add_question');
	Route::any('vox/edit/{id}/question/{question_id}', 				'VoxesController@edit_question');
	Route::any('vox/edit/{id}/question/{question_id}/delete-question-image', 'VoxesController@deleteQuestionImage');
	Route::any('vox/edit/{id}/question/{question_id}/delete-answer-image/{answer}', 'VoxesController@deleteAnswerImage');
	Route::post('vox/edit/question-del/{question_id}', 				'VoxesController@delete_question');
	Route::any('vox/edit/{id}/change-all', 							'VoxesController@reorder');
	Route::post('vox/edit/{id}/check-for-vox-changes', 				'VoxesController@checkVoxForChanges');
	Route::get('vox/duplicate/{id}', 								'VoxesController@duplicateSurvey');
	Route::get('vox/categories', 									'VoxesController@categories');
	Route::any('vox/categories/add', 								'VoxesController@add_category');
	Route::any('vox/categories/edit/{id}', 							'VoxesController@edit_category');
	Route::any('vox/categories/edit/{id}/delpic',   				'VoxesController@delete_cat_image');
	Route::any('vox/categories/delete/{id}', 						'VoxesController@delete_category');
	Route::get('vox/scales', 										'VoxesController@scales');
	Route::any('vox/scales/add', 									'VoxesController@add_scale');
	Route::any('vox/scales/edit/{id}', 								'VoxesController@edit_scale');
	Route::any('vox/scales/delete/{id}', 							'VoxesController@delete_scale');
	Route::any('vox/faq', 											'VoxesController@faq');
	Route::any('vox/faq-ios', 										'VoxesController@faqiOS');
	Route::any('vox/tests', 										'VoxesController@test');
	Route::any('vox/explorer/{vox_id?}/{question_id?}', 			'VoxesController@explorer');
	Route::any('vox/export-survey-data', 							'VoxesController@export_survey_data');
	Route::any('vox/duplicate-question', 							'VoxesController@duplicate_question');
	Route::post('search-questions', 								'VoxesController@getTitle');
	Route::post('vox-questions/mass-delete', 						'VoxesController@massdelete');
	Route::any('vox/export-stats', 									'VoxesController@exportStats');
	Route::post('vox/get-questions-count/{id}', 					'VoxesController@getQuestionsCount');
	Route::post('vox/get-respondents-count/{id}', 					'VoxesController@getRespondentsCount');
	Route::post('vox/get-respondents-question-count/{id}', 			'VoxesController@getRespondentsQuestionCount');
	Route::post('vox/get-reward/{id}', 								'VoxesController@getReward');
	Route::post('vox/get-duration/{id}', 							'VoxesController@getDuration');
	Route::post('vox/hide-survey/{id}', 							'VoxesController@hideSurvey');
	Route::post('vox/get-question-content/{id}', 					'VoxesController@getQuestionContent');
	Route::post('vox/add-question-content/{id}', 					'VoxesController@addQuestionContent');
	Route::any('vox/history', 										'VoxesController@voxesHistory');
	Route::any('vox/errors-resolved', 								'VoxesController@errorsResolved');

	Route::get('vox/polls', 										'PollsController@list');
	Route::any('vox/polls/add', 									'PollsController@add');
	Route::any('vox/polls/edit/{id}', 								'PollsController@edit');
	Route::any('vox/polls/edit/{id}/import', 						'PollsController@import');
	Route::get('vox/polls/delete/{id}', 							'PollsController@delete');	
	Route::any('vox/polls/change-date/{id}', 						'PollsController@change_poll_date');
	Route::any('vox/polls/change-question/{id}', 					'PollsController@change_poll_question');
	Route::any('vox/polls/duplicate/{id}', 							'PollsController@duplicate_poll');
	Route::any('vox/polls-explorer/{id?}', 							'PollsController@polls_explorer');
	Route::any('vox/polls-monthly-description', 					'PollsController@pollsMonthlyDescriptions');
	Route::any('vox/polls-monthly-description/add', 				'PollsController@pollsMonthlyDescriptionsAdd');
	Route::any('vox/polls-monthly-description/edit/{id}', 			'PollsController@pollsMonthlyDescriptionsEdit');
	Route::any('vox/polls-monthly-description/delete/{id}', 		'PollsController@pollsMonthlyDescriptionsDelete');

	Route::get('vox/paid-reports', 									'PaidReportsController@list');
	Route::any('vox/paid-reports/add', 								'PaidReportsController@add');
	Route::any('vox/paid-reports/edit/{id}', 						'PaidReportsController@edit');
	Route::post('vox/paid-reports/delete-gallery-photo/{id}', 		'PaidReportsController@deleteGalleryPhoto');
	Route::get('vox/paid-reports/delete/{id}', 						'PaidReportsController@delete');
	
	Route::get('orders', 											'OrdersController@list');
	Route::get('orders/sended/{id}', 								'OrdersController@sended');
	Route::post('orders/add-payment-info/{id}', 					'OrdersController@addPaymentInfo');

	Route::get('emails', 											'EmailsController@list');
	Route::get('emails/{what?}', 									'EmailsController@list');
	Route::get('emails/edit/{id}', 									'EmailsController@edit');
	Route::post('emails/edit/{id}', 								'EmailsController@save');
	Route::post('emails/add', 										'EmailsController@add');
	Route::get('emails/trp/send-engagement-email',  				'EmailsController@engagement_email');
	Route::get('emails/trp/send-monthly-email',  					'EmailsController@monthly_email');
	Route::get('email_validations/email_validations',				'EmailsController@list_validations');
	Route::any('email_validations/email_validations/valid/{id}',	'EmailsController@mark_valid');
	Route::get('email_validations/email_validations/stop',			'EmailsController@stop_validations');
	Route::get('email_validations/email_validations/start',			'EmailsController@start_validations');
	Route::get('email_validations/invalid_emails',					'EmailsController@invalid_emails');
	Route::get('email_validations/invalid_emails/delete/{id}',		'EmailsController@invalid_delete');
	Route::post('email_validations/invalid_emails/new',				'EmailsController@invalid_new');
	Route::get('email_validations/old_emails',						'EmailsController@old_emails');
	Route::get('email_validations/old_emails/delete/{id}',			'EmailsController@old_emails_delete');

	Route::any('rewards', 											'RewardsController@list');

	Route::any('claims/approve/{id}', 								'DentistClaimsController@approve');
	Route::any('claims/reject/{id}', 								'DentistClaimsController@reject');
	Route::any('claims/suspicious/{id}', 							'DentistClaimsController@suspicious');

	Route::get('vox/recommendations', 								'RecommendationsController@list');

	Route::get('pages/vox', 										'PagesSeoController@vox_list');
	Route::get('pages/trp', 										'PagesSeoController@trp_list');
	Route::any('pages/{platform}/add', 								'PagesSeoController@add');
	Route::any('pages/edit/{id}', 									'PagesSeoController@edit');
	Route::any('pages/edit/{id}/removepic', 						'PagesSeoController@removepic');
	Route::any('pages/{platform}/export', 							'PagesSeoController@export');
	Route::any('pages/{platform}/import', 							'PagesSeoController@import');

	Route::any('logs/{type?}', 										'LogsController@list');
	
	Route::any('export-import', 									'ImportExportController@list');

	Route::get('ban_appeals',	 									'BanAppealsController@list');
	Route::any('ban_appeals/approve/{id}', 							'BanAppealsController@approve');
	Route::any('ban_appeals/reject/{id}', 							'BanAppealsController@reject');
	Route::any('ban_appeals/pending/{id}', 							'BanAppealsController@pending');
	Route::any('ban_appeals/info/{id}', 							'BanAppealsController@userInfo');

	Route::any('support/content', 									'SupportController@questions');
	Route::any('support/content/add', 								'SupportController@add_question');
	Route::any('support/content/edit/{id}', 						'SupportController@edit_question');
	Route::any('support/content/delete/{id}', 						'SupportController@delete_question');
	Route::any('support/content/reorder', 							'SupportController@questionsReorder');
	Route::any('support/categories', 								'SupportController@categories');
	Route::any('support/categories/add', 							'SupportController@add_category');
	Route::any('support/categories/edit/{id}', 						'SupportController@edit_category');
	Route::any('support/categories/delete/{id}', 					'SupportController@delete_category');
	Route::any('support/categories/reorder', 						'SupportController@categoriesReorder');
	Route::any('support/contact', 									'SupportController@contact');
	Route::post('support/contact/{id}', 							'SupportController@sendAnswer');
	Route::post('support/contact/load-template/{id}',				'SupportController@loadTemplate');	
	Route::post('support/contact/delete/{id}',						'SupportController@deleteContact');

	Route::any('ips/bad', 											'IPsController@bad');
	Route::any('ips/vpn', 											'IPsController@vpn');

	Route::get('meetings',											'MeetingsController@list');
	Route::any('meetings/edit/{id}',								'MeetingsController@edit');

	Route::get('/images/{folder}/{id}/{thumb?}',					'ImagesController@getImage');
});


Route::group(['prefix' => 'api', 'namespace' => 'Api' ], function () {
	Route::get('index-voxes', 										'IndexController@indexVoxes');
	Route::get('users-stats', 										'IndexController@headerStats');
	Route::get('is-dentacoin-down', 			    				'IndexController@isDentacoinDown');
    Route::get('is-online', 			            				'IndexController@isOnline');
	Route::post('save-user-device',									'IndexController@saveUserDevice');
	Route::post('api-log-user',										'IndexController@apiLogUser');
	Route::get('get-ban-time-left',									'IndexController@getBanTimeLeft');
	Route::get('get-ban-info',										'IndexController@getBanInfo');
	Route::post('encrypt-user-token',								'IndexController@encryptUserToken');
	Route::post('social-profile-link',								'IndexController@socialProfileLink');
	Route::post('social-profile-image',								'IndexController@socialProfileImage');

	Route::get('all-voxes', 										'PaidDentalSurveysController@allVoxes');
	Route::get('get-voxes', 										'PaidDentalSurveysController@getVoxes');

	Route::get('get-faq', 											'FaqController@getFaq');
	
	Route::get('all-stats', 										'StatsController@allStats');

	Route::post('get-next-question', 								'VoxController@getNextQuestion');
	Route::post('survey-answer', 									'VoxController@surveyAnswer');
	Route::post('start-over', 										'VoxController@startOver');
	Route::get('welcome-survey',									'VoxController@welcomeSurvey');
	Route::post('welcome-survey-reward',							'VoxController@welcomeSurveyReward');
	Route::get('do-vox/{slug}',										'VoxController@doVox');
	Route::get('voxes-daily-limit',									'VoxController@dailyLimitReached');
	Route::post('dentist-request-survey',							'VoxController@dentistRequestSurvey');
	Route::any('recommend-dentavox',								'VoxController@recommendDentavox');
	
	Route::get('get-daily-polls',									'DailyPollsController@getPolls');
	Route::get('get-poll-content',									'DailyPollsController@getPollContent');
	Route::get('get-poll-stats',									'DailyPollsController@getPollStats');
	Route::post('poll/{id}', 										'DailyPollsController@doPoll');
	Route::post('poll-reward', 										'DailyPollsController@dailyPollReward');
	Route::get('poll-reward-price',									'DailyPollsController@pollRewardPrice');
	Route::post('todays-poll-answer',								'DailyPollsController@todaysPollAnswer');
	Route::post('get-daily-poll-by-date',							'DailyPollsController@getDailyPollByDate');
});


//Empty route
$reviewRoutes = function() {

	Route::any('test', 												'Front\YouTubeController@test');
	Route::post('civic', 											'CivicController@add');

	Route::get('sitemap-trusted-reviews.xml', 						'Front\SitemapController@links');
	Route::get('sitemap.xml', 										'Front\SitemapController@sitemap');
	Route::get('robots.txt', 										'Front\RobotsController@content');

	//after 20th refresh -> ban for 60 min
	Route::group(['middleware' => 'throttleIp:20,60'], function() {
		Route::get('user-logout',									'Auth\AuthenticateUser@getLogout');
		Route::post('authenticate-user',							'Auth\AuthenticateUser@authenticateUser');
	});

	Route::post('get-popup', 										'Front\IndexController@getPopup');
	
	Route::group(['prefix' => '{locale?}'], function() {

		//after 20th refresh -> ban for 60 min
		Route::group(['middleware' => 'throttleIp:20,60'], function() {
			Route::get('login', 									[ 'as' => 'login', 'uses' => 'Auth\AuthenticateUser@showLoginForm'] );
			Route::get('logout',									'Auth\AuthenticateUser@getLogout');
		});

		Route::get('widget/{id}/{hash}/{mode}', 					'WidgetController@widget');
		Route::any('widget-new/{id}/{hash}', 						'WidgetController@widget_new');

		Route::group(['namespace' => 'Front'], function() {

			//after 20th refresh -> ban for 60 min
			Route::group(['middleware' => 'throttleIp:20,60'], function() {
				Route::get('/', 									'IndexController@home');
				Route::any('welcome-dentist/claim/{id}/',			'IndexController@claim');
				Route::get('welcome-dentist/{session_id?}/{hash?}',	'IndexController@dentist');
				Route::get('remove-banner',							'IndexController@removeBanner');
				
				Route::post('lead-magnet-step1', 					'IndexController@lead_magnet_step1');
				Route::post('lead-magnet-step2', 					'IndexController@lead_magnet_step2');
				Route::get('lead-magnet-session', 					'IndexController@lead_magnet_session');
				Route::get('lead-magnet-results', 					'IndexController@redirectPage');
				Route::get('review-score-test', 					'IndexController@leadMagnetSurvey');
				Route::get('review-score-results', 					'IndexController@leadMagnetResults');
	
				Route::get('unsubscribe/{user_id}/{hash}', 			'UnsubscribeController@unsubscribe');
				Route::get('unsubscription/{user_id}/{hash}', 		'UnsubscribeController@new_unsubscribe');
				Route::get('unsubscribe-incomplete/{id}/{hash}', 	'UnsubscribeController@unsubscribe_incomplete');
	
				Route::post('register/upload', 						'RegisterController@upload');
				Route::get('register', 								'RegisterController@register');
				Route::post('verification-dentist', 				'RegisterController@verificationDentistShortDescription');
				Route::post('verification-dentist-work-hours', 		'RegisterController@verificationDentistWorkHours');
				Route::post('clinic-add-team', 						'RegisterController@clinic_add_team');
	
				Route::post('status', 								'LoginController@status');

				Route::any('dentist/{slug}/claim/{id}/',			'DentistController@claim_dentist');
				Route::post('dentist/{slug}/reply/{review_id}', 	'DentistController@reply');
				Route::get('dentist/{slug}/ask/{verification?}',	'DentistController@ask');
				Route::any('youtube', 								'DentistController@youtube');
				Route::post('recommend-dentist', 					'DentistController@recommend_dentist');

				Route::get('{slug}/branches', 						'BranchesController@branchesPage');	
	
				Route::get('page-not-found', 						'NotFoundController@home');
	
				Route::get('banned', 								'BannedController@home');
				Route::get('profile-redirect', 						'BannedController@profile_redirect');
			});

			Route::post('index-down', 								'IndexController@index_down');
			Route::post('index-dentist-down', 						'IndexController@index_dentist_down');

			Route::post('register-invite', 							'RegisterController@register_invite');
			Route::post('invite-dentist', 							'RegisterController@invite_dentist');

			Route::get('dentists/{query?}/{filter?}', 				'DentistsController@search');
			Route::get('dentist-listings-by-country', 				'DentistsController@country');
			Route::get('dentists-in-{country_slug}', 				'DentistsController@state');
			Route::get('dentists-in-{country_slug}/{state}', 		'DentistsController@city');
			Route::any('dentist/{slug}/{review_id}', 				'DentistController@list');
			Route::any('dentist/{slug}', 							'DentistController@list');
			Route::post('dentist-down', 							'DentistController@dentist_down');
			Route::any('full-review/{id}',							'DentistController@fullReview');
			Route::get('review/{id}', 								'DentistController@fullReview');
			Route::post('facebook-tab', 							'DentistController@dentist_fb_tab');
			Route::any('facebook-tab-reviews', 						'DentistController@dentist_fb_tab_reviews');
			Route::post('dentist-fb-tab', 							'DentistController@fb_tab');
	
			Route::post('profile/invite-new', 						'InvitationsController@inviteTeamMember');
			Route::post('profile/add-existing-dentist-team', 		'InvitationsController@inviteExistingTeamMember');
			Route::post('profile/dentists/invite', 					'InvitationsController@inviteDentist');

			Route::post('invite-new-dentist', 						'AddDentistController@invite_new_dentist');
	
			Route::get('faq', 										'FaqController@home');

			Route::group(['middleware' => 'auth:web'], function() {

				Route::post('write-review/{step?}', 				'DentistController@writeReview');
                Route::post('verify-review', 						'DentistController@verifyReview');

				Route::post('invite-patient-again',					'InvitationsController@invitePatientAgain');
				Route::post('profile/invite-whatsapp', 				'InvitationsController@inviteWhatsapp');
				Route::post('profile/invite-copypaste', 			'InvitationsController@inviteCopypaste');
				Route::post('profile/invite-copypaste-final', 		'InvitationsController@inviteCopypasteFinal');
				Route::post('profile/invite-file',			 		'InvitationsController@inviteFile');
				Route::get('profile/invites/delete/{id}', 			'InvitationsController@invitesDelete');
				Route::post('profile/asks/{type}/{id}', 			'InvitationsController@asksActions');
				Route::get('profile/dentists/{type}/{id}', 			'InvitationsController@teamMemberActions');
				Route::get('profile/clinics/delete/{id}', 			'InvitationsController@clinicDeletesTeamMember');
				Route::post('profile/clinics/invite', 				'InvitationsController@inviteClinic');

				Route::post('profile/gallery/{id?}', 				'ProfileController@gallery');
				Route::get('profile/gallery/delete/{id}', 			'ProfileController@gallery_delete');
				Route::post('profile/info/{id?}', 					'ProfileController@editUser');
				Route::post('profile/lang-delete/{id?}', 			'ProfileController@deleteLanguage');
				Route::post('profile/add-announcement/{id?}', 		'ProfileController@addAnnouncement');
				Route::get('profile/trp-iframe', 					'ProfileController@trp');
				Route::get('profile/check-assurance', 				'ProfileController@checkAssurance');
				Route::get('profile/check-dentacare', 				'ProfileController@checkDentacare');
				Route::get('profile/check-reviews', 				'ProfileController@checkReviews');
				Route::get('profile/first-guided-tour', 			'ProfileController@firstGuidedTour');
				Route::get('profile/first-guided-tour-remove', 		'ProfileController@removeFirstGuidedTour');
				Route::get('profile/reviews-guided-tour-remove', 	'ProfileController@removeReviewsGuidedTour');
				Route::get('profile/reviews-guided-tour/{layout?}', 'ProfileController@reviewsGuidedTour');
				Route::post('add-wallet-address', 					'ProfileController@addWalletAddress');
				Route::post('close-partner-wallet-popup', 			'ProfileController@closePartnerWalletPopup');

				Route::post('profile/add-new-branch/{step?}', 		'BranchesController@addNewBranch');
				Route::post('delete-branch', 						'BranchesController@deleteBranch');				
			});
			
			Route::get('{query?}/{filter?}', 						'DentistsController@search')->where('locale', '(en)');
		});
	});
};
Route::domain('reviews.dentacoin.com')->group($reviewRoutes);
Route::domain('urgent.reviews.dentacoin.com')->group($reviewRoutes);


$voxRoutes = function() {
	Route::get('sitemap-dentavox.xml', 								'Vox\SitemapController@links');
	Route::get('sitemap.xml', 										'Vox\SitemapController@sitemap');
	Route::get('robots.txt', 										'Vox\RobotsController@content');
	Route::post('get-popup', 										'Vox\IndexController@getPopup');

	//after 20th refresh -> ban for 60 min
	Route::group(['middleware' => 'throttleIp:20,60'], function() {
		Route::get('user-logout',									'Auth\AuthenticateUser@getLogout');
		Route::post('authenticate-user',							'Auth\AuthenticateUser@authenticateUser');
	});

	Route::group(['prefix' => '{locale?}'], function() {
		//after 20th refresh -> ban for 60 min
		Route::group(['middleware' => 'throttleIp:20,60'], function() {
			Route::get('logout',									'Auth\AuthenticateUser@getLogout');
			Route::get('login', 									[ 'as' => 'login', 'uses' => 'Auth\AuthenticateUser@showLoginForm'] );
		});

		Route::group(['namespace' => 'Vox'], function() {
			//after 20th refresh -> ban for 60 min
			Route::group(['middleware' => 'throttleIp:20,60'], function() {
				Route::get('registration', 							'RegisterController@list');

				Route::get('unsubscribe/{user_id}/{hash}', 			'UnsubscribeController@unsubscribe');
				Route::get('unsubscription/{user_id}/{hash}', 		'UnsubscribeController@new_unsubscribe');
				Route::get('unsubscribe-incomplete/{id}/{hash}', 	'UnsubscribeController@unsubscribe_incomplete');

				Route::get('banned', 								'BannedController@home');
				Route::get('profile-redirect', 						'BannedController@profile_redirect');
				
				Route::any('status', 								'LoginController@status');
			});
			
			//after 100th refresh -> ban for 60 min
			Route::group(['middleware' => 'throttleIp:100,60'], function() {
				Route::any('dental-survey-stats', 					'StatsController@home');
				Route::any('dental-survey-stats/{id}', 				'StatsController@stats');
				Route::post('create-stat-pdf', 						'StatsController@createPdf');
				Route::post('create-stat-png', 						'StatsController@createPng');
				Route::any('download-statistics', 					'StatsController@download');
				Route::any('download-pdf/{name}', 					'StatsController@download_file');
				Route::any('download-png/{name}', 					'StatsController@download_file_png');

				Route::any('paid-dental-surveys', 					'IndexController@surveys_public');
				Route::post('start-over', 							'VoxController@start_over');
				Route::get('remove-banner',							'VoxController@removeBanner');

				Route::any('daily-polls', 							'PollsController@list');

				Route::get('dental-industry-reports', 						'PaidReportsController@home');
				Route::get('dental-industry-reports/{slug}', 				'PaidReportsController@singleReport');
				Route::any('dental-industry-reports/{slug}/checkout', 		'PaidReportsController@reportCheckout');
				Route::any('dental-industry-reports/{slug}/payment/{id}', 	'PaidReportsController@reportPayment');

				Route::get('faq', 									'FaqController@home');
			});

			Route::any('daily-polls/{date}', 						'PollsController@show_popup_poll');
			Route::any('daily-polls/{date}/stats', 					'PollsController@show_popup_stats_poll');
			Route::post('poll/{id}', 								'PollsController@dopoll');
			Route::post('get-poll-content/{id}', 					'PollsController@getPollContent');
			Route::post('get-poll-stats/{id}', 						'PollsController@getPollStats');
			Route::get('hide-dailypoll', 							'PollsController@hidePoll');
			Route::get('polls-calendar-html', 						'PollsController@getCalendarHtml');

			Route::post('vox-public-down', 							'VoxController@vox_public_down');
			Route::any('paid-dental-surveys/{id}', 					'VoxController@vox');
			Route::post('get-next-question', 						'VoxController@getNextQuestion');

			Route::any('profile/vox-iframe', 						'ProfileController@vox');

			Route::group(['middleware' => 'auth:web'], function() {
				Route::post('profile/info/upload', 					'ProfileController@upload');
				Route::post('social-profile', 						'ProfileController@socialProfile');
			});

			Route::post('index-down', 								'IndexController@index_down');
			Route::any('voxes-sort', 								'IndexController@voxesSort');
			Route::post('voxes-get', 								'IndexController@getVoxes');
			
			//after 20th refresh -> ban for 60 min
			Route::group(['middleware' => 'throttleIp:20,60'], function() {
				Route::get('/', 									'IndexController@home');
				Route::post('request-survey', 						'IndexController@request_survey');
				Route::post('request-survey-patients', 				'IndexController@request_survey_patients');
				Route::post('recommend', 							'IndexController@recommend');
				Route::get('welcome-survey', 						'IndexController@welcome');

				Route::get('page-not-found', 						'NotFoundController@home');
				Route::get('{catch?}', 								'NotFoundController@catch');
			});
		});
	});
};

Route::domain('vox.dentacoin.com')->group($voxRoutes);
Route::domain('dentavox.dentacoin.com')->group($voxRoutes);
Route::domain('urgent.dentavox.dentacoin.com')->group($voxRoutes);