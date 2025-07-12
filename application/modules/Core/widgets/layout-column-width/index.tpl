<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2016-10-21 02:08:08Z john $
 * @author     John
 */
?>
<div id="column_width_<?php echo $this->identity ?>"> </div>
<script type="text/javascript">
  var layoutWidth = '<?php echo $this->columnWidth ?>';
  var layoutWidthWidget = scriptJquery('#column_width_<?php echo $this->identity ?>');
  if(layoutWidthWidget) {
    if(layoutWidthWidget.parent().parent().parent('.layout_right')) {
      layoutWidthWidget.parent().parent().parent('.layout_right').css('width', layoutWidth);
      layoutWidthWidget.parent().css('display', 'none');
    } 
    if(layoutWidthWidget.parent().parent().parent('.layout_left')) {
      layoutWidthWidget.parent().parent().parent('.layout_left').css('width', layoutWidth);
      layoutWidthWidget.parent().css('display', 'none');
    } 
    if(layoutWidthWidget.parent().parent('.layout_middle')) {
      layoutWidthWidget.parent().parent('.layout_middle').css('width', layoutWidth);
      layoutWidthWidget.parent().css('display', 'none');
    }
  }
</script>
