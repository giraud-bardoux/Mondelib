<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: IndexController.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Activity_FeelingsController extends Sesapi_Controller_Action_Standard
{
  public function feelingAction() {
    $feeling_id = $this->_getParam('feeling_id',1);
    $page = (int)  $this->_getParam('page', 1);
    $search = $this->_getParam('search','');
    $feeling_type = $this->_getParam('feeling_type',1);
    if($feeling_type == 1)
      $paginator = Engine_Api::_()->getDbTable('feelingicons','activity')->getPaginator(array('feeling_id'=>$feeling_id,'search'=>$search));
    else{
      $table = Engine_Api::_()->getDbTable('feelingicons', 'activity');
      $select = $table->select()->where('feeling_id =?', $feeling_id)->where('type =?', 2);
      $results = $table->fetchAll($select);
      $resource_typeArray = array();
      foreach($results as $result) {
        $resource_typeArray[] = $result->resource_type;
      }
      $searchtable = Engine_Api::_()->getDbTable('search', 'core');
      $select = $searchtable->select()->where('type in(?)', $resource_typeArray)->order('id DESC');
      
      if($search != '')
        $select->where('title LIKE ? OR description LIKE ? OR keywords LIKE ? OR hidden LIKE ?', '%' . $text . '%');
      $paginator = Zend_Paginator::factory($select);
    }
    $paginator->setCurrentPageNumber($page);
    $paginator->setItemCountPerPage($this->_getParam('limit',10));
    $results = array();
    $counter = 0;
    foreach($paginator as $result){
      if($feeling_type == 1){
       $icon = Engine_Api::_()->storage()->get($result->feeling_icon, "")->getPhotoUrl();
       $results[$counter]['feelingicon_id'] = $result->getIdentity();
       $results[$counter]['icon'] = $this->getBaseUrl('',$icon);
       $results[$counter]['resource_type'] = $result->resource_type;
       $results[$counter]['title'] = $result['title'];
      }else{
       $itemType = $result->type;
       if (Engine_Api::_()->hasItemType($itemType)) {
          $item = Engine_Api::_()->getItem($itemType, $result->id);
          $icon = $item->getPhotoUrl();
          $results[$counter]['feelingicon_id'] = $item->getIdentity();
          $results[$counter]['icon'] = $this->getBaseUrl('',$icon);
          $results[$counter]['resource_type'] = $item->getType();
          $results[$counter]['title'] = $result['title'];
       }
      }
       $counter++; 
    }
    $resultFeeling['feelings'] = $results;
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $resultFeeling),$extraParams));      
  }
  public function getFeelingsAction(){
    $page = (int)  $this->_getParam('page', 1);
    $search = $this->_getParam('search','');
    $paginator = Engine_Api::_()->getDbTable('feelings','activity')->getPaginator(array('notin'=>true,'search'=>$search));
    $paginator->setCurrentPageNumber($page);
    $paginator->setItemCountPerPage($this->_getParam('limit',10));
    $results = array();
    $counter = 0;
    
    foreach($paginator as $result){
       $icon = Engine_Api::_()->storage()->get($result->file_id, "")->getPhotoUrl();
       $results[$counter]['feeling_id'] = $result->getIdentity();
       $results[$counter]['feeling_type'] = (Int) $result->type;
       $results[$counter]['icon'] = $this->getBaseUrl('',$icon);
       $results[$counter]['title'] = $result['title'];
       $counter++;
    }
    
    $resultFeeling['feelings'] = $results;
    $extraParams['pagging']['total_page'] = $paginator->getPages()->pageCount;
    $extraParams['pagging']['total'] = $paginator->getTotalItemCount();
    $extraParams['pagging']['current_page'] = $paginator->getCurrentPageNumber();
    $extraParams['pagging']['next_page'] = $extraParams['pagging']['current_page']+1;
    Engine_Api::_()->getApi('response','sesapi')->sendResponse(array_merge(array('error'=>'0','error_message'=>'', 'result' => $resultFeeling),$extraParams));  
  }
}