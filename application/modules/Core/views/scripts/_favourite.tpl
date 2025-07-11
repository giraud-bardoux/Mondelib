<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _favourite.tpl 10245 2017-01-02 18:08:24Z lucas $
 * @author     John
 */
?>
<?php 
  $item = $this->item;
  $class = $this->class;
  $viewer = $this->viewer();
  $viewer_id = $viewer->getIdentity();
  $isFavourite = Engine_Api::_()->getDbTable('favourites', 'core')->isFavourite(array('resource_type' => $item->getType(), 'resource_id' => $item->getIdentity())); 
?>
<?php if(!empty($viewer_id)): ?>
  <a href="javascript:;" class="content_favourite favourite_<?php echo $item->getType(); ?>_<?php echo $item->getIdentity(); ?> <?php echo $class; ?> <?php echo ($isFavourite)  ? 'button_active' : '' ?>"  data-id="<?php echo $item->getIdentity() ; ?>" data-type="<?php echo $item->getType() ; ?>"><i class="fa fa-heart"></i><span><?php echo $item->favourite_count; ?></span></a>
<?php endif; ?>