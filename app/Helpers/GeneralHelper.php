<?php

namespace App\Helpers;

use DeviceDetector\Parser\Device\DeviceParserAbstract;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use DeviceDetector\DeviceDetector;

use \SendGrid\Mail\Mail as SendGridMail;
use \SendGrid\Mail\From as From;
use \SendGrid\Mail\To as To;

use App\Models\DentistPageview;
use App\Models\GasPrice;
use App\Models\Country;
use App\Models\Email;
use App\Models\User;

use Request;
use Image;

class GeneralHelper {

    public static function decode_base64_image($base64_image) {
        list($type, $base64_image) = explode(';', $base64_image);
        list(, $base64_image)      = explode(',', $base64_image);
        $base64_image = base64_decode($base64_image);
        return $base64_image;
    }

    public static function unregisteredSendGridTemplate($user, $to_email, $to_name, $template_id, $substitutions=null, $platform=null, $is_skipped=null, $anonymous_email=null) {

        $item = new Email;
        $item->user_id = $user->id;
        $item->template_id = $template_id;
        $item->meta = $substitutions;

        if($platform) {
            $item->platform = $platform;            
        } else {
            if( mb_substr(Request::path(), 0, 3)=='cms' || empty(Request::getHost()) ) {
                $item->platform = $user->platform;
            } else {
                $item->platform = mb_strpos( Request::getHost(), 'vox' )!==false ? 'vox' : 'trp';
            }
        }
        
        $item->save();

        if(empty($anonymous_email)) {
            
            if($user->id != 3 && !empty($item->template->subscribe_category)) {
                $cat = $item->template->subscribe_category;
                if($item->platform != 'dentacare' && $item->platform != 'dentists' && !in_array($item->platform, $user->$cat)) {
                    $item->unsubscribed = true;
                    $item->save();
                }
            }
        }

        $to_be_send = $user->sendgridEmailValidation($template_id, $to_email);

        if(!$to_be_send) {
            $item->invalid_email = true;
            $item->save();
        }

        if (empty($is_skipped) && empty($item->unsubscribed) && $to_be_send) {
            $sender = $item->platform=='vox' ? config('mail.from.address-vox') : ($item->platform == 'trp' ? config('mail.from.address') : config('mail.from.address-dentacoin'));
            $sender_name = $item->platform=='vox' ? config('mail.from.name-vox') : ($item->platform == 'trp' ? config('mail.from.name') : config('mail.from.name-dentacoin'));
            //$sender_name = config('platforms.'.$item->platform.'.name') ?? config('mail.from.name');

            $from = new From($sender, $sender_name);
            $tos = [new To( $to_email)];

            $email = new SendGridMail(
                $from,
                $tos
            );

            $email->setTemplateId($item->template->sendgrid_template_id);

            if($user->is_dentist && $template_id != 58 && $template_id != 59 && $template_id != 60 && $template_id != 61 && $template_id != 62 && $template_id != 106) {
                $email->addBcc("4097841@bcc.hubspot.com");
            }

            if($user->id == 3 && ($item->template->id == 84 || $item->template->id == 26 || $item->template->id == 83 || $item->template->id == 85 ) ) {

            } else {
                if ($item->template->category) {
                    $email->addCategory($item->template->category);
                } else {
                    $email->addCategory(strtoupper($item->platform).' Service '.($user->is_dentist ? 'Dentist' : 'Patient'));
                }
            }

            $domain = 'https://'.config('platforms.'.$user->platform.'.url').'/';
            $pageviews = DentistPageview::where('dentist_id', $user->id)->count();

            $defaulth_substitutions  = [
                "name" => $to_name,
                "platform" => $item->platform,
                "invite-patient" => getLangUrl( 'dentist/'.$user->slug, null, $domain).'?'. http_build_query(['popup'=>'popup-invite']),
                "lead-magnet-link" => $user->getLink().'?'. http_build_query(['popup'=>'popup-lead-magnet']),
                "homepage" => getLangUrl('/', null, $domain),
                "trp_profile" => $user->getLink(),
                "town" => $user->city_name ? $user->city_name : 'your town',
                "country" => $user->country_id ? Country::find($user->country_id)->name : 'your country',
                "unsubscribe" => 'https://api.dentacoin.com/api/update-single-email-preference/'.'?'. http_build_query(['fields'=>urlencode(self::encrypt(json_encode(array('email' => ($anonymous_email ? $anonymous_email : $to_email),'email_category' => $item->template->subscribe_category, 'platform' => $item->platform ))))]),
                "pageviews" => $pageviews,
                "trp" => url('https://reviews.dentacoin.com/'),
                "trp-dentist" => url('https://reviews.dentacoin.com/en/welcome-dentist/'),
                "vox" => url('https://dentavox.dentacoin.com/'),
                "partners" => url('https://dentacoin.com/partner-network'),
                "assurance" => url('https://assurance.dentacoin.com/'),
                "wallet" => url('https://wallet.dentacoin.com/'),
                "dcn" => url('https://dentacoin.com/'),
                "dentist" => url('https://dentists.dentacoin.com/'),
                "dentacare" => url('https://dentacare.dentacoin.com/'),
                "giftcards" => url('https://dentacoin.com/?payment=bidali-gift-cards'),
            ];

            if ($substitutions) {
                $defaulth_substitutions = array_merge($defaulth_substitutions, $substitutions);
            }

            foreach ($defaulth_substitutions as $key => $value) {
                $value = $value.'';
                $matches = '';
                preg_match_all("_(^|[\s.:;?\-\]<\(])(https?://[-\w;/?:@&=+$\|\_.!~*\|'()\[\]%#,☺]+[\w/#](\(\))?)(?=$|[\s',\|\(\).:;?\-\[\]>\)])_i", $value , $matches);

                if(!empty($matches)) {
                    foreach ($matches[0] as $match) {

                        $pos = mb_strpos($match, '?');
                        if ($pos === false) {
                            $separator = '?';
                        } else {
                            $separator = '&';
                        }
                        $new_match = $match.$separator.'utm_content='.urlencode($item->template->name);

                        $value = str_replace($match, $new_match, $value);                        
                    }                    
                }
                $defaulth_substitutions[$key] = $value;
            }

            $email->addDynamicTemplateDatas($defaulth_substitutions );
            
            $sendgrid = new \SendGrid(env('SENDGRID_PASSWORD'));
            $sendgrid->send($email);

            $item->sent = 1;
            $item->save();
        } else {
            $item->sent = 0;
            $item->save();
        }

        return $item;
    }

    public static function domain_exists($email, $record = 'MX'){
        if (mb_strpos($email, '@') !== false) {
            list($user, $domain) = explode('@', $email);
            return checkdnsrr($domain, $record);
        } else {
            return false;
        }
    }

    public static function validateName($name) {
        //users with the same name
        $result = false;

        $found_name = User::where('name', 'LIKE', $name)->withTrashed()->first();
     
        if ($found_name) {
            $result = true;
        }
     
        return $result;
    }

    public static function validateWebsite($website) {
        $result = false;

        $found_website = User::where('website', 'LIKE', $website)->withTrashed()->first();
     
        if ($found_website) {
            $result = true;
        }
     
        return $result;
    }

    public static function validateEmail($email) {
        //users with the same email
        $result = false;

        $clean_email = str_replace('.', '', $email);
        $found_email = User::where('email_clean', 'LIKE', $clean_email)->withTrashed()->first();
     
        if ($found_email) {
            $result = true;
        }
     
        return $result;
    }

    public static function validateLatin($string) {
        //only latin characters
        $result = false;
     
        if (preg_match("/^[\w\d\s\+\'\&.,-]*$/", $string)) {
            $result = true;
        }
     
        return $result;
    }

    public static function validateAddress($country, $address) {
        $kingdoms = [
            'United Arab Emirates',
        ];

        $query = $country.', '.$address;
        //dd($query);

        $geores = \GoogleMaps::load('geocoding')
        ->setParam ([
            'address'    => $query,
        ])
        ->get();

        $geores = json_decode($geores);
        $ret = [];
        // dd($geores);
        if(!empty($geores->results[0]->geometry->location)) {

            if(in_array($country, $kingdoms)) {
                $ret['state_name'] = $country;
                $ret['state_slug'] = str_replace([' ', "'"], ['-', ''], $country);
                $ret['city_name'] = $country;
            } else {
                $ret = self::parseAddress( $geores->results[0]->address_components );
            }

            $ret['lat'] = $geores->results[0]->geometry->location->lat;
            $ret['lon'] = $geores->results[0]->geometry->location->lng;

            // $ret['info'] = [];
            // foreach ($geores->results[0]->address_components as $ac) {
            //     $ret['info'][] = implode(', ', $ac->types).': '.$ac->long_name;
            // }
        }

        return $ret;
    }

    public static function parseAddress( $components ) {
        $ret = [];

        $country_fields = [
            'country',
        ];

        foreach ($country_fields as $sf) {
            if( empty($ret['country_name']) ) {
                foreach ($components as $ac) {
                    if( in_array($sf, $ac->types) ) {
                        
                        if (preg_match('/[اأإء-ي]/ui', $ac->long_name)) {
                            $cname = $ac->long_name;
                        } else {
                            $cname = iconv('UTF-8', 'ASCII//TRANSLIT', $ac->long_name);
                            $cname = iconv('ASCII', 'UTF-8', $cname);
                        }
                        $ret['country_name'] = $cname;
                        break;
                    }
                }
            } else {
                break;
            }
        }

        $state_fields = [
            'administrative_area_level_1',
            'administrative_area_level_2',
            'administrative_area_level_3',
        ];

        foreach ($state_fields as $sf) {
            if( empty($ret['state_name']) ) {
                foreach ($components as $ac) {
                    if( in_array($sf, $ac->types) ) {
                        if (preg_match('/[اأإء-ي]/ui', $ac->long_name)) {
                            $cname = $ac->long_name;
                        } else {
                            $cname = iconv('UTF-8', 'ASCII//TRANSLIT', $ac->long_name);
                            $cname = iconv('ASCII', 'UTF-8', $cname);
                        }
                        $ret['state_name'] = $cname;
                        $ret['state_slug'] = str_replace([' ', "'"], ['-', ''], strtolower($cname));
                        break;
                    }
                }
            } else {
                break;
            }
        }

        $city_fields = [
            'postal_town',
            'locality',
            'administrative_area_level_5',
            'administrative_area_level_4',
            'administrative_area_level_3',
            'administrative_area_level_2',
            'sublocality_level_1',
            'neighborhood',
        ];

        foreach ($city_fields as $sf) {
            if( empty($ret['city_name']) ) {
                foreach ( $components as $ac) {
                    if( in_array($sf, $ac->types) ) {
                        
                        if (preg_match('/[اأإء-ي]/ui', $ac->long_name)) {
                            $cname = $ac->long_name;
                        } else {
                            $cname = iconv('UTF-8', 'ASCII//TRANSLIT', $ac->long_name);
                            $cname = iconv('ASCII', 'UTF-8', $cname);
                        }
                        $ret['city_name'] = $cname;
                        break;
                    }
                }
            } else {
                break;
            }
        }

        $zip_fields = [
            'postal_code',
            'zip',
        ];

        foreach ($zip_fields as $sf) {
            if( empty($ret['zip']) ) {
                foreach ( $components as $ac) {
                    if( in_array($sf, $ac->types) ) {
                        
                        if (preg_match('/[اأإء-ي]/ui', $ac->long_name)) {
                            $cname = $ac->long_name;
                        } else {
                            $cname = iconv('UTF-8', 'ASCII//TRANSLIT', $ac->long_name);
                            $cname = iconv('ASCII', 'UTF-8', $cname);
                        }
                        $ret['zip'] = $cname;
                        break;
                    }
                }
            } else {
                break;
            }
        }

        return $ret;
    }

    public static function getTempImageName() {
        return md5( microtime(false) ).'.jpg';
    }

    public static function getTempImageUrl($name, $thumb = false) {
        return url('/storage/tmp/'.($thumb ? 'thumb-' : '').$name);
    }

    public static function getTempImagePath($name, $thumb = false) {
        $folder = storage_path().'/app/public/tmp';
        if(!is_dir($folder)) {
            mkdir($folder);
        }
        return $folder.'/'.($thumb ? 'thumb-' : '').$name;
    }

    public static function addTempImage($img) {

        $extensions = ['image/jpeg', 'image/png'];

        if (!in_array($img->mime(), $extensions)) {
            return [];
        }

        $name = self::getTempImageName();
        $to = self::getTempImagePath($name);
        $to_thumb = self::getTempImagePath($name, true);

        $img->resize(1920, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $img->save($to);

        if ($img->height() > $img->width()) {
            $img->heighten(400);
        } else {
            $img->widen(400);
        }
        $img->resizeCanvas(400, 400);

        // $img->heighten(400, function ($constraint) {
        //     $constraint->upsize();
        // });
        $img->save($to_thumb);
        
        return [ 
            self::getTempImageUrl($name, true), 
            self::getTempImageUrl($name), $name 
        ];
    }

    public static function isGasExpensive() {
        $gas = GasPrice::find(1);

        if($gas->gas_price > $gas->max_gas_price) {
            return true;
        }

        return false;
    }

    public static function encrypt($raw_text) {
        $length = openssl_cipher_iv_length(env('CRYPTO_METHOD'));
        $iv = openssl_random_pseudo_bytes($length);
        $encrypted = openssl_encrypt($raw_text, env('CRYPTO_METHOD'), env('CRYPTO_KEY'), OPENSSL_RAW_DATA, $iv);
        //here we append the $iv to the encrypted, because we will need it for the decryption
        $encrypted_with_iv = base64_encode($encrypted) . '|' . base64_encode($iv);
        return $encrypted_with_iv;
    }

    public static function decrypt($encrypted_text) {
        $arr = explode('|', $encrypted_text);
        if (count($arr)!=2) {
            return null;
        }
        $data = $arr[0];
        $iv = $arr[1];
        $iv = base64_decode($iv);

        try {
            $raw_text = openssl_decrypt($data, env('CRYPTO_METHOD'), env('CRYPTO_KEY'), 0, $iv);
        } catch (\Exception $e) {
            $raw_text = false;
        }

        return $raw_text;
    }

    public static function deviceDetector($item) {
        $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
        $dd = new DeviceDetector($userAgent);
        $dd->parse();

        if ($dd->isBot()) {
            // handle bots,spiders,crawlers,...
            $item->device = $dd->getBot();
        } else {
            $item->device = $dd->getDeviceName();
            $item->brand = $dd->getBrandName();
            $item->model = $dd->getModel();
            $item->os = in_array('name', $dd->getOs()) ? $dd->getOs()['name'] : '';
        }

        $item->save();
    }

    public static function getDirContents($dir, &$results = array()) {
        $files = scandir($dir);
    
        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            } else if ($value != "." && $value != "..") {
                self::getDirContents($path, $results);
                $results[] = $path;
            }
        }
    
        return $results;
    }

    public static function paginate($items, $perPage = 50, $page = null, $options = []) {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public static function checkFile($file, $allowedExtensions, $allowedMimetypes) {
        $allowedMimetypes[] = 'application/octet-stream';

        if(is_string($file)) { //for base64
            //checking file mimetype
            
            $f = finfo_open();
            $mime_type = finfo_buffer($f, $file, FILEINFO_MIME_TYPE);
            
            if (!in_array($mime_type, $allowedMimetypes)) {

                file_put_contents( base_path().'/storage/logs/upload-file.log', 
                    file_get_contents(base_path().'/storage/logs/upload-file.log').' <br/><br/>'.date("Y-m-d H:i:s").
                    '1. Image mime type: '.$mime_type.';'.(isset($_SERVER['REQUEST_URI']) ? ' URL: '.$_SERVER['REQUEST_URI'] : '')
                );

                return [
                    'error' => '1. Files can be only with '.implode(', .', $allowedExtensions).' formats. Please try again.'
                ];
            }
        } else {
            // if contains php tag
            try {
                $file_with_php = strpos(file_get_contents($file),'<?');
            } catch (\Exception $e) {
                $file_with_php = false;
            }

            if( $file_with_php !== false) { //strpos(file_get_contents($file),'<?php') !== false || 
                // do stuff
                file_put_contents( base_path().'/storage/logs/upload-file.log', 
                    file_get_contents(base_path().'/storage/logs/upload-file.log').' <br/><br/>'.date("Y-m-d H:i:s").
                    '2. Image with <? in content: '.$file_with_php.';'.(isset($_SERVER['REQUEST_URI']) ? ' URL: '.$_SERVER['REQUEST_URI'] : '')
                );

                return [
                    'error' => '2. Files can be only with '.implode(', .', $allowedExtensions).' formats. Please try again.'
                ];
            }
            
            //checking file extension
            if (!in_array(strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION)), $allowedExtensions)) {

                file_put_contents( base_path().'/storage/logs/upload-file.log', 
                    file_get_contents(base_path().'/storage/logs/upload-file.log').' <br/><br/>'.date("Y-m-d H:i:s").
                    '3. Image extension: '.pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION).';'.(isset($_SERVER['REQUEST_URI']) ? ' URL: '.$_SERVER['REQUEST_URI'] : '')
                );

                return [
                    'error' => '3. Files can be only with '.implode(', .', $allowedExtensions).' formats. Please try again.'
                ];
            }

            try {
                $file_mimetype = $file->getMimeType();
            } catch (\Exception $e) {
                $file_mimetype = $file->getClientMimeType();
            }

            //checking file mimetype
            if (!in_array($file_mimetype, $allowedMimetypes)) {

                file_put_contents( base_path().'/storage/logs/upload-file.log', 
                    file_get_contents(base_path().'/storage/logs/upload-file.log').' <br/><br/>'.date("Y-m-d H:i:s").
                    '4. Image mime type: '.$file_mimetype.';'.(isset($_SERVER['REQUEST_URI']) ? ' URL: '.$_SERVER['REQUEST_URI'] : '')
                );
                
                return [
                    'error' => '4. Files can be only with '.implode(', .', $allowedExtensions).' formats. Please try again.'.($file_mimetype)
                ];
            }

            //checking if error in file
            if ($file->getError()) {

                dd($file->getError());

                file_put_contents( base_path().'/storage/logs/upload-file.log', 
                    file_get_contents(base_path().'/storage/logs/upload-file.log').' <br/><br/>'.date("Y-m-d H:i:s").
                    '5. Image with error: '.$file->getError().';'.(isset($_SERVER['REQUEST_URI']) ? ' URL: '.$_SERVER['REQUEST_URI'] : '')
                );

                return [
                    'error' => '4. There is error with one or more of the files, please try with other files. '.($file->getError())
                ];
            }
        }

        $img = Image::make( $file )->orientate();

        //checking if file has height & width
        if ($img->height() > 1 && $img->width() > 1) {
        } else {

            file_put_contents( base_path().'/storage/logs/upload-file.log', 
                file_get_contents(base_path().'/storage/logs/upload-file.log').' <br/><br/>'.date("Y-m-d H:i:s").
                '6. Image without width/height: Height('.$img->height().'), Width('.$img->width().');'.(isset($_SERVER['REQUEST_URI']) ? ' URL: '.$_SERVER['REQUEST_URI'] : '')
            );

            return [
                'error' => '6. There is error with one or more of the files, please try with other files. Please try again.'
            ];
        }

        //checking file mimetype
        if (!in_array($img->mime(), $allowedMimetypes)) {

            file_put_contents( base_path().'/storage/logs/upload-file.log', 
                file_get_contents(base_path().'/storage/logs/upload-file.log').' <br/><br/>'.date("Y-m-d H:i:s").
                '7. Image mime type: '.$img->mime().';'.(isset($_SERVER['REQUEST_URI']) ? ' URL: '.$_SERVER['REQUEST_URI'] : '')
            );

            return [
                'error' => '7. Files can be only with '.implode(', .', $allowedExtensions).' formats. Please try again.'
            ];
        }

        return [
            'success' => true
        ];
    }
}