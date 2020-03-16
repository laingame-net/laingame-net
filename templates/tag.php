<?php $this->init_page(); // we must call this function at the begining of any page ?>
<ul>
<?php foreach($blocks as $skey => $block): ?>
  <li><a href="/block/view/<?=$block['id'].'/'.$lang?>"><?=$block['name']?></a></li>
<?php endforeach ?>
</ul>
