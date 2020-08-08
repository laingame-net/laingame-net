<?php

namespace Lain;

use \PDO;
use \PDOException;

function is_image_present($img_value) {
    return $img_value <> 65535;
}

function get_site_name($site_index) {
    return (($site_index == 0)?'A':'B');
}

function get_level ($pos) {
        global $q, $L;
        return intval(($pos-$q) / $L) + 1;
}

function get_pos_in_level ($pos) {
        global $q, $L;
        return ($pos-$q) % $L;
}

class Model {
    public $icons = array (
        0 => '_icon0.png',
        1 => '_icon1.png',
        2 => '_icon2.png',
        3 => '_icon3.png',
        4 => '_icon4.png',
        5 => '_icon5.png',
        6 => '_icon6.png',
        7 => '_icon7.png',
        8 => '_icon8.png',
        9 => '_icon9.png'
    );

    private $sql_get_block = "select db.id, db.name, db.site, db.pos, db.icon, db.sw, db.q, db.state, db.type, 
    info1.value info1, info2.value info2, info3.value info3, info4.value info4, 
    db.tag1 tag1_id, db.tag2 tag2_id, db.tag3 tag3_id,
    tg1.value tag1_value, tg2.value tag2_value, tg3.value tag3_value, 
    db.img1, db.img2, db.img3,
    trans.lang,
    trans.subtitles,
    db.need_id, need.name need_name,
    db.level,
    db.page_pos

    ,(SELECT  
    JSON_ARRAYAGG(db.id)
    FROM data_block db 
    left join data_block as need1 on db.need_id = need1.id
    WHERE db.need_id = :id
    group by db.need_id) reveals_ids

    ,(SELECT 
    JSON_ARRAYAGG(db.name)
    FROM data_block db 
    left join data_block as need2 on db.need_id = need2.id
    WHERE db.need_id = :id
    group by db.need_id) reveals_names

    from data_block db
    left join info as info1 on db.info1 = info1.id
    left join info as info2 on db.info2 = info2.id
    left join info as info3 on db.info3 = info3.id
    left join info as info4 on db.info4 = info4.id
    left join tag as tg1 on db.tag1 = tg1.id
    left join tag as tg2 on db.tag2 = tg2.id
    left join tag as tg3 on db.tag3 = tg3.id
    left join translation as trans on db.id = trans.id_block
    left join data_block as need on need.id = db.need_id
    where db.id = :id
    and (lang = :lang or lang is null)";

    private $sql_blocks_table = "SELECT
    m.id,
    m.icon,
    m.name,
    m.type,
    m.site,
    m.level,
    m.page_pos,
    m.row,
	m.col
    FROM data_block m
    ORDER BY m.site DESC, m.level desc, m.row desc, m.col asc";

    private $sql_blocks_by_tag = "SELECT
    db.id, name, info1, info2, info3, info4, 
    img1, img2, img3, site, pos, icon, sw,
    tg1.value tag1_value, tg2.value tag2_value, tg3.value tag3_value
    
    ,(SELECT  
    tg.value tag_value
    FROM tag tg
    WHERE tg.id = :tag) tag_value,
    
    q, state, need_id, `type`
    FROM data_block as db
    left join tag as tg1 on db.tag1 = tg1.id
    left join tag as tg2 on db.tag2 = tg2.id
    left join tag as tg3 on db.tag3 = tg3.id
    WHERE tag1 = :tag or tag2 = :tag or tag3 = :tag order by name";

    private $sql_langs = "SELECT site.lang FROM site";
    private $sql_lang_translation = "SELECT site.translation FROM site WHERE lang = :lang";
    private $sql_update_translation = "INSERT INTO translation (id_block, lang, subtitles, edited_by)
    VALUES (:id, :lang, :sub, :edited_by)
    ON DUPLICATE KEY
        UPDATE
    subtitles=:sub, edited_by=:edited_by";

    private $sql_history_list = "SELECT
    th.id_history,
    th.date_begin as date,
    th.edited_by as user_id,
    us.name as user_name
    FROM
    translation_history as th
    LEFT JOIN `user` as us ON us.id = th.edited_by
    WHERE change_type = 'edit' and id_block = :block_id and lang = :lang";

    private $sql_history_subtitles = "SELECT
    th.id_history,
    th.id,
    th.lang,
    th.subtitles,
    th.change_type,
    th.date_begin as date,
    th.edited_by as user_id,
    us.name as user_name,
    db.id as data_block_id,
    db.name as data_block_name
    FROM
    translation_history as th
    INNER JOIN data_block as db ON db.id = th.id_block
    LEFT JOIN `user` as us ON us.id = th.edited_by
    WHERE id_block = :block_id and lang = :lang and id_history <= :id_history
    ORDER BY id_history DESC
    LIMIT 2";

    private $sql_insert_user = "INSERT INTO laingame.`user`
    (email, name, password, can_edit) VALUES(:email, :name, :password, :can_edit)";

    private $sql_find_user = "SELECT id, email, name, password, created_at, can_edit
    FROM laingame.`user` WHERE LOWER(name)=LOWER(:name) LIMIT 1";

    private $db;

    public function __construct()
    {
        require '../config.php';
        if($this->db) return;
        try {
            $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    #PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
                    ];
            $this->db = new PDO('mysql:host='.$hostname.';dbname='.$dbname, $username, $password, $options);
        } catch (Exception $ex) {
            die("something went wrong. $ex");
        }

        $result = $this->db->prepare($this->sql_langs);
        $result->execute();
        $this->langs = $result->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getLangStrings($lang)
    {
        if(!in_array($lang, $this->langs)) $lang = 'en'; // set english if lang code is not regognised
        $result = $this->db->prepare($this->sql_lang_translation);
        $result->execute(['lang' => $lang]);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        return json_decode($row['translation']);
    }

    public function getBlockPrepare()
    {
        if(!isset($this->get_block)) {
            $this->get_block = $this->db->prepare($this->sql_get_block);
        }
        return $this->get_block;
    }

    public function getBlock($id, $lang)
    {
        $get_block = $this->getBlockPrepare();
        $get_block->execute(['id'=>$id, 'lang'=>$lang]);
        $result = $get_block->fetch(PDO::FETCH_ASSOC);
        if($result){
            if($result['subtitles']) {
                $result['subtitles'] = json_decode($result['subtitles'], true);
            }
            $result['reveals'] = [];
            if($result['reveals_ids'] and $result['reveals_names']) {
                $result['reveals'] = array_combine( json_decode( $result['reveals_ids'], true),
                                                    json_decode( $result['reveals_names'], true) );
            }
        }
        return $result;
    }
    
    public function getHistory($block_id, $lang)
    {
        $list = $this->db->prepare($this->sql_history_list);
        $list->execute(['block_id'=>$block_id, 'lang'=>$lang]);
        $results = $list->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }

    public function getHistorySubtitles($block_id, $lang, $id_history)
    {
        $list = $this->db->prepare($this->sql_history_subtitles);
        $list->execute(['block_id'=>$block_id, 'lang'=>$lang, 'id_history'=>$id_history]);
        $results = $list->fetchAll(PDO::FETCH_ASSOC);
        foreach($results as $key => $result){
            if($result['subtitles']) {
                $results[$key]['subtitles'] = json_decode($result['subtitles'], true);
            }
        }
        return $results;
    }

    public function getBlocksTable($site)
    {
        if($site != 0 or $site != 1) $site = 0;
        $content = array();
        $table_result = $this->db->prepare($this->sql_blocks_table);
        $table_result->execute(); //['site'=>$site]);
        while ($dd = $table_result->fetch(PDO::FETCH_ASSOC)) {
            $content[$dd['site']][$dd['level']][$dd['row']][$dd['col']] = array(
                'id' => $dd['id'],
                'icon' => $this->icons[$dd['icon']],
                'name' => $dd['name']
            );
        }
        return $content;
    }

    public function getBlocksByTag($tag)
    {
        if(!ctype_digit($tag) or intval($tag) > 416) return false;
        $table_result = $this->db->prepare($this->sql_blocks_by_tag);
        $table_result->execute(['tag'=>$tag]);
        $result = $table_result->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function updateTranslation($id, $lang, $translation_json, $user_id)
    {
        $result = $this->db->prepare($this->sql_update_translation);
        $result->execute(['id'=>$id, 'lang'=>$lang, 'sub'=> $translation_json, 'edited_by'=>$user_id]);
    }

    public function registerUser($name, $email, $password)
    {
        $result = $this->db->prepare($this->sql_insert_user);
        try{
            $ret = $result->execute([
                'name'=>$name,
                'email'=>$email,
                'password'=>password_hash($password, PASSWORD_DEFAULT),
                'can_edit'=>1]);
        }catch (PDOException $e){
            return intval($e->errorInfo[0]);
        }
        return $ret ? 0 : 1;
    }

    public function getUser($username, $password)
    {
        $result = $this->db->prepare($this->sql_find_user);
        try{
            $res = $result->execute(['name'=>$username]);
            if($result->rowCount() == 0)
              return 2;
            $user_from_db = $result->fetch(PDO::FETCH_ASSOC);
        }catch (PDOException $e){
            return intval($e->errorInfo[0]);
        }
        $ret = password_verify($password, $user_from_db['password']);
        return $ret ? $user_from_db : 1;
    }
}
