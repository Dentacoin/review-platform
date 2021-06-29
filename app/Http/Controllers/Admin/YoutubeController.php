<?php

namespace App\Http\Controllers\Admin;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;

use App\Http\Controllers\AdminController;

use App\Models\Review;
use App\Models\DcnReward;
use App\Models\Reward;

use Carbon\Carbon;

use Request;
use Route;
use Auth;

class YoutubeController extends AdminController {
    
    public function list() {

        if( Auth::guard('admin')->user()->role!='admin' && Auth::guard('admin')->user()->role!='support' ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $pending = Review::where('youtube_id', '!=', '')->where('youtube_approved', 0)->get();

        return $this->showView('youtube', array(
            'pending' => $pending,
        ));
    }

    public function approve($rid) {

        if( Auth::guard('admin')->user()->role!='admin' && Auth::guard('admin')->user()->role!='support' ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $review = Review::find($rid);

        if(!empty($review) && $review->youtube_id && $review->youtube_approved==0) {

            $status = $this->videosUpdate($review);
            
            if($status) {

                if( $review->verified ) {
                    $amount = Reward::getReward('review_video_trusted');
                    $reward = new DcnReward();
                    $reward->user_id = $review->user_id;
                    $reward->reward = $amount;
                    $reward->platform = 'trp';
                    $reward->type = 'review';
                    $reward->reference_id = $review->id;

                    $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
                    $dd = new DeviceDetector($userAgent);
                    $dd->parse();

                    if ($dd->isBot()) {
                        // handle bots,spiders,crawlers,...
                        $reward->device = $dd->getBot();
                    } else {
                        $reward->device = $dd->getDeviceName();
                        $reward->brand = $dd->getBrandName();
                        $reward->model = $dd->getModel();
                        $reward->os = in_array('name', $dd->getOs()) ? $dd->getOs()['name'] : '';
                    }
                    $reward->save();
                }
                
                $review->youtube_approved = true;
                $review->save();

                $review->afterSubmitActions();
                Request::session()->flash('success-message', 'Review was approved');                
            } else {
                Request::session()->flash('error-message', 'Review error approved');                
            }


        }
        return redirect('cms/trp/'.$this->current_subpage);
    }

    public function delete($rid) {

        if( Auth::guard('admin')->user()->role!='admin' ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $review = Review::find($rid);

        if(!empty($review) && $review->youtube_id && $review->youtube_approved==0) {

            $status = $this->videosDelete($review->youtube_id);
            
            if($status) {
                $review->delete();
                Request::session()->flash('success-message', 'The video and the review are deleted');                
            } else {
                Request::session()->flash('error-message', 'Deleted error');                
            }


        }

        return redirect('cms/trp/'.$this->current_subpage);
    }






    //
    //Youtube boilerplate
    //



    function videosDelete($videoId, $params = array('onBehalfOfContentOwner' => '')) {
        list($client, $service) = $this->setupClient();

        $params = array_filter($params);
        $service->videos->delete(
            $videoId,
            $params
        );
        
        return true;

    }

    function videosUpdate($review) {
        list($client, $service) = $this->setupClient();

        $videos = $service->videos->listVideos("status,snippet", array(
            'id' => $review->youtube_id
        ));

        // If $videos is empty, the specified video was not found.
        if (empty($videos)) {
            return false;
        } else {
            $updateVideo = $videos[0];
            $updateVideo['status']['privacyStatus'] = 'public';
            $updateVideo['snippet']['title'] = trans('trp.video-review.youtube-title', ['patient' => $review->user->getNames(), 'dentist' => ($review->clinic_id ? $review->clinic->getNames() : $review->dentist->getNames())]);
            $updateVideo['snippet']['description'] = trans('trp.video-review.youtube-description', ['patient' => $review->user->getNames(), 'dentist' => ($review->clinic_id ? $review->clinic->getNames() : $review->dentist->getNames())]);
            $videoUpdateResponse = $service->videos->update("status,snippet", $updateVideo);
        }

        return true;

    }

    function setupClient() {
        $client = $this->getClient();
        $service = new \Google_Service_YouTube($client);

        if (isset($_SESSION['token'])) {
            $client->setAccessToken($_SESSION['token']);
        }

        if (!$client->getAccessToken()) {
            print("no access token");
            exit;
        }

        return [$client, $service];

    }

    function videosInsert($client, $service, $media_file, $properties, $part, $params) {
        $params = array_filter($params);
        $propertyObject = $this->createResource($properties); // See full sample for function
        $resource = new \Google_Service_YouTube_Video($propertyObject);
        $client->setDefer(true);
        $request = $service->videos->insert($part, $resource, $params);
        $client->setDefer(false);
        $response = $this->uploadMedia($client, $request, $media_file, 'video/*');
        return $response->id;
    }



    function getClient() {
        $client = new \Google_Client();
        $client->setApplicationName('API Samples');
        $client->setScopes('https://www.googleapis.com/auth/youtube.force-ssl');
        // Set to name/location of your client_secrets.json file.
        $client->setAuthConfig( storage_path() . '/client_secrets.json');
        $client->setAccessType('offline');

        // Load previously authorized credentials from a file.
        $credentialsPath = storage_path() . '/yt-oauth2.json';
        if (file_exists($credentialsPath)) {
            $accessToken = json_decode(file_get_contents($credentialsPath), true);
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';


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

    // Add a property to the resource.
    function addPropertyToResource(&$ref, $property, $value) {
        $keys = explode(".", $property);
        $is_array = false;
        foreach ($keys as $key) {
            // For properties that have array values, convert a name like
            // "snippet.tags[]" to snippet.tags, and set a flag to handle
            // the value as an array.
            if (substr($key, -2) == "[]") {
                $key = substr($key, 0, -2);
                $is_array = true;
            }
            $ref = &$ref[$key];
        }

        // Set the property value. Make sure array values are handled properly.
        if ($is_array && $value) {
            $ref = $value;
            $ref = explode(",", $value);
        } elseif ($is_array) {
            $ref = array();
        } else {
            $ref = $value;
        }
    }

    // Build a resource based on a list of properties given as key-value pairs.
    function createResource($properties) {
        $resource = array();
        foreach ($properties as $prop => $value) {
            if ($value) {
                $this->addPropertyToResource($resource, $prop, $value);
            }
        }
        return $resource;
    }


    public function generateNewAccessToken() {
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
