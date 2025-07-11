<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9915 2013-02-15 01:30:19Z alex $
 * @author     John
 */
?>
<div class="generic_layout_container layout_top">
  <div class="generic_layout_container layout_middle">
    <?php echo $this->content()->renderWidget('user.user-setting-cover-photo'); ?>
  </div>
</div>
<div class="generic_layout_container layout_main user_setting_main_page_main">
  <div class="generic_layout_container layout_middle user_setting_main_middle">
    <div class="theiaStickySidebar">
      <div class="user_support_view">
        <div class="user_support_view_head d-flex flex-wrap align-items-start">
          <a href="<?php echo $this->url(array('module' => 'user', 'controller' => 'support', 'action' => 'index'), 'user_support', true) ?>" class="d-flex align-items-center justify-content-center"><i class="icon_back"></i></a>
          <div>
            <h3><?php echo $this->ticket->subject; ?></h3>
            <p class="m-0 font_color_light font_small">
              <span><?php echo $this->translate("Ticket ID: #%s", $this->ticket_id); ?></b></span>
              <span><?php echo $this->translate("Created: %s", $this->timestamp($this->ticket->creation_date)); ?></span>
              <?php if(!empty($this->ticket->category_id)) { ?>
                <?php $category = Engine_Api::_()->getItem('core_category', $this->ticket->category_id); ?>
                <?php if($category) { ?>
                  <span>
                    <?php echo $this->translate("Category: %s", $category->category_name); ?>
                    <?php if(!empty($this->ticket->subcat_id)) { ?>
                      <?php echo $this->translate('&#187;'); ?>
                      <?php $subcategory = Engine_Api::_()->getItem('core_category', $this->ticket->subcat_id); ?>
                      <?php echo $subcategory->category_name; ?>
                    <?php } ?>
                    <?php if(!empty($this->ticket->subsubcat_id)) { ?>
                      <?php echo $this->translate('&#187;'); ?>
                      <?php $subsubcategory = Engine_Api::_()->getItem('core_category', $this->ticket->subsubcat_id); ?>
                      <?php echo $subsubcategory->category_name; ?>
                    <?php } ?>
                  </span> 
                <?php } ?>
              <?php } ?>
            </p>
          </div>
        </div>
        <div class="user_support_messages">
          <?php foreach($this->paginator as $result) { ?>
            <?php $user = Engine_Api::_()->getItem('user', $result->user_id); ?>
            <?php $level = Engine_Api::_()->getItem('authorization_level', $user->level_id); ?>
            <div class="user_support_message_item">
              <div class="user_support_message_item_top">
                <p class="m-0"><span class="fw-bold"><?php echo $user->getTitle(); ?></span> <span class="m_label level_<?php echo $level->type; ?>"><?php echo $this->translate($level->getTitle()) ?></span></p>
                <p class="font_small font_color_light m-0"><?php echo $this->translate("Posted")?> <?php echo $this->timestamp($result->creation_date) ?></p>
              </div>
              <div class="user_support_message_info">
                <div class="user_support_message_info_des">
                  <?php echo nl2br($result->description); ?>
                </div>
              </div>
            </div>
          <?php } ?>
          <div class="user_support_message_item user_support_message_item_reply">
            <div class="user_support_message_info">
              <?php echo $this->form->render($this) ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script type="application/javascript">
  AttachEventListerSE('submit','#core_ticket_reply',function(e){
    e.preventDefault();
    
    if(scriptJquery('#core_message_error_message').length)
      scriptJquery('#core_message_error_message').remove();
    if(scriptJquery('#description').val() == '') {
      scriptJquery('#core_ticket_reply').before('<div id="core_message_error_message" class="error_msg d-flex align-items-center" id="core_error_message"><span>'+en4.core.language.translate('Please enter reply description.')+'</span></div>');
      return false;
    }
    
    if(!scriptJquery('#core_admin_overlay_content').length)
      scriptJquery('#core_ticket_reply').before('<div class="core_loading_cont_overlay" id="core_admin_overlay_content" style="display:block;"></div>');
    else
      scriptJquery('#core_admin_overlay_content').show();
    
    var form = scriptJquery('#core_ticket_reply');
    var formData = new FormData(this);
    formData.append('is_ajax', 1);
    formData.append('ticket_id', '<?php echo $this->ticket_id; ?>');
    formData.append('id', '<?php echo $this->user_id; ?>');
    formData.append('param', 1);
    
    submitFormAjax = scriptJquery.ajax({
      dataType: 'json',
      type:'POST',
      url: scriptJquery(this).attr('action'),
      data:formData,
      cache:false,
      contentType: false,
      processData: false,
      success:function(data) {
        scriptJquery('#core_admin_overlay_content').hide();
        scriptJquery('#description').val('');
        if(data.status == 1) {
          if(scriptJquery('#core_success_message').length)
            scriptJquery('#core_success_message').remove();
          scriptJquery('#core_ticket_reply').before('<div class="success_msg d-flex align-items-center" id="core_success_message"> <span>'+en4.core.language.translate('Your reply has been sent successfully.')+'</span></div>');
          //silence
          loadAjaxContentApp(window.location, true);
        }
      },
      error: function(data){
        //silence
      }
    });
  });
  scriptJquery('.user_settings_support').parent().addClass('active');
</script>
