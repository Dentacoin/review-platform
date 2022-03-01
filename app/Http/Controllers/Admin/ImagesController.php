<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

use App\Models\SupportContact;

class ImagesController extends AdminController {

    public function getImage($folder, $id, $thumbnail=false) {

        if($folder == 'support-contact') {
            $item = SupportContact::find($id);
            $file_extension = $item->file_extension;
        }

        $path = storage_path().'/app/private/private/'.$folder.'/'.($item->id%100).'/'.$item->id.($thumbnail ? '-thumb' : '').'.'.$file_extension.'?rev='.$item->updated_at->timestamp;

        $type = mime_content_type($path);
        header('Content-Type:'.$type);
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }
}