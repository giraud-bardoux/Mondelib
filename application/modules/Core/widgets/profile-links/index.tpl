<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<script type="text/javascript">
  en4.core.runonce.add(function(){

    <?php if( !$this->renderOne ): ?>
    var anchor = scriptJquery('#profile_links').parent();
    scriptJquery('#profile_links_previous').css("display",'<?php echo ( $this->paginator->getCurrentPageNumber() == 1 ? 'none' : '' ) ?>');
    scriptJquery('#profile_links_next').css("display",'<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' ) ?>');

    scriptJquery('#profile_links_previous').off('click').on('click', function(){
      en4.core.request.send(scriptJquery.ajax({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        dataType : 'html',
        method : 'post',
        data : {
          format : 'html',
          subject : en4.core.subject.guid,
          page : <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() - 1) ?>
        }
      }), {
        'element' : anchor
      })
    });

    scriptJquery('#profile_links_next').off('click').on('click', function(){
      en4.core.request.send(scriptJquery.ajax({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        dataType : 'html',
        method : 'post',
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


<ul class="profile_links" id="profile_links">
  <?php foreach( $this->paginator as $link ): ?>
    <li>
      <?php if($link->photo_id != 0 && empty($link->params['iframely']['html'])):?>
      <div class="profile_links_photo">
        <?php echo $this->htmlLink($link->getHref(), $this->itemPhoto($link), array('target' => '_blank')) ?>
      </div>
      <?php endif;?>
      <div class="profile_links_info">
        <div class="profile_links_title">
          <?php echo $this->htmlLink($link->getHref(), Engine_Api::_()->core()->DecodeEmoji($link->getTitle()), array('class' => 'font_color','target' => '_blank')) ?>
        </div>
        <div class="profile_links_description font_size_small">
          <?php echo $this->htmlLink($link->getHref(), Engine_Api::_()->core()->DecodeEmoji($link->getDescription()), array('class' => 'font_color_light','target' => '_blank')) ?>
        </div>
        <?php if( !$link->getOwner()->isSelf($link->getParent()) ): ?>
        <div class="profile_links_author font_color_light font_size_small">
          <?php echo $this->translate('Posted by %s', $link->getOwner()->__toString()) ?>
          <?php echo $this->timestamp($link->creation_date) ?>
        </div>
        <?php endif; ?>
        <?php if(!empty($link->params['iframely']['html'])):
          echo $link->params['iframely']['html'];
        endif;?>
      </div>

      <?php if ($link->isDeletable()){ ?>
        <div class="profile_links_delete">
          <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'core', 'controller' => 'link', 'action' => 'delete', 'link_id' => $link->link_id, 'format' => 'smoothbox'), $this->translate(''), array('class' => 'btn btn-alt smoothbox fa-solid fa-xmark', 'data-bs-toggle' => 'tooltip', 'title' => $this->translate('Delete Link'))); ?>
        </div>
      <?php } ?>

    </li>
  <?php endforeach; ?>
</ul>
<div class="profile_paginator">
  <div id="profile_links_previous" class="paginator_previous">
    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array(
      'onclick' => '',
      'class' => 'buttonlink icon_previous'
    )); ?>
  </div>
  <div id="profile_links_next" class="paginator_next">
    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array(
      'onclick' => '',
      'class' => 'buttonlink_right icon_next'
    )); ?>
  </div>
</div>
