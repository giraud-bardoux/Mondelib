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
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'childMenuItemName' => 'core_admin_main_settings_activity')); ?>

<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>
<?php
  $this->form->setTitle('Activity Feed Settings');
  $this->form->setDescription($this->translate('ACTIVITY_FORM_ADMIN_SETTINGS_GENERAL_DESCRIPTION',
      $this->url(array('module' => 'activity','controller' => 'settings', 'action' => 'types'), 'admin_default')));
  $this->form->getDecorator('Description')->setOption('escape', false);
?>
<div class='settings'>
  <?php echo $this->form->render($this); ?>
</div>

<script type="application/javascript">

en4.core.runonce.add(function() {
    var elem = scriptJquery('#composeroptions-element').find('ul').children();
    for(i=0;i<elem.length;i++){
      var value = scriptJquery(elem[i]).find('input').val();
      var label = scriptJquery(elem[i]).find('label').html();
      var html = label.split('|||');
      var splitZero = html[0];
      var splitOne = html[1];
    }
  });

  //repeatAds(<?php //echo Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.adsrepeatenable', 0); ?>);

  ads(<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.adsenable', 0); ?>);
  showLanguage(<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.translate', 1); ?>);

  function repeatAds(value) {
    if(value == 1) {
      document.getElementById('adsrepeattimes-wrapper').style.display = 'flex';		
    } else {
      document.getElementById('adsrepeattimes-wrapper').style.display = 'none';		
    }
  }

  function ads(value) { 
    if(!document.getElementById('adcampaignid')){
      document.getElementById('adsenable-wrapper').style.display = 'none';
      document.getElementById('adsrepeatenable-wrapper').style.display = 'none';
      //repeatAds(0);	
      return false;
    }
    if(value == 1){
      document.getElementById('adcampaignid-wrapper').style.display = 'flex';
      document.getElementById('adsrepeatenable-wrapper').style.display = 'flex';
      document.getElementById('adsrepeattimes-wrapper').style.display = 'flex';			
      //repeatAds(<?php //echo Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.adsrepeatenable', 0); ?>);
    }else{
      document.getElementById('adcampaignid-wrapper').style.display = 'none';
      document.getElementById('adsrepeatenable-wrapper').style.display = 'none';
      //repeatAds(0);	
      document.getElementById('adsrepeattimes-wrapper').style.display = 'none';		
    }
  }


  
  peopleymk(<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.peopleymk', 1); ?>);

  function peopleymk(value) {
    if(value == 1) {
      if(document.getElementById('peopleymkrepeattimes-wrapper'))
        document.getElementById('peopleymkrepeattimes-wrapper').style.display = 'flex';
      if(document.getElementById('pymkrepeatenable-wrapper'))
        document.getElementById('pymkrepeatenable-wrapper').style.display = 'flex';
    } else {
      if(document.getElementById('peopleymkrepeattimes-wrapper'))
        document.getElementById('peopleymkrepeattimes-wrapper').style.display = 'none';
      if(document.getElementById('pymkrepeatenable-wrapper'))
        document.getElementById('pymkrepeatenable-wrapper').style.display = 'none';
    }
  }

  function showLanguage(value){
    if(value == 1){
      document.getElementById('language-wrapper').style.display = 'flex';		
    }else{
      document.getElementById('language-wrapper').style.display = 'none';		
    }
  }
</script>
