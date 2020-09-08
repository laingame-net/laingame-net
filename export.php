<?php

#use PDO;
#use PDOException;

require_once "config.php";

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
    #die("something went wrong. $ex");
}

$sql_langs = "SELECT site.lang FROM site";
$sql_select_sub = "SELECT db.id, db.name, lang, subtitles
FROM laingame.`translation`
left join laingame.data_block db on laingame.`translation`.id_block = db.id";

$result = $db->prepare($sql_langs);
$result->execute();
$langs = $result->fetchAll(PDO::FETCH_COLUMN);

$dir = "export";
@mkdir($dir);
$actors = array();
foreach($langs as $lang)
{
    @mkdir($dir."/".$lang);
    $actors[$lang] = array();
}

$result = $db->prepare($sql_select_sub);
$result->execute();

while ($row = $result->fetch(PDO::FETCH_ASSOC)) 
{
    if($row){
        if($row['subtitles']) {
            $row['subtitles'] = json_decode($row['subtitles'], true);
        }
    }
    $row['dir'] = preg_replace('/[0-9]+/', '', $row['name']);
    @mkdir($dir."/".$row['lang']."/".$row['dir']);

    $subtitles = "";
    foreach($row["subtitles"] as $subtitle)
    {
        if(strpos($subtitle['text'], "\t"))
        {
            die($subtitle['text']);
        }
        if(strpos($subtitle['actor'], "\t"))
        {
            die($subtitle['actor']);
        }
        //if(strpos($subtitle['actor']," "))
        {
            echo $row['id']." ".$row['lang']." ".$subtitle['actor']."\n";
        }

        $subtitles .= $subtitle['actor']."\t".$subtitle['text']."\n";
        
        if(!array_key_exists($subtitle['actor'], $actors[ $row['lang'] ])){
            $actors[ $row['lang'] ][ $subtitle['actor'] ] = 1;
        }else{
            $actors[ $row['lang'] ][ $subtitle['actor'] ] += 1; // $actors[ $subtitle['actor'] ] ? $actors[ $subtitle['actor'] ]+1 : 1;
        }
    }
    //echo $row['lang']." ".$row['name']."\n";
    //var_dump($actors);
    file_put_contents($dir."/".$row['lang']."/".$row['dir']."/".$row['name'].".txt", $subtitles);
    if($row['lang'] == 'en'){
        foreach($langs as $lang){
            if(!file_exists($dir."/".$lang."/".$row['dir']."/".$row['name'].".txt"))
            {
                @mkdir($dir."/".$lang."/".$row['dir']);
                file_put_contents($dir."/".$lang."/".$row['dir']."/".$row['name'].".txt", $subtitles);
            }
        }
    }
}

foreach($actors as $lang => $act_ar){
    //echo $lang." ";
    //var_dump($act_ar);

    $actors_txt = "";
    foreach($act_ar as $key => $value){
        $actors_txt .= $value."\t".$key."\n";
    }
    //var_dump($act_ar);
    file_put_contents($dir."/".$lang."/actors.txt", $actors_txt);
}
