<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;

use Response;
use Request;
use DB;

use App\Models\Dcn;
use App\Models\DcnTransaction;
use App\Models\User;
use App\Models\Vox;
use App\Models\VoxQuestion;
use App\Models\Email;

class YouTubeController extends FrontController
{
    public function test() {
    	session([
    		'hi' => 'hi Miro',
    		'time' => time()
    	]);

    	echo 'Hi, the session should contain:<br/>
    	hi: '.session('hi').'<br/>
    	time: '.session('time');        

    }
}