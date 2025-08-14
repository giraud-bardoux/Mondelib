<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9915 2013-02-15 01:30:19Z alex $
 * @author     Jung
 */
?>
<?php include APPLICATION_PATH .  '/application/modules/Forum/views/scripts/_adminHeader.tpl';?>
<?php
$settings = Engine_Api::_()->getApi('settings', 'core');
if( $settings->getSetting('user.support.links', 0) == 1 ) {
	echo 'More info: <a href="https://community.socialengine.com/blogs/597/52/forums" target="_blank">See KB article</a>.';
} 
?>	
<br />
<br />

<script type="text/javascript">
  var moveCategoryUp = function(category_id) {
    var url = '<?php echo $this->url(array('action' => 'move-category')) ?>';
    scriptJquery.ajax({
      url : url,
      data : {
        format : 'json',
        category_id : category_id
      },
      success : function() {
        window.location.replace( window.location.href );
      }
    });
  }
  var moveForumUp = function(forum_id) {
    var url = '<?php echo $this->url(array('action' => 'move-forum')) ?>';
    scriptJquery.ajax({
      url : url,
      dataType:'json',
      data : {
        format : 'json',
        forum_id : forum_id
      },
      success : function() {
        window.location.replace( window.location.href );
      }
    });
  }
</script>

<div class="admin_forums_options">
  <a href="<?php echo $this->url(array('action'=>'add-category'));?>" class="admin_link_btn smoothbox admin_forums_createcategory"><?php echo $this->translate("Add Category") ?></a>
  <a href="<?php echo $this->url(array('action'=>'add-forum'));?>" class="admin_link_btn smoothbox admin_forums_create"><?php echo $this->translate("Add Forum") ?></a>
</div>

<br />

<ul class="admin_forum_categories">
  <?php foreach ($this->categories as $category):?>
  <li>
    <div class="admin_forum_categories_info">
      <div class="admin_forum_categories_options">
        <span class="admin_forums_moveup">      
          <?php echo $this->htmlLink('javascript:void(0);', $this->translate('move up'), array('onclick' => 'moveCategoryUp(' . $category->category_id .');'));?> |
        </span>
        <a href="<?php echo $this->url(array('action'=>'edit-category', 'category_id'=>$category->getIdentity()));?>" class="smoothbox"><?php echo $this->translate("edit") ?></a>
        | <a class="smoothbox" href="<?php echo $this->url(array('action'=>'delete-category', 'category_id'=>$category->getIdentity()));?>"><?php echo $this->translate("delete") ?></a>
      </div>
      <div class="admin_forum_categories_title">
        <?php echo $category->getTitle();?>
      </div>
    </div>
    <ul class="admin_forums">
      <?php foreach ($category->getChildren('forum_forum', array('order'=>'order')) as $forum):?>
      <li>
        <div class="admin_forums_options">
          <span class="admin_forums_moveup">
            <?php echo $this->htmlLink('javascript:void(0);', $this->translate('move up'), array('onclick' => 'moveForumUp(' . $forum->getIdentity() .');'));?> |
          </span>
          <a href="<?php echo $this->url(array('action'=>'edit-forum', 'forum_id'=>$forum->getIdentity()));?>" class="smoothbox"><?php echo $this->translate("edit") ?></a>
          | <a class="smoothbox" href="<?php echo $this->url(array('action'=>'delete-forum', 'forum_id'=>$forum->getIdentity()));?>"><?php echo $this->translate("delete") ?></a>
        </div>
        <div class="admin_forums_info">
          <span class="admin_forums_title">
            <?php echo $forum->getTitle();?>
          </span>
          <span class="admin_forums_description">
            <?php echo $forum->getDescription();?>
          </span>
          <span class="admin_forums_moderators">
            <span class="admin_forums_moderators_top">
              <?php echo $this->translate("Moderators") ?>
              (<a href="<?php echo $this->url(array('action'=>'add-moderator', 'format'=>'smoothbox', 'forum_id'=>$forum->getIdentity()));?>" class="smoothbox"><?php echo $this->translate("add") ?></a>)
            </span>
            <span>
              <?php
                $i = 0;
                foreach ($forum->getModeratorList()->getAllChildren() as $moderator)
                {
                  if ($i > 0)
                  {
                    echo ', ';
                  }
                  $i++;
                  echo $moderator->__toString() . ' (<a class="smoothbox" href="' . $this->url(array('action'=>'remove-moderator', 'forum_id'=>$forum->getIdentity(), 'user_id'=>$moderator->getIdentity())) . '">' . $this->translate("remove") . '</a>)';
                }
              ?>
            </span>
          </span>
        </div>
      </li>
<?php endforeach;?>
    </ul>
  </li>
  <?php endforeach;?>
</ul>

