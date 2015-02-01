<?php

class Controller_Webservice extends Controller
{
    public function action_default()
    {
        $params = array(
            'limit' => 20,
        );
        $stations = Dao_Station::select($params);

        exit(json_encode($stations));
    }
}