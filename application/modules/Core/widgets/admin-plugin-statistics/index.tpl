<?php
/**
* SocialEngine
*
* @category   Application_Core
* @package    Core
* @copyright  Copyright 2006-2021 Webligo Developments
* @license    http://www.socialengine.com/license/
* @version    $Id: index.tpl 9905 2021-11-09 $
* @author     John
*/
?>
<div class="admin_home_dashboard_item">
  <h3 class="header_section">
    <?php echo $this->translate("Contents Statistics from Plugins") ?>
  </h3>
  <ul class="admin_home_dashboard_links">
    <li>
      <a href="javascript:void(0)">
        <span class="_title"> <?php echo $this->translate("Members") ?> </span> 
        <span  class="_count"> <?php echo $this->locale()->toNumber($this->member_count); ?></span> 
      </a>
    </li>
    <li>
      <a href="javascript:void(0)">
        <span class="_title"> <?php echo $this->translate("Friendships") ?> </span> 
      <span  class="_count"> <?php echo $this->locale()->toNumber($this->friend_count) ?></span> 
      </a>
    </li>
    <li>
      <a href="javascript:void(0)">
        <span class="_title"> <?php echo $this->translate("Posts") ?> </span> 
        <span  class="_count"> <?php echo $this->locale()->toNumber($this->post_count) ?></span> 
      </a>
    </li>
    <li>
      <a href="javascript:void(0)">
        <span class="_title"> <?php echo $this->translate("Comments") ?> </span> 
      <span  class="_count"> <?php echo $this->locale()->toNumber($this->comment_count) ?></span> 
      </a>
    </li>
    <?php $i = 1;
      $j = 1;
      $alreadyShow = array();
    ?>
    <?php if (is_array($this->hooked_stats) && !empty($this->hooked_stats)): ?>
      <?php foreach ($this->hooked_stats as $key => $value): ?>
        <?php if ($value > 0): ?>
          <?php if($i == 4) continue; 
            $alreadyShow[] = $key;
          ?>
            <li>
              <a href="javascript:void(0)">
                <span class="_title"><?php echo ucfirst($this->translate(array($key, $key, $value))); ?></span>
                <span  class="_count"><?php echo $this->locale()->toNumber($value) ?></span>
              </a>
            </li>
        <?php $i++; endif; ?>
      <?php  endforeach; ?>
    <?php endif; ?>
    
    <?php //Show hide code ?>
    <?php if (engine_count($alreadyShow) > 0 && is_array($this->hooked_stats) && !empty($this->hooked_stats)): ?>
      <?php foreach ($this->hooked_stats as $key => $value): ?>
        <?php if ($value > 0 && !engine_in_array($key, $alreadyShow)): ?>
            <li class="collapse admin_plugin_stats">
              <a href="javascript:void(0)">
                <span class="_title"><?php echo ucfirst($this->translate(array($key, $key, $value))); ?></span>
                <span  class="_count"><?php echo $this->locale()->toNumber($value) ?></span>
              </a>
            </li>
        <?php $j++ ;endif; ?>
      <?php  endforeach; ?>
    <?php endif; ?>
  </ul>
  <?php if ($j >= 2 && is_array($this->hooked_stats) && !empty($this->hooked_stats)): ?>
    <div class="admin_core_hide_btn">
      <button class="admin_hide_button collapsed" type="button" data-bs-toggle="collapse" data-bs-target=".admin_plugin_stats" aria-expanded="false" aria-controls="collapseExample">
        <span class="admin_show_btn"> <?php echo $this->translate("Show More") ?> <i class="fas fa-angle-down"></i></span>
        <span  class="admin_hide_btn"> <?php echo $this->translate("Show Less") ?> <i class="fas fa-angle-up"></i></span>
      </button>
    </div>
  <?php endif; ?>
</div>
