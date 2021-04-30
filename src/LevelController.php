<?php
namespace Lain;

use Lain\View;

class LevelController
{

    public function __construct()
    {
        $this->model = (isset($model)) ? $model : $GLOBALS['model'];
        $this->view = new View("../templates");
    }

    public function ViewActionGet($id = "", $lang = "", $event = "")
    {
        list($site, $level) = explode('-', $id, 2);
        $data = $this->model->getLevel($site, $level);
        $this->view->render('level', 'level', array(
            'TITLE' => 'Level',
            'data_blocks' => $data,
            'lang' => $lang,
        ));
    }
}
