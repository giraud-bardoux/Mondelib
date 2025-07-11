<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9915 2013-02-15 01:30:19Z alex $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'parentMenuItemName' => 'core_admin_main_manage_verification', 'childMenuItemName' => 'core_admin_main_manage_verificationrequests')); ?>

<h2 class="page_heading"><?php echo $this->translate('Manage Verifications') ?></h2>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>
<h3><?php echo $this->translate("Manage Verification Requests") ?></h3>
<p>
  <?php echo $this->translate("This page displays user verification requests submitted by members on your site. If you need to search for a specific member, enter your search criteria in the fields below. </br />Note: You will receive verification requests only if you have enabled users to request verification for at least one member level on your site.") ?>
</p>
<script type="text/javascript">
  var currentOrder = '<?php echo $this->order ?>';
  var currentOrderDirection = '<?php echo $this->order_direction ?>';
  var changeOrder = function(order, default_direction){
    // Just change direction
    if( order == currentOrder ) {
      scriptJquery('#order_direction').value = ( currentOrderDirection == 'ASC' ? 'DESC' : 'ASC' );
    } else {
      scriptJquery('#order').val(order);
      scriptJquery('#order_direction').val(default_direction);
    }
    scriptJquery('#filter_form').trigger("submit");
  }

function multiModify()
{
  var multimodify_form = scriptJquery('#multimodify_form');
  if (multimodify_form.find("#submit_button").val() == 'delete')
  {
    return confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to delete the selected user accounts?")) ?>');
  }
}

function selectAll(obj) {
  scriptJquery('.checkbox').each(function(){
    scriptJquery(this).prop("checked",scriptJquery(obj).prop("checked"))
  });
}

<?php if( $this->openUser ): ?>
en4.core.runonce.add(function() {
  scriptJquery('#multimodify_form .admin_table_options a').each(function() {
    var el = scriptJquery(this);
    if( -1 < el.attr('href').indexOf('/edit/') ) {
      el.trigger("click");
    }
  });
});
<?php endif ?>
</script>

<div class='admin_search'>
  <?php echo $this->formFilter->render($this) ?>
</div>
<?php if($this->paginator->getTotalItemCount() > 0) { ?>
  <div class='admin_results'>
    <div>
      <?php $count = $this->paginator->getTotalItemCount() ?>
      <?php echo $this->translate(array("%s verification request found.", "%s verification requests found.", $count),
          $this->locale()->toNumber($count)) ?>
    </div>
    <div>
      <?php echo $this->paginationControl($this->paginator, null, null, array(
        'pageAsQuery' => true,
        'query' => $this->formValues,
        //'params' => $this->formValues,
      )); ?>
    </div>
  </div>
  <br />
  <div class="admin_table_form ">
  <form id='multimodify_form' method="post" action="<?php echo $this->url(array('action'=>'multi-modify'));?>" onSubmit="multiModify()">
    <table class='admin_table admin_responsive_table'>
      <thead>
        <tr>
          <!--<th style='width: 1%;'><input onclick="selectAll(this)" type='checkbox' class='checkbox'></th>-->
          <th style='width: 1%;'><?php echo $this->translate("ID") ?></th>
          <th><?php echo $this->translate("Display Name") ?></th>
          <th><?php echo $this->translate("Username") ?></th>
          <th ><?php echo $this->translate("Email") ?></th>
          <th class='admin_table_centered'><?php echo $this->translate("User Level") ?></th>
          <th class='admin_table_centered'><?php echo $this->translate("Approved") ?></th>
          <th class='admin_table_centered'><?php echo $this->translate("Verified") ?></th>
          <th><?php echo $this->translate("Request Date") ?></th>
          <th class='admin_table_options'><?php echo $this->translate("Options") ?></th>
        </tr>
      </thead>
      <tbody>
        <?php if( engine_count($this->paginator) ): ?>
          <?php foreach( $this->paginator as $item ):
            $user = $this->item('user', $item->user_id);
            ?>
            <tr>
              <!--<td><input <?php //if ($item->level_id == 1) echo 'disabled';?> name='modify_<?php //echo $item->verificationrequest_id;?>' value='<?php //echo $item->verificationrequest_id;?>' type='checkbox' class='checkbox'></td>-->
              <td  data-label="ID"><?php echo $item->user_id ?></td>
              <td data-label="<?php echo $this->translate("Display Name") ?>" class='admin_table_bold'>
                <?php echo $this->htmlLink($user->getHref(), $user->getTitle(), array('target' => '_blank'))?>
              </td>
              <td data-label="<?php echo $this->translate("Username") ?>" class='admin_table_user'><?php echo $this->htmlLink($this->item('user', $item->user_id)->getHref(), $this->item('user', $item->user_id)->username, array('target' => '_blank')) ?></td>
              <td data-label="<?php echo $this->translate("Email") ?>" class='admin_table_email'>
                <?php if( !$this->hideEmails ): ?>
                  <a href='mailto:<?php echo $item->email ?>'><?php echo $item->email ?></a>
                <?php else: ?>
                  (hidden)
                <?php endif; ?>
              </td>
              <td data-label="<?php echo $this->translate("User Level") ?>" class="admin_table_centered nowrap">
                <a href="<?php echo $this->url(array('module'=>'authorization','controller'=>'level', 'action' => 'edit', 'id' => $item->level_id)) ?>">
                  <?php echo $this->translate(Engine_Api::_()->getItem('authorization_level', $item->level_id)->getTitle()) ?>
                </a>
              </td>
              <td data-label="<?php echo $this->translate("Approved") ?>" class='admin_table_centered'>
                <?php echo ( $item->enabled ? $this->translate('Yes') : $this->translate('No') ) ?>
              </td>
              <td data-label="<?php echo $this->translate("Verified") ?>" class='admin_table_centered'>
                <?php echo ( $item->is_verified ? $this->translate('Yes') : $this->translate('No') ) ?>
              </td>
              <td data-label="<?php echo $this->translate("Date") ?>" class="nowrap">
                <?php echo $this->locale()->toDateTime($item->creation_date) ?>
              </td>
              <td class='admin_table_options'>
                <?php if(!empty($item->message)) { ?>
                  <a class='smoothbox' href='<?php echo $this->url(array('action' => 'view-verification-request', 'id' => $item->verificationrequest_id));?>'><?php echo $this->translate("View Details") ?></a>
                <?php } ?>
                |
                <a class='smoothbox' href='<?php echo $this->url(array('action' => 'approve-verification-request', 'id' => $item->verificationrequest_id));?>'><?php echo $this->translate("Approve") ?></a>
                |
                <a class='smoothbox' href='<?php echo $this->url(array('action' => 'reject-verification-request', 'id' => $item->verificationrequest_id));?>'><?php echo $this->translate("Reject") ?></a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
    <br />
<!--    <div class='buttons'>
      <button type='submit' name="submit_button" value="delete"><?php //echo $this->translate("Delete Selected") ?></button>
    </div>-->
  </form>
  </div>
<?php } else { ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("Currently, there are no verification requests.") ?>
    </span>
  </div>
<?php } ?>
<script type="application/javascript">
  scriptJquery('.core_admin_main_manage').parent().addClass('active');
  scriptJquery('.core_admin_main_manage_verification').addClass('active');
</script>
