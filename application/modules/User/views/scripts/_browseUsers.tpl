<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _browseUsers.tpl 9979 2013-03-19 22:07:33Z john $
 * @author     John
 */
?>
<?php //if ($this->isAjaxSearch): ?>
<h3>
  <?php echo $this->translate(array('%s member found.', '%s members found.', $this->totalUsers),$this->locale()->toNumber($this->totalUsers)) ?>
</h3>
<?php //endif; ?>
<?php $viewer = Engine_Api::_()->user()->getViewer();?>

<?php if( engine_count($this->users) ): ?>
  <?php
    $ulClass = '';
    $excludedLevels = array(1, 2, 3);
    $isAdmin = false;

    if( !$this->viewer()->getIdentity() ) {
      $ulClass = 'public_user';
    } else {
      $viewerId = $viewer->getIdentity();
      if( engine_in_array($viewer->level_id, $excludedLevels) ) {
        $isAdmin = true;
      } else {
        $registeredPrivacy = array('everyone', 'registered');
        $friendsIds = $viewer->membership()->getMembersIds();
      }
    }

?>
  <div class="row browse_mambers">
    <?php foreach( $this->users as $user ): ?>
      <div class="col-lg-3 col-md-6 browse_mambers_list_item">
        <article>
        <?php
          $showPhoto = false;
          $viewPrivacy = $user->view_privacy;

          if( !isset($viewerId) ) {
            if( $viewPrivacy == 'everyone' ) {
              $showPhoto = true;
            }
          } elseif( $isAdmin
            || $viewerId == $user->getIdentity()
            || engine_in_array($viewPrivacy, $registeredPrivacy)
            || ($viewPrivacy == 'member' && engine_in_array($user->getIdentity(), $friendsIds))) {
            $showPhoto = true;
          } elseif($viewPrivacy == 'network' ) {
            $netMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
            $viewerNetwork = $netMembershipTable->getMembershipsOfIds($viewer);
            $userNetwork = $netMembershipTable->getMembershipsOfIds($user);

            if( engine_in_array($user->getIdentity(), $friendsIds)
              || !empty(array_intersect($userNetwork, $viewerNetwork)) ) {
              $showPhoto = true;
            }
          }

          if( $showPhoto ){
            $profileImg = $this->itemBackgroundPhoto($user, 'thumb.profile');
          } else {
            $profileImg = '<span class="bg_item_photo bg_thumb_profile bg_item_photo_user bg_item_nophoto bg_item_nophoto_private"></span>';
          }
        ?>
          <div class="browse_mambers_list_item_thumb">
            <?php echo $this->htmlLink($user->getHref(), $profileImg) ?>
          </div>
          <div class='browse_mambers_list_item_info'>
            <div class="browse_mambers_list_item_name"><?php echo $this->htmlLink($user->getHref(), $user->getTitle(), array('class' => 'font_color')) ?></div>
            <?php if(0 && $user->status != "" ): ?>
              <div class="font_small">
                <?php echo $user->status; ?>
              </div>
              <div class="font_small font_color_light">
                <?php echo $this->timestamp($user->status_date) ?>
              </div>
            <?php endif; ?>
            <?php if( isset($viewerId) && $viewerId != $user->getIdentity() ): ?>
              <div class='browse_mambers_list_item_links'>
                <div class="_friend"><?php echo $this->userFriendship($user, $this->viewer(), 'icon') ?></div>
                <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.followenable',1)  && !Engine_Api::_()->user()->getViewer()->isSelf($user)) { ?>
                  <div class="_follow"><?php echo $this->partial('_followmembers.tpl', 'user', array('subject' => $user, 'iconType' => 'icon')); ?> </div>
                <?php } ?>
                <?php if(Engine_Api::_()->authorization()->getPermission($user, 'user', 'canblock') && Engine_Api::_()->authorization()->getPermission($viewer, 'user', 'block') && !engine_in_array($user->getIdentity(), $this->blockedUserIds) ) :?>
                  <div class="_block">
                    <?php echo '<a href ="'. $this->url(array(
                    'controller' => 'block',
                    'action' => 'add',
                    'user_id' => $user->getIdentity()
                    ),'user_extended',true)
                    . '" class = "btn btn-alt smoothbox" data-bs-toggle="tooltip" title="'. $this->translate("Block Member") . '"><i class="icon_user_block"></i></a>'; ?>
                  </div>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
       </article>
     </div>
    <?php endforeach; ?>
  </div>
<?php endif ?>

<?php if( $this->users ):
    $pagination = $this->paginationControl($this->users, null, null, array(
      'pageAsQuery' => true,
      'query' => $this->formValues,
    ));
  ?>
  <?php if( trim($pagination) ): ?>
    <div class='browsemembers_viewmore' id="browsemembers_viewmore">
      <?php echo $pagination ?>
    </div>
  <?php endif ?>
<?php endif; ?>

<script type="text/javascript">
  page = '<?php echo sprintf('%d', $this->page) ?>';
  totalUsers = '<?php echo sprintf('%d', $this->totalUsers) ?>';
  userCount = '<?php echo sprintf('%d', $this->userCount) ?>';
</script>
