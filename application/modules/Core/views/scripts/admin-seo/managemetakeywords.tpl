<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: managemetakeywords.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'childMenuItemName' => 'core_admin_main_settings_seo_managemetakeywords')); ?>

<h2 class="page_heading"><?php echo $this->translate('SEO Settings') ?></h2>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<h3 style="margin-bottom:6px;"><?php echo $this->translate("Meta Tags Settings for Widgetized Pages"); ?></h3>
<p><?php echo $this->translate("Here, you can enter the meta title, meta description and meta image for Search Engines for all the widgetized pages on your website.<br />The View pages of all the content and member profile page will have their own meta tags, so these tags will be appended to their tags automatically."); ?></p>

<div class='admin_search'>
  <?php echo $this->formFilter->render($this) ?>
</div>
<div class='admin_results'>
  <div>
    <?php $count = $this->paginator->getTotalItemCount() ?>
    <?php echo $this->translate(array("%s entry found.", "%s entries found.", $count), $this->locale()->toNumber($count)) ?>
  </div>
</div>
<?php if(is_countable($this->paginator) &&  engine_count($this->paginator)): ?>
  <form id='multidelete_form'>
    <table class='admin_table' style="width:100%;">
      <thead>
        <tr>
          <th align="left">
            <?php echo $this->translate("Page ID"); ?>
          </th>
          <th align="left">
            <?php echo $this->translate("Page Name"); ?>
          </th>
          <th align="left">
            <?php echo $this->translate("Meta Title"); ?>
          </th>
          <th align="left">
            <?php echo $this->translate("Meta Description"); ?>
          </th>
          <th align="left">
            <?php echo $this->translate("Meta Image"); ?>
          </th>
          <th align="left">
            <?php echo $this->translate("Options"); ?>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($this->paginator as $item): ?>
          <tr>
            <td><?php if( !empty($item->page_id) ){ echo $item->page_id; } ?></td>
            <td>
              <?php if( !empty($item->page_id) ) { ?> 
                <?php $url = $this->url(array('module' => 'core', 'controller' => 'content', 'action' => 'index'), 'admin_default').'?page='.$item->page_id;?>
                <a href="<?php echo $url; ?>" target="_blank"><?php echo $item->displayname; ?></a>
              <?php }else { ?>
              <?php echo '-'; } ?>
            </td>
            <td><?php if( !empty($item->title) ){ echo $item->title; } else { echo '-'; } ?></td>
            <td><?php if( !empty($item->description) ){ echo $item->description; }else { echo '-'; } ?></td>
            <td>
              <?php if(!empty($item->meta_image)): ?>
                <img height="100px;" width="100px;" alt="" src="<?php echo Engine_Api::_()->core()->getFileUrl($item->meta_image); ?>" />
              <?php else: ?>
                <?php echo "---"; ?>
              <?php endif; ?>
            </td>
            <td>
              <a href="<?php echo $this->url(array('action' => 'edit', 'page_id' => $item->page_id)) ?>"><?php echo $this->translate("Edit") ?>
              </a>
            </td>
          </tr>
        <?php  endforeach; ?>
      </tbody>
    </table>
  </form>
  <div>
    <?php echo $this->paginationControl($this->paginator); ?>
  </div>
<?php else: ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("No pages found with this criteria.") ?>
    </span>
  </div>
<?php endif; ?>
<script type="application/javascript">
  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_settings_seo').addClass('active');
</script>
