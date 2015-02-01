<?php

class Controller_Assets extends Controller
{
    public function action_marker()
    {
        $this->output_mode = PHPTAL::XML;
        header('Content-type: image/svg+xml');
    }
}