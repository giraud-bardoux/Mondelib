<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: php.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_stats", 'childMenuItemName' => 'core_admin_main_stats_resources')); ?>

<h2 class="page_heading">
  <?php echo $this->translate('Server Information') ?>
</h2>
<p>
<?php
  $settings = Engine_Api::_()->getApi('settings', 'core');
  if( $settings->getSetting('user.support.links', 0) == 1 ) {
    echo 'More info: <a href="https://community.socialengine.com/blogs/597/82/server-information" target="_blank">See KB article</a>.';
  } 
?>	
</p>
<style type="text/css">
  .server_heading{font-size: 22px;font-weight: 600;margin-bottom: 8px;}
  #phpinfo td, #phpinfo th, #phpinfo h1, #phpinfo h2 {font-family: sans-serif;}
  #phpinfo pre {margin: 0px; font-family: monospace;}
  #phpinfo a:link {color: #000099; text-decoration: none; background-color: #ffffff;}
  #phpinfo a:hover {text-decoration: underline;}
  #phpinfo table {border-collapse: collapse;margin:12px 0 20px;width: 100%;}
  #phpinfo .center {text-align: center;}
  #phpinfo .center table { margin-left: auto; margin-right: auto; text-align: left;}
  #phpinfo .center th { text-align: center !important; }
  #phpinfo td, #phpinfo th { padding: 5px 10px; border: 1px solid #ddd;vertical-align: baseline;word-break:break-all; }
  #phpinfo h1 {font-size: 150%;}
  #phpinfo h2 {font-size: 125%; font-weight:600;margin:10px 0;}
  #phpinfo .p {text-align: left;}
  #phpinfo .e {background-color: #f1eeff; min-width: 150px; color: #000000;}
  .dark_mode #phpinfo .e {background-color:#2c2c2c;color: #fff;}
  #phpinfo .h {background-color: #208ed3; font-weight: bold; color: #fff;}
  #phpinfo .h th{color:#fff;}
  #phpinfo .h h1 {font-weight: 600; color: #fff;margin:0;padding:5px 10px;font-size:17px;}
  #phpinfo .v {background-color: #fdfdfd; color: #000000;}
  .dark_mode #phpinfo .v {background-color: #333536; color: #fff;}
  #phpinfo .vr {background-color: #cccccc; text-align: right; color: #000000;}
  .dark_mode #phpinfo .vr {background-color: #2c2c2c;color: #fff;}
  #phpinfo img {float: right;}
  #phpinfo .v img {border: 1px solid #444;}
  #phpinfo hr {width: 100%; background-color: #cccccc; border: 0px; height: 1px; color: #000000;}
  #phpinfo h2 a { display: block; padding: 10px; font-size: 16px; border-radius: 5px;background-color: #fdfdfd;color: #161616;font-weight: 600; text-decoration:none !important;text-transform:capitalize;}
  .dark_mode #phpinfo h2 a{background-color:#2c2c2c;color:#fff;}


</style>

<div id="phpinfo">
  <?php echo $this->content; ?>
</div>
