<?php
/**
 * SocialEngine
 *
 * @category   Application_User
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
?>
<?php
  $subject = $this->subject ? $this->subject : $subject;
  
  $iconType = $this->iconType ? $this->iconType : ' ';

  $table = Engine_Api::_()->getDbtable('block', 'user');
  $viewer = Engine_Api::_()->user()->getViewer();
  $select = $table->select()->where('user_id = ?', $subject->getIdentity())->where('blocked_user_id = ?', $viewer->getIdentity())->limit(1);
  $row = $table->fetchRow($select); 
?>
<?php if ($row == NULL): ?>
  <?php if ($this->viewer()->getIdentity()): ?>
    <?php 
      if (null === $viewer) {
        $viewer = Engine_Api::_()->user()->getViewer();
      }

      if (!$viewer || !$viewer->getIdentity() || $subject->isSelf($viewer)) {
        return '';
      }

      $direction = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction', 1);

      // Get data
      if (!$direction) {
        $row = $subject->membership()->getRow($viewer);
      } else {
        $row = $viewer->membership()->getRow($subject);
      }
      // Check if friendship is allowed in the network
      $eligible = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.eligible', 2);
      if ($eligible == 0) {
        return '';
      }

      // check admin level setting if you can befriend people in your network
      else if ($eligible == 1) {

        $networkMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
        $networkMembershipName = $networkMembershipTable->info('name');

        $select = new Zend_Db_Select($networkMembershipTable->getAdapter());
        $select
          ->from($networkMembershipName, 'user_id')
          ->join($networkMembershipName, "`{$networkMembershipName}`.`resource_id`=`{$networkMembershipName}_2`.resource_id", null)
          ->where("`{$networkMembershipName}`.user_id = ?", $viewer->getIdentity())
          ->where("`{$networkMembershipName}_2`.user_id = ?", $subject->getIdentity())
        ;

        $data = $select->query()->fetch();

        if (empty($data)) {
          return '';
        }
      }

      // Two-way mode
      if(!$row->resource_id && !$row->user_id) {
        echo "<a href='javascript:void(0);' class='btn btn-alt user_add_btn user_addfriend_request' data-icontype='".$iconType ."' data-src = '".$subject->user_id."'><i class='fa fa-user-plus'></i><span>".$this->translate('Add Friend')."</span></a>";
      } else if($row && $row->user_approved == 0 ) {
        echo "<a href='javascript:void(0);' class='btn btn-alt user_cancel_request_btn user_cancelfriend_request' data-icontype='".$iconType ."' data-src = '".$subject->user_id."'><i class='fa fa-user-times'></i><span>".$this->translate('Cancel Friend Request')."</span></a>";
      } else if($row && $row->resource_approved == 0 ) {
        echo "<a href='javascript:void(0);' class='btn btn-alt user_actapt_request_btn user_acceptfriend_request' data-icontype='".$iconType ."' data-src = '".$subject->user_id."'><i class='fa fa-user-plus'></i><span>".$this->translate('Accept Friend Request')."</span></a>";
      }
      else if($row && $row->active ) {
        echo "<a href='javascript:void(0);' class='btn btn-alt user_remove_friend_btn user_removefriend_request' data-icontype='".$iconType ."' data-src = '".$subject->user_id."'><i class='fa fa-user-times'></i><span>".$this->translate('Remove Friend')."</span></a>";
      }
    ?>
  <?php endif; ?>
<?php endif; ?>