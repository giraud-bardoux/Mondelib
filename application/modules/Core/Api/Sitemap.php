<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Sitemap.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_Api_Sitemap extends Core_Api_Abstract {

  public function generateAllSitemapFile() {

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $sitemapsTable = Engine_Api::_()->getDbTable('sitemaps', 'core');
    $sitemapsTableName = $sitemapsTable->info('name');

    $select = $sitemapsTable->select()->where('enabled =?', '1');
    $results = $sitemapsTable->fetchAll($select);

    foreach($results as $result) {

        if($result->resource_type != 'menu_urls') {
            $content = Engine_Api::_()->getItem('core_sitemap', $result->sitemap_id);
            $sitemapArray = Engine_Api::_()->getApi('sitemap', 'core')->getContentSitemap($result->resource_type, $content);

            if(empty($sitemapArray))
                continue;

            //Check file is exist or not.
            $filepath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public'. DIRECTORY_SEPARATOR .'sitemap';
            if (!file_exists($filepath)) {
                @mkdir($filepath);
                @chmod($filepath, 0777);
            }
            $siteFileName = $filepath .DIRECTORY_SEPARATOR.'sitemap_'.$result->resource_type.'.xml';
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
        } else {
            $content = Engine_Api::_()->getItem('core_sitemap', $result->sitemap_id);
            $sitemapArray = Engine_Api::_()->getApi('sitemap', 'core')->getMenusSitemap();

            if(empty($sitemapArray))
                continue;

            //Check file is exist or not.
            $filepath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'public'. DIRECTORY_SEPARATOR .'sitemap';
            if (!file_exists($filepath)) {
                @mkdir($filepath);
                @chmod($filepath, 0777);
            }
            $siteFileName = $filepath .DIRECTORY_SEPARATOR.'sitemap_'.$result->resource_type.'.xml';
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
        }
    }

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

    $sitemapsTable = Engine_Api::_()->getDbTable('sitemaps', 'core');
    $sitemapsTableName = $sitemapsTable->info('name');
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $select = $sitemapsTable->select()->where('enabled =?', 1);
    $results = $sitemapsTable->fetchAll($select);

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

  public function getContentSitemap($resource_type, $content) {
    $authApi = Engine_Api::_()->authorization();
    $coreSearchTable = Engine_Api::_()->getDbTable('search', 'core');
    $coreSearchTableName = $coreSearchTable->info('name');

    $select = $coreSearchTable->select()->where("type = ?", $resource_type)->order('id DESC');

    if(!empty($content->limit))
        $select->limit($content->limit);

    $results = $coreSearchTable->fetchAll($select);

    $sitemapArray = array();
    foreach($results as $results) {

        $resource = Engine_Api::_()->getItem($results->type, $results->id);
        if(isset($resource->modified_date)) {
            $date =  $resource->modified_date;
        } elseif(isset($resource->creation_date)) {
            $date =  $resource->creation_date;
        } else {
            $date = gmdate('Y-m-d H:i:s');
        }
        if($resource && $authApi->isAllowed($resource, 'everyone', 'view') && $resource->getHref()) {
            $sitemapArray[]  = array(
                'uri' => $resource->getHref(),
                'priority' => $content->priority,
                'changefreq' => $content->frequency,
                'lastmod' => $date,
            );
        }
    }
    $sitemapArray = array_chunk($sitemapArray, '1000');
    return $sitemapArray;

  }

  public function getMenusSitemap() {

    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;

    $coreseo_select_menus = Engine_Api::_()->getApi('settings','core')->getSetting('coreseo_select_menus','');
    $coreseo_select_menus = json_decode($coreseo_select_menus);
    $coreseo_select_menus = $coreseo_select_menus ? $coreseo_select_menus : '';

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
    return $sitemapArray;
  }
}
