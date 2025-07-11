<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: install.php 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

class Harmony_Installer extends Engine_Package_Installer_Module {

  public function onInstall() {
    $db = $this->getDb();
    if($this->_databaseOperationType != 'upgrade') {
    
      //Quick links footer
      $menuitem_id = $db->select()
                      ->from('engine4_core_menuitems', 'id')
                      ->limit(1)
                      ->order('id DESC')
                      ->query()
                      ->fetchColumn();

      $db->query('INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) VALUES
      ("custom_'.$menuitem_id++.'", "core", "Start Poll", "", \'{"uri":"polls/create","icon":""}\', "harmony_quicklinks_footer", "", 1, 1, 1),
      ("custom_'.$menuitem_id++.'", "core", "Publish Video", "", \'{"uri":"videos/create","icon":""}\', "harmony_quicklinks_footer", "", 1, 1, 2),
      ("custom_'.$menuitem_id++.'", "core", "Join Groups", "", \'{"uri":"groups"}\', "harmony_quicklinks_footer", "", 1, 1, 3),
      ("custom_'.$menuitem_id++.'", "core", "Videos", "", \'{"uri":"videos","icon":"","target":"","enabled":"1"}\', "harmony_quicklinks_footer", "", 0, 1, 4),
      ("custom_'.$menuitem_id++.'", "core", "Music", "", \'{"uri":"music","icon":"","target":"","enabled":"1"}\', "harmony_quicklinks_footer", "", 0, 1, 5);');
      
      //About Us links footer
      $menuitem_id = $db->select()
                      ->from('engine4_core_menuitems', 'id')
                      ->limit(1)
                      ->order('id DESC')
                      ->query()
                      ->fetchColumn();

      $db->query('INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `enabled`, `custom`, `order`) VALUES
      ("custom_'.$menuitem_id++.'", "core", "Albums", "", \'{"uri":"albums","icon":""}\', "harmony_aboutlinks_footer", "", 1, 1, 1),
      ("custom_'.$menuitem_id++.'", "core", "Blogs", "", \'{"uri":"blogs","icon":""}\', "harmony_aboutlinks_footer", "", 1, 1, 2),
      ("custom_'.$menuitem_id++.'", "core", "Events", "", \'{"uri":"events"}\', "harmony_aboutlinks_footer", "", 1, 1, 3),
      ("custom_'.$menuitem_id++.'", "core", "Videos", "", \'{"uri":"videos","icon":"","target":"","enabled":"1"}\', "harmony_aboutlinks_footer", "", 0, 1, 4),
      ("custom_'.$menuitem_id++.'", "core", "Music", "", \'{"uri":"music","icon":"","target":"","enabled":"1"}\', "harmony_aboutlinks_footer", "", 0, 1, 5);');

			//Header work
			$db->query('DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`name` = "core.menu-main";');
			$db->query('DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`name` = "core.menu-mini";');
			$db->query('DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`name` = "core.menu-logo";');
			$db->query('DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`name` = "core.search-mini";');
			$db->query('DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`name` = "charity.header";');
      $parent_content_id = $db->select()
              ->from('engine4_core_content', 'content_id')
              ->where('type = ?', 'container')
              ->where('page_id = ?', '1')
              ->where('name = ?', 'main')
              ->limit(1)
              ->query()
              ->fetchColumn();
			if($parent_content_id) {
				$select = new Zend_Db_Select($db);
	      $select->from('engine4_core_content')
	          ->where('page_id = ?', 1)
	          ->where('type = ?', 'widget')
	          ->where('name = ?', 'harmony.header');
	      $info = $select->query()->fetch();
	      if(empty($info) ) {
					$db->insert('engine4_core_content', array(
							'type' => 'widget',
							'name' => 'harmony.header',
							'page_id' => 1,
							'parent_content_id' => $parent_content_id,
							'order' => 20,
					));
				}
			}
      
      //Footer Work
			$db->query('DELETE FROM engine4_core_content WHERE `engine4_core_content`.`page_id` = 2 AND `engine4_core_content`.`name` = "charity.footer";');
			$db->query('DELETE FROM engine4_core_content WHERE `engine4_core_content`.`page_id` = 2 AND `engine4_core_content`.`name` = "inspira.footer";');
			$db->query('DELETE FROM engine4_core_content WHERE `engine4_core_content`.`page_id` = 2 AND `engine4_core_content`.`name` = "core.menu-footer";');
			$db->query('DELETE FROM engine4_core_content WHERE `engine4_core_content`.`page_id` = 2 AND `engine4_core_content`.`name` = "core.menu-social-sites";');
			
			$parent_content_id = $db->select()
						->from('engine4_core_content', 'content_id')
						->where('type = ?', 'container')
						->where('page_id = ?', '2')
						->where('name = ?', 'main')
						->limit(1)
						->query()
						->fetchColumn();
			if($parent_content_id) {
				$select = new Zend_Db_Select($db);
	      $select->from('engine4_core_content')
	          ->where('page_id = ?', 2)
	          ->where('type = ?', 'widget')
	          ->where('name = ?', 'harmony.footer');
	      $info = $select->query()->fetch();
	      if(empty($info) ) {
					$db->insert('engine4_core_content', array(
						'type' => 'widget',
						'name' => 'harmony.footer',
						'page_id' => 2,
						'parent_content_id' => $parent_content_id,
						'order' => 888,
					));
				}
			}

			//Home Page
			$widgetOrder = 1;
			
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

			$select = new Zend_Db_Select($db);
      $select->from('engine4_core_content')
          ->where('page_id = ?', 3)
          ->where('type = ?', 'widget')
          ->where('name = ?', 'harmony.landing-page-banner');
      $info = $select->query()->fetch();
      if(empty($info) ) {
	      $db->insert('engine4_core_content', array(
	        'type' => 'widget',
	        'name' => 'harmony.landing-page-banner',
	        'page_id' => 3,
	        'parent_content_id' => $mainMiddleId,
	        'order' => $widgetOrder++,
	        'params' => '{"height":"450","title":"Where Connections Thrive!","description":"Join our network for Endless Opportunities, Innovation, and Collective Growth. We are one stop solution where Innovation meets Collaboration. ","btntext":"Join Us","btntextlink":"signup","photo1":"","photo2":"","photo3":"","nomobile":"0","name":"harmony.landing-page-banner"}',
	      ));
			}

      $select = new Zend_Db_Select($db);
      $select->from('engine4_core_content')
          ->where('page_id = ?', 3)
          ->where('type = ?', 'widget')
          ->where('name = ?', 'harmony.landing-page-features');
      $info = $select->query()->fetch();
      if(empty($info) ) {
	      $db->insert('engine4_core_content', array(
	        'type' => 'widget',
	        'name' => 'harmony.landing-page-features',
	        'page_id' => 3,
	        'parent_content_id' => $mainMiddleId,
	        'order' => $widgetOrder++,
	        'params' => '{"title":"Unlock the Magic of Our Remarkable Features","leftphoto":"","dummy1":null,"photo1":"","featuresheading1":"User-Friendly Experience","description1":"Enjoy a straightforward and easy-to-use platform, ensuring smooth navigation and interaction for all our community members.","dummy2":null,"photo2":"","featuresheading2":"Interactive Feeds","description2":" Stay connected with the latest updates and activities from your network through dynamic content feed, keeping your experience vibrant and ever-evolving.","dummy3":null,"photo3":"","featuresheading3":"Privacy Settings","description3":"Take control of your online presence with robust privacy options. Adjust the visibility of your profile, posts, and personal information to create a secure and trusted space.","dummy4":null,"photo4":"","featuresheading4":"Media Sharing Hub","description4":"Express yourself creatively by sharing various forms of media, such as photos, videos, and links. Connect with others in dynamic ways through multimedia.","nomobile":"0","name":"harmony.landing-page-features"}', 
	      ));
			}
      
			$select = new Zend_Db_Select($db);
      $select->from('engine4_core_content')
          ->where('page_id = ?', 3)
          ->where('type = ?', 'widget')
          ->where('name = ?', 'harmony.landing-page-counter-section');
      $info = $select->query()->fetch();
      if(empty($info) ) {
	      $db->insert('engine4_core_content', array(
	        'type' => 'widget',
	        'name' => 'harmony.landing-page-counter-section',
	        'page_id' => 3,
	        'parent_content_id' => $mainMiddleId,
	        'order' => $widgetOrder++,
	        'params' => '{"title":"Explore What Awaits You Inside!","btntext":"Signup","btntextlink":"signup","dummy1":null,"icon1":"fas fa-pager","count1":"100","text1":"Posts","dummy2":null,"icon2":"fas fa-user-friends","count2":"1500","text2":"Members","dummy3":null,"icon3":"fas fa-blog","count3":"1000","text3":"Blogs","dummy4":null,"icon4":"fas fa-table","count4":"500","text4":"Forums","dummy5":null,"icon5":"fas fa-newspaper","count5":"20000","text5":"Classifieds","nomobile":"0","name":"harmony.landing-page-counter-section"}', 
	      ));
			}

			$select = new Zend_Db_Select($db);
      $select->from('engine4_core_content')
          ->where('page_id = ?', 3)
          ->where('type = ?', 'widget')
          ->where('name = ?', 'harmony.landing-page-service');
      $info = $select->query()->fetch();
      if(empty($info) ) {
	      $db->insert('engine4_core_content', array(
	        'type' => 'widget',
	        'name' => 'harmony.landing-page-service',
	        'page_id' => 3,
	        'parent_content_id' => $mainMiddleId,
	        'order' => $widgetOrder++,
	        'params' => '{"title":"Discover Our Special Services Inside!","dummy1":null,"icon1":"fas fa-user-circle","featuresheading1":"Profile Creation","description1":"Build a personalized profile to showcase your interests, achievements, and personality to the community.","dummy2":null,"icon2":"fas fa-envelope","featuresheading2":"Messaging","description2":"Connect with friends, family, and colleagues through instant messaging, fostering real-time communication.","dummy3":null,"icon3":"fas fa-user-lock","featuresheading3":"Privacy Controls","description3":"Utilize robust privacy settings to manage the visibility of your profile and posts, ensuring a secure online presence.","dummy4":null,"icon4":"fas fa-photo-video","featuresheading4":"Media Sharing","description4":"Share photos, videos, and links to express yourself and connect with others through multimedia content.","dummy5":null,"icon5":"fas fa-users","featuresheading5":"Groups and Communities","description5":"Join or create groups based on shared interests. Engage in discussions, events, and collaborative activities.   ","dummy6":null,"icon6":"fas fa-calendar-week","featuresheading6":"Events and Calendar","description6":"Stay informed about upcoming events within your network and plan your schedule ahead of time.","dummy7":null,"icon7":"fas fa-pager","featuresheading7":"News Feed","description7":"Receive updates from friends, events, and groups in a dynamic feed, keeping you connected with the latest activities and content.","dummy8":null,"icon8":"fas fa-newspaper","featuresheading8":"Classifieds","description8":"Buy, sell, or trade goods and services within the community, creating a virtual marketplace for members within our community.","nomobile":"0","name":"harmony.landing-page-service"}', 
	      ));
			}
      
			$select = new Zend_Db_Select($db);
      $select->from('engine4_core_content')
          ->where('page_id = ?', 3)
          ->where('type = ?', 'widget')
          ->where('name = ?', 'harmony.landing-page-cta-section');
      $info = $select->query()->fetch();
      if(empty($info) ) {
	      $db->insert('engine4_core_content', array(
	        'type' => 'widget',
	        'name' => 'harmony.landing-page-cta-section',
	        'page_id' => 3,
	        'parent_content_id' => $mainMiddleId,
	        'order' => $widgetOrder++,
	        'params' => '{"title":"Connect, Create, and Celebrate: Where Every Moment Finds Its Community!","description":"Join our vibrant social network \u2013 where connections thrive, creativity blooms, and every moment becomes a celebration.","btntext":"Signup","btntextlink":"signup","nomobile":"0","name":"harmony.landing-page-cta-section"}',
	      ));
			}
      
			$select = new Zend_Db_Select($db);
      $select->from('engine4_core_content')
          ->where('page_id = ?', 3)
          ->where('type = ?', 'widget')
          ->where('name = ?', 'harmony.landing-page-why-choose');
      $info = $select->query()->fetch();
      if(empty($info) ) {
	      $db->insert('engine4_core_content', array(
	        'type' => 'widget',
	        'name' => 'harmony.landing-page-why-choose',
	        'page_id' => 3,
	        'parent_content_id' => $mainMiddleId,
	        'order' => $widgetOrder++,
	        'params' => '{"title":"How Our Community Works?","leftphoto":"","dummy1":null,"photo1":"","featuresheading1":"Effortless Access","description1":"Embark on your journey with us by signing up and logging in to seamlessly connect with our vibrant community.","dummy2":null,"photo2":"","featuresheading2":"Share your story","description2":"Dive in and share your updates, create groups, organize events, participate in polls, and much more, all within our community.","dummy3":null,"photo3":"","featuresheading3":"Go Global with Your Content","description3":"Easily share your posts, profiles, and content from our community to various social networking sites worldwide.","nomobile":"0","name":"harmony.landing-page-why-choose"}',
	      ));
			}
      
			$select = new Zend_Db_Select($db);
      $select->from('engine4_core_content')
          ->where('page_id = ?', 3)
          ->where('type = ?', 'widget')
          ->where('name = ?', 'harmony.landing-page-app-section');
      $info = $select->query()->fetch();
      if(empty($info) ) {
	      $db->insert('engine4_core_content', array(
	        'type' => 'widget',
	        'name' => 'harmony.landing-page-app-section',
	        'page_id' => 3,
	        'parent_content_id' => $mainMiddleId,
	        'order' => $widgetOrder++,
	        'params' => '{"title":"Stay Connected Anywhere & Everywhere!","description":"Access our platform effortlessly on-the-go through our user-friendly mobile apps.","androidapplink":"#","iosapplink":"#","apprightimage":"","nomobile":"0","name":"harmony.landing-page-app-section"}',
	      ));
			}
			//Home Page
		
      
      //Theme Enable
      $db->query('INSERT IGNORE INTO `engine4_core_themes` (`name`, `title`, `description`, `active`) VALUES
      ("harmony", "Harmony", "Harmony", 1);');
      $select = new Zend_Db_Select($db);
      $select->from('engine4_core_themes', 'name')
              ->where('active = ?', 1)
              ->limit(1);
      $themeActive = $select->query()->fetch();
      if($themeActive) {
        //$db->query("UPDATE  `engine4_core_themes` SET  `active` =  '0' WHERE  `engine4_core_themes`.`name` ='".$themeActive['name']."' LIMIT 1");
        //$db->query("UPDATE  `engine4_core_modules` SET  `enabled` =  '0' WHERE  `engine4_core_modules`.`name` ='".$themeActive['name']."' LIMIT 1");
        $db->query("UPDATE  `engine4_core_themes` SET  `active` =  '1' WHERE  `engine4_core_themes`.`name` ='harmony' LIMIT 1");
      }
      
      //Start Make extra file for harmony theme custom css
      $themeDirName = APPLICATION_PATH . '/application/themes/harmony';
			@chmod($themeDirName, 0777);
			$fileName = $themeDirName . '/harmony-custom.css';
			$fileexists = @file_exists($fileName);
			if (empty($fileexists)) {
				@chmod($themeDirName, 0777);
				$fh = @fopen($fileName, 'w');
				@fwrite($fh, '/* ADD YOUR CUSTOM CSS HERE */');
				@chmod($fileName, 0777);
				@fclose($fh);
				@chmod($fileName, 0777);
				@chmod($fileName, 0777);
			}
    }

		//Theme Color Default Installation
    $theme = APPLICATION_PATH . '/application/themes/harmony';
    @chmod($theme, 0777);
    $filename = $theme . '/theme-variables.css';
    $fileExists = @file_exists($filename);
    if (empty($fileExists)) {
      @chmod($theme, 0777);
      $fh = @fopen($filename, 'w');
      $constant = ':root {
				--themewidget-radius:0.625rem;
        --harmony-theme-color:#208ED3;
			  --harmony-body-background-color:#EFEDE9;
			  --harmony-font-color:#1E293B;
			  --harmony-font-color-light:#64748B;
			  --harmony-links-color:#208ED3;
			  --harmony-links-hover-color:#208ED3;
			  --harmony-headline-color:#1E293B;
			  --harmony-border-color:#d5d5d5;
			  --harmony-box-background-color:#FFFFFF;
			  --harmony-box-background-color-alt:#F5F5F5;
			  --harmony-comments-background-color:#FDFDFD;
			  --harmony-input-background-color:#FFFFFF;
			  --harmony-input-font-color:#1F1F1F;
			  --harmony-input-border-color:#CBD5E1;
			  --harmony-button-background-color:#208ED3;
			  --harmony-button-background-color-hover:#208ED3;
			  --harmony-button-font-color:#FFFFFF;
			  --harmony-button-border-color:#208ED3;
			  --harmony-header-background-color:#FFFFFF;
			  --harmony-mainmenu-background-color:#FFFFFF;
			  --harmony-mainmenu-links-color:#333333;
			  --harmony-mainmenu-links-hover-color:#000000;
			  --harmony-mainmenu-links-hover-background-color:#208ED3;
			  --harmony-minimenu-search-background-color:#F6F6F6;
				--harmony-minimenu-search-font-color:#1E293B;
			  --harmony-footer-background-color:#FDFDFD;
			  --harmony-footer-font-color:#1F1F1F;
			  --harmony-footer-links-color:#4f4f4f;
			  --harmony-footer-border-color:#FFFFFF;
			  --harmony-button-font-color-hover:#FFFFFF;
			  --harmony-button-border-color-hover:#208ED3;
			  --harmony-secondary-button-background-color:#f2f2f2;
			  --harmony-secondary-button-background-color-hover:#e5e5e5;
			  --harmony-secondary-button-font-color:#212529;
			  --harmony-secondary-button-font-color-hover:#212529;
			  --harmony-secondary-button-border-color:#E4E6EB;
			  --harmony-secondary-button-border-color-hover:#d8dadf;
				--harmony-font-family:"Default Font";
			  --harmony-heading-font-family:"Default Font";
			  --harmony-header-menu-font-family:"Default Font";
			  --harmony-tabs-font-family:"Default Font";
				--harmony-body-fontsize:0.85rem;
				--harmony-heading-fontsize:1.1rem;
				--harmony-mainmenu-fontsize:0.8rem;
				--harmony-tab-fontsize:0.875rem;
      }';
      @fwrite($fh, $constant);
      @chmod($filename, 0777);
      @fclose($fh);
      @chmod($filename, 0777);
      @chmod($filename, 0777);
      
			$db->query('INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
      ("harmony.theme.color", 1);');
    }
		//Theme Color Default Installation

		
    parent::onInstall();
  }
  
  
  public function onDisable() {
  
    $db = $this->getDb();
    
    //Header
    $db->query("DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`name` = 'harmony.header';");
    $parent_content_id = $db->select()
		        ->from('engine4_core_content', 'content_id')
		        ->where('type = ?', 'container')
		        ->where('page_id = ?', '1')
		        ->where('name = ?', 'main')
		        ->limit(1)
		        ->query()
		        ->fetchColumn();
		if($parent_content_id) {
		  $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.menu-mini',
        'page_id' => 1,
        'parent_content_id' => $parent_content_id,
        'order' => 9,
      ));
      $db->insert('engine4_core_content', array(
        'type' => 'widget',
        'name' => 'core.search-mini',
        'page_id' => 1,
        'parent_content_id' => $parent_content_id,
        'order' => 10,
      ));
			$db->insert('engine4_core_content', array(
		      'type' => 'widget',
		      'name' => 'core.menu-logo',
		      'page_id' => 1,
		      'parent_content_id' => $parent_content_id,
		      'order' => 11,
		  ));
		  $db->insert('engine4_core_content', array(
		      'type' => 'widget',
		      'name' => 'core.menu-main',
		      'page_id' => 1,
		      'parent_content_id' => $parent_content_id,
		      'order' => 12,
		  ));
	  }
	  
    //Footer Work
    $db->query('DELETE FROM engine4_core_content WHERE `engine4_core_content`.`page_id` = 2 AND `engine4_core_content`.`name` = "harmony.footer";');
		$db->query('DELETE FROM engine4_core_content WHERE `engine4_core_content`.`page_id` = 2 AND `engine4_core_content`.`name` = "core.menu-footer";');
		$parent_content_id = $db->select()
						->from('engine4_core_content', 'content_id')
						->where('type = ?', 'container')
						->where('page_id = ?', '2')
						->where('name = ?', 'main')
						->limit(1)
						->query()
						->fetchColumn();
		if(!empty($parent_content_id)) {
			$db->insert('engine4_core_content', array(
				'type' => 'widget',
				'name' => 'core.menu-footer',
				'page_id' => 2,
				'parent_content_id' => $parent_content_id,
				'order' => 9,
			));
		}
		
		//Theme Enabled and disabled
		$select = new Zend_Db_Select($db);
		$select->from('engine4_core_themes', 'name')
						->where('active = ?', 1)
						->limit(1);
		$themeActive = $select->query()->fetch();
		if($themeActive) {
			$db->query("UPDATE  `engine4_core_themes` SET  `active` =  '0' WHERE  `engine4_core_themes`.`name` ='".$themeActive['name']."' LIMIT 1");
			$db->query("UPDATE  `engine4_core_modules` SET  `enabled` =  '1' WHERE  `engine4_core_modules`.`name` ='harmony' LIMIT 1");
			$db->query("UPDATE  `engine4_core_themes` SET  `active` =  '1' WHERE  `engine4_core_themes`.`name` ='harmony' LIMIT 1");
		}
		$db->query("UPDATE `engine4_core_settings` SET `value` = '0' WHERE `engine4_core_settings`.`name` = 'harmony.changelanding';");
		
    parent::onDisable();
  }
  
  function onEnable() {
  
    $db = $this->getDb();

		//Header
		$db->query('DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`name` = "core.menu-main";');
		$db->query('DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`name` = "core.menu-mini";');
		$db->query('DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`name` = "core.menu-logo";');
		$db->query('DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`name` = "core.search-mini";');
		$db->query('DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`name` = "charity.header";');
    $parent_content_id = $db->select()
		        ->from('engine4_core_content', 'content_id')
		        ->where('type = ?', 'container')
		        ->where('page_id = ?', '1')
		        ->where('name = ?', 'main')
		        ->limit(1)
		        ->query()
		        ->fetchColumn();
		if($parent_content_id) {
			$select = new Zend_Db_Select($db);
			$select_content = $select
					->from('engine4_core_content')
					->where('page_id = ?', 1)
					->where('type = ?', 'widget')
					->where('name = ?', 'harmony.header')
					->limit(1);
			$content_id = $select_content->query()->fetchObject()->content_id;
			if(empty($content_id)) {
				$db->insert('engine4_core_content', array(
					'type' => 'widget',
					'name' => 'harmony.header',
					'page_id' => 1,
					'parent_content_id' => $parent_content_id,
					'order' => 20,
				));
			}
		}

	  //Footer
	  $db->query('DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`name` = "core.menu-footer";');
	  $db->query('DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`name` = "core.menu-social-sites";');
    $db->query('DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`name` = "charity.footer";');
		$parent_content_id = $db->select()
		        ->from('engine4_core_content', 'content_id')
		        ->where('type = ?', 'container')
		        ->where('page_id = ?', '2')
		        ->where('name = ?', 'main')
		        ->limit(1)
		        ->query()
		        ->fetchColumn();
		if(!empty($parent_content_id)) {
			$select = new Zend_Db_Select($db);
			$select_content = $select
					->from('engine4_core_content')
					->where('page_id = ?', 2)
					->where('type = ?', 'widget')
					->where('name = ?', 'harmony.footer')
					->limit(1);
			$content_id = $select_content->query()->fetchObject()->content_id;
			
			if(empty($content_id)) {
				$db->insert('engine4_core_content', array(
					'type' => 'widget',
					'name' => 'harmony.footer',
					'page_id' => 2,
					'parent_content_id' => $parent_content_id,
					'order' => 9,
				));
			}
	  }
    
    //Theme active
    $select = new Zend_Db_Select($db);
    $select->from('engine4_core_themes', 'name')
            ->where('active = ?', 1)
            ->limit(1);
    $themeActive = $select->query()->fetch();
    if($themeActive) {
			$db->query("UPDATE  `engine4_core_themes` SET  `active` =  '0' WHERE  `engine4_core_themes`.`name` ='".$themeActive['name']."' LIMIT 1");
			$db->query("UPDATE  `engine4_core_modules` SET  `enabled` =  '0' WHERE  `engine4_core_modules`.`name` ='".$themeActive['name']."' LIMIT 1");
	    $db->query("UPDATE  `engine4_core_themes` SET  `active` =  '1' WHERE  `engine4_core_themes`.`name` ='harmony' LIMIT 1");
    }
    $db->query("UPDATE `engine4_core_settings` SET `value` = '0' WHERE `engine4_core_settings`.`name` = 'harmony.changelanding';");

    parent::onEnable();
  }
}