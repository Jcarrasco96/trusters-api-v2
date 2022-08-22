<?php

namespace app\controllers;

use app\core\Controller;

class TestController extends Controller {

    public function index() {
        $json = file_get_contents("media/countryCode.json");

        $json = str_replace("\n", "", $json);

        $jsonObject = json_decode($json, true);

        $show = [];

        foreach ($jsonObject as $countryCode) {
            $show[] = $countryCode['phone_code'];
        }

//        echo '<pre>';
//        print_r($jsonObject);
//        echo '<pre>';

        return $show;
    }

}