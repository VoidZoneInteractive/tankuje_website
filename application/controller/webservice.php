<?php

class Controller_Webservice extends Controller
{
    public function action_default()
    {
        $params = array(
//            'limit' => 20,
        );
        $stations = Dao_Station::select($params);

	$companies = Dao_Company::select(array('indexby' => 'external_id'));

//exit(print_r($companies,1));

        foreach ($stations as &$station)
        {
            $station['lat'] = (float)$station['lat'];
            $station['lng'] = (float)$station['lng'];
            $station['company'] = $companies[$station['company_id']];
        }

        exit(json_encode($stations));
    }
}