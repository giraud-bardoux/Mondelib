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
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'parentMenuItemName' => 'core_admin_main_manage_tickets', 'lastMenuItemName' => 'Support Message')); ?>

<?php if( engine_count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<div class="user_support_view">
  <div class="user_support_view_head">
    <div class="user_support_view_title d-flex">
      <h3><?php echo $this->ticket->subject; ?></h3>
      <span class="ticket_btns">
        <?php if(!empty($this->ticket->resource_type) && $this->ticket->resource_id && $this->resource) { ?>
          <a href="<?php echo $this->resource->getHref(); ?>" target="_blank" class="btn-main"><?php echo $this->translate("View Content"); ?></a>
        <?php } ?>
        <a href="<?php echo $this->url(array('action' => 'edit-ticket', 'ticket_id' => $this->ticket_id)) ?>" class="smoothbox btn-main"><?php echo $this->translate("Edit"); ?></a>
        <a href='<?php echo $this->url(array('action' => 'delete', 'ticket_id' => $this->ticket_id)) ?>' class="smoothbox btn-danger"><?php echo $this->translate("Delete") ?></a>
      </span>
    </div>
    <p class="m-0 text_light">
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
  <div class="user_support_messages">
    <?php foreach($this->paginator as $result) { ?>
      <?php $user = Engine_Api::_()->getItem('user', $result->user_id); ?>
      <?php $level = Engine_Api::_()->getItem('authorization_level', $user->level_id); ?>
      <div class="user_support_message_item d-flex flex-wrap">
        <div class="ticket_left">
          <div class="ticket_thumb">
            <?php echo $this->itemBackgroundPhoto($user, 'thumb.icon'); ?>
          </div> 
          <div class="ticket_left_info"> 
            <p><a href="<?php echo $user->getHref(); ?>"><?php echo $user->getTitle(); ?></a></p>
            <p class="text_light font_small"><span class="m_label level_<?php echo $level->type; ?>"><?php echo $this->translate($level->getTitle()) ?></span></p>
            <p class="ticket_btns">
              <a href="<?php echo $this->url(array('action' => 'edit-reply', 'ticket_id' => $result->ticket_id, 'ticketreply_id' => $result->ticketreply_id)) ?>" class="smoothbox btn-main"><?php echo $this->translate("Edit"); ?></a> 
              <a href='<?php echo $this->url(array('action' => 'delete-reply', 'ticket_id' => $result->ticket_id, 'ticketreply_id' => $result->ticketreply_id)) ?>' class="smoothbox btn-danger"><?php echo $this->translate("Delete") ?></a>
            </p>
          </div>
        </div>
        <div class="ticket_right">
          <p class="font_small text_light m-0"><?php echo $this->translate("Posted")?> <?php echo $this->timestamp($result->creation_date) ?></p>
          <div class="user_support_message_info_des">
            <?php echo nl2br($result->description); ?>
          </div>
        </div>
      </div>
    <?php } ?>
    <div class="user_support_message_item_reply">
      <div class="user_support_message_info">
        <?php echo $this->form->render($this) ?>
      </div>
    </div>
  </div>
</div>

<?php if(!empty($this->ticket->resource_type) && !empty($this->ticket->resource_id)) { ?>
  <?php $resource = Engine_Api::_()->getItem($this->ticket->resource_type, $this->ticket->resource_id); ?>  
  <?php if($resource->approved) { ?>
    <script type="application/javascript">
      scriptJquery('#approved-1').parent().hide();
      scriptJquery('#approved-2').parent().show();
    </script>
  <?php } else { ?>
    <script type="application/javascript">
      scriptJquery('#approved-2').parent().hide();
      scriptJquery('#approved-1').parent().show();
    </script>
  <?php } ?>
<?php } ?>

<script type="application/javascript">
  
  AttachEventListerSE('submit','#core_ticket_reply',function(e){
    e.preventDefault();
    
    if(scriptJquery('#core_error_message').length)
      scriptJquery('#core_error_message').remove();
      
    if(scriptJquery('#description').val() == '') {
      scriptJquery('#core_ticket_reply').before('<div class="error_message mb-2" id="core_error_message" style="display:block;">'+en4.core.language.translate('Please enter reply description.')+'</div>');
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
          scriptJquery('#core_ticket_reply').before('<div class="success_message mb-2" id="core_success_message" style="display:block;">'+en4.core.language.translate('Your reply has been sent successfully.')+'</div>');
          //silence
          //scriptJquery("#core_ticket_reply")[0].reset();
          location.reload();
        }
      },
      error: function(data){
        //silence
      }
    });
  });

</script>

<script type="application/javascript">
  scriptJquery('.core_admin_main_manage').parent().addClass('active');
  scriptJquery('.core_admin_main_manage_tickets').addClass('active');
</script>
