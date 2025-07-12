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
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'parentMenuItemName' => 'core_admin_main_manage_comments', 'lastMenuItemName' => 'Comments on Contents')); ?>

<?php if( engine_count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<div class="admin_common_top_section">
  <h3><?php echo $this->translate("Manage Comments on Contents") ?></h3>
  <p><?php echo $this->translate("This page lists all the comments your users have posted on various contents on your site. You can use this page to monitor these comments and delete offensive material if necessary. Entering criteria into the filter fields will help you find specific comments. Leaving the filter fields blank will show all of the comments on contents on your social network.") ?> </p>
</div>  
<script type="text/javascript">
function multiModify(){
  var multimodify_form = scriptJquery('#multimodify_form');
  if (multimodify_form.submit_button.value == 'delete')
  {
    return confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to delete the selected comments?")) ?>');
  }
}
function selectAll(obj){
  scriptJquery('.checkbox').each(function(){
    scriptJquery(this).prop("checked",scriptJquery(obj).prop("checked"))
  });
}
</script>
<div class='admin_search admin_common_search admin_manage_activity_search'>
  <?php echo $this->formFilter->render($this) ?>
</div>
<?php $count = $this->paginator->getTotalItemCount() ?>
<?php if($count > 0) { ?>
  <div class='admin_results'>
    <div>
      <?php echo $this->translate(array("%s comment found.", "%s comments found.", $count), $this->locale()->toNumber($count)) ?>
    </div>
    <div>
      <?php echo $this->paginationControl($this->paginator, null, null, array(
        'pageAsQuery' => true,
        'query' => $this->formValues,
      )); ?>
    </div>
  </div>
  <div class="admin_table_form">
    <form id='multimodify_form' method="post" action="<?php echo $this->url(array('action'=>'multi-modify'));?>" onSubmit="multiModify()">
      <table class='admin_table admin_responsive_table'>
        <thead>
          <tr>
            <th style='width: 1%;'><input onclick="selectAll(this)" type='checkbox' class='checkbox'></th>
            <th style='width: 1%;'><?php echo $this->translate("ID") ?></th>
            <th><?php echo $this->translate("Comment") ?></th>
            <th><?php echo $this->translate("Content Type") ?></th>
            <th><?php echo $this->translate("Content Item") ?></th>
            <th><?php echo $this->translate("Commented By") ?></th>
            <th><?php echo $this->translate("Comment Date") ?></th>
            <th style='width:220px;' class='admin_table_options'><?php echo $this->translate("Options") ?></th>
          </tr>
        </thead>
        <tbody>
          <?php if( engine_count($this->paginator) ): ?>
            <?php foreach( $this->paginator as $item ): ?>
              <?php $resource = Engine_Api::_()->getItem($item->resource_type, $item->resource_id); ?>
              <tr>
                <td><input name='modify_<?php echo $item->getIdentity();?>' value='<?php echo $item->getIdentity();?>' type='checkbox' class='checkbox'></td>
                <td data-label="<?php echo $this->translate("Id") ?>"><?php echo $item->getIdentity() ?></td>
                <td data-label="<?php echo $this->translate("Comment") ?>">
                  <?php echo $this->partial('_activitycommentcontent.tpl', 'comment', array('comment' => $item)); ?>
                  <?php //echo $this->string()->truncate(Engine_Text_Emoji::decode($item->body), 45, '...') ?>
                </td>
                <td data-label="<?php echo $this->translate("Content Type") ?>"><?php echo ucfirst($resource->getShortType()); ?></td>
                <td data-label="<?php echo $this->translate("Content Item") ?>">
                  <div class="admin_table_comments">
                    <?php echo $this->itemBackgroundPhoto($resource, 'thumb.icon'); ?>
                    <a href="<?php echo $resource->getHref(); ?>"><?php echo $resource->getTitle() ? $resource->getTitle() : $this->translate("Untitled"); ?></a>
                  </div>
                </td>
                <td data-label="<?php echo $this->translate("Commented By") ?>">
                  <div class="admin_table_comments">
                    <?php $poster = Engine_Api::_()->getItem($item->poster_type, $item->poster_id); ?>
                    <?php echo $this->itemBackgroundPhoto($poster, 'thumb.icon'); ?>
                    <a href="<?php echo $poster->getHref(); ?>"><?php echo $poster->getTitle(); ?></a>
                  </div>
                </td>
                <td data-label="<?php echo $this->translate("Comment Date") ?>"><?php echo $this->timestamp($item->creation_date) ?></td>
                <td class='admin_table_options _comment_options'>
                  <a class="smoothbox" href='<?php echo $this->url(array('action' => 'read-comment', 'id' => $item->getIdentity(), 'resource_type' => 'core_comment')) ?>'>
                    <?php echo $this->translate("Read Comment") ?>
                  </a>
                  |
									<a target='_blank' href='<?php echo $resource->getHref();?>'>
                    <?php echo $this->translate("View Content") ?>
                  </a>
                  |
                  <a class='smoothbox' href='<?php echo $this->url(array('action' => 'delete', 'id' => $item->getIdentity()));?>'>
                    <?php echo $this->translate("Delete") ?>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
      <div class='buttons'>
        <button type='submit' name="submit_button" value="delete">
          <?php echo $this->translate("Delete Selected") ?>
        </button>
      </div>
    </form>
  </div>

<?php } else { ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no comments yet."); ?>
    </span>
  </div>
<?php } ?>
<script type="text/javascript">
  scriptJquery(``).insertBefore(scriptJquery('#date-date_from').attr("type","text").attr("autocomplete","off").attr("placeholder","From").datepicker({
      timepicker: false,
    })
  );
  scriptJquery(``).insertBefore(scriptJquery('#date-date_to').attr("type","text").attr("autocomplete","off").attr("placeholder","To").datepicker({
    timepicker: false,
   })
  );
</script>
<script type="application/javascript">
  scriptJquery('.core_admin_main_manage').parent().addClass('active');
  scriptJquery('.core_admin_main_manage_comments').addClass('active');
</script>
