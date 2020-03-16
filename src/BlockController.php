<?php
namespace Lain;

use Lain\Model;
use Lain\View;

class BlockController {

    public function __construct()
    {
	$this->model = new Model();
	$this->view = new View("../templates");
    }

    private function getBlock($id, $lang)
    {
	$data = $this->model->getBlock($id, $lang);
	if($lang != 'en' and $data == false) { // failback to english if requested lang is not available
		$data = $this->model->getBlock($id, 'en');
		if($data != false) {
			$data['failback_to_english'] = true;
			$data['edit_form'] = true;
		}
	}
	return $data;
    }

    public function ViewActionGet($id="", $lang="", $event="")
    {
	if(!in_array($lang, $this->model->langs)){
		$lang = 'en';
	}
	$data = $this->getBlock($id, $lang);
	if($data == false) {
		$this->view->render('error', null, array(
                  'TITLE' => 'Block view',
                  'error_message' => 'Block not found'
                ));
		return;
	}
	//$site = $this->model->getLangStrings($lang);
	$history_list = $this->model->getHistoryList($id, $lang);
	$this->view->render('block', null, array(
	  'TITLE' => 'Block view',
	  'block' => $data,
          'block_en' => array(),
	  'id' => $id,
	  'lang' => $lang,
	  'langs' => $this->model->langs,
          'history_list' => $history_list
	));
    }

    public function EditActionGet($id="", $lang="", $event="")
    {

	$data['block_en'] = $this->model->getBlock($id, 'en'); // get default english translation
	if($data['block_en'] != false)
	{
		if(!$data['block_en']['subtitles'])
		{
			// if english translation is not available then it not needed at all
	  		$this->view->render('error', null, array(
			    'TITLE' => 'Block editing page',
			    'error_message' => 'This file does not require translation.',
			    'id' => $id,
			    'lang' => $lang,
			));
			return;
		}
	}
	else
	{
	  	$this->view->render('error', null, array(
		    'TITLE' => 'Block editing page',
		    'error_message' => 'Block is not found.',
		    'id' => $id,
		    'lang' => $lang,
		));
		return;
	}

	// this lang is not registered
	if(!in_array($lang, $this->model->langs)) $lang = 'en';
	$param = array(
		'TITLE' => 'Block editing page',
		'id' => $id,
		'lang' => $lang,
	);
	if($lang == 'en')
	{
		$param['block'] = $data['block_en'];
	}
	else
	{
		$data['block'] = $this->model->getBlock($id, $lang);
		if(!$data['block'] or !$data['block']['subtitles']) {
			$data['block_en_subtitles_empty'] = $data['block_en']['subtitles'];
			foreach($data['block_en_subtitles_empty'] as $key => $val)
				$data['block_en_subtitles_empty'][$key]['text'] = '';
		}
		$param = array_merge($param, $data);
	}

	@session_start();
	session_write_close();// we close the session for writes.

	$this->view->render('block_edit', null, $param);
    }

    public function EditActionPost($id="", $lang="", $event="")
    {
	@session_start();

	// check posted data is valid arrays
	if(!@is_array($_POST['actor']) or !@is_array($_POST['text']) or count($_POST['actor']) != count($_POST['text']))
	{
		// show previous page if data is not valid
		$this->EditActionGet($id, $lang);
		return;
	}
	
	#$arr = array_combine($_POST['actor'], $_POST['text']);
	$to_json = array();
	foreach($_POST['actor'] as $key => $actor){
		$to_json[$key]['line'] = $key;
		$to_json[$key]['text'] = $_POST['text'][$key];
		$to_json[$key]['actor'] = $actor;
	}
	$json = json_encode($to_json);
	$this->model->updateTranslation($id, $lang, $json);
	session_write_close();// we close the session for writes.
	$this->ViewActionGet($id, $lang, '');
	return;

	$data = $this->getBlock($id, $lang);
	try {
	  $this->view->render('block_edit', null, array(
	    'TITLE' => 'sdfsdf sdf',
	    'block' => $data,
	    'lang' => $lang,
	  ));
	} catch (Exception $e) {
	    echo "error found ".$e->getMessage()."<br>".$e->getTraceAsString();
	}
    }
}
