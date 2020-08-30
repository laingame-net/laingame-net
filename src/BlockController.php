<?php
namespace Lain;

use Lain\View;

class BlockController {

    public function __construct()
    {
		$this->model = ($model) ?: $GLOBALS['model'];
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
		foreach($data['subtitles'] as $key => $sub){

			$data['subtitles'][$key]['text'] = htmlentities($sub['text']);
			$data['subtitles'][$key]['actor'] = htmlentities($sub['actor']);
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
					'TITLE' => 'Error',
					'error_message' => 'Block not found'
					));
			return;
		}
		$history_list = $this->model->getHistory($id, $lang);
		$this->view->render('block', null, array(
			'TITLE' => $data['name'].' Block',
			'block' => $data,
			'block_en' => array(),
			'id' => $id,
			'lang' => $lang,
			'langs' => $this->model->langs,
			'history_list' => $history_list
		));
	}

    public function HistoryActionGet($id="", $lang="", $history="")
    {
		$data = $this->getBlock($id, $lang);
		if($data == false) {
			$this->view->render('error', null, array(
					'TITLE' => 'Error',
					'error_message' => 'Block not found'
					));
			return;
		}
		$history_list = $this->model->getHistory($id, $lang);
		$subtitres_list = $this->model->getHistorySubtitles($id, $lang, $history);

		require 'simplediff.php';
		foreach($data["subtitles"] as $key => $sub)
		{
			// text
			$from = htmlspecialchars($subtitres_list[1]["subtitles"][$key]['text']);
			$to   = htmlspecialchars($subtitres_list[0]["subtitles"][$key]['text']);
			$data["subtitles"][$key]['text'] = htmlDiff($from, $to);
			// actor
			$from = htmlspecialchars($subtitres_list[1]["subtitles"][$key]['actor']);
			$to   = htmlspecialchars($subtitres_list[0]["subtitles"][$key]['actor']);
			$data["subtitles"][$key]['actor'] = htmlDiff($from, $to);
		}

		$this->view->render('block', null, array(
			'TITLE' => 'Block history',
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
		if(!$_SESSION['user'])
			header("Location: /site/login");

		$data['block_en'] = $this->model->getBlock($id, 'en'); // get default english translation
		foreach($data['block_en']['subtitles'] as $key => $sub){

			$data['block_en']['subtitles'][$key]['text'] = htmlentities($sub['text']);
			$data['block_en']['subtitles'][$key]['actor'] = htmlentities($sub['actor']);
		}
		if($data['block_en'] != false) {
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
		} else {
			$this->view->render('error', null, array(
				'TITLE' => 'Block editing page',
				'error_message' => 'Block is not found.',
				'id' => $id,
				'lang' => $lang,
			));
			return;
		}

		// this lang is not registered
		if(!in_array($lang, $this->model->langs)) {
			$lang = 'en';
		}
		$param = array(
			'TITLE' => 'Block editing page',
			'id' => $id,
			'lang' => $lang,
		);
		if($lang == 'en') {
			$param['block'] = $data['block_en'];
		} else {
			$data['block'] = $this->model->getBlock($id, $lang);

			foreach($data['block']['subtitles'] as $key => $sub){

                $data['block']['subtitles'][$key]['text'] = htmlentities($sub['text']);
                $data['block']['subtitles'][$key]['actor'] = htmlentities($sub['actor']);
			}

			if(!$data['block'] or !$data['block']['subtitles']) {
				$data['block_en_subtitles_empty'] = $data['block_en']['subtitles'];
				foreach($data['block_en_subtitles_empty'] as $key => $val) {
					$data['block_en_subtitles_empty'][$key]['text'] = '';
				}
			}
			$param = array_merge($param, $data);
		}
		$this->view->render('block_edit', null, $param);
    }

    public function EditActionPost($id="", $lang="", $event="")
    {
		if(!$_SESSION['user'])
			header("Location: /site/login");


		// check posted data is valid arrays
		if(!@is_array($_POST['actor']) or !@is_array($_POST['text']) or count($_POST['actor']) != count($_POST['text']))
		{
			// show previous page if data is not valid
			$this->EditActionGet($id, $lang);
			return;
		}
		
		$to_json = array();
		foreach($_POST['actor'] as $key => $actor){
			$to_json[$key]['line'] = $key;
			$to_json[$key]['actor'] = $actor;
			$to_json[$key]['text'] = $_POST['text'][$key];
		}
		$json = json_encode($to_json);
		$this->model->updateTranslation($id, $lang, $json, intval($_SESSION['user']->id));
		$this->ViewActionGet($id, $lang, '');
    }
}
