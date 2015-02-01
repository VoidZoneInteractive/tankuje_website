<?php

class Controller
{
    protected $action;

    protected $template;

    protected $template_path;

    protected $output_mode = PHPTAL::HTML5;

    public function __construct($action)
    {
        $this->action = $action;

        $this->template_path = str_replace('controller/', '', strtolower(str_replace('_', DS, get_class($this))));
        $this->template = new PHPTAL(TEMPLATES_PATH . DS . $this->template_path . DS . $this->action . '.xhtml');

        $this->{'action_' . $this->action}();

        switch($this->output_mode)
        {
            case PHPTAL::XML:
                $this->template->setOutputMode(PHPTAL::XML);
                break;

            default:
                $this->template->setOutputMode(PHPTAL::HTML5);
        }

        $this->template->setTemplateRepository(TEMPLATES_PATH);

        exit($this->template->execute());
    }

    public function before()
    {

    }
}