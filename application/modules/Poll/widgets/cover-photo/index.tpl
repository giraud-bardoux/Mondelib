<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php if (!empty($this->uploadDefaultCover)): ?>
    <div class="tip">
      <span>
          <?php echo $this->defaultCoverMessage;?>
      </span>
    </div>
    <br />
<?php endif; ?>
<?php if (isset($this->poll->level_id)) {
        $level_id = $this->poll->level_id;
      } else {
        $level_id = 0;
      }
      $coverPhotoId = Engine_Api::_()->getApi("settings", "core")
        ->getSetting("pollcoverphoto.preview.level.id.$level_id"); ?>
    <div class="profile_cover_wrapper">
      <div class="profile_cover_photo_wrapper" id="poll_cover_photo">
         <div class="shimmer_profile_cover_photo_wrapper"> 
        </div>           
      </div>
      <div class="profile_cover_head_section" id="poll_main_photo">
        <div class="shimmer_profile_cover_head_inner">
          <div class="shimmer_profile_main_photo_wrapper">
          </div>
          <div class="shimmer_cover_photo_profile_information">
            <div class="shimmer_cover_photo_profile_status">
              <h2></h2>
              <p class="shimmer_cover_photo_stats"></p>
            </div>
            <div class="shimmer_coverphoto_navigation">
              <ul>
                <li>
                  <a href="javascript:void(0)" class=""></a>
                </li>
              </ul>
            </div>    
          </div>         
        </div>        
      </div>
    </div>

    <div class="clr"></div>
<?php if (isset($this->poll->poll_id)) {
        $poll_id = $this->poll->poll_id;
      } else {
        $poll_id = 0;
      } ?>

<script type="text/javascript">
  en4.core.runonce.add(function () {
    document.coverPhoto = new Coverphoto({
      'block': scriptJquery('#poll_cover_photo'),
      'photoUrl': '<?php echo $this->url(array(
      'action' => 'get-cover-photo',
      'poll_id' => $poll_id,
      'photoType' => 'cover',
      'uploadDefaultCover' => $this->uploadDefaultCover,
      'level_id' => $this->level_id), 'poll_coverphoto', true); ?>',
      'buttons': '#cover_photo_options',
      'positionUrl': '<?php echo $this->url(array(
      'action' => 'reset-cover-photo-position',
      'poll_id' => $poll_id,
      'uploadDefaultCover' => $this->uploadDefaultCover,
      'level_id' => $this->level_id), 'poll_coverphoto', true); ?>',
      'position': <?php echo Zend_Json_Encoder::encode(array('top' => 0, 'left' => 0)); ?>,
      'uploadDefaultCover': '<?php echo $this->uploadDefaultCover; ?>',
    });

    document.mainPhoto = new Mainphoto({
      block: scriptJquery('#poll_main_photo'),
      photoUrl: '<?php echo $this->url(array(
        'action' => 'get-main-photo',
        'poll_id' => $poll_id,
        'photoType' => 'profile',
        'uploadDefaultCover' => $this->uploadDefaultCover), 'poll_coverphoto', true); ?>',
      buttons: '#cover_photo_options',
      positionUrl: '<?php echo $this->url(array(
        'action' => 'reset-position-cover-photo',
        'poll_id' => $poll_id), 'poll_coverphoto', true); ?>',
      position:<?php echo Zend_Json_Encoder::encode(array('top' => 0, 'left' => 0)); ?>
    });
  });
  
  function showSmoothBox(url) {
    Smoothbox.open(url);
  }
  
  en4.core.runonce.add(function () {
    setTimeout("setTabInsideLayout()", 500);
  });

  function setTabInsideLayout() {
    var tab = scriptJquery('#global_content').find('div.layout_core_container_tabs');
    if (tab.length && tab.hasClass('generic_layout_container layout_core_container_tabs')) {
      tab.removeClass('generic_layout_container layout_core_container_tabs');
      tab.addClass('generic_layout_container layout_core_container_tabs profile_cover_photo_tabs');
    }
  }
</script>
