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

    public function approve($id) {
        $item = DentistClaim::find($id);

        $item->status = 'approved';
        $item->save();

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

        $user = User::find($item->dentist_id);
        $user->password = $item->password;
        $user->status = 'approved';
        $user->ownership = 'approved';
        $user->save();

        $u = User::find(3);
        $tmpEmail = $u->email;
        $tmpName = $u->name;

        $u->email = $item->email;
        $u->name = $item->name;
        $u->save();

        $substitutions = [
            'trp_profile' => getLangUrl('dentist/'.$user->slug, null, 'https://reviews.dentacoin.com/'),
        ];

        $mail = $u->sendGridTemplate(26, $substitutions, 'trp');

        $u->email = $tmpEmail;
        $u->name = $tmpName;
        $u->save();

        $mail->delete();

        return redirect( 'cms/users/edit/'.$item->dentist_id );

    }

    public function reject($id) {
        $item = DentistClaim::find($id);

        $item->status = 'rejected';
        $item->save();

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

        return redirect( 'cms/users/edit/'.$item->dentist_id );

    }


    public function suspicious($id) {
        $item = DentistClaim::find($id);

        $item->status = 'suspicious';
        $item->save();

        $user = User::find($item->dentist_id);
        $user->ownership = 'suspicious';
        $user->save();

        $u = User::find(3);
        $tmpEmail = $u->email;
        $tmpName = $u->name;

        $u->email = $item->email;
        $u->name = $item->name;
        $u->save();
        $mail = $u->sendGridTemplate(67, null, 'trp');

        $u->email = $tmpEmail;
        $u->name = $tmpName;
        $u->save();

        $mail->delete();

        return redirect( 'cms/users/edit/'.$item->dentist_id );

    }

}
