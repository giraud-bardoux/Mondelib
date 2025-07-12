<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: tickets.tpl 9915 2013-02-15 01:30:19Z alex $
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
      <div class="user_setting_global_form">
          <div class="mb-3">
            <h3><?php echo $this->translate("Support Inbox") ?></h3>
          </div>
          <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.supportcreate', 1)) { ?>
            <div class="mb-2">
              <a href='<?php echo $this->url(array('module' => 'user', 'controller' => 'support', 'action' => 'create', 'id' => $this->user_id, 'param' => 1), 'user_support', true) ?>' class="smoothbox btn btn-primary"><i class="fas fa-plus"></i><span><?php echo $this->translate("Open New Ticket") ?></span></a>
            </div>
          <?php } ?>
          <div class="manage_search core_search_form">
            <?php echo $this->formFilter->render($this) ?>
          </div>
        <script type="text/javascript">
          function multiModify(){
            var multimodify_form = scriptJquery('#multimodify_form');
            if (multimodify_form.submit_button.value == 'delete')
            {
              return confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to delete the selected ticket?")) ?>');
            }
          }
          function selectAll(obj){
            scriptJquery('.checkbox').each(function(){
              scriptJquery(this).prop("checked",scriptJquery(obj).prop("checked"))
            });
          }
        </script>
        <script type="text/javascript">

          var modulename = 'core';
          var type = 'tickets';
          var category_id = '<?php echo $this->category_id; ?>';
          var subcat_id = '<?php echo $this->subcat_id; ?>';
          var subsubcat_id = '<?php echo $this->subsubcat_id; ?>';

          en4.core.runonce.add(function() {
            if(category_id && category_id != 0) {
              showSubCategory(category_id, subcat_id);
            } else {
              if(scriptJquery('#category_id').val()) {
                showSubCategory(scriptJquery('#category_id').val());
              } else {
                if(document.getElementById('subcat_id-wrapper'))
                  document.getElementById('subcat_id-wrapper').style.display = "none";
              }
            }

            if(subsubcat_id) {
              if(subcat_id && subcat_id != 0) {
                showSubSubCategory(subcat_id, subsubcat_id);
              } else {
                if(document.getElementById('subsubcat_id-wrapper'))
                  document.getElementById('subsubcat_id-wrapper').style.display = "none";
              }
            } else if(subcat_id) {
              showSubSubCategory(subcat_id);
            }
            else {
              if(document.getElementById('subsubcat_id-wrapper'))
                document.getElementById('subsubcat_id-wrapper').style.display = "none";
            }
          });

          en4.core.runonce.add(function() {
            scriptJquery("#selectall").click(function(){
              if(this.checked){
                scriptJquery('.checkbox').each(function(){
                  scriptJquery(".checkbox").prop('checked', true);
                });
              } else {
                scriptJquery('.checkbox').each(function(){
                  scriptJquery(".checkbox").prop('checked', false);
                });
              }
            });
          });
        </script>
        <?php $count = $this->paginator->getTotalItemCount() ?>
        <?php if($count > 0) {  ?>
          <div class='manage_table_count mb-2'>
            <?php echo $this->translate(array("%s ticket found.", "%s tickets found.", $count), $this->locale()->toNumber($count)) ?>
          </div>
          <form id='multimodify_form' method="post" action="<?php echo $this->url(array('action'=>'multi-modify'));?>" onSubmit="multiModify()">
            <div class="manage_table">
              <table>
                <thead>
                  <tr>
                    <!--<th style='width: 1%;'><input onclick="selectAll(this)" type='checkbox' class='checkbox'></th>-->
                    <th><?php echo $this->translate("Ticket ID") ?></th>
                    <th><?php echo $this->translate("Subject") ?></th>
                    <?php if(engine_count($this->categories) > 0) { ?>
                      <th><?php echo $this->translate("Category") ?></th>
                    <?php } ?>
                    <!--<th><?php //echo $this->translate("Submitted By") ?></th>-->
                    <th><?php echo $this->translate("Creation Date") ?></th>
                    <th><?php echo $this->translate("Last Reply") ?></th>
                    <th><?php echo $this->translate("Options") ?></th>
                  </tr>
                </thead>
                <tbody>
                  <?php if( ($this->paginator) ): ?>
                    <?php foreach( $this->paginator as $item ): ?>
                      <?php if(!empty($item->resource_type) && $item->resource_id) { ?>
                        <?php $resource = Engine_Api::_()->getItem($item->resource_type, $item->resource_id); ?>
                      <?php } ?>
                      <?php $poster = Engine_Api::_()->getItem('user', $item->user_id); ?>
                      <tr>
                        <!--<td><input name='modify_<?php //echo $item->getIdentity();?>' value='<?php //echo $item->getIdentity();?>' type='checkbox' class='checkbox'></td>-->
                        
                        <td data-label="<?php echo $this->translate("Ticket ID") ?>"><a href='<?php echo $this->url(array('action' => 'manage', 'ticket_id' => $item->ticket_id)) ?>'>#<?php echo $item->getIdentity() ?></a></td>
                        <td data-label="<?php echo $this->translate("Subject") ?>"><a href='<?php echo $this->url(array('action' => 'manage', 'ticket_id' => $item->ticket_id)) ?>'><?php echo $item->subject; ?></a></td>
                        <?php if(engine_count($this->categories) > 0) { ?>
                          <td data-label="Category">
                            <?php if(!empty($item->category_id)) { ?>
                              <?php $category = Engine_Api::_()->getItem('core_category', $item->category_id); ?>
                              <?php echo $category->category_name; ?>
                            <?php } else echo "---"; ?>
                          </td>
                        <?php } ?>
                        <!--<td data-label="<?php //echo $this->translate("Submitted By") ?>"><a href="<?php //echo $poster->getHref(); ?>"><?php //echo $poster->getTitle(); ?></a></td>-->
                        <td data-label="<?php echo $this->translate("Creation Date") ?>"><?php echo $this->timestamp($item->creation_date); ?></td>
                        <td data-label="<?php echo $this->translate("Last Reply") ?>"><?php echo $this->timestamp($item->lastreply_date); ?></td>
                        <td class='manage_table_options'>
                          <a href='<?php echo $this->url(array('action' => 'manage', 'ticket_id' => $item->ticket_id)) ?>'><?php echo $this->translate("Read Message") ?></a>
                          <?php if(!empty($item->resource_type) && $item->resource_id && $resource) { ?>
                            |
                            <a target='_blank' href='<?php echo $resource->getHref();?>'><?php echo $this->translate("View Content") ?></a>
                          <?php } ?>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif;  ?>
                </tbody>
              </table>
            </div>
        <!--      <div class='buttons'>
              <button type='submit' name="submit_button" value="delete">
                <?php //echo $this->translate("Delete Selected") ?>
              </button>
            </div>-->
          </form>
        <?php } else { ?>
          <div class="tip">
            <span>
              <?php echo $this->translate("There are no tickets yet."); ?>
            </span>
          </div>
        <?php } ?>
      </div>
      <script type="text/javascript">
        var coreselectedDate;
        scriptJquery('#date-date_from').attr("type","text").attr("autocomplete","off").attr("placeholder","<?php echo $this->translate('From'); ?>").datepicker({
        }).on('change', function(ev){
          coreselectedDate = scriptJquery('#date-date_from').val();
          scriptJquery('#date-date_to').datepicker('option', 'minDate', scriptJquery('#date-date_from').val());
        });
        
        scriptJquery('#date-date_to').attr("type","text").attr("autocomplete","off").attr("placeholder","<?php echo $this->translate('To'); ?>").datepicker({
          minDate: coreselectedDate,
        });
      </script>
    </div>
  </div>
</div>
