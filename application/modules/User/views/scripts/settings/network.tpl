<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: network.tpl 10110 2013-10-31 02:04:11Z andres $
 * @author     Alex
 */
?>
<script type="text/javascript">
  function joinNetwork(network_id)
  {
    scriptJquery('#join_id').val(network_id);
    scriptJquery('#network-form').trigger("submit");
    scriptJquery('#avaliable_networks').html("<div style='margin:15px 0;'><img class='loading_icon' src='" + en4.core.staticBaseUrl + "application/modules/Core/externals/images/loading.gif'/><?php echo $this->translate('Joining Network...')?></div>");
  }

  function leaveNetwork(network_id)
  {
    scriptJquery('#current_networks').html("<div><img class='loading_icon' src='" + en4.core.staticBaseUrl + "application/modules/Core/externals/images/loading.gif'/><?php echo $this->translate('Leaving Network...')?></div>");
    scriptJquery('#leave_id').val(network_id);
    scriptJquery('#network-form').trigger("submit");
  }
  en4.core.runonce.add(function()
  {
    var availableNetworks = <?php echo Zend_Json::encode($this->available_networks); ?>;
    let items = ''; 
    let text = '<?php echo $this->translate('Join Network') ?>';
    availableNetworks.forEach(function(item){
      items += `<li><div class="networks_title">${item.title}</div><a href="javascript:void(0);" onclick="joinNetwork(${item.id})">${text}</a></li>`
    })
    scriptJquery(`<ul class="networks" style="z-index: 42; overflow-y: hidden;"></ul>`).html(scriptJquery(items)).insertAfter(scriptJquery("#title"));
    scriptJquery("#title").attr("autocomplete","off");
    scriptJquery("#title").on("input",function(){
      let val = scriptJquery(this).val().toLowerCase();
      scriptJquery(".networks_title").each(function(){
        if(scriptJquery(this).html().toLowerCase().search(val) == -1){
          scriptJquery(this).parent().hide();
        } else {
          scriptJquery(this).parent().show();
        }
      });
    });

    // new OverText($('title'), {
    //   'textOverride' : '<?php //echo $this->translate('Start typing to filter...') ?>',
    //   'element' : 'label',
    //   'positionOptions' : {
    //     position: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
    //     edge: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
    //     offset: {
    //       x: ( en4.orientation == 'rtl' ? -4 : 4 ),
    //       y: 2
    //     }
    //   }
    // });
  });
</script>
<div class="generic_layout_container layout_top">
  <div class="generic_layout_container layout_middle">
    <?php echo $this->content()->renderWidget('user.user-setting-cover-photo'); ?>
  </div>
</div>
<div class="generic_layout_container layout_main user_setting_main_page_main">
  <div class="generic_layout_container layout_left">
    <div class="theiaStickySidebar">
      <?php echo $this->content()->renderWidget('user.settings-menu'); ?>
    </div>
  </div>
  <div class="generic_layout_container layout_middle user_setting_main_middle">
    <div class="theiaStickySidebar">
      <div class='netwroks_main_section'>
        <div class='networks_left'>
          <h3><?php echo $this->translate('Available Networks');?></h3>
          <?php if(!empty($this->network_suggestions)):?>
          <p>
            <?php echo $this->translate('To add a new network, begin typing its name below.');?>
          </p>
          <div id='avaliable_networks'>
            <?php echo $this->form->render($this) ?>
          </div>
          <?php if(false):?>
            <ul class='networks'>
            <?php foreach ($this->network_suggestions as $network): ?>
              <li>
                <div>
                  <?php echo $network->title ?> <span>(<?php echo $this->translate(array('%s member.', '%s members.', $network->membership()->getMemberCount()),$this->locale()->toNumber($network->membership()->getMemberCount())) ?>)</span>
                </div>
                <?php if( $network->assignment == 0 ): ?>
                  <a href='javascript:void(0);' onclick="joinNetwork(<?php echo $network->network_id;?>)"><?php echo $this->translate('Join Network');?></a>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
            </ul>
          <?php endif;?>
          <?php else:?>
            <div class="tip">
              <span><?php echo $this->translate('There are currently no avaliable networks to join.');?></span>
            </div>
            <div style='display:none;'>
              <?php echo $this->form->render($this) ?>
            </div>
          <?php endif; ?>
        </div>
        <div class='networks_right'>
          <h3><?php echo $this->translate('My Networks');?></h3>
          <p>
            <?php echo $this->translate(array('You belong to %s network.', 'You belong to %s networks.', engine_count($this->networks)),$this->locale()->toNumber(engine_count($this->networks))) ?>
          </p>
          <ul id='current_networks' class='networks'>
            <?php foreach ($this->networks as $network): ?>
              <?php if(empty($network->network_id)) continue; ?>
              <li>
                <div>
                  <?php echo $this->translate($network->title) ?> <span>(<?php echo $this->translate(array('%s member.', '%s members.', $network->membership()->getMemberCount()),$this->locale()->toNumber($network->membership()->getMemberCount())) ?>)</span>
                </div>
                <?php if( $network->assignment == 0 ): ?>
                  <a href='javascript:void(0);' onclick="leaveNetwork(<?php echo $network->network_id;?>)"><?php echo $this->translate('Leave Network');?></a>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
