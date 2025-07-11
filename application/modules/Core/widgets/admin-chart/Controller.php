<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_Widget_AdminChartController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
  
    $table = Engine_Api::_()->getDbTable('statistics', 'core');
    $actionTable = Engine_Api::_()->getDbTable('actions', 'activity');
    
    $currentTime = date('Y-m-d H:i:s');
    $endTime = date('Y-m-d H:i:s', strtotime("-1 week"));
    
    $actionsSelect = $actionTable->select()
        ->from($actionTable->info('name'), array(new Zend_Db_Expr('"actions" AS type'), 'count(action_id) as total', 'date', 'DATE_FORMAT(date,"%Y-%m-%d") as hourtime'))
        ->where("(" . $actionTable->info('name') . ".date) between ('$endTime') and ('$currentTime')")
        ->group("DATE_FORMAT(date,'%Y-%m-%d')");
    
    $viewSelect = $table->select()
        ->from($table->info('name'), array(new Zend_Db_Expr('"views" AS type'), 'SUM(value) as total', 'date', 'DATE_FORMAT(date,"%Y-%m-%d") as hourtime'))
        ->where('type = ?', 'core.views')
        ->where("(" . $table->info('name') . ".date) between ('$endTime') and ('$currentTime')")
        ->group("DATE_FORMAT(date,'%Y-%m-%d')");
        
    $commentSelect = $table->select()
        ->from($table->info('name'), array(new Zend_Db_Expr('"comments" AS type'), 'SUM(value) as total', 'date', 'DATE_FORMAT(date,"%Y-%m-%d") as hourtime'))
        ->where('type = ?', 'core.comments')
        ->where("(" . $table->info('name') . ".date) between ('$endTime') and ('$currentTime')")
        ->group("DATE_FORMAT(date,'%Y-%m-%d')");
        
    $signupSelect = $table->select()
        ->from($table->info('name'), array(new Zend_Db_Expr('"signup" AS type'), 'SUM(value) as total', 'date', 'DATE_FORMAT(date,"%Y-%m-%d") as hourtime'))
        ->where('type = ?', 'user.creations')
        ->where("(" . $table->info('name') . ".date) between ('$endTime') and ('$currentTime')")
        ->group("DATE_FORMAT(date,'%Y-%m-%d')");
    
    $dataSelect = $viewSelect . ' ' . 'UNION' . ' ' . $commentSelect . 'UNION' . ' ' . $signupSelect . ' ' . 'UNION' . ' ' . $actionsSelect;

    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
    $results = $db->query($dataSelect)->fetchAll();
    
    $array1 = $array2 = $array3 = $array4 = array();
    foreach ($results as $result) {
      if ($result['type'] == 'actions')
        $array1[$result['hourtime']] = $result['total'];
      elseif ($result['type'] == 'views')
        $array2[$result['hourtime']] = $result['total'];
      elseif ($result['type'] == 'comments')
        $array4[$result['hourtime']] = $result['total'];
      elseif ($result['type'] == 'signup')
        $array3[$result['hourtime']] = $result['total'];
    }
    
    $dateArray = $this->date_range( date('Y-m-d', strtotime("-7 day")), date('Y-m-d'));
    
    $actionsArray = $viewArray = $signupArray = $commentArray = array();
    
    foreach($dateArray as $date) {
    
      if(!empty($array1[$date]))
        $actionsArray[$date] = $array1[$date];
      else 
      $actionsArray[$date] = 0;
      
      if(!empty($array2[$date]))
        $viewArray[$date] = $array2[$date];
      else 
      $viewArray[$date] = 0;
      
      if(!empty($array3[$date]))
        $signupArray[$date] = $array3[$date];
      else 
      $signupArray[$date] = 0;
      
      if(!empty($array4[$date]))
        $commentArray[$date] = $array4[$date];
      else 
      $commentArray[$date] = 0;
    }
    
    $this->view->dateArray = json_encode(array_keys($viewArray));
    $this->view->actionsData = json_encode(array_values($actionsArray));
    $this->view->viewsData = json_encode(array_values($viewArray));
    $this->view->commentsData = json_encode(array_values($commentArray));
    $this->view->signupData = json_encode(array_values($signupArray));
  }
  
  function date_range($first, $last, $step = '+1 day', $output_format = 'Y-m-d' ) {
    $dates = array();
    $current = strtotime($first);
    $last = strtotime($last);

    while( $current <= $last ) {

        $dates[] = date($output_format, $current);
        $current = strtotime($step, $current);
    }
    return $dates;
  }
}
