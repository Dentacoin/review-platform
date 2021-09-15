<?php

namespace App\Helpers;

class GeneralHelper {

    public static function decode_base64_image($base64_image) {
        list($type, $base64_image) = explode(';', $base64_image);
        list(, $base64_image)      = explode(',', $base64_image);
        $base64_image = base64_decode($base64_image);
        return $base64_image;
    }

}