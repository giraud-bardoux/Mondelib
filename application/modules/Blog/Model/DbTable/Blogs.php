<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Blogs.php 10193 2014-05-01 13:48:30Z lucas $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Blog_Model_DbTable_Blogs extends Core_Model_Item_DbTable_Abstract
{
    protected $_rowClass = "Blog_Model_Blog";

    /**
     * Gets a select object for the user's blog entries
     *
     * @param Core_Model_Item_Abstract $user The user to get the messages for
     * @return Zend_Db_Table_Select
     */
    public function getBlogsSelect($params = array())
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewerId = $viewer->getIdentity();
        $table = Engine_Api::_()->getDbtable('blogs', 'blog');
        $rName = $table->info('name');

        $tmTable = Engine_Api::_()->getDbtable('TagMaps', 'core');
        $tmName = $tmTable->info('name');
        //$tmTable = Engine_Api::_()->getDbtable('tagmaps', 'blog');
        //$tmName = $tmTable->info('name');
				
				$select = $table->select()
            ->setIntegrityCheck(false)
            ->from($rName)
        ;
				
				//Location search
				$select = $this->getLocationItemsSelect(array_merge($params, array('modulename' => 'blog', 'resource_type' => 'blog')), $select);

				if(!empty($params['orderby']) && $params['orderby'] == 'atoz') {
					$select->order($rName .'.title ASC');
				} else if(!empty($params['orderby']) && $params['orderby'] == 'ztoa') {
					$select->order($rName .'.title DESC');
				} else  {
					$select->order( !empty($params['orderby']) ? $rName.'.'.$params['orderby'].' DESC' : $rName.'.creation_date DESC' );
				}

        if( !empty($params['user_id']) && is_numeric($params['user_id']) )
        {
            $owner = Engine_Api::_()->getItem('user', $params['user_id']);
            $select = $this->getProfileItemsSelect($owner, $select);
        } elseif( !empty($params['user']) && $params['user'] instanceof User_Model_User ) {
            $owner = $params['user'];
            $select = $this->getProfileItemsSelect($owner, $select);
        } elseif( isset($params['users']) ) {
            $str = (string) ( is_array($params['users']) ? "'" . join("', '", $params['users']) . "'" : $params['users'] );
            $select->where($rName.'.owner_id in (?)', new Zend_Db_Expr($str));
            if( !engine_in_array($viewer->level_id, $this->_excludedLevels) ) {
                $select->where("view_privacy != ? ", 'owner');
            }

        } else if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('blog.allow.unauthorized', 0)){
            $param = array();
            $select = $this->getItemsSelect($param, $select);
        }

        if( !empty($params['tag']) )
        {
            $select
                //->setIntegrityCheck(false)
                //->from($rName)
                ->joinLeft($tmName, "$tmName.resource_id = $rName.blog_id")
                ->where($tmName.'.resource_type = ?', 'blog')
                ->where($tmName.'.tag_id = ?', $params['tag']);
        }

        if( !empty($params['category']) )
        {
            $select->where($rName.'.category_id = ?', $params['category']);
        }
        
        if( !empty($params['category_id']) )
        {
            $select->where($rName.'.category_id = ?', $params['category_id']);
        }

        if( !empty($params['subcat_id']) )
        {
            $select->where($rName.'.subcat_id = ?', $params['subcat_id']);
        }
        if( !empty($params['subsubcat_id']) )
        {
            $select->where($rName.'.subsubcat_id = ?', $params['subsubcat_id']);
        }

        if( isset($params['draft']) && $params['draft'] != '')
        {
            $select->where($rName.'.draft = ?', $params['draft']);
        }

        //else $select->group("$rName.blog_id");

        // Could we use the search indexer for this?
        if( !empty($params['search']) )
        {
            $select->where($rName.".title LIKE ? OR ".$rName.".body LIKE ?", $params['search'].'%');
        }

        if( !empty($params['start_date']) )
        {
            $select->where($rName.".creation_date > ?", date('Y-m-d', $params['start_date']));
        }

        if( !empty($params['end_date']) )
        {
            $select->where($rName.".creation_date < ?", date('Y-m-d', $params['end_date']));
        }

        if( !empty($params['visible']) )
        {
            $select->where($rName.".search = ?", $params['visible']);
        }
				
				if(!isset($params['showblog']) && empty($params['showblog'])) {
					$select->where('approved =?', 1);
					$select->where('parent_type = ?', 'user');
				}
				if(isset($params['actionName']) && $params['actionName'] == 'manage') {
					$select->where('parent_type = ?', 'user');
				}

        $select = Engine_Api::_()->network()->getNetworkSelect($rName, $select);

        if( !empty($owner) ) {
            return $select;
        }
        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('blog.allow.unauthorized', 0))
            return $this->getAuthorisedSelect($select);
        else
            return $select;
    }

    /**
     * Gets a paginator for blogs
     *
     * @param Core_Model_Item_Abstract $user The user to get the messages for
     * @return Zend_Paginator
     */
    public function getBlogsPaginator($params = array())
    {
        $paginator = Zend_Paginator::factory($this->getBlogsSelect($params));
        if( !empty($params['page']) )
        {
            $paginator->setCurrentPageNumber($params['page']);
        }
        if( !empty($params['limit']) )
        {
            $paginator->setItemCountPerPage($params['limit']);
        }

        if( empty($params['limit']) )
        {
            $page = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('blog.page', 12);
            $paginator->setItemCountPerPage($page);
        }

        return $paginator;
    }

    /**
     * Returns an array of dates where a given user created a blog entry
     *
     * @param User_Model_User user to calculate for
     * @return Array Dates
     */
    public function getArchiveList($spec)
    {
        if( !($spec instanceof User_Model_User) ) {
            return null;
        }

        $localeObject = Zend_Registry::get('Locale');
        if( !$localeObject ) {
            $localeObject = new Zend_Locale();
        }

        $dates = $this->select()
            ->from($this, 'creation_date')
            ->where('owner_type = ?', 'user')
            ->where('owner_id = ?', $spec->getIdentity())
            ->where('draft = ?', 0)
            ->order('blog_id DESC')
            ->query()
            ->fetchAll(Zend_Db::FETCH_COLUMN);

        $time = time();

        $archive_list = array();
        foreach( $dates as $date ) {

            $date = strtotime($date);
            $ltime = localtime($date, true);
            $ltime["tm_mon"] = $ltime["tm_mon"] + 1;
            $ltime["tm_year"] = $ltime["tm_year"] + 1900;

            // LESS THAN A YEAR AGO - MONTHS
            if( $date + 31536000 > $time ) {
                $date_start = mktime(0, 0, 0, $ltime["tm_mon"], 1, $ltime["tm_year"]);
                $date_end = mktime(0, 0, 0, $ltime["tm_mon"] + 1, 1, $ltime["tm_year"]);
                $type = 'month';

                $dateObject = new Zend_Date($date);
                $format = $localeObject->getTranslation('yMMMM', 'dateitem', $localeObject);
                $label = $dateObject->toString($format, $localeObject);
            }
            // MORE THAN A YEAR AGO - YEARS
            else {
                $date_start = mktime(0, 0, 0, 1, 1, $ltime["tm_year"]);
                $date_end = mktime(0, 0, 0, 1, 1, $ltime["tm_year"] + 1);
                $type = 'year';

                $dateObject = new Zend_Date($date);
                $format = $localeObject->getTranslation('yyyy', 'dateitem', $localeObject);
                if( !$format ) {
                    $format = $localeObject->getTranslation('y', 'dateitem', $localeObject);
                }
                $label = $dateObject->toString($format, $localeObject);
            }

            if( !isset($archive_list[$date_start]) ) {
                $archive_list[$date_start] = array(
                    'type' => $type,
                    'label' => $label,
                    'date' => $date,
                    'date_start' => $date_start,
                    'date_end' => $date_end,
                    'count' => 1
                );
            } else {
                $archive_list[$date_start]['count']++;
            }
        }

        return $archive_list;
    }
    
    public function isBlogExists($category_id, $categoryType = 'category_id') {
      return $this->select()
              ->from($this->info('name'), 'blog_id')
              ->where($categoryType . ' = ?', $category_id)
              ->query()
              ->fetchColumn();
    }
}
