<?php
namespace Lain;

use Lain\Model;

class View
{
    var $ext = ".php";
    var $postfix = ".tpl";
    var $template = "index";

    public function __construct($templates_path)
    {
        $this->templates_path = $templates_path;
        $this->current_path = getcwd();
    }

    public function template_shutdown_function()
    {
        if(is_array($this->parameters)) extract($this->parameters); // expand parameters to template too
        $CONTENT = ob_get_clean(); // CONTENT variable contains rendered page in a template
        include($this->current_path.DIRECTORY_SEPARATOR.$this->templates_path.DIRECTORY_SEPARATOR.$this->template.$this->postfix.$this->ext);
    }

    public function init_page()
    {
        register_shutdown_function(array($this, 'template_shutdown_function'));
        ob_start();
    }

    public function render($page_name, $template, $parameters)
    {
        $this->page_name = $page_name;
        $this->template = ($template) ?? $this->template;
        $this->parameters = $parameters ?? [];
        //$this->parameters['site'] = $this->model->getLangStrings();
        if(is_array($parameters)) extract($parameters);
        include( $this->templates_path . DIRECTORY_SEPARATOR . $page_name . $this->ext );
    }

    // TODO A large amount of data could slow your app down with this approach because the str_replace is inefficient.
    public function parse_html($html, $args)
    {
        foreach($args as $key => $val) $html = str_replace("#[$key]", $val, $html);
        return $html;
    }
}
