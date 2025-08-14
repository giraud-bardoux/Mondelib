<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: user-search.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */
?>
<?php if (engine_count($this->paginator) > 1):?>
<?php echo $this->translate("Your search returned too many results; only displaying the first 20.") ?>
<?php endif;?>
<?php foreach ($this->paginator as $user):?>
<?php if (!$this->forum->isModerator($user)):?>
  <li>
    <a href='javascript:addModerator(<?php echo $user->getIdentity();?>);'><?php echo $user->getTitle();?></a>
  </li>
<?php endif;?>
<?php endforeach;?>
