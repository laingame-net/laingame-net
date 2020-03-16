<?php $this->init_page(); // we must call this function at the begining ?>
Available languages:
<?php foreach ($langs ?? [] as $val): ?>
  <a href="/home/index/view/<?=$val?>"><?=$val?></a>
<?php endforeach; ?>
  <div class='table'>
<?php foreach($data_blocks as $skey => $site): ?>
    <div class='row site'>
      <div class='cell'>
        <a href="/site/view/<?=$skey.'/'.$lang?>"><?="site ".$skey?></a>
      </div>
    </div>
<?php foreach($site as $lkey => $level): ?>
    <div class='row level'>
      <div class='cell'>
        <a href="/level/view/<?=$lkey.'/'.$lang?>"><?="level ".$lkey?></a>
      </div>
    </div>
<?php foreach ($level as $rkey => $row): ?>
    <div class='row'>
      <div class='table'>
<?php for ($i = 7; ($i >= 0); $i--): ?>
        <div class='cell'>
<?php if(isset($row[$i])): ?>
          <a href="/block/view/<?=$row[$i]['id'].'/'.$lang?>">
            <img src="/media/icons/<?=$row[$i]['icon']?>">
            <br>
            <?=$row[$i]['name']?>
          </a>
<?php endif ?>
        </div>
<?php endfor ?>
      </div>
    </div>
<?php endforeach ?>
<?php endforeach ?>
<?php endforeach ?>
  </div>
