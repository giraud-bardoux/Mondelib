<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: sanity.tpl 9747 2012-07-26 02:08:08Z john $
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
  <li>
    <a href="<?php echo $this->url(array('action' => 'db-info')) ?>">
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
<?php $this->headTitle($this->translate('Step %1$s', 1))->headTitle($this->translate('System Test')) ?>
<div class="install_heding_description">
  <h1>
    <?php echo $this->translate('Step 1: Check Requirements') ?>
  </h1>
  <p>
    <?php echo $this->translate('Great! Next, let\'s make sure your server has everything it needs to support SocialEngine. If any of the requirements below are marked with red, you will need to address them before continuing. If items are marked with yellow, we recommend that you address them before installing, but you can continue if you wish.') ?>
  </p>
</div>
<div class='sanity_wrapper'>
  <div>
    <ul class='sanity'>
      <?php foreach( $this->test->getTests() as $test ): ?>
        <li>
          <div>
            <?php echo $test->getName() ?>
          </div>
          <?php if( !$test->hasMessages() ): ?>
            <div class='sanity-ok'>
              <?php echo $test->getEmptyMessage(); ?>
          </div>
          <?php else: ?>
            <?php
              $errLevel = $test->getMaxErrorLevel();
              $errClass = ( $errLevel & 4 ? 'sanity-error' : ($errLevel & 3 ? 'sanity-notice' : 'sanity-ok' ));
            ?>
            <div class='<?php echo $errClass ?>'>
              <?php foreach( $test->getMessages() as $message ): ?>
								<?php if($test->getName() == 'mod_rewrite' && $errClass == 'sanity-notice') { ?>
									<?php echo $this->translate('Your server configuration is not running PHP as an Apache module so you\'ll need to manually verify that mod_rewrite is enabled in Apache. <br /> More info: <a href="https://stackoverflow.com/questions/18310183/how-to-check-for-mod-rewrite-on-php-cgi" target="_blank"> See KB article</a>.'); ?> <br />
								<?php } else { ?>
									<?php echo $message->toString() ?>
                <?php } ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
<?php if( $this->force ): ?>
  <p>
    <?php echo $this->translate('Force Install?') ?>
  </p>
  <div>
    <button onclick="window.location.href = '<?php echo $this->url(array('action' => 'db-info')) ?>';">
      <?php echo $this->translate('Force Install?...') ?>
    </button>
  </div>
<?php elseif( $this->maxOtherErrorLevel >= 4 ): ?>
  <p class="warning_ads">
    <?php echo $this->translate('
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
        <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z"/>
       <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z"/>
      </svg>  Please address all of the issues highlighted in red before continuing with the installation.') ?>
  </p>
  <div class="admin_install_btn"> 
    <button onclick="window.location.replace(window.location.href);">
      <?php echo $this->translate('Check Again') ?>
    </button>
  </div>
<?php elseif( $this->maxFileErrorLevel >= 4 ): ?>
  <p  class="warning_ads">
    <?php echo $this->translate(' 
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
      <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z"/>
      <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z"/>
    </svg> 
    Please address all of the issues highlighted in red before continuing with the installation.'); ?>
  </p>

  <p class="warning_ads">
    <?php echo $this->translate('
     <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
       <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z"/>
       <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z"/>
      </svg> 
      We noticed that some permissions have not been set correctly. To solve this, you can either attempt to %s (using your FTP information), or you can set the permissions manually by logging in with your FTP client and setting the necessary permissions, as shown above.',
    $this->htmlLink(array('action' => 'vfs', 'reset' => false), $this->translate('do it automatically'))) ?>
  </p>

  <div class="admin_install_btn">
    <button onclick="window.location.replace(window.location.href);">
      <?php echo $this->translate('Check Again') ?>
    </button>
  </div>
<?php else: ?>
  <div class="admin_install_btn">
    <button onclick="window.location.href = '<?php echo $this->url(array('action' => 'db-info')) ?>';">
      <?php echo $this->translate(' Continue') ?>
    </button>

  </div>
<?php endif; ?>
