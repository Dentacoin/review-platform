<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\User;
use App\Models\DentistClaim;
use Carbon\Carbon;

use DB;
use Validator;
use Response;
use Request;
use Route;
use Auth;

class DentistClaimsController extends AdminController
{
    public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {
        parent::__construct($request, $route, $locale);

        $this->statuses = [
            'waiting' => 'Waiting',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];
    }

    public function edit($id) {

        $item = DentistClaim::find($id);

        if (!empty($item)) {

            if(Request::isMethod('post')) {
                $validator = Validator::make($this->request->all(), [
                    'name' => array('required', 'string'),
                    'email' => array('required', 'email'),
                    'phone' => array('required', 'string'),
                    'job' => array('required', 'string'),
                    'explain_related' => array('required', 'string'),
                ]);

                if ($validator->fails()) {
                    return redirect('cms/claims/edit/'.$item->id)
                    ->withInput()
                    ->withErrors($validator);
                } else {

                    if ($this->request->input('status') == 'approved') {

                        if ($item->status!=$this->request->input('status')) {
                            $dentist_claims = DentistClaim::where('dentist_id', $item->dentist_id)->where('id', '!=', $item->id)->get();

                            if (!empty($dentist_claims)) {
                                foreach ($dentist_claims as $dk) {
                                    $dk->status = 'rejected';
                                    $dk->save();

                                    $u = User::find(3);
                                    $tmpEmail = $u->email;
                                    $tmpName = $u->name;

                                    $u->email = $dk->email;
                                    $u->name = $dk->name;
                                    $u->save();
                                    $mail = $u->sendGridTemplate(66, null, 'trp');

                                    $u->email = $tmpEmail;
                                    $u->name = $tmpName;
                                    $u->save();

                                    $mail->delete();
                                }
                            }
                        }

                        $user = User::find($item->dentist_id);
                        $user->name = $this->request->input('name');
                        $user->email = $this->request->input('email');
                        $user->phone = $this->request->input('phone');
                        $user->password = $item->password;
                        $user->status = 'approved';
                        $user->ownership = 'approved';
                        $user->save();

                        if ($this->request->input('status') && $item->status!=$this->request->input('status')) {
                            $user->sendGridTemplate(26);
                        }
                    }

                    if ($this->request->input('status') == 'rejected' && $item->status!=$this->request->input('status')) {
                        $user = User::find($item->dentist_id);
                        $user->ownership = 'rejected';
                        $user->save();

                        $u = User::find(3);
                        $tmpEmail = $u->email;
                        $tmpName = $u->name;

                        $u->email = $item->email;
                        $u->name = $item->name;
                        $u->save();
                        $mail = $u->sendGridTemplate(66, null, 'trp');

                        $u->email = $tmpEmail;
                        $u->name = $tmpName;
                        $u->save();

                        $mail->delete();
                    }
                    
                    $item->name = $this->request->input('name');
                    $item->email = $this->request->input('email');
                    $item->phone = $this->request->input('phone');
                    $item->job = $this->request->input('job');
                    $item->explain_related = $this->request->input('explain_related');
                    $item->status = $this->request->input('status');
                    $item->save();

                    $this->request->session()->flash('success-message', 'Claim Profile Updated' );
                    return redirect('cms/users/edit/'.$item->dentist_id);
                }

            }

            return $this->showView('claims-edit', array(
                'item' => $item,
                'statuses' => $this->statuses
            ));
        }
    }

}
