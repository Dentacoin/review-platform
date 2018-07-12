<?php

namespace App\Http\Controllers\Front;
use App\Http\Controllers\FrontController;
use App\Models\User;
use App\Models\UserCategory;
use Route;
use Request;
use Validator;

class AddController extends FrontController
{


    public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {
        parent::__construct($request, $route, $locale);
        $this->fields = [
            'name' => [
                'type' => 'text',
                'required' => true,
                'min' => 3,
            ],
            'categories' => [
                'type' => 'checkboxes',
                'values' => $this->categories
            ],
            'country_id' => [
                'type' => 'country',
                'required' => true,
            ],
            'city_id' => [
                'type' => 'city',
                'required' => true,
            ],
            'zip' => [
                'type' => 'text',
                'required' => true,
            ],
            'address' => [
                'type' => 'text',
                'required' => true,
            ],
            'phone' => [
                'type' => 'text',
                'subtype' => 'phone',
                'required' => true,
            ],
            'website' => [
                'type' => 'text',
            ],
            'email' => [
                'type' => 'text',
                'required' => true,
                'is_email' => true,
                'unique' => 'users,email',
            ],
        ];
    }

	public function list($locale=null) {


        if(Request::isMethod('post') && !empty($this->user) && $this->user->is_verified $this->user->email ) {

            $validator_arr = [];
            foreach ($this->fields as $key => $value) {
                $arr = [];
                if (!empty($value['required'])) {
                    $arr[] = 'required';
                }
                if (!empty($value['is_email'])) {
                    $arr[] = 'email';
                }
                if (!empty($value['min'])) {
                    $arr[] = 'min:'.$value['min'];
                }
                if (!empty($value['unique'])) {
                    $arr[] = 'unique:'.$value['unique'];
                }

                if (!empty($arr)) {
                    $validator_arr[$key] = $arr;
                }
            }

            $validator = Validator::make(Request::all(), $validator_arr);

            if ($validator->fails()) {
                return redirect( getLangUrl('add') )
                ->withInput()
                ->withErrors($validator);
            } else {


                $phone = ltrim( str_replace(' ', '', Request::Input('phone')), '0');

                $other = User::where([
                    ['country_id', Request::input('country_id')],
                    ['phone', $phone],
                ])->first();
                if(!empty($other)) {
                    return redirect( getLangUrl('add') )
                    ->withInput()
                    ->withErrors(['phone' => trans('front.common.phone-already-used')]);
                }

                $newuser = new User;
                $newuser->is_dentist = 1;
                $newuser->password = bcrypt(Request::input('name').Request::input('email'));
                $newuser->invited_by = $this->user->id;


                foreach ($this->fields as $key => $value) {
                    if($key=='categories') {
                    } else if($key=='phone') {
                        $newuser->phone = $phone;
                    } else {
                        $newuser->$key = Request::input($key);
                    }
                }
                $newuser->save();
                
                if(!empty(Request::input('categories'))) {
                    foreach (Request::input('categories') as $cat) {
                        $newc = new UserCategory;
                        $newc->user_id = $newuser->id;
                        $newc->category_id = $cat;
                        $newc->save();
                    }
                }
                
                $newuser->sendTemplate( 9 );
                
                Request::session()->flash('success-message', trans('front.page.add.dentists-added'));
                return redirect( $newuser->getLink() );
            }
        }

        return $this->ShowView('add', array(
            'fields' => $this->fields,
            
        )); 
	}

}