<?php
namespace Lain;

use Lain\Model;
use Lain\View;

class TagController {

    public function __construct()
    {
	$this->model = new Model();
	$this->view = new View("../templates");
    }

    public function ViewActionGet($tag="", $lang="", $event="")
    {
	$data = $this->model->getBlocksByTag($tag);
	if($data == false) {
		$this->view->render('error', null, array(
		  'TITLE'=>'Tags'
		));
		return;
	}
	$data=['lang'=>$lang, 'tag'=>$tag, 'blocks'=>$data, 'TITLE'=>'Tags']; // data to send from our logic -> view
	$this->view->render('tag', null, $data);
    }
}
