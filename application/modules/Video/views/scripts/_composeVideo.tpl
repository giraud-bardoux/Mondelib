<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _composeVideo.tpl 10245 2014-05-28 18:08:24Z lucas $
 * @author     Jung
 */
?>

<?php
  $allowed = 0;
  $user = Engine_Api::_()->user()->getViewer();
  $allowed_upload = (bool) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('video', $user, 'upload');
  $ffmpeg_path = (bool) Engine_Api::_()->getApi('settings', 'core')->video_ffmpeg_path;
  $youtubeEnabled = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('video.youtube.apikey');
  if($allowed_upload && $ffmpeg_path) $allowed = 1;
?>
<?php 
if( Engine_Api::_()->core()->hasSubject() ) {
  // Get subject
  $subject = Engine_Api::_()->core()->getSubject();
  if($subject && $subject->getType() == 'group') {
    $videoPrivacy = $subject->authorization()->isAllowed(null, 'video');
    if(!$videoPrivacy) return;
    $canCreate = Engine_Api::_()->authorization()->isAllowed('group', $user, 'video');
    if(!$canCreate)
      return;
    $allowedUpload = Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('group', $user, 'videoupload');
    if($allowedUpload && $ffmpeg_path) $allowed = 1;
    else $allowed = 0;
  }
}
?>
<script type="text/javascript">
  en4.core.runonce.add(function() {
      var type = 'wall';
      if (composeInstance.options.type) type = composeInstance.options.type;
      composeInstance.addPlugin(new Composer.Plugin.Video({
        title : '<?php echo $this->translate('Add Video') ?>',
        lang : {
          'Add Video' : '<?php echo $this->string()->escapeJavascript($this->translate('Add Video')) ?>',
          'Select File' : '<?php echo $this->string()->escapeJavascript($this->translate('Select File')) ?>',
          'cancel' : '<?php echo $this->string()->escapeJavascript($this->translate('cancel')) ?>',
          'Attach' : '<?php echo $this->string()->escapeJavascript($this->translate('Attach')) ?>',
          'Loading...' : '<?php echo $this->string()->escapeJavascript($this->translate('Loading...')) ?>',
          'Choose Source': '<?php echo $this->string()->escapeJavascript($this->translate('Choose Source')) ?>',
          'My Computer': '<?php echo $this->string()->escapeJavascript($this->translate('My Computer')) ?>',
          'My Computer': '<?php echo $this->string()->escapeJavascript($this->translate('My Device')) ?>',
          'To upload a video from your computer, please use our full uploader.': '<?php echo $this->string()->escapeJavascript($this->translate('To upload a video from your computer, please use our <a href="%1$s">full uploader</a>.', $this->url(array('action' => 'create', 'type'=>'upload'), 'video_general')), false) ?>',
          'To upload a video from your device, please use our full uploader.': '<?php echo $this->string()->escapeJavascript($this->translate('To upload a video from your device, please use our <a href="%1$s">full uploader</a>.', $this->url(array('action' => 'create', 'type'=> 'upload'), 'video_general')), false) ?>'
        },
        allowed : <?php echo $allowed; ?>,
        type : type,
        requestOptions : {
          'url' : en4.core.baseUrl + 'video/index/compose-upload/format/json/c_type/'+type
        }
      }));
  });
</script>
