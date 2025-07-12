<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _egroup_content.tpl 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
 
?>
<?php $action = $this->action;
if(!$action ||  !$this->viewer()->getIdentity()) return; ?>
<?php $isPageSubject = empty($this->isPageSubject) ? $this->viewer() : $this->isPageSubject; ?>
<?php  
   
    $module = Engine_Api::_()->getDbTable('actionTypes','activity')->getActionType($action->type);
    $moduleName = $module->module;
    if($moduleName != "sesgroup" && $action->object_type != "sesgroup_group"){
      return;
    }
?>
<?php 
      $subjectPage = $this->subject();
      if($subjectPage && empty($this->isPageSubject)){
        if(Engine_Api::_()->getDbTable('grouproles','sesgroup')->toCheckUserGroupRole($this->viewer()->getIdentity(),$subjectPage->getIdentity(),'manage_dashboard','delete')){
          $attributionType = Engine_Api::_()->getDbTable('postattributions','sesgroup')->getGroupPostAttribution(array('group_id' => $subjectPage->getIdentity()));        
          $pageAttributionType = Engine_Api::_()->authorization()->isAllowed('sesgroup_group', $this->viewer(), 'seg_attribution');
          $allowUserChoosePageAttribution = Engine_Api::_()->authorization()->isAllowed('sesgroup_group', $this->viewer(), 'seg_attribution_allowuser');
          if (!$pageAttributionType || $attributionType == 0) {
            $isPageSubject = $this->viewer();
          }
          if($pageAttributionType && !$allowUserChoosePageAttribution) {
            $isPageSubject = $this->viewer();
          }
          if($pageAttributionType && $allowUserChoosePageAttribution && $attributionType == 1) {
             $isPageSubject = $subjectPage;
          }
        }
      }
?>
  <?php $actionIdentity = is_array($action) ? $action->getIdentity() : 0?>
<li class="sesgroup_switcher_cnt custom_switch_val activity_owner_selector activity_owner_selector_c">
  <a href="javascript:;" class="sesgroup_feed_change_option_a _feed_change_option_a _st" data-subject="<?php echo !empty($isPageSubject) ? $isPageSubject->getGuid() : $this->viewer()->getGuid(); ?>" data-actionid="<?php echo $action->getIdentity(); ?>" data-rel="<?php echo $isPageSubject->getGuid(); ?>" data-src="<?php echo $isPageSubject->getPhotoUrl(); ?>">
    <img class="epage_elem_cnt" src="<?php echo $isPageSubject->getPhotoUrl(); ?>" />
    <i class="fa fa-caret-down epage_elem_cnt"></i>
  </a>
  <a href="javascript:;" class="sesgroup_feed_change_option _lin" style="left:0; top:0; height:100%; width:100%; position:absolute;"></a>
</li>
<script type="application/javascript">
en4.core.runonce.add(function() {
    if(typeof changePageCommentUser == "function"){
      changsesgroupCommentUser(<?php echo $actionIdentity ?>);
    }
});
  
</script>
