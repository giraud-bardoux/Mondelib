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

class Core_Widget_AdminRecentActivityController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
  
    //Action type
    $mainActionTypes = array();
    $masterActionTypes = Engine_Api::_()->getDbtable('actionTypes', 'activity')->getActionTypes();
    foreach( $masterActionTypes as $type ) {
      $mainActionTypes[] = $type->type;
    }
    
    $table = Engine_Api::_()->getDbTable('actions', 'activity');
    $select = $table->select()
            ->from($table->info('name'))
            ->order('action_id DESC')
            ->limit(10);
    if(engine_count($mainActionTypes) > 0) {
      $select->where('type IN (?)', $mainActionTypes);
    }
    $this->view->results = $table->fetchAll($select);
  }
}
