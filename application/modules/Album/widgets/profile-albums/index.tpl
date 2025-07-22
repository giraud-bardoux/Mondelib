<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */
?>

<script type="text/javascript">
  en4.core.runonce.add(function(){

    <?php if( !$this->renderOne ): ?>
    var anchor = scriptJquery('#profile_albums').parent();
    document.getElementById('profile_albums_previous').style.display = '<?php echo ( $this->paginator->getCurrentPageNumber() == 1 ? 'none' : '' ) ?>';
    document.getElementById('profile_albums_next').style.display = '<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' ) ?>';

    scriptJquery('#profile_albums_previous').off('click').on('click', function(){
      en4.core.request.send(scriptJquery.ajax({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        data : {
          format : 'html',
          subject : en4.core.subject.guid,
          page : <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() - 1) ?>
        }
      }), {
        'element' : anchor
      })
    });

    scriptJquery('#profile_albums_next').off('click').on('click', function(){
      en4.core.request.send(scriptJquery.ajax({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        data : {
          format : 'html',
          subject : en4.core.subject.guid,
          page : <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() + 1) ?>
        }
      }), {
        'element' : anchor
      })
    });
    <?php endif; ?>
  });
</script>
<div class="container no-padding" id="profile_albums">
  <div class="row">
    <?php foreach( $this->paginator as $album ): ?>
      <div class="col-lg-4 col-md-6 grid_outer">
            <div class="grid_wrapper albums_block">
        <a class="thumbs_photo" href="<?php echo $album->getHref(); ?>">
          <span style="background-image: url(<?php echo $album->getPhotoUrl('thumb.normal'); ?>);"></span>
        </a>
        <p class="thumbs_info">
          <span class="thumbs_title">
            <?php echo $this->htmlLink($album, $this->string()->chunk($this->string()->truncate( $this->translate($album->getTitle()), 45), 10)) ?>
          </span>
          <?php echo $this->translate(array('%s photo', '%s photos', $album->count()),$this->locale()->toNumber($album->count())) ?>
          <br />
          <?php echo $this->partial('_rating.tpl', 'core', array('item' => $album, 'module' => 'album', 'param' => 'show')); ?>
        </p>
      </div>
    </div>
    <?php endforeach;?>
  </div>
</div>

<div class="profile_paginator">
  <div id="profile_albums_previous" class="paginator_previous">
    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array(
      'onclick' => '',
      'class' => 'buttonlink icon_previous'
    )); ?>
  </div>
  <div id="profile_albums_next" class="paginator_next">
    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array(
      'onclick' => '',
      'class' => 'buttonlink_right icon_next'
    )); ?>
  </div>
</div>
