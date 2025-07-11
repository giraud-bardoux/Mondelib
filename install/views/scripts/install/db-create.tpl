<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: db-create.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<ul class="intsall_admin_step">
  <li class="active">
    <a href="<?php echo $this->url(array('action' => 'sanity')) ?>">
      <span class="cont_number">
        1
      </span>
    </a>
  </li>
  <li class="active">
    <a href="<?php echo $this->url(array('action' => 'db-info'), '', true) ?>?clear=1';">
      <span class="cont_number">
        2
      </span>
    </a>
  </li>
  <li>
    <a href="<?php echo $this->url(array('action' => 'account')) ?>">
      <span class="cont_number">
        3
      </span>
    </a>
  </li>
</ul>
<div class="install_heding_description">
  <h1>
    <?php echo $this->translate('Step 2: Setup MySQL Database') ?>
  </h1>
  <?php if( !empty($this->error) ): ?>
    <p class="error_desc">
      <?php echo $this->error ?>
    </p>
  <?php if( $this->code == 2 ): ?>
    <button onclick="window.location.replace('<?php echo $this->url() ?>?force=1');">Overwrite</button>
  <?php elseif( $this->code == 3 ): ?>
    <button onclick="window.location.replace('<?php echo $this->url() ?>?force=2');">Continue Anyway</button>
  <?php endif; ?>
<?php endif; ?>
<?php if( !empty($this->status) ): ?>
  <p>
    Success! Your SocialEngine database is now ready to go.
  </p>
 </div>  
    <div class="admin_install_btn">
    <button class="back_btn"  onclick="window.location.href = '<?php echo $this->url(array('action' => 'db-sanity'), '', true) ?>?clear=1';">
      <?php echo $this->translate('Back') ?>
    </button>
   <button onclick="window.location.replace('<?php echo $this->url(array('action' => 'account')) ?>');">Continue </button>
  </div>
<?php endif; ?>
