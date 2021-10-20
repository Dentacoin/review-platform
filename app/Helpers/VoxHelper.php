<?php

namespace App\Helpers;

class VoxHelper {

    public static function translateQuestionInfo($lang_code, $vox) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://api.deepl.com/v2/translate");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
                    "auth_key=".env('DEEPL_AUTH_KEY')."&text=".$vox->slug."&target_lang=".strtoupper($lang_code));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $slug = curl_exec ($ch);
        curl_close ($ch);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://api.deepl.com/v2/translate");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
                    "auth_key=".env('DEEPL_AUTH_KEY')."&text=".$vox->title."&target_lang=".strtoupper($lang_code));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $title = curl_exec ($ch);
        curl_close ($ch);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://api.deepl.com/v2/translate");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
                    "auth_key=".env('DEEPL_AUTH_KEY')."&text=".$vox->description."&target_lang=".strtoupper($lang_code));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $description = curl_exec ($ch);
        curl_close ($ch);

        $translation = $vox->translateOrNew($lang_code);
        $translation->vox_id = $vox->id;
        $translation->slug = json_decode($slug, true)['translations'][0]['text'];
        $translation->title = json_decode($title, true)['translations'][0]['text'];
        $translation->description = json_decode($description, true)['translations'][0]['text'];
        $translation->save();
    }

    public static function translateQuestionAnswers($lang_code, $question) {

        $translation = $question->translateOrNew($lang_code);
        $translation->vox_question_id = $question->id;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"https://api.deepl.com/v2/translate");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
                    "auth_key=".env('DEEPL_AUTH_KEY')."&text=".$question->question."&target_lang=".strtoupper($lang_code));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec ($ch);
        curl_close ($ch);

        $translation->question = isset(json_decode($server_output, true)['translations']) ? json_decode($server_output, true)['translations'][0]['text'] : '';

        //dd($data['answers-'.$key]);

        if(!$question->vox_scale_id) {

            $answers = json_decode($question->answers, true);
            if($answers) {
                $translated_answers = [];
                foreach($answers as $a) {
                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL,"https://api.deepl.com/v2/translate");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS,
                                "auth_key=".env('DEEPL_AUTH_KEY')."&text=".$a."&target_lang=".strtoupper($lang_code));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $server_output = curl_exec ($ch);
                    curl_close ($ch);

                    $translated_answers[] = json_decode($server_output, true)['translations'][0]['text'];
                }

                // dd($translated_answers);

                $translation->answers = json_encode( $translated_answers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE );
            } else {
                $translation->answers = '';                            
            }
        } else {
            $translation->answers = '';                            
        }

        $translation->save();
    }

    public static function translateSurvey($lang_code, $vox) {
        self::translateQuestionInfo($lang_code, $vox);

        foreach($vox->questions as $question) {
            self::translateQuestionAnswers($lang_code, $question);
        }
    }

}