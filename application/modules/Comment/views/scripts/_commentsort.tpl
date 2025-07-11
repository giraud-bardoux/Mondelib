<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _commentsort.tpl 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<?php $commentreverseorder = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.commentreverseorder', 1); ?>
<?php $searchType = $this->searchType ? $this->searchType : (empty($commentreverseorder) ? 'oldest' : 'newest'); ?>
<div class="comment_sort">
  <div class="comment_pulldown_wrapper" data-actionid="<?php echo $this->action->getIdentity(); ?>">
    <a href="javascript:void(0);" class="search_advcomment_txt dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false"><span><b><?php echo $this->translate('Sort By:') ?></b><?php echo $searchType == 'newest' ? $this->translate("Newest") : $this->translate('Oldest'); ?> </span></a>
    <ul class="dropdown-menu dropdown-menu-end search_adv_comment">
      <li><a href="javascript:;" data-type="newest" class="dropdown-item search_adv_comment_a <?php echo ($searchType == 'newest' ? 'active' : '') ?>"><?php echo $this->translate("Newest"); ?></a>
      </li>
      <li><a href="javascript:;" data-type="oldest" class="dropdown-item search_adv_comment_a <?php echo ($searchType == 'oldest' ? 'active' : '') ?>"><?php echo $this->translate("Oldest"); ?></a>
      </li>
    </ul>
  </div>
</div>