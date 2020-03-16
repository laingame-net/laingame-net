<?php
namespace Lain;

use Lain\Model;
use Lain\View;

class LevelController {

    public function __construct()
    {
	$this->model = new Model();
	$this->view = new View("../templates");
    }

    public function ViewActionGet($id="", $lang="", $event="")
    {
	$data = $this->model->getBlocksTable(0);
        $this->view->render('index', 'index', array(
          'TITLE' => 'Level',
          'data_blocks' => $data,
          'lang' => $lang
        ));
    }
}
