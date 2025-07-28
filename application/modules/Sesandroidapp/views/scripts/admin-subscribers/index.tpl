<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesandroidapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: index.tpl 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>
<?php include APPLICATION_PATH .  '/application/modules/Sesandroidapp/views/scripts/dismiss_message.tpl';?>
<h2 class="page_heading">
  <?php echo $this->translate('Native Android Mobile App'); ?>
</h2>
<?php if( engine_count($this->navigation)): ?>
  <div class='tabs'> <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?> </div>
<?php endif; ?>
<h3>Manage Mobile App Subscribers</h3>
<p>This page lists all the subscribers of your Android mobile app. You can also revoke users to suspend the current session of the app on their Phones or Tablets. After revoking, they will have to restart the app. This is normally done if you forcely want your users to restart their app in case you have done any changes to the splash screen or the welcome screen.<br>
You can also delete  a user as per your requirements. 
Entering criteria into the filter fields will help you find specific subscriber. Leaving the filter fields blank will show all the subscribers on your social network.
</p>
<div class='admin_search sesandroidapp_search_form'>
  <?php echo $this->form->render($this) ?>
</div>
<?php $counter = $this->paginator->getTotalItemCount(); ?> 
<?php if(is_countable($this->paginator) &&  engine_count($this->paginator)): ?>
  <div class="sesandroidapp_search_result">
    <?php echo $this->translate(array('%s token found.', '%s tokens found.', $counter), $this->locale()->toNumber($counter)) ?>
  </div>
<form id="multidelete_form" action="<?php echo $this->url();?>" onSubmit="return multiDelete()" method="POST">
  <table class='admin_table'>
    <thead>
      <tr>
        <th class='admin_table_short'>Token ID</th>
        <th><?php echo $this->translate('User ID') ?></th>
        <th><?php echo $this->translate('User') ?></th>
        <th class="admin_table_centered"><?php echo $this->translate('Email') ?></th>
        <th class="admin_table_centered"><?php echo $this->translate('Revoke') ?></th>
        <th class="admin_table_centered"><?php echo $this->translate('Session') ?></th>
        <th class="admin_table_centered"><?php echo $this->translate('Creation Date') ?></th>
        <th><?php echo $this->translate('Options') ?></th>
      </tr>
    </thead>
    <tbody>
        <?php foreach ($this->paginator as $item):
            $user = Engine_Api::_()->getItem('user',$item->user_id);
            if(!$user->getIdentity())
              continue;
         ?>
          <tr>
            <td class="admin_table_centered"><?php echo $item->aouthtoken_id; ?></td>
            <td class="admin_table_centered"><?php echo $item->user_id; ?></td>
            <td><?php echo $this->htmlLink($user->getHref(), $user->getTitle()); ?></td> 
            <td><a href="mailto:<?php echo _ENGINE_ADMIN_NEUTER ? "(hidden)" : $user->email; ?>"><?php echo _ENGINE_ADMIN_NEUTER ? "(hidden)" : $user->email; ?></a></td>
            <td class="admin_table_centered"><?php echo $item->revoked == 1 ?   $this->htmlLink(
                array('route' => 'default', 'module' => 'sesandroidapp', 'controller' => 'admin-subscribers', 'action' => 'revoked', 'id' => $item->aouthtoken_id),$this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesandroidapp/externals/images/admin/error.png', '', array('title'=> $this->translate('Click to grant access'))),array('class'=>'openRevoked')) : $this->htmlLink(
                array('route' => 'default', 'module' => 'sesandroidapp', 'controller' => 'admin-subscribers', 'action' => 'revoked', 'id' => $item->aouthtoken_id),$this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesandroidapp/externals/images/admin/check.png', '', array('title'=> $this->translate('Click to revoke access'))),array('class'=>'openRevoked')) ; ?></td>
            <td class="admin_table_centered"><?php echo $item->sessions; ?></td>
            <td class="admin_table_centered"><?php echo $item->creation_date; ?></td>
            <td>
              <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'sesandroidapp', 'controller' => 'admin-subscribers', 'action' => 'delete', 'id' => $item->aouthtoken_id), $this->translate("Delete"), array('class' => 'smoothbox')) ?>
            </td>
          </tr>
        <?php endforeach; ?>
    </tbody>
  </table>
</form>
<div>
  <?php echo $this->paginationControl($this->paginator); ?>
</div>
<?php else: ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("No token found.") ?>
    </span>
  </div>
<?php endif; ?>
<script type="application/javascript">
scriptJquery('.openRevoked').click(function(e){
  e.preventDefault();  
  Smoothbox.open(scriptJquery(this).attr('href'));
  parent.Smoothbox.close;
	return false;
})
</script>
