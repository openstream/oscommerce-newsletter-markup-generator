<?php

  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $action = (isset($HTTP_GET_VARS['action']) ? $HTTP_GET_VARS['action'] : '');
  if(isset($_GET['list_type'])){
   $_SESSION['markup_generator_list_type'] = $_GET['list_type'];
  }
  if(isset($_GET['show_desc'])){
   $_SESSION['markup_generator_show_desc'] = $_GET['show_desc'];
  }

  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert':
        if(!is_array($_SESSION['markup_generator'])){
         $_SESSION['markup_generator'] = array();
        }
        $_SESSION['markup_generator'][(int)$HTTP_POST_VARS['products_id']] = (int)$HTTP_POST_VARS['products_id'];
        tep_redirect(tep_href_link('markup-generator.php'));

        break;
      case 'deleteconfirm':
        $temp_arr = array();
        foreach($_SESSION['markup_generator'] as $products_id){
         if($products_id != (int)$HTTP_GET_VARS['sID']){
          $temp_arr[$products_id] = $products_id;
         }
        }
        $_SESSION['markup_generator'] = $temp_arr;
        tep_redirect(tep_href_link('markup-generator.php', 'page=' . $HTTP_GET_VARS['page']));
        break;
    }
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
<?php
  if ( ($action == 'new') || ($action == 'edit') ) {
?>
<link rel="stylesheet" type="text/css" href="includes/javascript/calendar.css">
<script language="JavaScript" src="includes/javascript/calendarcode.js"></script>
<?php
  }
?>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="SetFocus();">
<div id="popupcalendar" class="text"></div>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
<?php
  if ( ($action == 'new') ) {
?>
      <tr><form name="new_special" <?php echo 'action="' . tep_href_link('markup-generator.php', tep_get_all_get_params(array('action', 'info', 'sID')) . 'action=insert', 'NONSSL') . '"'; ?> method="post">
        <td><br><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_SPECIALS_PRODUCT; ?>&nbsp;</td>
            <td class="main"><?php echo tep_draw_products_pull_down('products_id', 'style="font-size:10px"'); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
       <td class="main" align="right" valign="top"><br><?php echo tep_image_submit('button_insert.gif', IMAGE_INSERT). '&nbsp;&nbsp;&nbsp;<a href="' . tep_href_link('markup-generator.php', 'page=' . $HTTP_GET_VARS['page'] . (isset($HTTP_GET_VARS['sID']) ? '&sID=' . $HTTP_GET_VARS['sID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
      </form></tr>
<?php
  } else {
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRODUCTS_PRICE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $specials_query_raw = "select p.*, pd.*, s.specials_new_products_price, s.expires_date, s.status from " . TABLE_PRODUCTS . " p LEFT JOIN " . TABLE_SPECIALS . " s ON p.products_id = s.products_id LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON p.products_id = pd.products_id where pd.language_id = '" . (int)$languages_id . "' AND p.products_id IN (".(is_array($_SESSION['markup_generator']) && count($_SESSION['markup_generator']) ? implode(', ', $_SESSION['markup_generator']) : '0').") order by pd.products_name";
    $specials_split = new splitPageResults($HTTP_GET_VARS['page'], MAX_DISPLAY_SEARCH_RESULTS, $specials_query_raw, $specials_query_numrows);
    $specials_query = tep_db_query($specials_query_raw);
    while ($specials = tep_db_fetch_array($specials_query)) {
      if ((!isset($HTTP_GET_VARS['sID']) || (isset($HTTP_GET_VARS['sID']) && ($HTTP_GET_VARS['sID'] == $specials['products_id']))) && !isset($sInfo)) {
        $products_query = tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int)$specials['products_id'] . "'");
        $products = tep_db_fetch_array($products_query);
        $sInfo_array = array_merge($specials, $products);
        $sInfo = new objectInfo($sInfo_array);
      }

      if (isset($sInfo) && is_object($sInfo) && ($specials['products_id'] == $sInfo->products_id)) {
        echo '<tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('markup-generator.php', 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->products_id . '&action=edit') . '\'">' . "\n";
      } else {
        echo '<tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link('markup-generator.php', 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $specials['products_id']) . '\'">' . "\n";
      }
?>
                <td  class="dataTableContent"><?php echo $specials['products_name']; ?></td>
                <td  class="dataTableContent" align="right"><?php if($specials['specials_new_products_price']) : ?><span class="oldPrice"><?php echo $currencies->format($specials['products_price']); ?></span> <span class="specialPrice"><?php echo $currencies->format($specials['specials_new_products_price']); ?></span><?php else : ?><?php echo $currencies->format($specials['products_price']); ?><?php endif; ?></td>
                <td class="dataTableContent" align="right"><?php if (isset($sInfo) && is_object($sInfo) && ($specials['products_id'] == $sInfo->products_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link('markup-generator.php', 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $specials['products_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
      </tr>
<?php

     if($_SESSION['markup_generator_list_type']){
      $gentxt .= (++$odd_row%3 == 1 ? '<tr>' : '').'
       <td align="center" valign="top" width="33%" style="font-size:11px;">
        <a href="'.HTTP_CATALOG_SERVER.DIR_WS_CATALOG.'product_info.php?products_id='.$specials['products_id'].'">'.tep_image(HTTP_CATALOG_SERVER.DIR_WS_CATALOG.DIR_WS_IMAGES.$specials['products_image'], $specials['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT).'</a><br />
        <a href="'.HTTP_CATALOG_SERVER.DIR_WS_CATALOG.'product_info.php?products_id='.$specials['products_id'].'" style="font-weight: bold; ">' . tep_output_string_protected($specials['products_name']) . '</a><br />       
        '.($specials['specials_new_products_price'] ? '<s>'.$currencies->display_price($specials['products_price'], tep_get_tax_rate($specials['products_tax_class_id'])) . '</s><br /><font color="red">' . $currencies->display_price($specials['specials_new_products_price'], tep_get_tax_rate($specials['products_tax_class_id'])).'</font>' : $currencies->display_price($specials['products_price'], tep_get_tax_rate($specials['products_tax_class_id']))).'
        '.($_SESSION['markup_generator_show_desc'] ? '<br /><br />' . substr(strip_tags($specials['products_description']), 0, PRODUCT_LIST_TEASER_LENGTH) . (strlen($specials['products_description']) > 0 ? ' ...' : '') : '').'
        <br /><br />
       </td>'.($odd_row%3 == 0 ? '</tr>' : '');
     }else{
      $gentxt .= '
       <tr bgcolor="'.(++$odd_row%2 ? '#ffffff' : '#eceeec').'">
       <td align="center" valign="top" style="padding:5px 1px;"><a href="'.HTTP_CATALOG_SERVER.DIR_WS_CATALOG.'product_info.php?products_id='.$specials['products_id'].'">'.tep_image(HTTP_CATALOG_SERVER.DIR_WS_CATALOG.DIR_WS_IMAGES.$specials['products_image'], $specials['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT).'</a></td>
       <td style="padding:5px 1px;" valign="top">'.$specials['products_model'].'</td>
       <td style="padding:5px 1px;" valign="top"><a href="'.HTTP_CATALOG_SERVER.DIR_WS_CATALOG.'product_info.php?products_id='.$specials['products_id'].'" style="font-weight: bold; ">' . tep_output_string_protected($specials['products_name']) . '</a>&nbsp;<br>' . ($_SESSION['markup_generator_show_desc'] ? substr(strip_tags($specials['products_description']), 0, PRODUCT_LIST_TEASER_LENGTH) . (strlen($specials['products_description']) > 0 ? ' ...' : '') : '').'</td>
       <td style="padding:5px 1px;" valign="top" align="right">'.($specials['specials_new_products_price'] ? '<s>'.$currencies->display_price($specials['products_price'], tep_get_tax_rate($specials['products_tax_class_id'])) . '</s><br /><font color="red">' . $currencies->display_price($specials['specials_new_products_price'], tep_get_tax_rate($specials['products_tax_class_id'])).'</font>' : $currencies->display_price($specials['products_price'], tep_get_tax_rate($specials['products_tax_class_id']))).'</td>
       <td style="padding:5px 1px;" valign="top" align="center"><a href="'.HTTP_CATALOG_SERVER.DIR_WS_CATALOG.'product_info.php?products_id='.$specials['products_id'].'&action=buy_now"><img border="0" src="'.HTTP_CATALOG_SERVER.DIR_WS_CATALOG.'includes/sts_templates/teeblaetter/images/german/buttons/button_buy_now.gif" alt="Jetzt kaufen" title="Jetzt kaufen"></a></td></tr>';
     }
    }
?>
              <tr>
                <td colspan="4"><table border="0" width="100%" cellpadding="0"cellspacing="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $specials_split->display_count($specials_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $HTTP_GET_VARS['page'], TEXT_DISPLAY_NUMBER_OF_SPECIALS); ?></td>
                    <td class="smallText" align="right"><?php echo $specials_split->display_links($specials_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $HTTP_GET_VARS['page']); ?></td>
                  </tr>
<?php
  if (empty($action)) {
   $gentxt = '<table width="100%" cellspacing="0" cellpadding="0"'.($_SESSION['markup_generator_list_type'] ? '' : ' style="border-color:#E7EAF1;border-spacing:1px;border-style:solid;border-width:3px 1px;font-size:11px;"').'>'.($_SESSION['markup_generator_list_type'] ? '' : '<tr><th bgcolor="#CCCCCC">&nbsp;&nbsp;</th><th bgcolor="#CCCCCC">&nbsp;Best.Nr.&nbsp;</th><th bgcolor="#CCCCCC">&nbsp;Produkte&nbsp;</th><th align="right" bgcolor="#CCCCCC">&nbsp;Preis&nbsp;</th><th align="center" bgcolor="#CCCCCC">&nbsp;Bestellen&nbsp;</th></tr>').$gentxt.'</table>';
   $js_url = preg_replace('/&?list_type=./ism', '', $_SERVER['REQUEST_URI']);
   $js_url .= (preg_match('/\.php$/ism', $js_url) ? '?' : '&').'list_type=';
   $js_url2 = preg_replace('/&?show_desc=./ism', '', $_SERVER['REQUEST_URI']);
   $js_url2 .= (preg_match('/\.php$/ism', $js_url2) ? '?' : '&').'show_desc=';
?>
                  <tr><td colspan="4" align="right"><?php echo '<a href="' . tep_href_link('markup-generator.php', 'page=' . $HTTP_GET_VARS['page'] . '&action=new') . '">' . tep_image_button('button_new_product.gif', IMAGE_NEW_PRODUCT) . '</a>'; ?></td></tr>
                  <tr><td colspan="4" class="smallText"><br />
<input type="radio" name="list_type" onclick="location.href = '<?php echo $js_url ?>0'" value="0"<?php echo $_SESSION['markup_generator_list_type'] ? '' : ' checked="checked"'; ?> /> List &nbsp; <input type="radio" name="list_type" onclick="location.href = '<?php echo $js_url ?>1'" value="1"<?php echo $_SESSION['markup_generator_list_type'] ? ' checked="checked"' : ''; ?> /> Grid<br /><br />
<input type="radio" name="show_desc" onclick="location.href = '<?php echo $js_url2 ?>0'" value="0"<?php echo $_SESSION['markup_generator_show_desc'] ? '' : ' checked="checked"'; ?> /> No description &nbsp; <input type="radio" name="show_desc" onclick="location.href = '<?php echo $js_url2 ?>1'" value="1"<?php echo $_SESSION['markup_generator_show_desc'] ? ' checked="checked"' : ''; ?> /> Short Description<br /><br />
<?php echo $gentxt; ?></td></tr>
                  <tr><td colspan="4"><br /><textarea style="width:100%" rows="10"><?php echo $gentxt; ?></textarea></td></tr>
<?php
  }
?>
                </table></td>
              </tr>
            </table></td>
<?php

  $heading = array();
  $contents = array();

  switch ($action) {
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_SPECIALS . '</b>');
      $contents = array('form' => tep_draw_form('specials', 'markup-generator.php', 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->products_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $sInfo->products_name . '</b>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . '&nbsp;<a href="' . tep_href_link('markup-generator.php', 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->products_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (is_object($sInfo)) {
        $heading[] = array('text' => '<b>' . $sInfo->products_name . '</b>');
        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link('markup-generator.php', 'page=' . $HTTP_GET_VARS['page'] . '&sID=' . $sInfo->products_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_info_image($sInfo->products_image, $sInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT).'<br /><br />');
      }
  }
  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
   $box = new box;
   echo '<td width="25%" valign="top">'.$box->infoBox($heading, $contents).'</td>';
  }
}

?>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>