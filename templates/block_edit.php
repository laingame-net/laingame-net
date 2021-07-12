<?php $this->init_page() // we must call this function at the begining of any page ?>
<script>

const GAPIKey = "<?=$google_api_key?>";

// Abstract API request function
function gTranslateApiRequest(item) {
  url = "https://translation.googleapis.com/language/translate/v2";
  url += "?key=" + GAPIKey;
  translationObj = {
        q: item.innerHTML,
        source: 'ja',
        target: 'en',
        format: 'text'
      };
  var trackingJSON = JSON.stringify(translationObj);
  var xhr = new XMLHttpRequest();
  xhr.open('POST', url);
  xhr.setRequestHeader('Content-Type', 'application/json');
  xhr.setRequestHeader('Accept',       'application/json');

  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      var resp = JSON.parse(xhr.responseText);
      item.innerHTML += " "+resp.data.translations[0].translatedText;
    }
  }
  xhr.send(trackingJSON);
}

function main()
{
  media = document.getElementById("media")
  if(media) media.volume=0.5;
  autosize(document.querySelectorAll('textarea'));

  document.querySelectorAll('.ja').forEach(item => {
    item.addEventListener('touchstart', event => {
      gTranslateApiRequest(item);
    })
    item.addEventListener('dblclick', event => {
      gTranslateApiRequest(item);
    })
  });
}


window.onload = main;

</script>
<!-- Available languages: 
<?php foreach ($langs ?? [] as $val): ?>
  <a href="/block/view/<?=$block['id'].'/'.$val?>"><?=$val?></a>  
<?php endforeach; ?>
  <a href="/lang/add/">add</a>
-->
<div class="container">
  <div class="block">
    <div class='table'>
      <div class='row'>
        <div class='cell'>
          <div class='table'>
            <div class='row'><div class='cell'>ID:</div><div class='cell'>
              <a href="/block/edit/<?=($block['id']-1).'/'.$lang?>">&lt;-</a>
              <a href="/block/edit/<?=$block['id'].'/'.$lang?>"><?=$block['name']?></a>
              <a href="/block/edit/<?=($block['id']+1).'/'.$lang?>">-&gt;</a>
            </div></div>
  
            <div class='row'><div class='cell'>Location:</div><div class='cell'>
              <a href="<?='/#site'.$block['site'].'level'.$block['level']?>"><?="site ".$block['site']." , level ".$block['level'].", #".$block['page_pos']?></a>
            </div></div>
  
            <div class='row'><div class='cell'>Misc:</div><div class='cell'>
              <?="SW: ".$block['sw'].", q: ".$block['q'].", state: ".$block['state'].", type: ".$block['type'].PHP_EOL;?>
            </div></div>
  
            <div class='row'><div class='cell'>Depends on:</div><div class='cell'>
              <?= ( !empty($block['need_name']) ) ? '<a href="/block/edit/'.$block['need_id'].'/'.$lang.'">'.$block['need_name']."</a>\n" : "nothing\n"; ?>
            </div></div>
  
            <div class='row'><div class='cell'>Reveals:</div><div class='cell'>
<?php if(!$block['reveals']): ?>
              nothing
<?php endif; ?>
<?php foreach ($block['reveals'] ?? [] as $rid => $name): ?>
              <a href="/block/edit/<?=$rid."/".$lang?>"><?=$name?></a>
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
<?php if($block['type'] == 2): // audio ?>
    <div class='table'>
      <div class='row'>
<?php for($i=1; $i<=3; $i++):?>
<?php if(isset($block['img'.$i])):?>
<?php $img_name = sprintf("img_%d_%03d", $block['site'],$block['img'.$i]);?>
        <div class='cell' style='text-align:center;'><a href="/media/upscale/<?=$img_name?>.jpg" target="_blank"><img src="/media/<?=$img_name?>.png"></a></div>
<?php endif // if isset block img?>
<?php endfor?>
      </div>
    </div>
<?php endif // if($block['type'] == 2 ?>
  </div>
</div>
<div class="container <?=($block['type'] == 2) ? 'sticky' : ''?>">
  <div class="block">
<?php if($block['type'] == 2): // audio ?>
    <video id="media" width="640" controls="" height="32" >
      <source src="/media/<?=$block['name']?>.mp4" type="video/mp4">
    </video>
<?php elseif($block['type'] == 3): // video ?>
<video id="media" width="640" controls=""> <!-- height="32" -->
      <source src="/media/<?=$block['name']?>.mp4" type="video/mp4">
</video>
<?php endif // if($block['type'] == 2 ?>
  </div>
</div>
<br>
<?php if( $block['block_en']['subtitles'] ): ?>
  <div class='table'>
    <div class='row'>
      <div class='cell' style="width:70%">
        <form action="/block/edit/<?=$id?>/<?=$lang?>" method="post">

        <table style="border:0">
          <tr>
            <td style="width:16%"><input type="submit" class="btn" value="Save" /></td>
            <td style="width:24%"><?php if($history_list):?>
Previous translation versions:<br>
<?php foreach ($history_list ?? [] as $key => $history): ?>
  <a href="/block/history/<?=$block['id']?>/<?=$lang?>?_event=<?=$history['id_history']?>"
title="Edited by: <?=$history['user_name']."\n"?>Date: <?=$history['date']?>"><?=$key+1?></a>
<?php endforeach ?>
<?php endif ?></td>
            <td style="width:60%"></td>
          </tr>
        </table>
        <br>

        <table>
        <tr>
          <th style="width:15%">Actor</th>
          <th style="width:30%">Russian translation</th>
          <th style="width:25%">English translation</th>
          <th style="width:30%">Comment</th>
        </tr>
<?php foreach ($block['block_ru']['subtitles'] ?? [] as $key => $line): ?>
        <tr>
          <td><textarea type="text" id="actor<?=$key?>" name="actor[]"><?=$line['actor']?></textarea></td>
          <td><textarea type="text" id="text<?=$key?>" name="text[]"><?=$line['text']?></textarea></td>
          <td><?=@$block['block_en']['subtitles'][$key]['text']?></td>
          <td><textarea type="text" id="comment<?=$key?>" name="comment[]"><?=$line['comment']?></textarea></td>
        </tr>
<?php endforeach ?>
        </table>
        <br>
        <table style="border:0">
          <tr>
            <td style="width:20%"><input type="submit" class="btn" value="Save" /></td>
            <td><?php if($history_list):?>
Previous translation versions:<br>
<?php foreach ($history_list ?? [] as $key => $history): ?>
  <a href="/block/history/<?=$block['id']?>/<?=$lang?>?_event=<?=$history['id_history']?>"
title="Edited by: <?=(_($history['user_name']) ?: "unknown")."\n"?>Date: <?=$history['date']?>"><?=$key+1?></a>
<?php endforeach ?>
<?php endif ?></td>
            <td style="width:20%"></td>
          </tr>
        </table>
        </form>
      </div>

      <div class='cell'>
        <table>
        <tr>
          <th>Actor</th>
          <th>Japanese transcription</th>
        </tr>
<?php foreach ($block['block_jp']['subtitles'] ?? [] as $line): ?>
        <tr>
          <td class="ja"><?=$line['actor']?></td>
          <td class="ja"><?=$line['text']?></td>
        </tr>
<?php endforeach ?>
        </table>
      </div>
    </div>
  </div>

<?php else: # if( $block['subtitles'] ):?>
<p>Translation of this file is not required</p>
<?php endif ?>
<br>
<br>
<br>
<br>
