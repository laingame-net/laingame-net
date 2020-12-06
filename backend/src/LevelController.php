<?php
namespace Lain;

use Lain\View;

class LevelController
{

    public function __construct()
    {
        $this->model = ($model) ?: $GLOBALS['model'];
        $this->view = new View("../templates");
    }

    public function ViewActionGet($id = "", $lang = "", $event = "")
    {
        $data = $this->model->getBlocksTable(0);
        $this->view->render('level', null, array(
            'TITLE' => 'Level',
            'data_blocks' => $data,
            'lang' => $lang,
            'level_id' => $id,
        ));
    }
}
