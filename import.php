<?php

require_once "config.php";

function interpolateQuery($query, $params) {
    $keys = array();

    # build a regular expression for each parameter
    foreach ($params as $key => $value) {
        $params[$key] = "'".$params[$key]."'";
        if (is_string($key)) {
            $keys[] = '/:'.$key.'/';
        } else {
            $keys[] = '/[?]/';
        }
    }

    $query = preg_replace($keys, $params, $query, 1, $count);

    return $query;
}

try {
    $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            #PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"
            ];
    $db = new PDO('mysql:host=localhost:3306;dbname='.$dbname, $username, $password, $options);
} catch (Exception $ex) {
    var_dump($ex);
    die("something went wrong. $ex");
}

$sql_blocks = "SELECT `name`, id FROM data_block";

$sql_instert_trans = "INSERT INTO `translation` (id_block, lang, subtitles, edited_by) VALUES(:id_block, :lang, :subtitles, 0)";

$result = $db->prepare($sql_blocks);
$result->execute();
$blocks_ids = $result->fetchAll(PDO::FETCH_KEY_PAIR);

$lang = 'jp';

$dir = "import/$lang/";
$import_files = array("Cou.json", "Eda.json", "Ekm.json", "Ere.json");

$i = 0;

$result = $db->prepare($sql_instert_trans);

foreach($import_files as $file_name)
{
    $id_block = array();
    $string = file_get_contents($dir.$file_name);
    $json_a = json_decode($string, true);
    foreach($json_a as $name => $block)
    {
        if(!array_key_exists($name, $blocks_ids))
            die("block not found = ".$name."\n");

        $prepared_block = array( 'id_block'=>$blocks_ids[$name], 'lang'=>$lang, 'subtitles'=>json_encode( $block ) );
        $id_block[ $blocks_ids[$name] ] = $prepared_block;

        //echo interpolateQuery( $sql_instert_trans,  $prepared_block );
        //echo "\n";

        try{
            $result->execute( $prepared_block );
        } catch (\PDOException $e) {
            var_dump($e);
        }
    }
}