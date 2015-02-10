<?php

class Controller_Assets extends Controller
{
    public function action_marker()
    {
        $this->output_mode = PHPTAL::XML;


        if (isset($_GET['output']) && $_GET['output'] == 'png')
        {
            $pngFilename = MEDIA_PATH . DS . 'markers' . DS . Helper::sanitize('marker_' . $_GET['value'] . '.png');

            $svgData = $this->template->execute();
            $im = new Imagick();
            $im->readImageBlob($svgData);
            $im->setImageFormat('png24');
            $im->resizeImage(200, 200, imagick::FILTER_LANCZOS, 1);
            $im->writeImage($pngFilename);

            header('Content-type: image/png');

            exit(file_get_contents($pngFilename));
        }

        header('Content-type: image/svg+xml');
    }
}