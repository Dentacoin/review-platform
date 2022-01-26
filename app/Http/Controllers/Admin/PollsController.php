<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\PollsMonthlyDescription;
use App\Models\VoxCategory;
use App\Models\PollAnswer;
use App\Models\VoxScale;
use App\Models\Poll;

use App\Helpers\AdminHelper;
use App\Imports\Import;
use Carbon\Carbon;

use Validator;
use Response;
use Request;
use Route;
use Auth;

class PollsController extends AdminController {

    public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {
        parent::__construct($request, $route, $locale);

        $this->statuses = [
            'scheduled' => 'Scheduled',
            'open' => 'Open',
            'closed' => 'Closed',
        ];

        $v_categories = VoxCategory::get();

    	foreach ($v_categories as $key => $value) {
    		$this->poll_categories[$value['id']] = $value['name'];
    	}
    }

    public function list() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if (!empty($this->request->input('date' ))) {
            $order = $this->request->input( 'date' );
            $polls = Poll::orderBy('launched_at', $order);
        } else {
            $polls = Poll::orderBy('launched_at', 'desc');
        }

        if(!empty($this->request->input('search-polls-from'))) {
            $firstday = new Carbon($this->request->input('search-polls-from'));
            $polls = $polls->where('launched_at', '>=', $firstday);
        }
        if(!empty($this->request->input('search-polls-to'))) {
            $lastday = new Carbon($this->request->input('search-polls-to'));
            $polls = $polls->where('launched_at', '<=', $lastday);
        }

        $total_count = $polls->count();
        $page = max(1,intval(request('page')));
        $ppp = 100;
        $adjacents = 2;
        $total_pages = ceil($total_count/$ppp);

        $paginations = AdminHelper::paginationsFunction($total_pages, $adjacents, $page);
        $start = $paginations['start'];
        $end = $paginations['end'];

        $polls = $polls->skip( ($page-1)*$ppp )->take($ppp)->get();

        $current_url = url('cms/vox/polls');

        $pagination_link = "";
        foreach (Request::all() as $key => $value) {
            if($key != 'page') {
                $pagination_link .= '&'.$key.'='.($value === null ? '' : $value);
            }
        }

    	return $this->showView('polls', array(
            'search_polls_from' => $this->request->input('search-polls-from'),
            'search_polls_to' => $this->request->input('search-polls-to'),
    		'categories' => $this->poll_categories,
            'polls' => $polls,
	        'statuses' => $this->statuses,
            'total_count' => $total_count,
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'page' => $page,
            'pagination_link' => $pagination_link,
            'current_url' => $current_url,
        ));
    }

    public function add() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if(Request::isMethod('post')) {

            $newpoll = new Poll;
        	$newpoll->launched_at = !empty($this->request->input('launched_at')) ? $this->request->input('launched_at') : null ;
		    $newpoll->category = !empty($this->request->input('category')) ? $this->request->input('category') : null;
            $newpoll->status = 'scheduled';
            $newpoll->scale_id = $this->request->input('scale-id');
            $newpoll->dont_randomize_answers = $this->request->input('dont_randomize_answers');
	        $newpoll->save();

	        foreach ($this->langs as $key => $value) {
	            if(!empty($this->request->input('question-'.$key))) {
	                $translation = $newpoll->translateOrNew($key);
	                $translation->poll_id = $newpoll->id;
	                $translation->question = $this->request->input('question-'.$key);
                    $translation->save();
	            }

	            if(!empty( $this->request->input('answers-'.$key) )) {

                    $newAnswers = $this->request->input('answers-'.$key);

                    $newAnswersArr = [];
                    foreach ($newAnswers as $ka => $va) {
                        if(!empty($va)) {
                            $newAnswersArr[] = $va;
                        }
                    }

                    if(!empty($newAnswersArr)) {
                        $translation = $newpoll->translateOrNew($key);
                        $translation->answers = json_encode( $newAnswersArr );
                        $translation->save();
                    }
                }
	        }
            
	        $newpoll->save();

            $exisiting_date = Poll::where('id', '!=', $newpoll->id)->where('launched_at', $this->request->input('launched_at'))->first();

            if(!empty($exisiting_date)) {
                Request::session()->flash('error-message', 'Daily Poll launched date ('.$this->request->input('launched_at').') is already taken');
                return redirect('cms/vox/polls/edit/'.$newpoll->id);
            }

            $validator = Validator::make($this->request->all(), [
                'launched_at' => array('required'),
                'category' => array('required'),
            ]);

            if ($validator->fails()) {
                return redirect('cms/vox/polls/edit/'.$newpoll->id)
                ->withInput()
                ->withErrors($validator);
            } else {
                Request::session()->flash('success-message', 'Daily Poll Addede');
                if(request('stay-on-same-page')) {
                    return redirect('cms/vox/polls/edit/'.$newpoll->id);
                } else {
                    return redirect('cms/vox/polls/');
                }
            }
        }

        return $this->showView('polls-form', array(
    		'categories' => $this->poll_categories,
	        'statuses' => $this->statuses,
            'scales' => VoxScale::orderBy('id', 'DESC')->get()->pluck('title', 'id')->toArray(),
        ));
    }

    public function edit( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

    	$item = Poll::find($id);

        if(!empty($item)) {

	        if(Request::isMethod('post')) {

                $validator = Validator::make($this->request->all(), [
                    'launched_at' => array('required'),
                    'category' => array('required'),
                ]);

                if ($validator->fails()) {
                    return redirect('cms/vox/polls/edit/'.$item->id)
                    ->withInput()
                    ->withErrors($validator);
                } else {

                    $exisiting_date = Poll::where('id', '!=', $item->id)->where('launched_at', $this->request->input('launched_at'))->first();

                    if(!empty($exisiting_date)) {
                        Request::session()->flash('error-message', 'Daily Poll launched date ('.$this->request->input('launched_at').') is already taken');
                        return redirect('cms/vox/polls/edit/'.$item->id);
                    }

    		        $item->status = $this->request->input('status');
            		$item->launched_at = $this->request->input('launched_at');
    		        $item->category = $this->request->input('category');
                    $item->scale_id = $this->request->input('scale-id');
                    $item->dont_randomize_answers = $this->request->input('dont_randomize_answers');
    		        $item->save();

    		        foreach ($this->langs as $key => $value) {
    		            if(!empty($this->request->input('question-'.$key))) {
    		                $translation = $item->translateOrNew($key);
    		                $translation->poll_id = $item->id;
    		                $translation->question = $this->request->input('question-'.$key);
    		            }

    		            if(!empty( $this->request->input('answers-'.$key) )) {
                            $oldAnswers = json_decode($translation->answers);
                            $newAnswers = $this->request->input('answers-'.$key);

                            $newAnswersArr = [];
                            foreach ($newAnswers as $ka => $va) {
                                if(!empty($va)) {
                                    $newAnswersArr[] = $va;
                                }
                            }

                            if(!empty($newAnswersArr)) {

                                $translation->answers = json_encode( $newAnswersArr );
                                $translator = [];

                                if(!empty($oldAnswers)) {
                                    foreach ($oldAnswers as $key => $value) {
                                        $translator[($key+1)] = array_search($value, $newAnswersArr) + 1;
                                    }
                                }

                                PollAnswer::where('poll_id', $item->id)->update([
                                    'editing' => 1
                                ]);
                                foreach ($translator as $old => $new) {
                                    PollAnswer::where('poll_id', $item->id)->where('answer', $old)->where('editing', 1)->update([
                                        'answer' => $new,
                                        'editing' => null
                                    ]);
                                }
                            }
    	                } else {
    	                    $translation->answers = '';
    	                }

    	                $translation->save();
    		        }
    		        $item->save();

                    Request::session()->flash('success-message', 'Daily Poll Edited');
                    if(request('stay-on-same-page')) {
                        return redirect('cms/vox/polls/edit/'.$item->id);
                    } else {
                        return redirect('cms/vox/polls/');
                    }
                }
	        }

            $time = !empty($item->launched_at) ? $item->launched_at->timestamp : '';
            if (!empty($time)) {
                $newformat = date('d-m-Y',$time);
            } else {
                $newformat = null;
            }            

	        return $this->showView('polls-form', array(
	            'item' => $item,
                'scales' => VoxScale::orderBy('id', 'DESC')->get()->pluck('title', 'id')->toArray(),
    			'categories' => $this->poll_categories,
	            'statuses' => $this->statuses,
                'poll_date' => $newformat,
	        ));
	    } else {
            return redirect('cms/'.$this->current_page.'/polls/');
        }
    }

    public function delete( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        Poll::destroy( $id );

        $this->request->session()->flash('success-message', 'Poll deleted' );
        return redirect('cms/vox/polls/');
    }

    public function change_poll_question( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $poll = Poll::find($id);

        if(!empty($poll)) {
            $translation = $poll->translateOrNew('en');
            $translation->question = Request::input('val');
            $translation->save();
            return Response::json( ['success' => true] );
        } else {
            return Response::json( ['success' => false] );
        }
    }

    public function change_poll_date( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $poll = Poll::find($id);

        if(!empty($poll)) {
            $poll->launched_at = Request::input('val');
            $poll->save();
            return Response::json( ['success' => true] );
        } else {
            return Response::json( ['success' => false] );
        }
    }

    public function duplicate_poll($id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $poll = Poll::find($id);

        if(!empty($poll)) {

            $clone = $poll->replicate();
			$clone->push();

   //      	foreach($poll->translations as $trans)
			// {
			//     $clone->translations()->attach($trans);
			//     // you may set the timestamps to the second argument of attach()
			// }

			foreach ($this->langs as $key => $value) {
	            if(!empty($poll->question)) {
	                $translation = $clone->translateOrNew($key);
	                $translation->poll_id = $clone->id;
	                $translation->question = $poll->question;

	                if(!empty( $poll->answers )) {
	                    $translation->answers = $poll->answers;
	                } else {
	                    $translation->answers = '';                            
	                }

	                $translation->save();
	            }
	        }
			        
            Request::session()->flash('success-message', 'Poll duplicated');
            return redirect('cms/vox/polls');
        } else {
            return redirect('cms/vox/polls');
        }
    }

    public function polls_explorer($poll_id=null) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if(!empty($poll_id)) {

            $poll = Poll::find($poll_id);
            $respondents = PollAnswer::where('poll_id', $poll_id )->get();

            $time = $poll->launched_at->timestamp;
            $newformat = date('d-m-Y',$time);

            $viewParams = [
                'poll_id' => $poll_id,
                'respondents' => $respondents,
                'poll' => $poll,
                'polls' => Poll::orderBy('launched_at', 'desc')->get(),
                'poll_date' => $newformat,
            ];
        } else {
            $viewParams = [
                'polls' => Poll::orderBy('launched_at', 'desc')->get(),
            ];
        }

        return $this->showView('polls-explorer', $viewParams);
    }

    public function import($id) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = Poll::find($id);

        if(!empty($item)) {

            $newName = '/tmp/'.str_replace(' ', '-', Input::file('table')->getClientOriginalName());
            copy( Input::file('table')->path(), $newName );

            $results = Excel::toArray(new Import, $newName );

            if(!empty($results)) {

                $answers = [];
                foreach ($results[0] as $key => $value) {
                    foreach ($value as $k => $v) {
                        if(!empty($v)) {
                            $answers[] = $v;
                        }
                    }
                }

                $translation = $item->translateOrNew('en');

                $cur_answers = !empty($translation->answers) ? (!empty(json_decode($translation->answers, true)[0]) ? json_decode($translation->answers, true) : [] ) : [];
                $new_answers = !empty($cur_answers) ? array_merge($cur_answers, $answers) : $answers;

                if(!empty($new_answers)) {
                    $translation->answers = json_encode($new_answers);
                    
                    $translation->save();
                }
            }
            
            unlink($newName);

            $this->request->session()->flash('success-message', 'Answers imported');
            return redirect('cms/vox/polls/edit/'.$item->id);

        } else {
            return redirect('cms/vox/polls/');
        }
    }

    public function pollsMonthlyDescriptions() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin', 'support'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $descriptions = PollsMonthlyDescription::orderBy('year', 'desc')->orderBy('month', 'desc');

        if(!empty($this->request->input('month'))) {
            $descriptions = $descriptions->where('month', $this->request->input('month'));
        }
        if(!empty($this->request->input('year'))) {
            $descriptions = $descriptions->where('year', $this->request->input('year'));
        }

        $total_count = $descriptions->count();
        $page = max(1,intval(request('page')));
        $ppp = 100;
        $adjacents = 2;
        $total_pages = ceil($total_count/$ppp);

        $paginations = AdminHelper::paginationsFunction($total_pages, $adjacents, $page);
        $start = $paginations['start'];
        $end = $paginations['end'];

        $descriptions = $descriptions->skip( ($page-1)*$ppp )->take($ppp)->get();

        $current_url = url('cms/vox/polls-monthly-description');

        $pagination_link = "";
        foreach (Request::all() as $key => $value) {
            if($key != 'page') {
                $pagination_link .= '&'.$key.'='.($value === null ? '' : $value);
            }
        }

        return $this->showView('polls-monthly-description', array(
            'month' => $this->request->input('month'),
            'year' => $this->request->input('year'),
            'descriptions' => $descriptions,
            'total_count' => $total_count,
            'count' =>($page - 1)*$ppp ,
            'start' => $start,
            'end' => $end,
            'total_pages' => $total_pages,
            'page' => $page,
            'pagination_link' => $pagination_link,
            'current_url' => $current_url,
        ));
    }

    public function pollsMonthlyDescriptionsAdd() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if(Request::isMethod('post')) {

            $newdesc = new PollsMonthlyDescription;
            $newdesc->month = !empty($this->request->input('month')) ? $this->request->input('month') : null ;
            $newdesc->year = !empty($this->request->input('year')) ? $this->request->input('year') : null;
            $newdesc->save();

            foreach ($this->langs as $key => $value) {
                if(!empty($this->request->input('description-'.$key))) {
                    $translation = $newdesc->translateOrNew($key);
                    $translation->polls_monthly_description_id = $newdesc->id;
                    $translation->description = $this->request->input('description-'.$key);
                    $translation->save();
                }
            }
            $newdesc->save();

            $exisiting_date = PollsMonthlyDescription::where('id', '!=', $newdesc->id)->where('month', $this->request->input('month'))->where('year', $this->request->input('year'))->first();

            if(!empty($exisiting_date)) {
                Request::session()->flash('error-message', 'Daily Polls monthly description date ('.$this->request->input('month').'.'.$this->request->input('year').') is already taken');
                return redirect('cms/vox/polls-monthly-description/edit/'.$newdesc->id);
            }

            $validator = Validator::make($this->request->all(), [
                'month' => array('required'),
                'year' => array('required'),
            ]);

            if ($validator->fails()) {
                return redirect('cms/vox/polls-monthly-description/edit/'.$newdesc->id)
                ->withInput()
                ->withErrors($validator);
            } else {
                Request::session()->flash('success-message', 'Daily Polls monthly description added');
                return redirect('cms/vox/polls-monthly-description');
            }
        }

        return $this->showView('polls-monthly-description-form');
    }

    public function pollsMonthlyDescriptionsEdit( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = PollsMonthlyDescription::find($id);

        if(!empty($item)) {

            if(Request::isMethod('post')) {

                $validator = Validator::make($this->request->all(), [
                    'month' => array('required'),
                    'year' => array('required'),
                ]);

                if ($validator->fails()) {
                    return redirect('cms/vox/polls-monthly-description/edit/'.$item->id)
                    ->withInput()
                    ->withErrors($validator);
                } else {

                    $exisiting_date = PollsMonthlyDescription::where('id', '!=', $item->id)->where('month', $this->request->input('month'))->where('year', $this->request->input('year'))->first();

                    if(!empty($exisiting_date)) {
                        Request::session()->flash('error-message', 'Daily Polls monthly description date ('.$this->request->input('month').'.'.$this->request->input('year').') is already taken');
                        return redirect('cms/vox/polls-monthly-description/edit/'.$item->id);
                    }

                    $item->month = $this->request->input('month');
                    $item->year = $this->request->input('year');
                    $item->save();

                    foreach ($this->langs as $key => $value) {
                        if(!empty($this->request->input('description-'.$key))) {
                            $translation = $item->translateOrNew($key);
                            $translation->polls_monthly_description_id = $item->id;
                            $translation->description = $this->request->input('description-'.$key);
                        }

                        $translation->save();
                    }
                    $item->save();

                    Request::session()->flash('success-message', 'Daily Polls monthly description edited');
                    return redirect('cms/vox/polls-monthly-description/');
                }
            }

            return $this->showView('polls-monthly-description-form', array(
                'item' => $item,
            ));
        } else {
            return redirect('cms/'.$this->current_page.'/polls-monthly-description/');
        }
    }

    public function pollsMonthlyDescriptionsDelete( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin', 'admin'])) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }
        
        PollsMonthlyDescription::destroy( $id );

        $this->request->session()->flash('success-message', 'Polls monthly description deleted' );
        return redirect('cms/vox/polls-monthly-description/');
    }
}