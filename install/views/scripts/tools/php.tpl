<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: php.tpl 7250 2010-09-01 07:42:35Z john $
 * @author     John
 */
?>

<h2 class="server_heading">
  <?php echo $this->translate('Server Information') ?>
</h2>

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
  #phpinfo td, #phpinfo th { padding: 5px 10px; border: 1px solid #ddd; vertical-align: baseline;word-break:break-all; }
  #phpinfo h1 {font-size: 150%;}
  #phpinfo h2 {font-size: 125%; font-weight:600;margin:10px 0;}
  #phpinfo .p {text-align: left;}
  #phpinfo .e {background-color: #f1eeff; min-width: 150px;color: #000000;}
  #phpinfo .h {background-color: #208ed3; font-weight: bold; color: #fff;}
  #phpinfo .h th{color:#fff;}
  #phpinfo .h h1 {font-weight: 600; color: #fff;margin:0;padding:5px 10px;font-size:17px;}
  #phpinfo .v {background-color: #fdfdfd; color: #000000;}
  #phpinfo .vr {background-color: #cccccc; text-align: right; color: #000000;}
  #phpinfo img {float: right;}
  #phpinfo .v img {border: 1px solid #444;}
  #phpinfo hr {width: 100%; background-color: #cccccc; border: 0px; height: 1px; color: #000000;}
  #phpinfo h2 a { display: block; padding: 10px; font-size: 16px; border-radius: 5px;background-color: #fdfdfd;color: #161616;font-weight: 600; text-decoration:none !important;text-transform:capitalize;}
</style>

<div id="phpinfo">
  <?php echo $this->content; ?>
</div>
