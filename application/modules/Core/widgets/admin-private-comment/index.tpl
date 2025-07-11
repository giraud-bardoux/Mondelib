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
    <h5> <?php echo $this->translate("Recent Comments on Contents") ?></h5>
    <?php if(engine_count($this->comments) > 0) { ?>
      <div class="dropdwon_section">
        <a href="<?php echo $this->url(array("controller" => 'manage-comments'), 'admin_default', true); ?>" class="view_btn">
          <?php echo $this->translate("View All") ?>
        </a> 
      </div>
    <?php } ?>
  </div>
  <table class="admin_message_common_table">
    <?php if(engine_count($this->comments) > 0) { ?>
      <thead>
        <tr>
          <th><?php echo $this->translate("Member Name") ?></th>
          <th><?php echo $this->translate("Comment") ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($this->comments as $item) { ?>
          <?php $poster = Engine_Api::_()->getItem($item->poster_type, $item->poster_id); ?>
          <?php $resource = Engine_Api::_()->getItem($item->resource_type, $item->resource_id); ?>
          <tr>
            <td>
              <div class="admin_table_img">
                <figure>
                  <a href="<?php echo $poster->getHref(); ?>"><?php echo $this->itemBackgroundPhoto($poster, 'thumb.icon'); ?></a>
                </figure>
                <div class="admin_table_right">
                    <span><a href="<?php echo $poster->getHref(); ?>"><?php echo $poster->getTitle(); ?></a></span>
                    <span><?php echo $this->timestamp($item->creation_date) ?></span>
                </div>
              </div>
            </td>
            <td><?php echo $this->partial('_activitycommentcontent.tpl', 'comment', array('comment' => $item)); ?><?php //echo $this->string()->truncate($item->body, 20); ?> <b class="_message"> <?php echo $this->translate("in %s", ucfirst($resource->getShortType())); ?>  </b>  </td> 
          </tr>
        <?php } ?>
      </tbody>
    <?php } else { ?>
      <div class="tip">
        <span><?php echo $this->translate("There are no comments on contents yet."); ?></span>
      </div>
    <?php } ?>
  </table>
</div>

<div class="admin_home_dashboard_item">
  <div class="admin_quick_heading">
    <h5> <?php echo $this->translate("Recent Comments on Activity Feeds") ?></h5>
    <?php if(engine_count($this->activityComments) > 0) { ?>
      <div class="dropdwon_section">
        <a href="<?php echo $this->url(array("controller" => 'manage-comments', "action" => "activity"), 'admin_default', true); ?>" class="view_btn">
          <?php echo $this->translate("View All") ?>
        </a> 
      </div>
    <?php } ?>
  </div>
  <table class="admin_message_common_table">
    <?php if(engine_count($this->activityComments) > 0) { ?>
      <thead>
        <tr>
          <th><?php echo $this->translate("Member Name") ?></th>
          <th><?php echo $this->translate("Comment") ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($this->activityComments as $item) { ?>
          <?php $poster = Engine_Api::_()->getItem($item->poster_type, $item->poster_id); ?>
          <?php $resource = Engine_Api::_()->getItem("activity_comment", $item->resource_id); ?>
          <tr>
            <td>
              <div class="admin_table_img">
                <figure>
                  <a href="<?php echo $poster->getHref(); ?>"><?php echo $this->itemBackgroundPhoto($poster, 'thumb.icon'); ?></a>
                </figure>
                <div class="admin_table_right">
                    <span><a href="<?php echo $poster->getHref(); ?>"><?php echo $poster->getTitle(); ?></a></span>
                    <span><?php echo $this->timestamp($item->creation_date) ?></span>
                </div>
              </div>
            </td>
            <td><?php echo $this->partial('_activitycommentcontent.tpl', 'comment', array('comment' => $item)); ?><?php //echo $this->string()->truncate($item->body, 20); ?> </td> 
          </tr>
        <?php } ?>
      </tbody>
    <?php } else { ?>
      <div class="tip">
        <span><?php echo $this->translate("There are no comments on activity feeds yet."); ?></span>
      </div>
    <?php } ?>
  </table>
</div>
