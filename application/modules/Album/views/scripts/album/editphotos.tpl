<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: editphotos.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */
?>
<script type="text/javascript">
  var attachAutoSuggest = function (tagId) {
    en4.core.runonce.add(function() {
      scriptJquery('#'+tagId).selectize({
        maxItems: 10,
        valueField: 'label',
        labelField: 'label',
        searchField: 'label',
        create: true,
        load: function(query, callback) {
            if (!query.length) return callback();
            scriptJquery.ajax({
              url: '<?php echo $this->url(array('controller' => 'tag', 'action' => 'suggest'), 'default', true) ?>',
              data: { value: query },
              success: function (transformed) {
                callback(transformed);
              },
              error: function () {
                  callback([]);
              }
            });
        }
      });
    });
  }
</script>
<div class="layout_middle">
  <div class="generic_layout_container">
    <div class="headline">
      <h2><?php echo $this->translate('Photo Albums');?></h2>
      <div class="tabs">
        <?php
          // Render the menu
          echo $this->navigation()
            ->menu()
            ->setContainer($this->navigation)
            ->render();
        ?>
      </div>
    </div>
  </div>
</div>
<div class="layout_middle">
  <div class="generic_layout_container">
    <h3>
      (<?php echo $this->translate(array('%s photo', '%s photos', $this->album->count()),$this->locale()->toNumber($this->album->count())) ?>)
      <?php echo $this->htmlLink($this->album->getHref(), $this->album->getTitle()) ?>
    </h3>
    <form action="<?php echo $this->escape($this->form->getAction()) ?>" method="<?php echo $this->escape($this->form->getMethod()) ?>" class="manage_photos_form form_submit_ajax">
      <?php echo $this->form->album_id; ?>
      <div class='row manage_photos'>
        <?php foreach( $this->paginator as $photo ): ?>
          <div class="col-6 col-md-4">
            <div class="manage_photos_photo mb-3">
              <?php echo $this->htmlLink($photo->getHref(), $this->itemPhoto($photo, 'thumb.normal'))  ?>
            </div>
            <div class="manage_photos_info d-flex flex-wrap gap-1">
              <?php
                $key = $photo->getGuid();
                echo $this->form->getSubForm($key)->render($this);
              ?>
              <div class="manage_photos_cover">
                <input type="radio" name="cover" value="<?php echo $photo->getIdentity() ?>" <?php if( $this->album->photo_id == $photo->getIdentity() ): ?> checked="checked"<?php endif; ?> />
                  <label><?php echo $this->translate('Album Cover');?></label>
              </div>
            </div>
          </div>
          <script type="text/javascript">
            attachAutoSuggest('<?php echo $key . '-tags'; ?>');
          </script>
        <?php endforeach; ?>
      </div>
      <?php echo $this->form->submit->render(); ?>
    </form>
    <?php if( $this->paginator->count() > 0 ): ?>
      <?php echo $this->paginationControl($this->paginator); ?>
    <?php endif; ?>
  </div>
</div>
