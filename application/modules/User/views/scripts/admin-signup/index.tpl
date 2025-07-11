<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'parentMenuItemName' => 'core_admin_main_signup', 'childMenuItemName' => 'core_admin_signupprocess')); ?>

<h2 class="page_heading"><?php echo $this->translate('Signup & Profile Settings') ?></h2>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<script type="text/javascript">
  en4.core.runonce.add(function() {
    scriptJquery('#step_list').addClass('sortable');
    var SortablesInstance = scriptJquery('#step_list').sortable({
      stop: function( event, ui ) {
        var ids = [];
        scriptJquery('#step_list > li').each(function(e) {
          var el = scriptJquery(this);
          ids.push(el.attr('id'));
        });
        <?php if( _ENGINE_ADMIN_NEUTER ) { ?>
          alert('disabled');
          return false;
        <?php } ?>
        // Send request
        var url = '<?php echo $this->url(array('action' => 'signuporder')) ?>';
        scriptJquery.ajax({
            url : url,
            dataType : 'json',
            data : {
              format : 'json',
              order : ids
            }
        });
      }
    });
  });
</script>
<h2><?php echo $this->translate("Member Signup Process") ?></h2>
<p><?php echo $this->translate("USER_VIEWS_SCRIPTS_ADMINSIGNUP_INDEX_DESCRIPTION") ?><br>
  <?php
  $settings = Engine_Api::_()->getApi('settings', 'core');
  if( $settings->getSetting('user.support.links', 0) == 1 ) {
    echo 'More info: <a href="https://community.socialengine.com/blogs/597/27/signup-process" target="_blank">See KB article</a>';
  } 
  ?>
</p>	
<?php if( !empty($this->error) ): ?>
  <ul class="form-errors">
    <li>
      <?php echo $this->error ?>
    </li>
  </ul>
<?php endif; ?>

<div class='admin_signup_wrapper'>
  <div class='admin_signup_steps'>
    <ul id="step_list">
      <?php  foreach( $this->steps as $step ): ?>
        <li class='sortable' id='step_<?php echo $step->signup_id ?>'>
          <a href='<?php echo $this->url(array('signup_id'=>$step->signup_id));?>'><?php echo $this->translate("ADMIN_SIGNUP_STEP_" . strtoupper($step->class)) ?></a>
        </li>
      <?php endforeach;?>
    </ul>
  </div>
  <div class='admin_signup_settings'>
    <div class='form_elements'>
      <?php echo $this->partial($this->script[0], $this->script[1], array(
        'form' => $this->form,
        'current_step'=>$this->current_step
      )) ?>
    </div>
  </div>
</div>
<script type="application/javascript">
  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_signup').addClass('active');
</script>
