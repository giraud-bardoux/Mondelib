<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: AdminSettingsController.php 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Harmony_AdminSettingsController extends Core_Controller_Action_Admin {

  public function indexAction() {
  
    $db = Engine_Db_Table::getDefaultAdapter();
  
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('harmony_admin_main', array(), 'harmony_admin_main_settings');

    $this->view->form = $form = new Harmony_Form_Admin_Settings_Global();

    if ($this->getRequest()->isPost() && $form->isValid($this->_getAllParams())) {
    
      $values = $form->getValues();
      
      $changelanding = Engine_Api::_()->getApi('settings', 'core')->getSetting('harmony.changelanding', 0);
      if (isset($values['harmony_changelanding']) && !empty($values['harmony_changelanding']) && $changelanding != $values['harmony_changelanding']) {
        $this->landingpageSet($values['harmony_changelanding']);
      }
      
      if (isset($values['harmony_headernonloggedinoptions']))
        $values['harmony_headernonloggedinoptions'] = serialize($values['harmony_headernonloggedinoptions']);
      else
        $values['harmony_headernonloggedinoptions'] = serialize(array());

      if (isset($values['harmony_headerloggedinoptions']))
        $values['harmony_headerloggedinoptions'] = serialize($values['harmony_headerloggedinoptions']);
      else
        $values['harmony_headerloggedinoptions'] = serialize(array());

      foreach ($values as $key => $value) {
        Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
      }

      //Save constant
      $themeConstants = Engine_Api::_()->harmony()->themeConstants();
      Engine_Api::_()->core()->saveThemeVariables($themeConstants, $form, 'harmony');

      $form->addNotice('Your changes have been saved.');
    }
  }
  
  public function footerAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('harmony_admin_main', array(), 'harmony_admin_main_footer');

    $this->view->form = $form = new Harmony_Form_Admin_Settings_Footer();

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();
      foreach ($values as $key => $value) {
        Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
      }
      $form->addNotice('Your changes have been saved.');
    }
  }

  public function stylingAction() {
  
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('harmony_admin_main', array(), 'harmony_admin_main_styling');

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->view->customtheme_id = $this->_getParam('customtheme_id', 1);

    $this->view->form = $form = new Harmony_Form_Admin_Settings_Styling();

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

      $db = Engine_Db_Table::getDefaultAdapter();
      
      $values = $form->getValues();
      unset($values['header_settings']);
      unset($values['footer_settings']);
      unset($values['body_settings']);
      unset($values['custom_themes']);
      $theme_id = $values['theme_color'];

      if (isset($_POST['save'])) {
        $themeConstants = Engine_Api::_()->harmony()->themeConstants($values);
        Engine_Api::_()->core()->saveThemeVariables($themeConstants, $form, 'harmony');
        Engine_Api::_()->getApi('settings', 'core')->setSetting('harmony.theme.color', $theme_id);
        Engine_Api::_()->getApi('settings', 'core')->setSetting('contrast.mode', $values['contrast_mode']);
      }
      
      foreach ($values as $key => $value) {
        if ((isset($_POST['submit']) || isset($_POST['save'])) && $values['theme_color'] > '3') {
          foreach($values as $key => $value) {
            $db->query("UPDATE `engine4_harmony_customthemes` SET `value` = '".$value."' WHERE `engine4_harmony_customthemes`.`theme_id` = '".$theme_id."' AND  `engine4_harmony_customthemes`.`column_key` = '".$key."';");
          }
        }
      }

      $form->addNotice('Your changes have been saved.');
      $this->_helper->redirector->gotoRoute(array('module' => 'harmony', 'controller' => 'settings', 'action' => 'styling', 'customtheme_id' => $values['theme_color']),'admin_default',true);
    }
    $this->view->activatedTheme = $settings->getSetting('harmony.theme.color', 1);
  }

  public function addAction() {

    $this->_helper->layout->setLayout('admin-simple');
    
    $customtheme_id = $this->_getParam('customtheme_id', 0);
    
    $this->view->form = $form = new Harmony_Form_Admin_Settings_CustomTheme();
    if ($customtheme_id) {
      $form->setTitle("Edit Custom Theme Name");
      $form->submit->setLabel('Save Changes');
      $customtheme_id = $customtheme_id + 1;
      $customtheme = Engine_Api::_()->getItem('harmony_customthemes', $customtheme_id);
      $form->populate($customtheme->toArray());
    }
    
    if ($this->getRequest()->isPost()) {
    
      if (!$form->isValid($this->getRequest()->getPost()))
        return;
      
      $table = Engine_Api::_()->getDbtable('customthemes', 'harmony');
      
      $db = $table->getAdapter();
      $db->beginTransaction();
      try {
        
        $values = $form->getValues();

        if(!$customtheme_id) {
            $customtheme = $table->createRow();
            $customtheme->setFromArray($values);
            $customtheme->save();

            $theme_id = $customtheme->customtheme_id;

            if(!empty($values['customthemeid'])) {

                $dbInsert = Engine_Db_Table::getDefaultAdapter();

                $getThemeValues = $table->getThemeValues(array('customtheme_id' => $values['customthemeid']));
                foreach($getThemeValues as $key => $value) {
                    $dbInsert->query("INSERT INTO `engine4_harmony_customthemes` (`name`, `value`, `column_key`,`default`,`theme_id`) VALUES ('".$values['name']."','".$value->value."','".$value->column_key."','1','".$theme_id."') ON DUPLICATE KEY UPDATE `value`='".$value->value."';");
                }
                $db->query("UPDATE `engine4_harmony_customthemes` SET `value` = '" . $theme_id . "' WHERE theme_id = " . $theme_id . " AND column_key = 'custom_theme_color';");
                $db->query('DELETE FROM `engine4_harmony_customthemes` WHERE `engine4_harmony_customthemes`.`theme_id` = "0";');
            }
        } else if(!empty($customtheme_id)) {
          $theme_id = $customtheme_id = $customtheme_id - 1;
          $db->query("UPDATE `engine4_harmony_customthemes` SET `name` = '" . $values['name'] . "' WHERE theme_id = " . $customtheme_id);
        }
        $db->commit();
        if(!$customtheme_id) {
          $message = array('New Custom theme created successfully.');
        } else {
          $message = array('New Custom theme edited successfully.');
        }
        return $this->_forward('success', 'utility', 'core', array(
          'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'harmony', 'controller' => 'settings', 'action' => 'styling', 'customtheme_id' => $theme_id),'admin_default',true),
          'messages' => $message,
        ));
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    }
  }

  public function deleteAction() {

    $this->_helper->layout->setLayout('admin-simple');
    $this->view->customtheme_id = $customtheme_id = $this->_getParam('customtheme_id', 0);

    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $dbQuery = Zend_Db_Table_Abstract::getDefaultAdapter();
        $dbQuery->query("DELETE FROM engine4_harmony_customthemes WHERE theme_id = ".$customtheme_id);
        $db->commit();
        $activatedTheme = Engine_Api::_()->getApi('settings', 'core')->getSetting('harmony.theme.color', 1);
        $this->_forward('success', 'utility', 'core', array(
          'parentRedirect' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'harmony', 'controller' => 'settings', 'action' => 'styling', 'customtheme_id' => $activatedTheme),'admin_default',true),
          'messages' => array('You have successfully delete custom theme.')
        ));
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
    } else {
      $this->renderScript('admin-settings/delete.tpl');
    }
  }
  
  public function getcustomthemecolorsAction() {

    $customtheme_id = $this->_getParam('customtheme_id', null);
    if(empty($customtheme_id))
      return;
    
    if(engine_in_array($customtheme_id, array(1,2,3)))
      $default = 0;
    else
      $default = 1;
      
    $themecustom = Engine_Api::_()->getDbTable('customthemes', 'harmony')->getThemeKey(array('theme_id'=>$customtheme_id, 'default' => $default));
    $customthecolorArray = array();
    foreach($themecustom as $value) {
      $customthecolorArray[] = $value['column_key'].'||'.$value['value'];
    }
    echo json_encode($customthecolorArray);die;
  }

  public function landingpageSet($value) {

    $db = Zend_Db_Table_Abstract::getDefaultAdapter();

    // Get page param
    $pageTable = Engine_Api::_()->getDbtable('pages', 'core');
    $contentTable = Engine_Api::_()->getDbtable('content', 'core');
    
    // Make new page
    $pageObject = $pageTable->createRow();
    $pageObject->displayname = "Backup - Landing Page";
    $pageObject->provides = 'no-subject';
    $pageObject->save();
    $new_page_id = $pageObject->page_id;
    
    $old_page_content = $db->select()
        ->from('engine4_core_content')
        ->where('`page_id` = ?', 3)
        ->order(array('type', 'content_id'))
        ->query()
        ->fetchAll();
    
    $content_count = engine_count($old_page_content);
    for($i = 0; $i < $content_count; $i++){
      $contentRow = $contentTable->createRow();
      $contentRow->page_id = $new_page_id;
      $contentRow->type = $old_page_content[$i]['type'];
      $contentRow->name = $old_page_content[$i]['name'];
      if( $old_page_content[$i]['parent_content_id'] != null ) {
        $contentRow->parent_content_id = $content_id_array[$old_page_content[$i]['parent_content_id']];            
      }
      else{
        $contentRow->parent_content_id = $old_page_content[$i]['parent_content_id'];
      }
      $contentRow->order = $old_page_content[$i]['order'];
      $contentRow->params = $old_page_content[$i]['params'];
      $contentRow->attribs = $old_page_content[$i]['attribs'];
      $contentRow->save();
      $content_id_array[$old_page_content[$i]['content_id']] = $contentRow->content_id;
    }

    $widgetOrder = 1;
    $db->query('DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`page_id` = "3";');

    $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'main',
        'page_id' => 3,
        'order' => 1,
    ));
    $mainId = $db->lastInsertId();

    $db->insert('engine4_core_content', array(
        'type' => 'container',
        'name' => 'middle',
        'page_id' => 3,
        'parent_content_id' => $mainId,
        'order' => 2,
    ));
    $mainMiddleId = $db->lastInsertId();

    if($value == 1) {

      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'harmony.landing-page-banner',
        'page_id' => 3,
        'parent_content_id' => $mainMiddleId,
        'order' => $widgetOrder++,
        'params' => '{"height":"450","title":"Where Connections Thrive!","description":"Join our network for Endless Opportunities, Innovation, and Collective Growth. We are one stop solution where Innovation meets Collaboration. ","btntext":"Join Us","btntextlink":"signup","photo1":"","photo2":"","photo3":"","nomobile":"0","name":"harmony.landing-page-banner"}',
      ));
      
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'harmony.landing-page-features',
        'page_id' => 3,
        'parent_content_id' => $mainMiddleId,
        'order' => $widgetOrder++,
        'params' => '{"title":"Unlock the Magic of Our Remarkable Features","leftphoto":"","dummy1":null,"photo1":"","featuresheading1":"User-Friendly Experience","description1":"Enjoy a straightforward and easy-to-use platform, ensuring smooth navigation and interaction for all our community members.","dummy2":null,"photo2":"","featuresheading2":"Interactive Feeds","description2":" Stay connected with the latest updates and activities from your network through dynamic content feed, keeping your experience vibrant and ever-evolving.","dummy3":null,"photo3":"","featuresheading3":"Privacy Settings","description3":"Take control of your online presence with robust privacy options. Adjust the visibility of your profile, posts, and personal information to create a secure and trusted space.","dummy4":null,"photo4":"","featuresheading4":"Media Sharing Hub","description4":"Express yourself creatively by sharing various forms of media, such as photos, videos, and links. Connect with others in dynamic ways through multimedia.","nomobile":"0","name":"harmony.landing-page-features"}', 
      ));
      
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'harmony.landing-page-counter-section',
        'page_id' => 3,
        'parent_content_id' => $mainMiddleId,
        'order' => $widgetOrder++,
        'params' => '{"title":"Explore What Awaits You Inside!","btntext":"Signup","btntextlink":"signup","dummy1":null,"icon1":"fas fa-pager","count1":"100","text1":"Posts","dummy2":null,"icon2":"fas fa-user-friends","count2":"1500","text2":"Members","dummy3":null,"icon3":"fas fa-blog","count3":"1000","text3":"Blogs","dummy4":null,"icon4":"fas fa-table","count4":"500","text4":"Forums","dummy5":null,"icon5":"fas fa-newspaper","count5":"20000","text5":"Classifieds","nomobile":"0","name":"harmony.landing-page-counter-section"}', 
      ));

      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'harmony.landing-page-service',
        'page_id' => 3,
        'parent_content_id' => $mainMiddleId,
        'order' => $widgetOrder++,
        'params' => '{"title":"Discover Our Special Services Inside!","dummy1":null,"icon1":"fas fa-user-circle","featuresheading1":"Profile Creation","description1":"Build a personalized profile to showcase your interests, achievements, and personality to the community.","dummy2":null,"icon2":"fas fa-envelope","featuresheading2":"Messaging","description2":"Connect with friends, family, and colleagues through instant messaging, fostering real-time communication.","dummy3":null,"icon3":"fas fa-user-lock","featuresheading3":"Privacy Controls","description3":"Utilize robust privacy settings to manage the visibility of your profile and posts, ensuring a secure online presence.","dummy4":null,"icon4":"fas fa-photo-video","featuresheading4":"Media Sharing","description4":"Share photos, videos, and links to express yourself and connect with others through multimedia content.","dummy5":null,"icon5":"fas fa-users","featuresheading5":"Groups and Communities","description5":"Join or create groups based on shared interests. Engage in discussions, events, and collaborative activities.   ","dummy6":null,"icon6":"fas fa-calendar-week","featuresheading6":"Events and Calendar","description6":"Stay informed about upcoming events within your network and plan your schedule ahead of time.","dummy7":null,"icon7":"fas fa-pager","featuresheading7":"News Feed","description7":"Receive updates from friends, events, and groups in a dynamic feed, keeping you connected with the latest activities and content.","dummy8":null,"icon8":"fas fa-newspaper","featuresheading8":"Classifieds","description8":"Buy, sell, or trade goods and services within the community, creating a virtual marketplace for members within our community.","nomobile":"0","name":"harmony.landing-page-service"}', 
      ));
      
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'harmony.landing-page-cta-section',
        'page_id' => 3,
        'parent_content_id' => $mainMiddleId,
        'order' => $widgetOrder++,
        'params' => '{"title":"Connect, Create, and Celebrate: Where Every Moment Finds Its Community!","description":"Join our vibrant social network \u2013 where connections thrive, creativity blooms, and every moment becomes a celebration.","btntext":"Signup","btntextlink":"signup","nomobile":"0","name":"harmony.landing-page-cta-section"}',
      ));
      
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'harmony.landing-page-why-choose',
        'page_id' => 3,
        'parent_content_id' => $mainMiddleId,
        'order' => $widgetOrder++,
        'params' => '{"title":"How Our Community Works?","leftphoto":"","dummy1":null,"photo1":"","featuresheading1":"Effortless Access","description1":"Embark on your journey with us by signing up and logging in to seamlessly connect with our vibrant community.","dummy2":null,"photo2":"","featuresheading2":"Share your story","description2":"Dive in and share your updates, create groups, organize events, participate in polls, and much more, all within our community.","dummy3":null,"photo3":"","featuresheading3":"Go Global with Your Content","description3":"Easily share your posts, profiles, and content from our community to various social networking sites worldwide.","nomobile":"0","name":"harmony.landing-page-why-choose"}',
      ));
      
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'harmony.landing-page-app-section',
        'page_id' => 3,
        'parent_content_id' => $mainMiddleId,
        'order' => $widgetOrder++,
        'params' => '{"title":"Stay Connected Anywhere & Everywhere!","description":"Access our platform effortlessly on-the-go through our user-friendly mobile apps.","androidapplink":"#","iosapplink":"#","apprightimage":"","nomobile":"0","name":"harmony.landing-page-app-section"}',
      ));
    }
  }

  public function manageFontsAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('harmony_admin_main', array(), 'harmony_admin_main_managefonts');

    $this->view->form = $form = new Harmony_Form_Admin_Settings_Fonts();

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

      $values = $form->getValues();
      
      $fontArray = array();

      if($values['harmony_googlefonts']) {

        $googleFontVariants = Engine_Api::_()->core()->getGoogleFonts('variants');

        $fontArray['harmony_body_fontfamily'] = $values['harmony_googlebody_fontfamily'];
        $fontArray['harmony_heading_fontfamily'] = $values['harmony_googleheading_fontfamily'];
        $fontArray['harmony_mainmenu_fontfamily'] = $values['harmony_googlemainmenu_fontfamily'];
        $fontArray['harmony_tab_fontfamily'] = $values['harmony_googletab_fontfamily'];

        $fontArray['harmony_body_fontsize'] = $values['harmony_googlebody_fontsize'];
        $fontArray['harmony_heading_fontsize'] = $values['harmony_googleheading_fontsize'];
        $fontArray['harmony_mainmenu_fontsize'] = $values['harmony_googlemainmenu_fontsize'];
        $fontArray['harmony_tab_fontsize'] = $values['harmony_googletab_fontsize'];

        $values['harmony_googlebody_fontfamilyvariants'] = implode(",", $googleFontVariants[$values['harmony_googlebody_fontfamily']]);
        $values['harmony_googleheading_fontfamilyvariants'] = implode(",", $googleFontVariants[$values['harmony_googleheading_fontfamily']]);
        $values['harmony_googlemainmenu_fontfamilyvariants'] = implode(",", $googleFontVariants[$values['harmony_googlemainmenu_fontfamily']]);
        $values['harmony_googletab_fontfamilyvariants'] = implode(",", $googleFontVariants[$values['harmony_googletab_fontfamily']]);
      } else {
        $fontArray['harmony_body_fontfamily'] = $values['harmony_body_fontfamily'];
        $fontArray['harmony_heading_fontfamily'] = $values['harmony_heading_fontfamily'];
        $fontArray['harmony_mainmenu_fontfamily'] = $values['harmony_mainmenu_fontfamily'];
        $fontArray['harmony_tab_fontfamily'] = $values['harmony_tab_fontfamily'];

        $fontArray['harmony_body_fontsize'] = $values['harmony_body_fontsize'];
        $fontArray['harmony_heading_fontsize'] = $values['harmony_heading_fontsize'];
        $fontArray['harmony_mainmenu_fontsize'] = $values['harmony_mainmenu_fontsize'];
        $fontArray['harmony_tab_fontsize'] = $values['harmony_tab_fontsize'];
      }

      foreach ($values as $key => $value) {
        Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
      }
      
      //Save constant
      $themeConstants = Engine_Api::_()->harmony()->themeConstants(array(), $fontArray);
      Engine_Api::_()->core()->saveThemeVariables($themeConstants, $form, 'harmony');

      $form->addNotice('Your changes have been saved.');
      $this->_helper->redirector->gotoRoute(array());
    }
  }
}
