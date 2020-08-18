<?php $this->init_page() // we must call this function at the begining of any page ?>
<script>
window.onload = function() {
  document.getElementById("media").volume=0.5;
}
</script>
Available languages: 
<?php foreach ($langs ?? [] as $val): ?>
  <a href="/block/view/<?=$block['id'].'/'.$val?>"><?=$val?></a>  
<?php endforeach; ?>
  <a href="/lang/add/">add</a>  
  <div class='table'>
    <div class='row'>
      <div class='cell'>
        <div class='table'>
          <div class='row'><div class='cell'>ID:</div><div class='cell'>
            <a href="/block/view/<?=($block['id']-1).'/'.$lang?>">&lt;-</a>
            <a href="/block/view/<?=$block['id'].'/'.$lang?>"><?=$block['name']?></a>
            <a href="/block/view/<?=($block['id']+1).'/'.$lang?>">-&gt;</a>
          </div></div>

          <div class='row'><div class='cell'>Location:</div><div class='cell'>
            <a href="/#!<?=$block['level']?>"><?="site ".$block['site']." , level ".$block['level'].", #".$block['page_pos']?></a>
          </div></div>

          <div class='row'><div class='cell'>Misc:</div><div class='cell'>
            <?="SW: ".$block['sw'].", q: ".$block['q'].", state: ".$block['state'].", type: ".$block['type'].PHP_EOL;?>
          </div></div>

          <div class='row'><div class='cell'>Depends on:</div><div class='cell'>
            <?= ( !empty($block['need_name']) ) ? '<a href="/block/view/'.$block['need_id'].'/'.$lang.'">'.$block['need_name']."</a>\n" : "nothing\n"; ?>
          </div></div>

          <div class='row'><div class='cell'>Reveals:</div><div class='cell'>
<?php if(!$block['reveals']): ?>
            nothing
<?php endif; ?>
<?php foreach ($block['reveals'] ?? [] as $rid => $name): ?>
            <a href="/block/view/<?=$rid."/".$lang?>"><?=$name?></a>
<?php endforeach; ?>
          </div></div>

        </div>
      </div>
      <div class='cell'>
        <div class='table'>
          <div class='row'><div class='cell'>Info:</div><div class='cell'>
            <?=$block['info1']?><br>
            <?=$block['info2']?><br>
            <?=$block['info3']?><br>
            <?=$block['info4']?><br>
          </div></div>
          <div class='row'><div class='cell'>Tags:</div><div class='cell'>
            <a href="/tag/view/<?=$block['tag1_id'].'/'.$lang?>"><?=$block['tag1_value']?></a><br>
            <a href="/tag/view/<?=$block['tag2_id'].'/'.$lang?>"><?=$block['tag2_value']?></a><br>
            <a href="/tag/view/<?=$block['tag3_id'].'/'.$lang?>"><?=$block['tag3_value']?></a>
          </div></div>
        </div>
      </div>
    </div>
  </div>

<?php if($block['type'] == 2): // video ?>
  <video id="media" width="640" controls="" height="32" >
    <source src="/media/<?=$block['name']?>.mp4" type="video/mp4">
  </video>
  <div class='table'>
    <div class='row'>
<?php for($i=1; $i<=3; $i++):?>
<?php if(isset($block['img'.$i])):?>
      <div class='cell' style='text-align:center;'><img src="/media/<?=sprintf("img_%d_%03d.png", $block['site'],$block['img'.$i])?>"></div>
<?php endif // if isset block img?>
<?php endfor?>
    </div>
  </div>
<?php elseif($block['type'] == 3): // audio ?>
<video id="media" width="640" controls=""> <!-- height="32" -->
    <source src="/media/<?=$block['name']?>.mp4" type="video/mp4">
</video>
<?php endif // if($block['type'] == 2 ?>

<?php if( $block['subtitles'] ): ?>

<?php if( isset($block['failback_to_english']) ): ?>
Warning! Translation on your language (<?=$lang?>) in not available. Showed English instead.<br>You can help by providing translation on your language below.
<?php endif ?>

<?php if( isset($block['edit_form']) ): ?>
<form action="/block/edit/<?=$id?>/<?=$lang?>" method="post">
  <table>
    <tr>
      <th>Actor</th>
      <th>English (default language)</th>
      <th>If you want to help please fill <?=$lang?> translation and press Submit</th>
    </tr>
<?php foreach ($block['subtitles'] ?? [] as $key => $line): ?>
    <tr>
      <td style="width:40px"><input type="text" id="actor<?=$key?>" style="width:93%" name="actor[]" value="<?=$line['actor']?>"></td>
      <td style="width:800px"><?=$line['text']?></td>
      <td style="width:800px"><input type="text" id="text<?=$key?>" style="width:99%" name="text[]" value=""></td>
    </tr>
<?php endforeach ?>
  </table>
  <input type='submit' name='submit' style="width:100px" value='Submit'>
</form>

<?php else: ?>
You can edit this translation <a href="/block/edit/<?=$block['id'].'/'.$lang?>">here</a>.

<table>
  <tr>
    <th>Actor</th>
    <th>Text</th>
  </tr>
<?php foreach ($block['subtitles'] ?? [] as $line): ?>
  <tr>
    <td><?=$line['actor']?></td>
    <td><?=$line['text']?></td>
  </tr>
<?php endforeach ?>
</table>
<?php if($history_list):?>
Previous translation versions: 
<?php foreach ($history_list ?? [] as $key => $history): ?>
<a href="/block/history/<?=$block['id']?>/<?=$lang?>?_event=<?=$history['id_history']?>"
title="Edited by: <?=($history['user_name'] ?: "unknown")."\n"?>Date: <?=$history['date']?>"><?=$key+1?></a>
<?php endforeach ?>
<?php endif ?>

<?php endif ?>
<?php else: # if( $block['subtitles'] ):?>
<p>Translation of this file is not required</p>
<?php endif ?>
