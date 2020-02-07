<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\Poll;
use App\Models\PollAnswer;
use App\Models\VoxCategory;
use App\Models\VoxScale;
use App\Models\DcnReward;

use Illuminate\Support\Facades\Input;

use Image;
use Request;
use Response;
use Route;
use Excel;
use DB;
use Validator;

use Carbon\Carbon;


class PollsController extends AdminController
{

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

        //Here we generates the range of the page numbers which will display.
        if($total_pages <= (1+($adjacents * 2))) {
          $start = 1;
          $end   = $total_pages;
        } else {
          if(($page - $adjacents) > 1) { 
            if(($page + $adjacents) < $total_pages) { 
              $start = ($page - $adjacents);            
              $end   = ($page + $adjacents);         
            } else {             
              $start = ($total_pages - (1+($adjacents*2)));  
              $end   = $total_pages;               
            }
          } else {               
            $start = 1;                                
            $end   = (1+($adjacents * 2));             
          }
        }

        $polls = $polls->skip( ($page-1)*$ppp )->take($ppp)->get();

        //If you want to display all page links in the pagination then
        //uncomment the following two lines
        //and comment out the whole if condition just above it.
        /*$start = 1;
        $end = $total_pages;*/

        $current_url = url('cms/vox/polls');

        $pagination_link = (!empty($this->request->input('search-polls-from')) ? '?search-polls-from='.$this->request->input( 'search-polls-from' ) : '').(!empty($this->request->input('search-polls-to')) ? '&search-polls-to='.$this->request->input( 'search-polls-to' ) : '');

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

        if(Request::isMethod('post')) {

            $validator = Validator::make($this->request->all(), [
                'launched_at' => array('required'),
                'category' => array('required'),
            ]);

            if ($validator->fails()) {
                return redirect('cms/vox/polls/add')
                ->withInput()
                ->withErrors($validator);
            } else {

                $newpoll = new Poll;
            	$newpoll->launched_at = $this->request->input('launched_at');
    		    $newpoll->category = $this->request->input('category');
                $newpoll->status = 'scheduled';
                $newpoll->scale_id = $this->request->input('scale-id');
                $newpoll->dont_randomize_answers = $this->request->input('dont_randomize_answers');
    	        $newpoll->save();

    	        foreach ($this->langs as $key => $value) {
    	            if(!empty($this->request->input('question-'.$key))) {
    	                $translation = $newpoll->translateOrNew($key);
    	                $translation->poll_id = $newpoll->id;
    	                $translation->question = $this->request->input('question-'.$key);
    	            }

    	            if(!empty( $this->request->input('answers-'.$key) )) {
                        $translation->answers = json_encode( $this->request->input('answers-'.$key) );
                    } else {
                        $translation->answers = '';
                    }

                    $translation->save();
    	        }
    	        $newpoll->save();

                Request::session()->flash('success-message', 'Daily Poll Added');
                return redirect('cms/vox/polls');
            }            
        }

        return $this->showView('polls-form', array(
    		'categories' => $this->poll_categories,
	        'statuses' => $this->statuses,
            'scales' => VoxScale::orderBy('id', 'DESC')->get()->pluck('title', 'id')->toArray(),
        ));
    }

    public function edit( $id ) {
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
    	                    $translation->answers = json_encode( $newAnswers );

                            $translator = [];

                            foreach ($oldAnswers as $key => $value) {
                                $translator[($key+1)] = array_search($value, $newAnswers) + 1;
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

    	                } else {
    	                    $translation->answers = '';
    	                }

    	                $translation->save();
    		        }
    		        $item->save();

    		        Request::session()->flash('success-message', 'Daily Poll Edited');
                	return redirect('cms/vox/polls/');
                }
	        }

            $time = $item->launched_at->timestamp;
            $newformat = date('d-m-Y',$time);

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
        Poll::destroy( $id );

        $this->request->session()->flash('success-message', 'Poll deleted' );
        return redirect('cms/vox/polls/');
    }

    public function change_poll_question( $id ) {
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

        if(!empty($poll_id)) {

            $poll = Poll::find($poll_id);
            $respondents = PollAnswer::where('poll_id', $poll_id )->get();

            $time = $poll->launched_at->timestamp;
            $newformat = date('d-m-Y',$time);
            //dd( request()->input('country') );

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

        $item = Poll::find($id);

        if(!empty($item)) {

            global $reversedRows;

            Excel::load( Input::file('table')->path() , function($reader) use ($item)  {

                // Getting all results
                global $results, $reversedRows;
                $results = [];
                $reader->each(function($sheet) {
                    global $results;
                    $results[] = $sheet->toArray();
                });

                $answers = [];
                foreach ($results[0] as $key => $value) {
                    foreach ($value as $k => $v) {
                        $answers[] = $v;
                    }
                }

                foreach ($this->langs as $key => $value) {
                    $translation = $item->translateOrNew($key);

                    $cur_answers = !empty($translation->answers) ? (!empty(json_decode($translation->answers, true)[0]) ? json_decode($translation->answers, true) : [] ) : [];
                    $new_answers = !empty($cur_answers) ? array_merge($cur_answers, $answers) : $answers;

                    $translation->answers = json_encode($new_answers);

                    $translation->save();
                }

            });

            $this->request->session()->flash('success-message', 'Answers imported');
            return redirect('cms/vox/polls/edit/'.$item->id);

        } else {
            return redirect('cms/vox/polls/');
        }
    }

}