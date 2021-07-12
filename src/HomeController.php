<?php
namespace Lain;

use Lain\View;

class HomeController
{

    public function __construct()
    {
        $this->model = (isset($model)) ? $model : $GLOBALS['model'];
        $this->view = new View("../templates");
    }

    public function indexActionGet($id = "", $lang = "", $event = "")
    {
        $data = $this->model->getBlocksTable(0);
        $latest_changes = $this->model->getLatestChanges();
        $this->view->render('index', null, array(
            'TITLE' => 'Lain Game sites',
            'data_blocks' => $data,
            'latest_changes' => $latest_changes,
            'lang' => $lang,
            'langs' => $this->model->langs,
        ));
    }
}
