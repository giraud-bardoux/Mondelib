<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminSeoController.php 10197 2014-05-05 21:09:21Z andres $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_AdminSeoController extends Core_Controller_Action_Admin {

  public function indexAction() {
  
    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_settings_seo', array(), 'core_admin_main_settings_seo_settings');

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $this->view->form = $form = new Core_Form_Admin_Seo_Global();

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();
      foreach ($values as $key => $value) {
        if($value != '')
        $settings->setSetting($key, $value);
      }
      $form->addNotice('Your changes have been saved.');
    }
  }
  
  public function managemetakeywordsAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_settings_seo', array(), 'core_admin_main_settings_seo_managemetakeywords');

    $this->view->formFilter = $formFilter = new Core_Form_Admin_Seo_Filter();

    // Process form
    $values = array();
    if ($formFilter->isValid($this->_getAllParams()))
      $values = $formFilter->getValues();

    foreach ($values as $key => $value) {
      if (null === $value) {
        unset($values[$key]);
      }
    }

    $values = array_merge(array( 'order' => 'page_id', 'order_direction' => 'DESC'), $values);
    $this->view->assign($values);

    $select = Engine_Api::_()->getDbtable('pages', 'core')->select();
    
    $select->where('page_id NOT IN (?)', array(1,2));

    if (!empty($values['displayname']))
      $select->where('displayname LIKE ?', $values['displayname'] . '%');

    if (!empty($values['title']))
      $select->where('title LIKE ?', $values['title'] . '%');

    if (!empty($values['description']))
      $select->where('description LIKE ?', $values['description'] . '%');

    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(20);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }
  
  public function editAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_settings_seo', array(), 'core_admin_main_settings_seo_managemetakeywords');

    $corePageItem = Engine_Api::_()->getItem('core_page', $this->_getParam('page_id'));

    $form = $this->view->form = new Core_Form_Admin_Seo_Edit();
    $form->execute->setLabel('Save Changes');
    $form->populate($corePageItem->toArray());

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {

      $values = $form->getValues();

      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $corePageItem->setFromArray($values);
        $corePageItem->save();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      $form->addNotice('Your changes have been saved.');
    }
    $this->renderScript('admin-seo/edit.tpl');
  }
  
  public function robotoAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_settings_seo', array(), 'core_admin_main_settings_seo_roboto');

    $writeable['coreseo'] = false;
    try {
      if(!file_exists(APPLICATION_PATH . "/robots.txt")) {
        throw new Core_Model_Exception('Missing file');
      } else {
        $this->checkWriteable(APPLICATION_PATH . "/robots.txt");
      }
      $writeable['coreseo'] = true;
    } catch(Exception $e) {
      $this->view->errorMessage = $e->getMessage();
    }
    $this->view->writeable = $writeable;
    $this->view->activeFileContents = file_get_contents(APPLICATION_PATH . '/robots.txt');
  }
  

  public function opensearchAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_settings_seo', array(), 'core_admin_main_settings_seo_opensearch');
    
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $writeable = array();
    $writeable['coreseo'] = false;
    try {
      if(!file_exists(APPLICATION_PATH . "/osdd.xml")) {
        $dom = new DOMDocument();
        $xml_file_name = APPLICATION_PATH . "/osdd.xml";
        $dom->save($xml_file_name);
        $xml_content = '<?xml version="1.0" encoding="UTF-8"?>
        <OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
        <ShortName>'.$_SERVER['HTTP_HOST'].'</ShortName>
        <Description>'.$_SERVER['HTTP_HOST'].'Search Description'.'</Description>
        <InputEncoding>[UTF-8]</InputEncoding>
        <Image width="16" height="16" type="image/x-icon">'.$_SERVER['HTTP_HOST'].'/favicon.ico</Image>
        <Url type="text/html" template="'.$view->absoluteUrl($view->baseUrl('search')).'" method="GET">
            <Param name="query" value="{searchTerms}"/>
            <Param name="otracker" value="{start}"/>
        </Url>
        </OpenSearchDescription>';
        file_put_contents($xml_file_name, $xml_content);
        throw new Core_Model_Exception('Missing file');
      } else {
        $this->checkWriteable(APPLICATION_PATH . "/osdd.xml");
      }
      $writeable['coreseo'] = true;
    } catch(Exception $e) {
      $this->view->errorMessage = $e->getMessage();
    }
    $this->view->writeable = $writeable;
    $this->view->activeFileContents = file_get_contents(APPLICATION_PATH . '/osdd.xml');
  }
  
  public function schemaMarkupAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_settings_seo', array(), 'core_admin_main_settings_seo_schemamarkup');
    
    $this->view->form = $form = new Core_Form_Admin_Seo_Schema();

    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();
      foreach ($values as $key => $value) {
        if (Engine_Api::_()->getApi('settings', 'core')->hasSetting($key, $value))
          Engine_Api::_()->getApi('settings', 'core')->removeSetting($key);
        if (!$value && strlen($value) == 0)
          continue;
        Engine_Api::_()->getApi('settings', 'core')->setSetting($key, $value);
      }
      $form->addNotice('Your changes have been saved.');
    }
  }

  public function checkWriteable($path) {

    if( !file_exists($path) ) {
      throw new Core_Model_Exception('Path doesn\'t exist');
    }
    if( !is_writeable($path) ) {
      throw new Core_Model_Exception('Path is not writeable');
    }
    if( !is_dir($path) ) {
      if( !($fh = fopen($path, 'ab')) ) {
        throw new Core_Model_Exception('File could not be opened');
      }
      fclose($fh);
    }
  }

  public function saveAction() {
    
    $file = $this->_getParam('basePath');
    $body = $this->_getParam('body');

    if( !$this->getRequest()->isPost() ) {
      $this->view->status = false;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_("Bad method");
      return;
    }

    if(!$file || !$body ) {
      $this->view->status = false;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_("Bad params");
      return;
    }

    //Check file
    $basePath = $file;
    try {
      $this->checkWriteable($basePath);
    } catch( Exception $e ) {
      $this->view->status = false;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_("Not writeable");
      return;
    }

    // Now lets write the custom file
    if( !file_put_contents($basePath, $body) ) {
      $this->view->status = false;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Could not save contents');
      return;
    }
    $this->view->status = true;
  }
  

  public function sitemapAction() {

    $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('core_admin_main_settings_seo', array(), 'core_admin_main_settings_seo_sitemap');

    $availableTypes = Engine_Api::_()->getApi('search', 'core')->getAvailableTypes();

    $contentable = Engine_Api::_()->getDbTable('sitemaps', 'core');
    if (is_countable($availableTypes) && engine_count($availableTypes) > 0) {

      $options = array();
      foreach ($availableTypes as $index => $type) {

        $options[$type] = $ITEM_TYPE = strtoupper('ITEM_TYPE_' . $type);

        $hasType = Engine_Api::_()->getDbTable('sitemaps', 'core')->hasType(array('resource_type' => $type));
        if (!$hasType) {

            $values = array('resource_type' => $type, 'title' => $this->view->translate($ITEM_TYPE), 'frequency' => 'always', 'priority' => '0.5', 'limit' => '0', 'enabled' => 1);
            $row = $contentable->createRow();
            $row->setFromArray($values);
            $row->save();
        }
      }
    }

    $this->view->formFilter = $formFilter = new Core_Form_Admin_Seo_Sitemap();

    // Process form
    $values = array();
    if ($formFilter->isValid($this->_getAllParams()))
      $values = $formFilter->getValues();

    foreach ($values as $key => $value) {
      if (null === $value) {
        unset($values[$key]);
      }
    }

    $values = array_merge(array('order' => 'sitemap_id', 'order_direction' => 'DESC'), $values);
    $this->view->assign($values);

    $select = Engine_Api::_()->getDbtable('sitemaps', 'core')->select();

    if (!empty($values['title']))
      $select->where('title LIKE ?', $values['title'] . '%');

    if (isset($_GET['enabled']) && $_GET['enabled'] != '')
      $select->where('enabled = ?', $values['enabled']);

    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(10);
    $paginator->setCurrentPageNumber($this->_getParam('page', 1));
  }
  
  public function generateallAction() {

    Engine_Api::_()->getApi('sitemap', 'core')->generateAllSitemapFile();
    return $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => true,
      'parentRefresh' => true,
      'messages' => array('You have successfully generated the sitemap.')
    ));
  }

  public function generateAction() {

    $id = $this->_getParam('sitemap_id');
    $content = Engine_Api::_()->getItem('core_sitemap', $id);
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $resource_type = $content->resource_type;
    if(empty($resource_type))
        return;

    $authApi = Engine_Api::_()->authorization();

    if($resource_type != 'menu_urls') {
        $coreSearchTable = Engine_Api::_()->getDbTable('search', 'core');
        $coreSearchTableName = $coreSearchTable->info('name');

        $select = $coreSearchTable->select()->where("type = ?", $resource_type)->order('id DESC');
        if(!empty($content->limit))
            $select->limit($content->limit);

        $results = $coreSearchTable->fetchAll($select);

        $sitemapArray = array();
        if(count($results) > 0) {
					foreach($results as $results) {
						$hasItemType = Engine_Api::_()->hasItemType($results->type);
						if($hasItemType) {
							
							$resource = Engine_Api::_()->getItem($results->type, $results->id);
							if($resource) {
								if(isset($resource->modified_date)) {
										$date =  $resource->modified_date;
								} elseif(isset($resource->creation_date)) {
										$date =  $resource->creation_date;
								} else {
										$date = gmdate('Y-m-d H:i:s');
								}
								if($resource && $resource->getHref()) {
									$sitemapArray[]  = array(
										'uri' => $resource->getHref(),
										'priority' => $content->priority,
										'changefreq' => $content->frequency,
										'lastmod' => $date,
									);
								}
							} else {
								return $this->_forward('success', 'utility', 'core', array(
									'smoothboxClose' => true,
									'parentRefresh' => true,
									'messages' => array('There is no content to generate the sitemap.')
								));
							}
						}
					}
					$sitemapArray = array_chunk($sitemapArray, '1000');
				}
    } else {
        $coreseo_select_menus = Engine_Api::_()->getApi('settings','core')->getSetting('coreseo_select_menus','');
        $coreseo_select_menus = json_decode($coreseo_select_menus);

        $coreMenusApi = Engine_Api::_()->getApi('menus', 'core');
        $menustable = Engine_Api::_()->getDbTable('menus', 'core');
        $select = $menustable->select()->where('id IN (?)', $coreseo_select_menus);
        $allmenus = $menustable->fetchAll($select);
        $navigation = new Zend_Navigation();
        foreach($allmenus as $allmenu) {
            $pages = $coreMenusApi->getMenuParams($allmenu->name);
            $navigation->addPages($pages);
        }

        $checkURLValidator = new Zend_Validate_Sitemap_Loc();
        $allURLs = array();
        foreach($navigation as $urls) {
            if ($checkURLValidator->isValid($view->absoluteUrl($urls->getHref())))
                $allURLs[] = $view->absoluteUrl($urls->getHref());
        }

        if(engine_count($allURLs) > 0) {
            $sitemapArray = array();
            foreach($allURLs as $results) {
                $sitemapArray[]  = array(
                    'uri' => $results,
                    'priority' => $content->priority,
                    'changefreq' => $content->frequency,
                    'lastmod' => gmdate('Y-m-d H:i:s'),
                );
            }
            $sitemapArray = array_chunk($sitemapArray, '1000');
        }
    }

    if(empty($sitemapArray)) {
			return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
        'messages' => array('There is no content to generate the sitemap.')
			));
    }

    //Check file is exist or not.
    $filepath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public'. DIRECTORY_SEPARATOR .'sitemap';
    if (!file_exists($filepath)) {
        @mkdir($filepath);
        @chmod($filepath, 0777);
    }
    $siteFileName = $filepath .DIRECTORY_SEPARATOR.'sitemap_'.$resource_type.'.xml';

    $params = array();

    foreach ($sitemapArray as $key => $sitemapContent) {
      $container = new Zend_Navigation($sitemapContent);
      $sitemap = $view->sitemap($container)->render();
      $params['data'] = $view->sitemap($container)->render();
      file_put_contents($siteFileName, $sitemap);
      @chmod($siteFileName, 0777);

      $this->makeCompressContentFile($params, $content);
    }
    $content->modified_date = gmdate('Y-m-d H:i:s');
    $content->save();

    $this->makeCommonSitemapFile();

    return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
        'messages' => array('You have successfully generated the sitemap for this content.')
    ));
  }

  public function makeCompressContentFile($params, $content) {
    //Check file is exist or not.
    $filepath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public'. DIRECTORY_SEPARATOR .'sitemap';
    if (!file_exists($filepath)) {
        @mkdir($filepath);
        @chmod($filepath, 0777);
    }
    $siteFileName = $filepath .DIRECTORY_SEPARATOR.'sitemap_'.$content->resource_type.'.xml.gz';
    $gzdata = gzencode($params['data'], 9);
    file_put_contents($siteFileName, $gzdata);
    @chmod($siteFileName, 0777);
  }

  public function makeCommonSitemapFile() {

    $contentsTable = Engine_Api::_()->getDbTable('sitemaps', 'core');
    $contentsTableName = $contentsTable->info('name');
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $select = $contentsTable->select()->where('enabled =?', 1);
    $results = $contentsTable->fetchAll($select);

    $filePath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public'. DIRECTORY_SEPARATOR .'sitemap';

    $allContentXMLArray = array();

    foreach($results as $result) {
        //Check file is exist or not.
        $file_path = $filePath .DIRECTORY_SEPARATOR . 'sitemap_'.$result->resource_type.'.xml.gz';
        $siteFileName = $view->absoluteUrl($view->baseUrl('public/sitemap/sitemap_'.$result->resource_type.'.xml.gz'));
        if(file_exists($file_path)) {
            $allContentXMLArray[] = array(
                'uri' => $siteFileName,
                'lastmod'   => $result->modified_date,
            );
        }
    }
    $container = new Zend_Navigation($allContentXMLArray);
    $sitemap = $view->sitemap($container)->render();
    $params['data'] = $view->sitemap($container)->render();

    //Check file is exist or not.
    $filepath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public'. DIRECTORY_SEPARATOR .'sitemap';
    if (!file_exists($filepath)) {
        @mkdir($filepath);
        @chmod($filepath, 0777);
    }
    $siteFileName = $filepath .DIRECTORY_SEPARATOR.'sitemap'.'.xml';
    file_put_contents($siteFileName, $sitemap);
    @chmod($siteFileName, 0777);
    $this->makeCompressCommonFile($params);
  }

  public function makeCompressCommonFile($params) {
    //Check file is exist or not.
    $filepath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public'. DIRECTORY_SEPARATOR .'sitemap';
    if (!file_exists($filepath)) {
        @mkdir($filepath);
        @chmod($filepath, 0777);
    }
    $siteFileName = $filepath .DIRECTORY_SEPARATOR.'sitemap'.'.xml.gz';
    $gzdata = gzencode($params['data'], 9);
    file_put_contents($siteFileName, $gzdata);
    @chmod($siteFileName, 0777);
  }

  public function downloadxmlAction() {
    header("Content-Type: application/xml;");
    header("Content-Disposition: attachment; filename=sitemap.xml;");
    readfile("public/sitemap/sitemap.xml");
    exit();
    return;
  }

  public function downloadgzipAction() {
    header("Content-Type: application/xml;");
    header("Content-Disposition: attachment; filename=sitemap.xml.gz;");
    readfile("public/sitemap/sitemap.xml.gz");
    exit();
    return;
  }

  public function enabledAction() {

    $id = $this->_getParam('sitemap_id');
    if (!empty($id)) {
      $item = Engine_Api::_()->getItem('core_sitemap', $id);
      $item->enabled = !$item->enabled;
      $item->save();
    }
    $this->_redirect('admin/core/seo/sitemap');
  }

  public function selectedmenusAction() {

    $this->_helper->layout->setLayout('admin-simple');
    $content = Engine_Api::_()->getItem('core_sitemap', $this->_getParam('sitemap_id'));
    $form = $this->view->form = new Core_Form_Admin_Seo_Selectedmenus();
    $form->execute->setLabel('Save Changes');
    $form->populate($content->toArray());
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();

      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $coreseo_select_menus = json_encode($values['coreseo_select_menus']);
        Engine_Api::_()->getApi('settings', 'core')->setSetting('coreseo.select.menus', $coreseo_select_menus);
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
        'messages' => array('You have successfully selected the menus.')
      ));
    }
  }

  public function editSettingsAction() {

    $this->_helper->layout->setLayout('admin-simple');
    $content = Engine_Api::_()->getItem('core_sitemap', $this->_getParam('sitemap_id'));
    $form = $this->view->form = new Core_Form_Admin_Seo_EditSettings();
    $form->execute->setLabel('Save Changes');
    $form->populate($content->toArray());
    if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
      $values = $form->getValues();
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $content->setFromArray($values);
        $content->save();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
        'messages' => array('You have successfully edited the entry.')
      ));
    }
  }
}
