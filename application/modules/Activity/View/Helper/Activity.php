<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: Activity.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Activity_View_Helper_Activity extends Zend_View_Helper_Abstract
{
  public function activity(Activity_Model_Action $action = null, array $data = array(), $method = null, $show_all_comments = false)
  {
    if( null === $action )
    {
      return '';
    }
    if(empty($data['enabledModuleNames'])){
      $data['enabledModuleNames'] = Engine_Api::_()->getDbTable('modules', 'core')->getEnabledModuleNames();
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $activity_moderate = Engine_Api::_()->getDbtable('permissions', 'authorization')
        ->getAllowed('user', $viewer, 'activity');
    $form = new Activity_Form_Comment();
    $data = array_merge($data, array(
      'actions' => array($action),
      'commentForm' => $form,
      'user_limit' => Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_userlength'),
      'allow_delete' => Engine_Api::_()->getApi('settings', 'core')->getSetting('activity_userdelete'),
      'activity_moderate' =>$activity_moderate,
      'viewAllComments' => $show_all_comments,
      'ulInclude'=> empty($data['ulInclude']) ? true : false,
      'onlyComment'=> empty($data['onlyComment']) ? true : false,
      'userphotoalign' => !empty($data['userphotoalign']) ? $data['userphotoalign'] : 'left',
      
    ));

//     $enabledModuleNames = Engine_Api::_()->getDbTable('modules', 'core')->getEnabledModuleNames();
//     $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
//     $module = Engine_Api::_()->getDbTable('actionTypes','activity')->getActionType($action->type);
//     $moduleName = $module->module;
// 
//     foreach ($enabledModuleNames as $module){
//       try{
//           $attributionAllowed = Engine_Api::_()->$module()->isFeedAttributionAllowed($viewer,$view,$moduleName,$action);
//           if($attributionAllowed){
//             $data["isPageSubject"] = $attributionAllowed;
//           }
//       }catch(Exception $e){
//         // silence
//       }
//     }

    if($method == 'update'){
       $type = !empty($data['type']) ? $data['type'] : '';
       // If has a page, display oldest to newest
        if( null !== ( @$page = $data['page']) ) {
          $comments = $action->getComments('0',$page,$type);
          $data['comments'] = $comments;
          $data['page'] = $page;
        } else {
          // If not has a page, show the
          $comments = $action->getComments(0,'zero',$type);
          $data['comments'] = $comments;
          $data['page'] = 0;
        }
        // echo "<pre>";var_dump($data);die;
        return $this->view->partial(
        '_activityComments.tpl',
        'comment',
        $data
      );
       
    }else{
      return $this->view->partial(
        '_activityText.tpl',
        'activity',
        $data
        );
      }
    }
}
