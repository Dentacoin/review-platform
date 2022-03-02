<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\SupportContact;
use App\Models\BanAppeal;

class ImagesController extends AdminController {

    public function getImage($folder, $id, $thumbnail=false) {

        if($folder == 'support-contact') {
            $item = SupportContact::find($id);
            $file_extension = $item->file_extension;
        } else if($folder == 'appeals') {
            $item = BanAppeal::find($id);
            $file_extension = 'jpg';
        }

        $path = storage_path().'/app/private/'.$folder.'/'.($item->id%100).'/'.$item->id.($thumbnail ? '-thumb' : '').'.'.$file_extension;

        try {
            $type = mime_content_type($path);
            header('Content-Type:'.$type);
            header('Content-Length: ' . filesize($path));
            readfile($path);
        } catch (\Exception $e) {

        }
    }
}