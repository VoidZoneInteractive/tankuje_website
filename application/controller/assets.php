<?php

class Controller_Assets extends Controller
{
    public function action_marker()
    {
        $this->output_mode = PHPTAL::XML;

        $this->template->price = array(
            '#4B5E24',
            '#F39200',
            '#990000',
            'low' => '#4B5E24',
            'medium' => '#F39200',
            'high' => '#990000',
        );

        $this->template->newness = array(
            '#E30513',
            '#FCEA0F',
            '#8CAA2B',
            '#CCCCCC',
            'low' => '#E30513',
            'medium' => '#FCEA0F',
            'high' => '#8CAA2B',
            'old' => '#CCCCCC',
        );


        if (isset($_GET['output']) && $_GET['output'] == 'png')
        {
            $pngFilename = MEDIA_PATH . DS . 'markers' . DS . Helper::sanitize('marker_' . $_GET['value'] . '.png');

            $svgData = $this->template->execute();
            $im = new Imagick();
            $im->setBackgroundColor(new ImagickPixel('transparent'));
            $im->readImageBlob($svgData);
            $im->setImageFormat('png32');
            $im->resizeImage(200, 200, imagick::FILTER_LANCZOS, 1);
            $im->writeImage($pngFilename);

            header('Content-type: image/png');

            exit(file_get_contents($pngFilename));
        }

        header('Content-type: image/svg+xml');
    }
}