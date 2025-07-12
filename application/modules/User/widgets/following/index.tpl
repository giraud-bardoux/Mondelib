<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<script type="text/javascript">
  en4.core.runonce.add(function(){
    <?php if( !$this->renderOne ): ?>
    var anchor = scriptJquery('#user_profile_following').parent();
    scriptJquery('#user_profile_following_previous').css("display",'<?php echo ( $this->paginator->getCurrentPageNumber() == 1 ? 'none' : '' ) ?>');
    scriptJquery('#user_profile_following_next').css("display",'<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() ? 'none' : '' ) ?>');

    scriptJquery('#user_profile_following_previous').off('click').on('click', function(){
      en4.core.request.send(scriptJquery.ajax({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        dataType: 'html',
        method : 'post',
        data : {
          format : 'html',
          subject : '<?php echo $this->subject->getGuid() ?>',
          page : <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() - 1) ?>
        }
      }), {
        'element' : anchor
      })
    });

    scriptJquery('#user_profile_following_next').off('click').on('click', function(){
      en4.core.request.send(scriptJquery.ajax({
        url : en4.core.baseUrl + 'widget/index/content_id/' + <?php echo sprintf('%d', $this->identity) ?>,
        dataType: 'html',
        method : 'post',
        data : {
          format : 'html',
          subject : '<?php echo $this->subject->getGuid() ?>',
          page : <?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() + 1) ?>
        }
      }), {
        'element' : anchor
      })
    });
    <?php endif; ?>
  });
  
</script>
<div class="user_profile_friends row" id="user_profile_following">
  <?php foreach( $this->paginator as $member ): ?>
    <?php if($member->getIdentity()) { ?>
      <div class="col-sm-6 user_profile_friends_item" id="user_following_<?php echo $member->getIdentity() ?>">
        <div class="user_profile_friends_inner">
          <div class="user_profile_friends_left">
            <div class="user_profile_friends_img">
              <?php echo $this->htmlLink($member->getHref(), $this->itemBackgroundPhoto($member, 'thumb.profile'), array('class' => 'profile_friends_icon')) ?>
            </div>  
            <div class="profile_friends_content">
              <div class="_title">
                <?php echo $this->htmlLink($member->getHref(), $member->getTitle()) ?>
              </div>
              <span class="_username">
                <?php echo $this->translate("@%s", $member->username) ?>
              </span>
            </div>
          </div>
          <div class="user_profile_friends_right">
            <?php if($this->viewer()->getIdentity() && $member->getIdentity() != $this->viewer()->getIdentity()) { ?>
              <?php echo $this->partial('_followmembers.tpl', 'user', array('subject' => $member)); ?>
            <?php } ?>
          </div>
        </div> 
      </div>
    <?php } ?>
  <?php endforeach ?>
</div>
<div class="profile_paginator"> 
  <div id="user_profile_following_previous" class="paginator_previous">
    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Previous'), array(
      'onclick' => '',
      'class' => 'buttonlink icon_previous'
    )); ?>
  </div>
  <div id="user_profile_following_next" class="paginator_next">
    <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Next'), array(
      'onclick' => '',
      'class' => 'buttonlink_right icon_next'
    )); ?>
  </div>
</div>
