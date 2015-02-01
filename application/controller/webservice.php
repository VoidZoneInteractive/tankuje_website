<?php

class Controller_Webservice extends Controller
{
    public function action_default()
    {
        $stations = Dao_Station::select();

        exit(json_encode($stations));
    }
}