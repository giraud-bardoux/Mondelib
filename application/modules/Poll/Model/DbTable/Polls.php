<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Polls.php 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Poll_Model_DbTable_Polls extends Core_Model_Item_DbTable_Abstract
{
    protected $_rowClass = 'Poll_Model_Poll';

    public function getPollSelect($params = array())
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        // Setup
        $params = array_merge(array(
            'user_id' => null,
            'order' => 'creation_date',
            'search' => '',
            'closed' => 0,
        ), $params);

        $table = Engine_Api::_()->getItemTable('poll');
        $tableName = $table->info('name');

        $select = $table
            ->select()
            ->setIntegrityCheck(false)
            ->from($tableName);
				
				//Location search
				$select = $this->getLocationItemsSelect(array_merge($params, array('modulename' => 'poll', 'resource_type' => 'poll')), $select);
				
        // User
        if( !empty($params['user_id']) && is_numeric($params['user_id']) ) {
            $owner = Engine_Api::_()->getItem('user', $params['user_id']);
            $select = $this->getProfileItemsSelect($owner, $select);
        } elseif( isset($params['users']) && is_array($params['users']) ) {
            if( empty($params['users']) ) {
                return $select ->where('1 != 1');
            }
            $select
                ->where('user_id IN (?)', $params['users']);

            if( !engine_in_array($viewer->level_id, $this->_excludedLevels) ) {
                $select->where("view_privacy != ? ", 'owner');
            }

        } else {
            $param = array();
            $select = $this->getItemsSelect($param, $select);
        }

        // Browse
        if( isset($params['browse']) ) {
            $select->where('search = ?', (int) (bool) $params['browse']);
        }
        
        if( !empty($params['category']) )
        {
            $select->where('category_id = ?', $params['category']);
        }
        
        if( !empty($params['category_id']) )
        {
            $select->where('category_id = ?', $params['category_id']);
        }

        if( !empty($params['subcat_id']) )
        {
            $select->where('subcat_id = ?', $params['subcat_id']);
        }
        if( !empty($params['subsubcat_id']) )
        {
            $select->where('subsubcat_id = ?', $params['subsubcat_id']);
        }

        // Closed
        if( !isset($params['closed']) || null === $params['closed'] ) {
            $params['closed'] = 0;
        }
        $select
            ->where('is_closed = ?', $params['closed']);

				if(!empty($params['order']) && $params['order'] == 'atoz') {
					$select->order($tableName .'.title ASC');
				} else if(!empty($params['order']) && $params['order'] == 'ztoa') {
					$select->order($tableName .'.title DESC');
				} else  {
					$select->order( !empty($params['order']) ? $tableName.'.'.$params['order'].' DESC' : $tableName.'.creation_date DESC' );
				}

        if( !empty($params['search']) ) {
            // Add search table
            $searchTable = Engine_Api::_()->getDbtable('search', 'core');
            $db = $searchTable->getAdapter();
            $sName = $searchTable->info('name');
            $rName = $tableName;
            $select
                ->joinRight($sName, $sName . '.id=' . $rName . '.poll_id', null)
                ->where($sName . '.type = ?', 'poll')
                ->where(new Zend_Db_Expr($db->quoteInto('MATCH(' . $sName . '.`title`, ' . $sName . '.`description`, ' . $sName . '.`keywords`, ' . $sName . '.`hidden`) AGAINST (? IN BOOLEAN MODE)', $params['search'])))
                //->order(new Zend_Db_Expr($db->quoteInto('MATCH(' . $sName . '.`title`, ' . $sName . '.`description`, ' . $sName . '.`keywords`, ' . $sName . '.`hidden`) AGAINST (?) DESC', $params['text'])))
            ;
        }
        
				if(!isset($params['showpoll']) && empty($params['showpoll'])) {
					$select->where($tableName.'.approved =?', 1);
					$select->where($tableName.'.parent_type = ?', 'user');
				}
				if(isset($params['actionName']) && $params['actionName'] == 'manage') {
					$select->where($tableName.'.parent_type = ?', 'user');
				}
				
        $select = Engine_Api::_()->network()->getNetworkSelect($tableName, $select, 'user_id');

        if( !empty($owner) ) {
            return $select;
        }
        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.allow.unauthorized', 0)){
            return $select;
        }else{
            return $this->getAuthorisedSelect($select);
        }

    }

    /**
     * Gets a paginator for polls
     *
     * @param Core_Model_Item_Abstract $user The user to get the messages for
     * @return Zend_Paginator
     */
    public function getPollsPaginator($params = array())
    {
        return Zend_Paginator::factory($this->getPollSelect($params));
    }

    public function getItemsSelect($params, $select = null)
    {
        if( $select == null ) {
            $select = $this->select();
        }
        if( isset($params['search']) ) {
            $select->where("search = ?", $params['search']);
        }
        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.allow.unauthorized', 0)){
            return $select;
        }
        $table = $this->info('name');
        $registeredPrivacy = array('everyone', 'registered');
        $viewer = Engine_Api::_()->user()->getViewer();
        if( $viewer->getIdentity() && !engine_in_array($viewer->level_id, $this->_excludedLevels) ) {
            $viewerId = $viewer->getIdentity();
            $netMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
            $viewerNetwork = $netMembershipTable->getMembershipsOfIds($viewer);
            if( !empty($viewerNetwork) ) {
                array_push($registeredPrivacy,'owner_network');
            }
            $friendsIds = $viewer->membership()->getMembersIds();
            $friendsOfFriendsIds = $friendsIds;
            foreach( $friendsIds as $friendId ) {
                $friend = Engine_Api::_()->getItem('user', $friendId);
                $friendMembersIds = $friend->membership()->getMembersIds();
                $friendsOfFriendsIds = array_merge($friendsOfFriendsIds, $friendMembersIds);
            }
        }
        if( !$viewer->getIdentity() ) {
            $select->where("view_privacy = ?", 'everyone');
        } elseif( !engine_in_array($viewer->level_id, $this->_excludedLevels) ) {
            $select->Where("$table.user_id = ?", $viewerId)
                ->orwhere("view_privacy IN (?)", $registeredPrivacy);
            if( !empty($friendsIds) ) {
                $select->orWhere("view_privacy = 'owner_member' AND $table.user_id IN (?)", $friendsIds);
            }
            if( !empty($friendsOfFriendsIds) ) {
                $select->orWhere("view_privacy = 'owner_member_member' AND $table.user_id IN (?)", $friendsOfFriendsIds);
            }
            if( empty($viewerNetwork) && !empty($friendsOfFriendsIds) ) {
                $select->orWhere("view_privacy = 'owner_network' AND $table.user_id IN (?)", $friendsOfFriendsIds);
            }
            $subquery = $select->getPart(Zend_Db_Select::WHERE);
            $select ->reset(Zend_Db_Select::WHERE);
            $select ->where(implode(' ',$subquery));
        }

        return $select;
    }

    public function getProfileItemsSelect($owner, $select = null)
    {
        if( $select == null ) {
            $select = $this->select();
        }
        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.allow.unauthorized', 0)){
            return $select;
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        $viewerId = $viewer->getIdentity();
        if( !empty($owner) ) {
            $ownerId = $owner->getIdentity();
        }
        $isOwnerOrAdmin = false;
        if( !empty($viewerId) && ($ownerId == $viewerId || engine_in_array($viewer->level_id, $this->_excludedLevels)) ) {
            $isOwnerOrAdmin = true;
        }
        if( !empty($owner) && $owner instanceof Core_Model_Item_Abstract ) {
            $select
                ->where('user_id = ?', $ownerId);

            if( $isOwnerOrAdmin ) {
                return $select;
            }
            $isOwnerViewerLinked = true;
            if( $viewer->getIdentity() ) {
                $restrictedPrivacy = array('owner');
                $ownerFriendsIds = $owner->membership()->getMembersIds();
                if( !engine_in_array($viewerId, $ownerFriendsIds) ) {
                    array_push($restrictedPrivacy, 'owner_member');
                    $friendsOfFriendsIds = array();
                    foreach( $ownerFriendsIds as $friendId ) {
                        $friend = Engine_Api::_()->getItem('user', $friendId);
                        $friendMembersIds = $friend->membership()->getMembersIds();
                        $friendsOfFriendsIds = array_merge($friendsOfFriendsIds, $friendMembersIds);
                    }
                    if( !engine_in_array($viewerId, $friendsOfFriendsIds) ) {
                        array_push($restrictedPrivacy, 'owner_member_member');
                        $netMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
                        $viewerNetwork = $netMembershipTable->getMembershipsOfIds($viewer);
                        $ownerNetwork = $netMembershipTable->getMembershipsOfIds($owner);
                        $checkViewer = array_intersect($viewerNetwork, $ownerNetwork);
                        if( empty($checkViewer) ) {
                            $isOwnerViewerLinked = false;
                        }
                    }
                }
                if( $isOwnerViewerLinked ) {
                    $select->where("view_privacy NOT IN (?)", $restrictedPrivacy);
                    return $select;
                }
            }
            $select->where("view_privacy = ?", 'everyone');
        }
        return $select;
    }
    
    public function isPollExists($category_id, $categoryType = 'category_id') {
      return $this->select()
              ->from($this->info('name'), 'poll_id')
              ->where($categoryType . ' = ?', $category_id)
              ->query()
              ->fetchColumn();
    }
}
