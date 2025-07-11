<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9905 2013-02-14 02:46:28Z alex $
 * @author     John
 */
?>


<div class="admin_home_dashboard_item">
  <div class="admin_quick_heading">
    <h5><?php echo $this->translate("Recent Activity Feeds") ?></h5>
    <?php if(engine_count($this->results) > 0) { ?>
      <div class="dropdwon_section">
        <a href="<?php echo $this->url(array("controller" => 'manage-activity'), 'admin_default', true); ?>" class="view_btn">
          <?php echo $this->translate("View All") ?>
        </a> 
      </div>
    <?php } ?>
  </div>
  <?php if(engine_count($this->results) > 0) { ?>
    <ul class="admin_home_activity">
      <?php foreach($this->results as $result) { ?>
        <li>
          <span class="activity_time"><?php echo $this->shorttimestamp($result->getTimeValue()) ?></span>
          <div class="activity_right_side">
            <?php $contentData = $this->getContent($result, array('resource_id' => $result->resource_id, 'resource_type' => $result->resource_type)); ?>
            <?php if (!empty($contentData[1])) { ?>
             <?php echo $contentData[1]; ?>
            <?php } else { ?>
              <?php echo $this->getActionContent($result)?>
            <?php } ?>
          </div>
          <a class="_view_feed_btn" href="<?php echo $result->getHref(); ?>" target="_blank"><?php echo $this->translate("View Feed"); ?></a>
        </li>
      <?php } ?>
    </ul>
  <?php } else { ?>
  <div class="tip">
    <span><?php echo $this->translate("There are no activity yet."); ?></span>
  </div>
<?php } ?>
</div>
