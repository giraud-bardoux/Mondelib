<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: complete.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php
  $appBaseHref = str_replace('install/', '', $this->url(array(), 'default', true));
  $appBaseUrl = rtrim(str_replace('\\', '/', dirname($this->baseUrl())), '/');
?>
<div class="sucess_admin_page">
  <?php $this->headTitle($this->translate('Step %1$s', 5))->headTitle($this->translate('Complete')) ?>
  <h1>
    <?php echo $this->translate('Congratulations! You\'re ready to go.') ?>
  </h1>
<p>
  <?php echo $this->translate('We\'ve successfully completed the installation process.') ?>
  <?php
    $appBaseHref = str_replace('install/', '', $this->url(array(), 'default', true));
    $url = $appBaseHref . 'admin/';
  ?>
  <?php echo $this->translate(' You can now sign in to your %1$s and get started.',
    $this->htmlLink($url, $this->translate('control panel'))) ?>
</p>
 <p>
   <?php echo $this->translate('Thanks again for choosing SocialEngine. We hope you enjoy using it as much as we enjoyed making it!') ?>
  </p>

  <p class="love">
    <?php echo $this->translate('Love,') ?>
  <span> <?php echo $this->translate('The SE Team') ?> </span>
  </p>
  <?php if( !empty($this->form) ): ?>
    <?php echo $this->form->render($this) ?>
  <?php endif; ?>
  <div class="succes_btn_group">
    <a href="<?php echo $this->url(array(), 'logout') ?>?return=<?php echo urlencode($appBaseHref . 'admin/') ?>" class="btn btn-info"> Go to Admin Panel</a>
  </div>
</div>

