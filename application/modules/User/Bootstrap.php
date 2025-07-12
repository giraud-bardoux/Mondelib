<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Bootstrap.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Bootstrap extends Engine_Application_Bootstrap_Abstract
{
  public function __construct($application)
  {
    parent::__construct($application);
    
    // Add view helper and action helper paths
    $this->initViewHelperPath();
    $this->initActionHelperPath();
		
    // Get viewer
    $viewer = Engine_Api::_()->user()->getViewer();
		if($viewer->getIdentity() && $viewer->level_id != 1 && !empty($_FILES)) {
			foreach($_FILES as $key => $files) {
				if(is_array($files['name'])) {
					foreach($files['name'] as $key => $file) {
						$this->isValidUpload($file);
					}
				} else {
					$this->isValidUpload($files['name']);
				}
			}
		}
		
    // Check if they were disabled
    if( $viewer->getIdentity() && !$viewer->enabled ) {
      Engine_Api::_()->user()->getAuth()->clearIdentity();
      Engine_Api::_()->user()->setViewer(null);
    }

    // Check user online state
    $table = Engine_Api::_()->getDbtable('online', 'user');
    $table->check($viewer);

		$front = Zend_Controller_Front::getInstance();
		$front->registerPlugin(new User_Plugin_Core);
  }
  
	function isValidUpload($file) {
		$extension = strtolower(ltrim(strrchr($file, '.'), '.'));
		if(in_array($extension, array('php', 'js'))) {
			if(empty($_GET['restApi'])) {
				echo json_encode(array('status' => false, 'message' => 'Invalid upload request.'));
				exit();
			} else {
				echo json_encode(array('result' => array("loggedin_user_id" => Engine_Api::_()->user()->getViewer()->getIdentity()), 'error' => true, 'message' => 'Invalid upload request.', 'error_message' => 'Invalid upload request.', 'session_id' => session_id()));
				exit();
			}
		}
	}
}
