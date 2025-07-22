<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Events.php 9829 2012-11-27 01:13:07Z richard $
 * @author     Sami
 */

/**
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     Sami
 */
class Event_Model_DbTable_Events extends Core_Model_Item_DbTable_Abstract
{
    protected $_rowClass = "Event_Model_Event";

    public function getEventPaginator($params = array())
    {
        return Zend_Paginator::factory($this->getEventSelect($params));
    }

    public function getEventSelect($params = array())
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $table = Engine_Api::_()->getItemTable('event');
        $tableName = $table->info('name');
        $select = $table->select()
            ->setIntegrityCheck(false)
            ->from($tableName)
        ;
        
        //Location search
				$select = $this->getLocationItemsSelect(array_merge($params, array('modulename' => 'event', 'resource_type' => 'event')), $select);
				
        if( isset($params['search']) ) {
            $select->where('search = ?', (bool) $params['search']);
        }

        if( isset($params['owner']) && $params['owner'] instanceof Core_Model_Item_Abstract ) {
            $owner = $params['owner'];
            $select = $this->getProfileItemsSelect($owner, $select);
        } elseif( isset($params['user_id']) && !empty($params['user_id']) ) {
            $owner = Engine_Api::_()->getItem('user', $params['user_id']);
            $select = $this->getProfileItemsSelect($owner, $select);
        } elseif( isset($params['users']) && is_array($params['users']) ) {
            $users = array();
            foreach( $params['users'] as $user_id ) {
                if( is_int($user_id) && $user_id > 0 ) {
                    $users[] = $user_id;
                }
            }
            // if users is set yet there are none, $select will always return an empty rowset
            if( empty($users) ) {
                return $select->where('1 != 1');
            } else {
                $select->where("user_id IN (?)", $users);

                if( !engine_in_array($viewer->level_id, $this->_excludedLevels) ) {
                    $select->where("view_privacy != ? ", 'owner');
                    $getViewer = $this->getViewerEvents();
                    if( empty($getViewer) ) {
                        $select->where("view_privacy != ? ", 'member');
                    } else {
                        $select->where("view_privacy != 'member' OR (view_privacy = 'member' AND event_id IN (?))", $this->getViewerEvents());
                    }
                }

            }
        } else if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('event.allow.unauthorized', 0)){
            $param = array();
            $select = $this->getItemsSelect($param, $select);
        }

        // Category
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

        //Full Text
        if( !empty($params['search_text']) ) {
            $search_text = $params['search_text'];
            $select->where($tableName.".description LIKE '%$search_text%' or ".$tableName.".title LIKE '%$search_text%'");
        }
        
        if(!isset($params['showevent']) && empty($params['showevent'])) {
					$select->where('approved =?', 1);
					$select->where('parent_type = ?', 'user');
				}

        // Endtime
        if( isset($params['past']) && !empty($params['past']) ) {
            $select->where("endtime <= FROM_UNIXTIME(?)", time());
        } elseif( isset($params['future']) && !empty($params['future']) ) {
            $select->where("endtime > FROM_UNIXTIME(?)", time());
        } 

				if(!empty($params['order']) && $params['order'] == 'atoz') {
					$select->order($tableName .'.title ASC');
				} else if(!empty($params['order']) && $params['order'] == 'ztoa') {
					$select->order($tableName .'.title DESC');
				} else  {
					$select->order( !empty($params['order']) ? $tableName.'.'.$params['order'].' DESC' : $tableName.'.starttime ASC' );
				}

        $select = Engine_Api::_()->network()->getNetworkSelect($table->info('name'), $select, 'user_id');

        if( !empty($owner) ) {
            return $select;
        }

        if (!Engine_Api::_()->getApi('settings', 'core')->getSetting('event.allow.unauthorized', 0)) {
            return $this->getAuthorisedSelect($select);
        }else
            return $select;
    }

    public function getItemsSelect($params, $select = null)
    {
        if( $select == null ) {
            $select = $this->select();
        }
        if( isset($params['search']) ) {
            $select->where("search = ?", $params['search']);
        }
        if (Engine_Api::_()->getApi('settings', 'core')->getSetting('event.allow.unauthorized', 0)){
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

            $viewerEvents = $this->getViewerEvents();
        }

        if( !$viewer->getIdentity() ) {
            $select->where("view_privacy = ?", 'everyone');
        } elseif( !engine_in_array($viewer->level_id, $this->_excludedLevels) ) {
            $select->Where("$table.parent_id = ?", $viewerId)
                ->orwhere("view_privacy IN (?)", $registeredPrivacy);

            if( !empty($friendsIds) ) {
                $select->orWhere("view_privacy = 'owner_member' AND $table.parent_id IN (?)", $friendsIds);
            }

            if( !empty($friendsOfFriendsIds) ) {
                $select->orWhere("view_privacy = 'owner_member_member' AND $table.parent_id IN (?)", $friendsOfFriendsIds);
            }

            if( empty($viewerNetwork) && !empty($friendsOfFriendsIds) ) {
                $select->orWhere("view_privacy = 'owner_network' AND $table.parent_id IN (?)", $friendsOfFriendsIds);
            }

            if( !empty($viewerEvents) ) {
                $select->orWhere("view_privacy != 'owner' AND $table.event_id IN (?)", $viewerEvents);
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
                ->where('parent_id = ?', $ownerId)
                ->order('starttime DESC')
            ;

            if( $isOwnerOrAdmin ) {
                return $select;
            }

            $isOwnerViewerLinked = true;

            if( $viewer->getIdentity() ) {
                $viewerEvents = $this->getViewerEvents();
                $restrictedPrivacy = array('owner', 'member');

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
                    if( !empty($viewerEvents) ) {
                        $select->orWhere("view_privacy != 'owner' AND parent_id = $ownerId AND event_id IN (?)", $viewerEvents);
                    }
                    return $select;
                }
            }
            
            if( !empty($viewerEvents) ) {
                $select->where("view_privacy = 'everyone' OR (view_privacy != 'owner' AND parent_id = $ownerId AND event_id IN (?))", $viewerEvents);
            } else {
              $select->where("view_privacy = ?", 'everyone');
            }
        }

        return $select;
    }

    public function getViewerEvents()
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $eventMembershipTable = Engine_Api::_()->getDbtable('membership', 'event');
        return $eventMembershipTable->getMembershipsOfIds($viewer);
    }
    
    public function isEventExists($category_id, $categoryType = 'category_id') {
      return $this->select()
              ->from($this->info('name'), 'event_id')
              ->where($categoryType . ' = ?', $category_id)
              ->query()
              ->fetchColumn();
    }
}
