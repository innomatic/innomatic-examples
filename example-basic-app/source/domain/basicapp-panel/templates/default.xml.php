<table>
  <args>
    <!-- When the value is an array passed with the set() method,
    you must set the type="array" attribute in the XML tag, since the
    $headers variable has been transformed to a serialized array.
     -->
    <headers type="array"><?=$headers?></headers>
  </args>
  <children>

<!-- Since $itemsArray has been passed with the setArray() method,
you get it as a PHP array and not as a serialized array.
 -->
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
