<?php

namespace App\Http\Controllers\Vox;
use App\Http\Controllers\FrontController;

use Illuminate\Support\Facades\Input;

use App\Models\UserInvite;
use App\Models\User;

use Carbon\Carbon;

use Request;

class RegisterController extends FrontController {

    public function invite_accept($locale=null) {

        if (!empty(Request::input('info'))) {
            $info = User::decrypt(base64_decode(Request::input('info')));

            if (!empty($info)) {
                $id = json_decode($info, true)['user_id'];
                $hash = json_decode($info, true)['hash'];

                if (isset(json_decode($info, true)['inv_id'])) {
                    $inv_id = json_decode($info, true)['inv_id'];
                } else {
                    $inv_id = null;
                }

                $user = User::find($id);

                if (!empty($user) && $user->canInvite('vox')) {

                    if ($hash == $user->get_invite_token()) {
                        // check for GET variables and build query string
                        $get = count($_GET) ? ('?' . http_build_query($_GET)) : '';

                        if($this->user) {
                            if($this->user->id==$user->id) {
                                Request::session()->flash('error-message', trans('vox.page.registration.invite-yourself'));
                            } else {
                                if(!$this->user->wasInvitedBy($user->id)) {
                                    $inv = UserInvite::find($inv_id);
                                    if(empty($inv)) {
                                        $inv = UserInvite::where('user_id', $user->id)->where('invited_email', 'LIKE', $this->user->email)->first();
                                    }
                                    if(empty($inv)) {
                                        $inv = new UserInvite;
                                        $inv->user_id = $user->id;
                                    }
                                    $inv->invited_name = $this->user->name;
                                    $inv->invited_email = $this->user->email;
                                    $inv->invited_id = $this->user->id;
                                    $inv->save();
                                }
                                Request::session()->flash('success-message', trans('vox.page.registration.invitation-registered', [ 'name' => $user->name ]));
                            }
                            return redirect( getLangUrl('/').$get );
                        } else {
                            $sess = [
                                'invited_by' => $user->id,
                            ];
                            $inv = UserInvite::find($inv_id);
                            if(!empty($inv)) {
                                $sess['invitation_name'] = $inv->invited_name;
                                $sess['invitation_email'] = $inv->invited_email;
                                $sess['invitation_id'] = $inv->id;
                            }
                            session($sess);

                            Request::session()->flash('success-message', trans('vox.page.registration.invitation', [ 'name' => $user->name ]));
                            return redirect( getLangUrl('paid-dental-surveys').'#register'.$get ); 
                        }

                    }
                }
            }
        }
        return redirect('/');
    }
}