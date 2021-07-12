<?php
namespace Lain;

use Lain\View;

class BlockController
{

    public function __construct()
    {
        $this->model = (isset($model)) ? $model : $GLOBALS['model'];
        $this->view = new View("../templates");
    }

    private function getBlock($id, $lang)
    {
        $data = $this->model->getBlock($id, $lang);
        if ($lang != 'en' and $data == false) { // failback to english if requested lang is not available
            $data = $this->model->getBlock($id, 'en');
            if ($data != false) {
                $data['failback_to_english'] = true;
                $data['edit_form'] = true;
            }
        }
        foreach ($data['subtitles'] as $key => $sub) {

            $data['subtitles'][$key]['text'] = htmlentities($sub['text']);
            $data['subtitles'][$key]['actor'] = htmlentities($sub['actor']);
        }
        return $data;
    }

    public function ViewActionGet($id = "", $lang = "", $event = "")
    {
        if (!in_array($lang, $this->model->langs)) {
            $lang = 'en';
        }
        $data = $this->getBlock($id, $lang);
        if ($data == false) {
            $this->view->render('error', null, array(
                'TITLE' => 'Error',
                'error_message' => 'Block not found',
            ));
            return;
        }
        $history_list = $this->model->getHistory($id, $lang);
        $this->view->render('block', null, array(
            'TITLE' => $data['name'] . ' Block',
            'block' => $data,
            'block_en' => array(),
            'id' => $id,
            'lang' => $lang,
            'langs' => $this->model->langs,
            'history_list' => $history_list,
        ));
    }

    public function HistoryActionGet($id = "", $lang = "", $history = "")
    {
        $data = $this->getBlock($id, $lang);
        if ($data == false) {
            $this->view->render('error', null, array(
                'TITLE' => 'Error',
                'error_message' => 'Block not found',
            ));
            return;
        }
        $history_list = $this->model->getHistory($id, $lang);
        $subtitres_list = $this->model->getHistorySubtitles($id, $lang, $history);

        require 'simplediff.php';
        foreach ($data["subtitles"] as $key => $sub) {
            // text
            $from = htmlspecialchars(@$subtitres_list[1]["subtitles"][$key]['text']);
            $to = htmlspecialchars(@$subtitres_list[0]["subtitles"][$key]['text']);
            $data["subtitles"][$key]['text'] = htmlDiff($from, $to);
            // actor
            $from = htmlspecialchars(@$subtitres_list[1]["subtitles"][$key]['actor']);
            $to = htmlspecialchars(@$subtitres_list[0]["subtitles"][$key]['actor']);
            $data["subtitles"][$key]['actor'] = htmlDiff($from, $to);
            // comment
            $from = htmlspecialchars(@$subtitres_list[1]["subtitles"][$key]['comment']);
            $to = htmlspecialchars(@$subtitres_list[0]["subtitles"][$key]['comment']);
            $data["subtitles"][$key]['comment'] = htmlDiff($from, $to);
        }

        $this->view->render('block', null, array(
            'TITLE' => 'Block history',
            'block' => $data,
            'block_en' => array(),
            'id' => $id,
            'lang' => $lang,
            'langs' => $this->model->langs,
            'history_list' => $history_list,
        ));
    }

    public function EditActionGet($id = "", $lang = "", $event = "")
    {
        include '../config.php';
        if (!isset($_SESSION['user'])) {
            header("Location: /site/login");
        }

        $data = $this->model->getBlock($id, 'en'); // get english translation
        $data['block_jp'] = $this->model->getBlock($id, 'jp'); // get japanse translation
        $data['block_ru'] = $this->model->getBlock($id, 'ru'); // get russian translation
        $data['block_en'] = $data;
        $history_list = $this->model->getHistory($id, 'ru');

        if ($data['block_ru'] === false) {
            $data['block_ru']['subtitles'] = $data['block_en']['subtitles'];
            foreach ($data['block_ru']['subtitles'] as $key => $value) {
                $data['block_ru']['subtitles'][$key]["text"] = '';
                $data['block_ru']['subtitles'][$key]["comment"] = '';
            }
        }

        $this->view->render('block_edit', null, array(
            'TITLE' => $data['name'] . ' Block',
            'google_api_key' => $google_api_key,
            'error_message' => '',
            'block' => $data,
            'id' => $id,
            'lang' => 'ru',
            'langs' => $this->model->langs,
            'history_list' => $history_list,
        ));
    }

    public function EditActionPost($id = "", $lang = "", $event = "")
    {
        if (!$_SESSION['user']) {
            header("Location: /site/login");
        }
        // check posted data is valid arrays
        if (!@is_array($_POST['actor']) or !@is_array($_POST['text']) or count($_POST['actor']) != count($_POST['text'])) {
            // show previous page if data is not valid
            $this->EditActionGet($id, 'ru');
            return;
        }

        $to_json = array();
        foreach ($_POST['actor'] as $key => $actor) {
            $to_json[$key]['line'] = $key;
            $to_json[$key]['actor'] = $actor;
            $to_json[$key]['text'] = $_POST['text'][$key];
            $to_json[$key]['comment'] = $_POST['comment'][$key];
        }
        $json = json_encode($to_json);

        try {
            $this->model->updateTranslation($id, 'ru', $json, intval($_SESSION['user']->id));
        } catch (PDOException $e) {
            $this->view->render('block_edit', null, array(
                'TITLE' => 'Block edidting',
                'error_message' => 'Error occured',
                'lang' => 'ru',
                'langs' => $this->model->langs,
            ));
        }
        $this->EditActionGet($id, 'ru');
    }
}
