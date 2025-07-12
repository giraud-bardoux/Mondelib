<?php ?>

<?php if ($this->viewer->getIdentity()) { ?>
  <div class="user_cover_buttons">
    <ul>
      <?php
      $label = $this->translate("Edit My Profile");
      if (!$this->viewer->isSelf($this->subject)) {
        $label = $this->translate("Edit User Profile");
      }
      $auth = $this->subject->isSuperAdmin() ? $this->viewer->isSuperAdmin($this->subject) : 1;
      if ($this->subject->authorization()->isAllowed($this->viewer, 'edit') && $auth) {
      ?>
        <li>
          <a  class="btn btn-primary" href="<?php echo $this->url(array('controller' => 'edit', 'action' => 'profile', 'id' => ($this->viewer->getGuid(false) == $this->subject->getGuid(false) ? null : $this->subject->getIdentity())), 'user_extended', true)  ?>">
            <i class="fas fa-user-edit"></i>
            <span> <?php echo $this->translate($label); ?> </span>
          </a>
        </li>
      <?php } ?>
      <?php
      $messageAllowed = true;
      if (!$this->viewer->getIdentity() || $this->viewer->getGuid(false) === $this->subject->getGuid(false)) {
        $messageAllowed = false;
      } else {
        // Get setting?
        $this->viewerPermission = Engine_Api::_()->authorization()->getPermission($this->viewer->level_id, 'messages', 'create');
        if (Authorization_Api_Core::LEVEL_DISALLOW === $this->viewerPermission) {
          $messageAllowed = false;
        } else {
          $this->subjectPermission = Engine_Api::_()->authorization()->getPermission($this->subject->level_id, 'messages', 'create');
          if (Authorization_Api_Core::LEVEL_DISALLOW === $this->subjectPermission) {
            $messageAllowed = false;
          } else {
            $messageAuth = Engine_Api::_()->authorization()->getPermission($this->viewer->level_id, 'messages', 'auth');
            if ($messageAuth == 'none') {
              $messageAllowed = false;
            } else if ($messageAuth == 'friends') {
              $friendship_status = $this->subject->membership()->getRow($this->viewer);
              if (!$friendship_status || $friendship_status->active == 0) {
                $messageAllowed = false;
              }
            }
          }
        }
      }
      if ($messageAllowed) {
        if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('echat')) { ?>
          <li>
            <a class="btn btn-primary echat_profile_echatmessages send_message_<?php echo $this->subject->getIdentity(); ?>" href="javascript:;">
              <i class="fas fa-comment-dots"></i>
              <span> <?php echo $this->translate('Send Message'); ?> </span>
            </a>
          </li>
      <?php }else{
      ?>
        <li>
          <a class="btn btn-primary smoothbox" href="<?php echo $this->url(array('action' => 'compose', 'to' => $this->subject->getIdentity(), 'format' => 'smoothbox'), 'messages_general', true) ?>">
            <i class="fas fa-comment-dots"></i>
            <span> <?php echo $this->translate('Send Message'); ?> </span>
          </a>
        </li>
      <?php }
      }
      ?>

      <?php
      $allowFriendhship = true;
      if (!$this->viewer->getIdentity() || $this->viewer->getGuid(false) === $this->subject->getGuid(false)) {
        $allowFriendhship = false;
      } else {
        // No blocked
        if ($this->viewer->isBlockedBy($this->subject)) {
          $allowFriendhship = false;
        }
        // Check if friendship is allowed in the network
        $eligible = (int) $settings->getSetting('user.friends.eligible', 2);
        if (!$eligible) {
          $allowFriendhship = false;
        }
        // check admin level setting if you can befriend people in your network
        else if ($eligible == 1) {
          $networkMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
          $networkMembershipName = $networkMembershipTable->info('name');
          $select = new Zend_Db_Select($networkMembershipTable->getAdapter());
          $select
            ->from($networkMembershipName, 'user_id')
            ->join($networkMembershipName, "`{$networkMembershipName}`.`resource_id`=`{$networkMembershipName}_2`.resource_id", null)
            ->where("`{$networkMembershipName}`.user_id = ?", $this->viewer->getIdentity())
            ->where("`{$networkMembershipName}_2`.user_id = ?", $this->subject->getIdentity());
          $data = $select->query()->fetch();
          if (empty($data)) {
            $allowFriendhship = false;
          }
        }
        $friendsParams = array();
        if ($allowFriendhship) {
          // Two-way mode
          $row = $this->viewer->membership()->getRow($this->subject);
          if (null === $row) {
            // Add
            $friendsParams[] = array(
              'label' => 'Add Friend',
              'class' => 'btn btn-primary smoothbox icon_friend_add',
              'route' => 'user_extended',
              'params' => array(
                'controller' => 'friends',
                'action' => 'add',
                'user_id' => $this->subject->getIdentity()
              ),
            );
          } else if ($row->user_approved == 0) {
            // Cancel request
            $friendsParams[] = array(
              'label' => 'Cancel Friend Request',
              'class' => 'btn btn-alt smoothbox icon_friend_remove',
              'route' => 'user_extended',
              'params' => array(
                'controller' => 'friends',
                'action' => 'cancel',
                'user_id' => $this->subject->getIdentity()
              ),
            );
          } else if ($row->resource_approved == 0) {
            // Approve request
            $friendsParams[] = array(
              'label' => 'Approve Friend Request',
              'class' => 'btn btn-primary smoothbox icon_friend_add',
              'route' => 'user_extended',
              'params' => array(
                'controller' => 'friends',
                'action' => 'confirm',
                'user_id' => $this->subject->getIdentity()
              ),
            );
          } else {
            // Remove friend
            $friendsParams[] = array(
              'label' => 'Remove Friend',
              'class' => 'btn btn-alt smoothbox icon_friend_remove',
              'route' => 'user_extended',
              'params' => array(
                'controller' => 'friends',
                'action' => 'remove',
                'user_id' => $this->subject->getIdentity()
              ),
            );
          }
        }
      }

      if (@$friendsParams && engine_count(@$friendsParams)) {
      ?>
        <?php foreach ($friendsParams as $params) { ?>
          <li>
            <a href="<?php echo $this->url($params["params"], $params["route"], true) ?>" class="<?php echo $params['class']; ?>">
              <span> <?php echo $this->translate($params["label"]); ?> </span>
            </a>
          </li>
        <?php } ?>
      <?php } ?>
      <?php if ($settings->getSetting('core.followenable', 1)  && !Engine_Api::_()->user()->getViewer()->isSelf($this->subject)) { ?>
        <?php $getFollowUserStatus = Engine_Api::_()->getDbTable('follows', 'user')->getFollowUserStatus($this->subject->user_id); ?>
        <li>
          <?php echo $this->partial('_followmembers.tpl', 'user', array('subject' => $this->subject)); ?>
        </li>
      <?php } ?>
      <li class="dropdown">
        <a href="javascript:void(0)" class="btn btn-alt" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="icon_option_menu"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <?php foreach ($this->userNavigation as $link) : ?>
            <li>
              <a class="<?php echo  'dropdown-item' . ($link->getClass() ? ' ' . $link->getClass() : '') . (!empty($link->get('icon')) ? $link->get('icon') : ''); ?>" href='<?php echo $link->getHref() ?>' aria-label="<?php echo $this->translate($link->getlabel()) ?>">
                <?php echo $this->translate($link->getlabel()) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </li>
    </ul>
  </div>
<?php } ?>
<?php if (0 && $this->auth) : ?>
  <!-- <div class="user_cover_status" id="user_profile_status_container">
    <?php //$status = Engine_Text_Emoji::decode($this->subject()->status); ?>
    <?php //echo $this->viewMore($this->getHelper('getActionContent')->smileyToEmoticons($status)) ?>
    <?php //if (!empty($this->subject()->status) && $this->subject()->isSelf($this->viewer)) : ?>
      <a class="profile_status_clear" href="javascript:void(0);" onclick="en4.user.clearStatus();">(<?php //echo $this->translate('clear') ?>)</a>
    <?php //endif; ?>
  </div> -->
<?php endif; ?>

<?php if ($settings->getSetting('core.followenable', 1) && Engine_Api::_()->user()->getViewer()->getIdentity()  && !Engine_Api::_()->user()->getViewer()->isSelf($this->subject)) { ?>
  <?php $getFollowUserStatus = Engine_Api::_()->getDbTable('follows', 'user')->getFollowUserStatus($this->subject->user_id); ?>
  <?php
  $follow_verification = false;
  if (!empty($settings->getSetting('core.allowuserverfication', 0)) && $this->viewer->follow_verification) {
    $follow_verification = true;
  } elseif (empty($settings->getSetting('core.allowuserverfication', 0)) && empty($settings->getSetting('core.autofollow', 0))) {
    $follow_verification = true;
  }
  ?>
  <?php if ($follow_verification && $getFollowUserStatus && $this->viewer->getIdentity() == $getFollowUserStatus->user_id && $getFollowUserStatus->user_approved == 0) { ?>
    <div class="user_cover_follow_request" id="coverphoto_follow_inner">
      <ul class="user_cover_follow_request_buttons">
        <li class="coverphoto_follow_text"><?php echo $this->translate("%s wants to follow you.", $this->subject->getTitle()); ?></li>
        <li>
          <a id="user_follow_accept_<?php echo $getFollowUserStatus->follow_id; ?>" href='javascript:;' data-action="accept" data-follow_id="<?php echo $getFollowUserStatus->follow_id; ?>" data-url='<?php echo $this->subject->getIdentity(); ?>' class='btn btn-success user_follow follow_accept_btn user_follow_<?php echo $this->subject->getIdentity(); ?>'>
            <i class='fa fa-check'></i>
            <span><?php echo $this->translate('Confirm'); ?></span>
          </a>
        </li>
        <li>
          <a id="user_follow_reject_<?php echo $getFollowUserStatus->follow_id; ?>" href='javascript:;' data-action="reject" data-follow_id="<?php echo $getFollowUserStatus->follow_id; ?>" data-url='<?php echo $this->subject->getIdentity(); ?>' class='btn btn-danger user_follow follow_reject_btn user_follow_<?php echo $this->subject->getIdentity(); ?>'>
            <i class='fa fa-times'></i>
            <span><?php echo $this->translate('Delete'); ?></span>
          </a>
        </li>
      </ul>
    </div>
  <?php } ?>
<?php } ?>
