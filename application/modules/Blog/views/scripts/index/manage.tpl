<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: manage.tpl 9987 2013-03-20 00:58:10Z john $
 * @author     Jung
 */
?>

<script type="text/javascript">
  var pageAction =function(page){
    scriptJquery('#page').val(page);
    scriptJquery('#filter_form').submit();
  }
</script>

<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
  <ul class="manage_listing blogs_manage">
    <?php foreach( $this->paginator as $item ): ?>
      <li class="manage_listing_item">
        <article>
          <div class='manage_listing_thumb'>
            <?php echo $this->htmlLink($item->getHref(), $this->itemBackgroundPhoto($item, 'thumb.profile')) ?>
          </div>
          <div class='manage_listing_info'>
            <div class="manage_listing_header">
              <div class='manage_listing_title'>
                <?php echo $this->htmlLink($item->getHref(), $item->getTitle()) ?><?php if(!empty($item->draft)) { ?><i class="icon_draft" data-bs-toggle="tooltip" title="<?php echo $this->translate("Draft")?>"></i><?php } ?>
              </div>
              <div class="dropdown options_menu">
                <button class="btn btn-alt" type="button" id="manageoption" data-bs-toggle="dropdown" aria-expanded="false"><i class="icon_option_menu"></i></button>
                <ul class="dropdown-menu dropdown-option-menu dropdown-menu-end" aria-labelledby="manageoption">
                  <li><?php echo $this->htmlLink(array('action' => 'edit', 'blog_id' => $item->getIdentity(), 'route' => 'blog_specific', 'reset' => true), $this->translate('Edit Entry'), array('class' => 'dropdown-item icon_edit')) ?></li>
                  <li><?php echo $this->htmlLink(array('route' => 'default', 'module' => 'blog', 'controller' => 'index', 'action' => 'delete', 'blog_id' => $item->getIdentity(), 'format' => 'smoothbox'), $this->translate('Delete Entry'), array('class' => 'dropdown-item smoothbox icon_delete')); ?>
                </ul>
              </div>
            </div>
            <div class="manage_listing_owner">
              <span><?php echo $this->translate('Posted');?> <?php echo $this->timestamp(strtotime($item->creation_date)) ?></span>
            </div>
            
            <?php if( $item->comment_count > 0 || $item->like_count > 0 || $item->view_count > 0) :?>
              <div class='manage_listing_stats'>              
                <?php if( $item->comment_count > 0 ) :?>
                  <span><i class="icon_comment"></i><?php echo $this->translate(array('%s Comment', '%s Comments', $item->comment_count), $this->locale()->toNumber($item->comment_count)) ?></span>
                <?php endif; ?>
                <?php if( $item->like_count > 0 ) :?>
                  <span><i class="icon_like"></i><?php echo $this->translate(array('%s Like', '%s Likes', $item->like_count), $this->locale()->toNumber($item->like_count)) ?></span>
                <?php endif; ?>
                <?php if( $item->view_count > 0 ) :?>
                  <span class="views_blog"><i class="icon_view"></i><?php echo $this->translate(array('%s View', '%s Views', $item->view_count), $this->locale()->toNumber($item->view_count)) ?></span>
                <?php endif; ?>
              </div>
            <?php endif; ?>
            <div class='manage_listing_desc'>
              <?php $readMore = ' ' . $this->translate('Read More') . '...';?>
              <?php echo $this->string()->truncate($this->string()->stripTags($item->body), 180, $this->htmlLink($item->getHref(), $readMore) ) ?>
            </div>
            <?php echo $this->partial('_approved_tip.tpl', 'core', array('item' => $item)); ?>
          </div>
        </article>
      </li>
    <?php endforeach; ?>
  </ul>

<?php elseif($this->search): ?>
  <div class="no_result_tip">
    <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="<?php echo $this->translate("No Result")?>"></i>
    <p><?php echo $this->translate('You do not have any blog entries that match your search criteria.');?></p>
  </div>
<?php else: ?>
  <div class="no_result_tip">
    <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="<?php echo $this->translate("No Result")?>"></i>
    <p><?php echo $this->translate('You do not have any blog entries.');?></p>
    <?php if( $this->canCreate ): ?>
      <p><?php echo $this->translate('Get started by %1$swriting%2$s a new entry.', '<a href="'.$this->url(array('action' => 'create'), 'blog_general').'">', '</a>'); ?></p>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php echo $this->paginationControl($this->paginator, null, null, array(
  'pageAsQuery' => true,
  'query' => $this->formValues,
  //'params' => $this->formValues,
)); ?>


<script type="text/javascript">
  scriptJquery('.core_main_blog').parent().addClass('active');
</script>
