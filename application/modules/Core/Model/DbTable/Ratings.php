<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Ratings.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
class Core_Model_DbTable_Ratings extends Engine_Db_Table
{
  protected $_rowClass = "Core_Model_Rating";
  public function checkRated($params = array())
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    $select = $this->select()
      ->setIntegrityCheck(false)
      ->where('resource_id = ?', $params['resource_id'])
      ->where('resource_type = ?', $params['resource_type'])
      ->where('user_id = ?', $viewer_id)
      ->limit(1);
    $row = $this->fetchAll($select);

    if (engine_count($row) > 0)
      return true;
    return false;
  }

  public function getRating($params = array())
  {
    $rating_sum = $this->select()
      ->from($this->info('name'), new Zend_Db_Expr('SUM(rating)'))
      ->where('resource_id = ?', $params['resource_id'])
      ->where('resource_type = ?', $params['resource_type'])
      ->group('resource_id')
      ->group('resource_type')
      ->query()
      ->fetchColumn(0);

    $total = $this->ratingCount($params);
    if ($total)
      $rating = $rating_sum / $this->ratingCount($params);
    else
      $rating = 0;

    return $rating;
  }

  public function ratingCount($params = array())
  {

    $rName = $this->info('name');
    $select = $this->select()
      ->from($rName)
      ->where($rName . '.resource_id = ?', $params['resource_id'])
      ->where($rName . '.resource_type = ?', $params['resource_type']);
    $row = $this->fetchAll($select);
    $total = engine_count($row);
    return $total;
  }

  public function setRating($params = array())
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer_id = $viewer->getIdentity();

    $rName = $this->info('name');
    $select = $this->select()
      ->from($rName)
      ->where($rName . '.resource_id = ?', $params['resource_id'])
      ->where($rName . '.resource_type = ?', $params['resource_type'])
      ->where($rName . '.user_id = ?', $viewer_id);
    $row = $this->fetchRow($select);
    if (empty($row)) {
      // create rating
      Engine_Api::_()->getDbTable('ratings', 'core')->insert(
        array(
          'resource_id' => $params['resource_id'],
          'resource_type' => $params['resource_type'],
          'user_id' => $viewer_id,
          'rating' => $params['rating']
        )
      );
    }
  }
}
