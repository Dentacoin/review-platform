<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use App\Models\IncompleteRegistration;
use App\Models\WithdrawalsCondition;
use App\Models\ScrapeDentistResult;
use App\Models\UnclaimedDentist;
use App\Models\DcnTransaction;
use App\Models\ScrapeDentist;
use App\Models\EmailTemplate;
use App\Models\AnonymousUser;
use App\Models\DentistClaim;
use App\Models\DcnCashout;
use App\Models\PollAnswer;
use App\Models\CronjobRun;
use App\Models\UserAction;
use App\Models\UserInvite;
use App\Models\Blacklist;
use App\Models\VoxAnswer;
use App\Models\DcnReward;
use App\Models\UserLogin;
use App\Models\GasPrice;
use App\Models\UserBan;
use App\Models\Country;
use App\Models\UserAsk;
use App\Models\Review;
use App\Models\Reward;
use App\Models\Civic;
use App\Models\Email;
use App\Models\User;
use App\Models\Poll;
use App\Models\VoxScale;
use App\Models\Vox;
use App\Models\Dcn;
use App\Models\InvalidEmail;

use Carbon\Carbon;

use Response;
use Request;
use Mail;
use Auth;
use Log;
use DB;

class YouTubeController extends FrontController {

    /**
     * recover token from admin for youtube video reviews
     */
    public function test() {

        if(!empty($this->admin)) {

            // $ch = curl_init();

            // curl_setopt($ch, CURLOPT_URL,"https://api.deepl.com/v2/translate");
            // curl_setopt($ch, CURLOPT_POST, 1);
            // curl_setopt($ch, CURLOPT_POSTFIELDS,
            //             "auth_key=a3f913f9-babe-c503-f268-a4830e780054&text=Hello, world&target_lang=ES");
            // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));


            // // receive server response ...
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // $server_output = curl_exec ($ch);

            // curl_close ($ch);

            // // further processing ....
            
            // dd(json_decode($server_output, true)['translations'][0]['text']);



            // curl https://api.deepl.com/v2/translate \
            // -d auth_key=[yourAuthKey] \
            // -d "text=Hello, world!" \
            // -d "target_lang=DE"

            // $voxes = Vox::get();

            // foreach($voxes as $vox) {

            //     $ch = curl_init();

            //     curl_setopt($ch, CURLOPT_URL,"https://api.deepl.com/v2/translate");
            //     curl_setopt($ch, CURLOPT_POST, 1);
            //     curl_setopt($ch, CURLOPT_POSTFIELDS,
            //                 "auth_key=a3f913f9-babe-c503-f268-a4830e780054&text=".$vox->slug."&target_lang=ES");
            //     curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            //     $slug = curl_exec ($ch);
            //     curl_close ($ch);

            //     $ch = curl_init();

            //     curl_setopt($ch, CURLOPT_URL,"https://api.deepl.com/v2/translate");
            //     curl_setopt($ch, CURLOPT_POST, 1);
            //     curl_setopt($ch, CURLOPT_POSTFIELDS,
            //                 "auth_key=a3f913f9-babe-c503-f268-a4830e780054&text=".$vox->title."&target_lang=ES");
            //     curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            //     $title = curl_exec ($ch);
            //     curl_close ($ch);

            //     $ch = curl_init();

            //     curl_setopt($ch, CURLOPT_URL,"https://api.deepl.com/v2/translate");
            //     curl_setopt($ch, CURLOPT_POST, 1);
            //     curl_setopt($ch, CURLOPT_POSTFIELDS,
            //                 "auth_key=a3f913f9-babe-c503-f268-a4830e780054&text=".$vox->description."&target_lang=ES");
            //     curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            //     $description = curl_exec ($ch);
            //     curl_close ($ch);

            //     $translation = $vox->translateOrNew('es');
            //     $translation->vox_id = $vox->id;
            //     $translation->slug = json_decode($slug, true)['translations'][0]['text'];
            //     $translation->title = json_decode($title, true)['translations'][0]['text'];
            //     $translation->description = json_decode($description, true)['translations'][0]['text'];
            //     $translation->save();

            //     foreach($vox->questions as $question) {
                    
            //         $translation = $question->translateOrNew('es');
            //         $translation->vox_question_id = $question->id;

            //         $ch = curl_init();

            //         curl_setopt($ch, CURLOPT_URL,"https://api.deepl.com/v2/translate");
            //         curl_setopt($ch, CURLOPT_POST, 1);
            //         curl_setopt($ch, CURLOPT_POSTFIELDS,
            //                     "auth_key=a3f913f9-babe-c503-f268-a4830e780054&text=".$question->question."&target_lang=ES");
            //         curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            //         $server_output = curl_exec ($ch);
            //         curl_close ($ch);

            //         $translation->question = json_decode($server_output, true)['translations'][0]['text'];

            //         //dd($data['answers-'.$key]);

            //         if(!$question->vox_scale_id) {

            //             $answers = json_decode($question->answers, true);
            //             if($answers) {
            //                 $translated_answers = [];
            //                 foreach($answers as $a) {
            //                     $ch = curl_init();

            //                     curl_setopt($ch, CURLOPT_URL,"https://api.deepl.com/v2/translate");
            //                     curl_setopt($ch, CURLOPT_POST, 1);
            //                     curl_setopt($ch, CURLOPT_POSTFIELDS,
            //                                 "auth_key=a3f913f9-babe-c503-f268-a4830e780054&text=".$a."&target_lang=ES");
            //                     curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            //                     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            //                     $server_output = curl_exec ($ch);
            //                     curl_close ($ch);

            //                     $translated_answers[] = json_decode($server_output, true)['translations'][0]['text'];
            //                 }

            //                 // dd($translated_answers);

            //                 $translation->answers = json_encode( $translated_answers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE );
            //             } else {
            //                 $translation->answers = '';                            
            //             }
            //         } else {
            //             $translation->answers = '';                            
            //         }

            //         $translation->save();
            //     }
            // }

            $client = new \Google_Client();
            $client->setApplicationName('API Samples');
            $client->setScopes('https://www.googleapis.com/auth/youtube.force-ssl');
            // Set to name/location of your client_secrets.json file.
            $client->setAuthConfig( storage_path() . '/client_secrets.json');
            $client->setAccessType('offline');

            // Load previously authorized credentials from a file.
            $credentialsPath = storage_path() . '/yt-oauth2.json';
            if (false && file_exists($credentialsPath)) {
                $accessToken = json_decode(file_get_contents($credentialsPath), true);
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);

                if (isset($_GET['code'])) {

                    $credentialsPath = storage_path() . '/yt-oauth2.json';
                    // Exchange authorization code for an access token.
                    $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);

                    // Store the credentials to disk.
                    if(!file_exists(dirname($credentialsPath))) {
                        mkdir(dirname($credentialsPath), 0700, true);
                    }
                    file_put_contents($credentialsPath, json_encode($accessToken));
                }

                return;
            }
            $client->setAccessToken($accessToken);

            // Refresh the token if it's expired.
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
            }

            return $client;
        }
    }
}