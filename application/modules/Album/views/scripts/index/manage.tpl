<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: manage.tpl 10217 2014-05-15 13:41:15Z lucas $
 * @author     Sami
 */
?>

<script type="text/javascript">
//<![CDATA[
  en4.core.runonce.add(function() {
    scriptJquery('#sort').on('change', function(){
      scriptJquery(this).parent('form').submit();
      const formData = new FormData(scriptJquery(this));
      const params = new URLSearchParams(formData);
      let url = scriptJquery(this).attr("action")+"?"+params;
      window.history.pushState({state:'new'},'', url);
      loadAjaxContentApp(url);
    });

    var category_id = scriptJquery('#category_id');
    if( category_id != null ){
      category_id.on('change', function(){
        scriptJquery(this).parent('form').trigger("submit");
      });
    }
  })
//]]>
</script>
<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
  <ul class='manage_listing albums_manage'>
    <?php foreach( $this->paginator as $album ): ?>
      <li class="manage_listing_item">
        <article>
          <div class="manage_listing_thumb">
            <?php echo $this->htmlLink($album->getHref(), $this->itemBackgroundPhoto($album, 'thumb.normal')) ?>
          </div>
          <div class="manage_listing_info">
            <div class="manage_listing_header">
              <div class="manage_listing_title">
                <?php echo $this->htmlLink($album->getHref(), $this->translate($album->getTitle())) ?>
              </div>
              <div class="dropdown options_menu">
                <button class="btn btn-alt" type="button" id="manageoption" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="icon_option_menu"></i>
                </button>
                <ul class="dropdown-menu dropdown-option-menu dropdown-menu-end" aria-labelledby="manageoption">
                  <li><?php echo $this->htmlLink(array('route' => 'album_specific', 'action' => 'editphotos', 'album_id' => $album->album_id, 'slug' => $album->getSlug()), $this->translate('Manage Photos'), array('class' => 'dropdown-item icon_photos')) ?></li>
                  <li><?php echo $this->htmlLink(array('route' => 'album_specific', 'action' => 'edit', 'album_id' => $album->album_id, 'slug' => $album->getSlug()), $this->translate('Edit Settings'), array('class' => 'dropdown-item icon_edit')) ?></li>
                  <li><?php echo $this->htmlLink(array('route' => 'album_specific', 'action' => 'delete', 'album_id' => $album->album_id, 'slug' => $album->getSlug(), 'format' => 'smoothbox'), $this->translate('Delete Album'), array('class' => 'dropdown-item smoothbox icon_delete')) ?></li>
                </ul>
              </div>
            </div>
            <div class="manage_listing_stats">
              <span><i class="icon_photos"></i> <?php echo $this->translate(array('%s photo', '%s photos', $album->count()),$this->locale()->toNumber($album->count())) ?></span>
            </div>
            <div class="manage_listing_desc">
              <?php echo $album->getDescription() ?>
            </div>
            <?php echo $this->partial('_approved_tip.tpl', 'core', array('item' => $album)); ?>
          </div>
        </article>
      </li>
    <?php endforeach; ?>
    </ul>
  <?php if( $this->paginator->count() > 1 ): ?>
    <?php echo $this->paginationControl($this->paginator, null, null); ?>
  <?php endif; ?>
<?php else: ?>
  <div class="no_result_tip">
    <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="<?php echo $this->translate("No Result")?>"></i>
    <p id="no-album">
      <?php echo $this->translate('You do not have any albums yet.');?>
      <?php if( $this->canCreate ): ?>
        <?php $create = $this->translate('Be the first to %1$screate%2$s one!', '<a href="'.$this->url(array('action' => 'upload')).'">', '</a>'); ?>
        <script type="text/javascript">
          if(!DetectMobileQuick() && !DetectIpad()){
            var create = '<?php echo $create ?>';
            var text = document.getElementById('no-album');
            text.innerHTML = text.innerHTML + create ;
          }
        </script>
      <?php endif; ?>
    </p>
  </div>	
<?php endif; ?>


<script type="text/javascript">
  scriptJquery('.core_main_album').parent().addClass('active');
</script>
