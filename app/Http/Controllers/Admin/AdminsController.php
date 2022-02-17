<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use Illuminate\Http\Request as Requestt;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Input;
use Illuminate\Pagination\Paginator;

use App\Models\DcnTransactionHistory;
use App\Models\AdminMessage;
use App\Models\AdminAction;
use App\Models\UserHistory;
use App\Models\UserPhoto;
use App\Models\AdminIp;
use App\Models\Admin;

use Validator;
use Response;
use Request;
use Image;
use Auth;
use App;

class AdminsController extends AdminController {

    public function __construct(Requestt $request) {
        parent::__construct($request);
        $this->langslist = ['' => '-'];
        foreach(config('langs')['admin'] as $k=> $v) {
            $this->langslist[$k] = $v['name'];
        }

        $this->roles = [
            'admin' => 'Admin',
            'super_admin' => 'Super admin',
            'translator' => 'Translator',
            'voxer' => 'Voxer',
            'support' => 'Support',
        ];

        $this->domainlist = [];
        foreach( config('admin.pages.translations.subpages') as $k => $sp) {
            $this->domainlist[$k] = trans('admin.page.translations.'.$k.'.title');
        }

        foreach(config('langs')['admin'] as $k=> $v) {
            $this->langslist[$k] = $v['name'];
        }
    }

    public function list( ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

    	return $this->showView('admins', array(
        	'admins_list' => Admin::get(),
            'roles' => $this->roles,
        ));
    }

    public function add( ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $validator = Validator::make($this->request->all(), [
            'name' => array('required'),
            'username' => array('required', 'unique:admins,username', 'min:3'),
            'password' => array('required', 'min:6'),
            'email' => array('required', 'email', 'unique:admins,email')
        ]);

        if ($validator->fails()) {
            return redirect('cms/admins/admins')
            ->withInput()
            ->withErrors($validator);
        } else {
            
            $newadmin = new Admin;
            $newadmin->name = $this->request->input('name');
            $newadmin->username = $this->request->input('username');
            $newadmin->password = bcrypt($this->request->input('password'));
            $newadmin->email = $this->request->input('email');
            $newadmin->role = $this->request->input('role');
            $newadmin->user_id = $this->request->input('user_id');
            $newadmin->save();

            $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.added') );
            return redirect('cms/admins/admins');
        }
    }

    public function delete( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        Admin::destroy( $id );

        $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.deleted') );
        return redirect('cms/admins/admins');
    }

    public function edit( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $item = Admin::find($id);

        if(!empty($item)) {

            return $this->showView('admins-edit', array(
                'item' => $item,
                'langslist' => $this->langslist,
                'roles' => $this->roles,
                'domainlist' => $this->domainlist,
            ));
        } else {
            return redirect('cms/admins/admins');
        }
    }

    public function update( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }
        
        $item = Admin::find($id);

        if(!empty($item)) {
        	$validator = Validator::make($this->request->all(), [
                'username' => array('required', 'string'),
                'email' => array('email'),
            ]);

            if ($validator->fails()) {
                return redirect('cms/admins/admins/edit/'.$item->id)
                ->withInput()
                ->withErrors($validator);
            } else {
                $item->username = $this->request->input('username');
                if(!empty( $this->request->input('password') )) {
                    $item->password = bcrypt($this->request->input('password'));
                }
                $item->name = $this->request->input('name');
                $item->email = $this->request->input('email');
                $item->comments = $this->request->input('comments');
                $item->role = $this->request->input('role');
                $item->lang_from = $this->request->input('lang_from');
                $item->lang_to = $this->request->input('lang_to');
                $item->text_domain = !empty($this->request->input('text_domain')) ? implode(',', $this->request->input('text_domain')) : '';
                $item->email_template_type = $this->request->input('email_template_type');
                $item->user_id = $this->request->input('user_id');
                $item->save();

                $this->request->session()->flash('success-message', trans('admin.page.'.$this->current_page.'.updated') );
                return redirect('cms/admins/admins');
            }
        } else {
            return redirect('cms/admins/admins');
        }
    }

    public function listIps() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        if(Request::isMethod('post')) {

            $validator = Validator::make($this->request->all(), [
                'ip' => array('required'),
            ]);

            if ($validator->fails()) {
                return redirect('cms/admins/ips')
                ->withInput()
                ->withErrors($validator);
            } else {
                
                $new_whitelist = new AdminIp;
                $new_whitelist->ip = $this->request->input('ip');
                $new_whitelist->comment = $this->request->input('comment');
                $new_whitelist->save();

                $this->request->session()->flash('success-message', 'IP added' );
                return redirect('cms/admins/ips');
            }
        }

        $items = AdminIp::get();

        return $this->showView('admin-ips', array(
            'items' => $items,
        ));
    }

    public function deleteIp( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        AdminIp::destroy( $id );

        $this->request->session()->flash('success-message', 'IP deleted' );
        return redirect('cms/admins/ips');
    }

    public function actionsHistory() {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $actions = UserHistory::with(['admin', 'user'])->whereNotNull('admin_id');
        if(!empty(request('search-admin-id'))) {
            $actions = $actions->where('admin_id', request('search-admin-id'));
        }
        $actions = $actions->get();

        $admin_actions = AdminAction::with(['admin', 'user'])->whereNotNull('admin_id');
        if(!empty(request('search-admin-id'))) {
            $admin_actions = $admin_actions->where('admin_id', request('search-admin-id'));
        }
        $admin_actions = $admin_actions->get();

        $transaction_actions = DcnTransactionHistory::with(['admin'])->whereNotNull('admin_id');
        if(!empty(request('search-admin-id'))) {
            $transaction_actions = $transaction_actions->where('admin_id', request('search-admin-id'));
        }
        $transaction_actions = $transaction_actions->get();

        $actions = $actions->concat($transaction_actions);
        $actions = $actions->concat($admin_actions);
        $actions = $actions->sortByDesc('created_at');
        $actions = $this->paginate($actions)->withPath('cms/admins/actions-history/');

        $admins = Admin::get();

        return $this->showView('admin-actions-history', array(
            'admins' => $admins,
            'actions' => $actions,
            'search_admin_id' => request('search-admin-id')
        ));
    }

    private function paginate($items, $perPage = 50, $page = null, $options = []) {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function resetAuth( $id ) {

        if( !in_array(Auth::guard('admin')->user()->role, ['super_admin']) ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }

        $admin = Admin::find( $id );
        $admin->two_factor_auth = false;
        $admin->save();

        return redirect('cms/admins/admins/');
    }

    public function readMessage( $id ) {

        $message = AdminMessage::find($id);

        if(!empty($message) && !$message->is_read && !empty($this->user) && $this->user->id == $message->admin_id) {
            $message->is_read = true;
            $message->save();
        }

        return Response::json([
            'success' => true,
        ]);
    }

    public function messagesList( ) {

        if( Auth::guard('admin')->user()->id != 1 ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }
    	return $this->showView('admins-messages');
    }

    public function addMessage() {

        if( Auth::guard('admin')->user()->id != 1 ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }
            
        $newadmin = new AdminMessage;
        $newadmin->admin_id = $this->request->input('admin_id');
        $newadmin->message = $this->request->input('message');
        $newadmin->save();

        $this->request->session()->flash('success-message', 'Message added' );
        return redirect('cms/admins/messages');
    }

    public function uploadFile() {

        if( Auth::guard('admin')->user()->id != 1 ) {
            $this->request->session()->flash('error-message', 'You don\'t have permissions' );
            return redirect('cms/home');            
        }
        
        if(Request::isMethod('post')) {
            
            foreach($_FILES['file']['name'] as $k => $file) {
                $file_name = explode('.', $file)[0];
                $gallery = UserPhoto::find($file_name);

                $img = Image::make( Input::file('file')[$k] )->orientate();
                $gallery->addImage($img);
            }
        }

        return $this->showView('upload-file');
    }

}