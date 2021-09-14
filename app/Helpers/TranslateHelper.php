<?php

namespace App\Helpers;
use GuzzleHttp\Client as Guzzle;

class TranslateHelper {
    public static function translate(string $string = '', string $source, string $target) {
        
        $client = new Guzzle();
        $translate = $client->request("POST", "https://translate.astian.org/translate", [
            'form_params' => [
                'q' => $string,
                'source' => $source,
                'target' => $target
            ]
        ]);

        if($translate->getStatusCode() == 200){
            return json_decode($translate->getBody())->translatedText;
        } else {
            return "Failed translation";
        }
    }
}