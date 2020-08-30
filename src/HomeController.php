<?php
namespace Lain;

use Lain\View;

class HomeController {

    public function __construct()
    {
		$this->model = ($model) ?: $GLOBALS['model'];
        $this->view = new View("../templates");
    }

    public function indexActionGet($id="", $lang="", $event="")
    {
        $data = $this->model->getBlocksTable(0);
        $this->view->render('index', null, array(
            'TITLE' => 'Lain Game sites',
            'data_blocks' => $data,
            'lang' => $lang,
            'langs' => $this->model->langs,
        ));
    }
}
