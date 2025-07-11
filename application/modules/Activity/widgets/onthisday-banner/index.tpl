<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: index.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
 
?>
<?php 
$oldTimeZone = date_default_timezone_get();
date_default_timezone_set($this->viewer()->timezone);
?>
<div class="activity_tip_box activity_onthisday_banner clearfix ">
	<div class="activity_onthisday_banner_date">
  	<span class="activity_onthisday_banner_date_month"><?php echo date('M') ?></span>
  	<span class="activity_onthisday_banner_date_day"><?php echo date('d'); ?></span>
  </div>  
</div>
<?php date_default_timezone_set($oldTimeZone); ?>
