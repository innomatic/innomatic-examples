<table>
  <args>
    <headers type="array"><?=$headers?></headers>
  </args>
  <children>

<?php foreach($itemsArray as $row => $item): ?>

    <label row="<?=$row?>" col="0">
      <args>
        <label><?=\Shared\Wui\WuiXml::cdata($item['name'])?></label>
      </args>
    </label>
    <label row="<?=$row?>" col="1">
      <args>
        <label><?=\Shared\Wui\WuiXml::cdata($item['dateArray'])?></label>
      </args>
    </label>
    <innomatictoolbar row="<?=$row?>" col="2">
      <args>
        <frame>false</frame>
        <toolbars type="array"><?=\Shared\Wui\WuiXml::encode($item['toolbar'])?></toolbars>
      </args>
    </innomatictoolbar>

<?php endforeach; ?>

  </children>
</table>
