<?php $this->init_page(); // we must call this function at the begining ?>
<div class=float-global>
<!-- Available languages:
<?php foreach ($langs ?? [] as $val): ?>
  <a href="/home/index/view/<?=$val?>"><?=$val?></a>
<?php endforeach; ?>
-->
<div class="container">
 <div class='table'>
  <?php foreach($data_blocks as $skey => $site): ?>
    <div class='row site'>
      <div class='cell'>
        <a href="/site/view/<?=$skey.'/'.$lang?>"><?="site ".$skey?></a>
      </div>
    </div>
    <?php foreach($site as $lkey => $level): ?>
      <div class='row level'>
        <div class='cell' id="<?='site'.$skey.'level'.$lkey?>">
          <a href="/level/view/<?=(($skey==1)?'a':'b').'-'.$lkey.'/'.$lang?>"><?="site ".$skey." level ".$lkey?></a>
        </div>
      </div>
      <?php foreach ($level as $rkey => $row): ?>
        <div class='row'>
          <div class='table'>
            <?php for ($i = 7; ($i >= 0); $i--): ?>
              <div class='cell'>
              <?php if(isset($row[$i])): ?>
                <a href="/block/edit/<?=$row[$i]['id'].'/'.$lang?>">
                  <img src="/media/icons<?=((@$row[$i]['langs'][0]==NULL) or in_array('ru',$row[$i]['langs'])) ? '/grey' : '' ?>/<?=$row[$i]['icon']?>">
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
 </div><?// div class=table ?>
</div><? // div class=container ?>

<div class=changes>
  <h3>Latest Changes</h3>
  <table>
  <tr>
    <th>Who</th>
    <th>When</th>
    <th>What</th>
    <th>Block</th>
  </tr>
<?php foreach($latest_changes as $change): ?>
  <tr>
    <td><?=$change['user_name']?></td>
    <td><?=$change['date_begin']?></td>
    <td><?=$change['change_type']?></td>
    <td><?="<a href=block/edit/".$change['block_id'].">".$change['block_name']."</a>"?></td>
  </tr>
<?php endforeach ?>
  </table> 
</div>

</div><?// div class=float-global ?>
