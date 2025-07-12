<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: db-sanity.tpl 9747 2012-07-26 02:08:08Z john $
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
  <p>
    <?php echo $this->translate('We\'ve successfully connected to the database. Now, let\'s make sure your MySQL server has everything it needs to support SocialEngine.') ?>
  </p>
</div>  
<div class="sanity_wrapper">
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
                <?php echo $message->toString() ?> <br />
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
<div>
  <?php if( $this->maxErrorLevel >= 4 ): ?>
  <div class="admin_install_btn">
    <button class="back_btn" onclick="window.location.href = '<?php echo $this->url(array('action' => 'db-info'), '', true) ?>?clear=1';">
      <?php echo $this->translate('Back') ?>
    </button>
    <button onclick="window.location.replace(window.location.href);">
      <?php echo $this->translate('Try Again') ?>
    </button>
  </div>
  <?php else: ?>
  <div class="admin_install_btn">
    <button class="back_btn"  onclick="window.location.href = '<?php echo $this->url(array('action' => 'db-info'), '', true) ?>?clear=1';">
      <?php echo $this->translate('Back') ?>
    </button>
    <button onclick="window.location.href = '<?php echo $this->url(array('action' => 'db-create'), '', true) ?>';">
      <?php echo $this->translate('Continue') ?>
    </button>
  </div>
  <?php endif; ?>
</div>
