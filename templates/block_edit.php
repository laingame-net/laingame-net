<?php $this->init_page() // we must call this function at the begining of any page ?>
  <h1><?=is_array($block) ? $block['name'] : $block_en['name']?> (<?=$lang?>)</h1>

  <div class='table'>
    <div class='row'>
<?php if( isset($block_en['subtitles']) ): ?>
      <div class='cell'>
        <table>
          <tr>
            <th>Actor</th>
            <th>Text (en)</th>
          </tr>
<?php foreach ($block_en['subtitles'] ?? [] as $key => $line): ?>
          <tr>
            <td style="width:40px"><input readonly type="text" id="actor<?=$key?>" name="actor[]" value="<?=$line['actor']?>"></td>
            <td style="width:800px"><input readonly type="text" id="text<?=$key?>" name="text[]" value="<?=$line['text']?>"></td>
          </tr>
<?php endforeach ?>
        </table>
      </div>
<?php endif; ?>

      <div class='cell'>
        <form action="/block/edit/<?=$id?>/<?=$lang?>" method="post">
          <table>
            <tr>
              <th>Actor</th>
              <th>Text (<?=$lang?>)</th>
            </tr>
<?php foreach ($block['subtitles'] ?? $block_en_subtitles_empty ?? [] as $key => $line): ?>
            <tr>
              <td style="width:40px"><input type="text" id="actor<?=$key?>" name="actor[]" value="<?=$line['actor']?>"></td>
              <td style="width:800px"><input type="text" id="text<?=$key?>" name="text[]" value="<?=$line['text']?>"></td>
            </tr>
<?php endforeach ?>
          </table>
          <input type='submit' name='submit' value='Submit'>
        </form>
      </div>
    </div>
  </div>

