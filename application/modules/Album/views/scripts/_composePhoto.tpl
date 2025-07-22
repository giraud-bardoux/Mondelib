<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _composePhoto.tpl 10245 2014-05-28 18:08:24Z lucas $
 * @author     Sami
 */
?>
<?php
  $user = Engine_Api::_()->user()->getViewer();
  $allowed_create = (bool) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('album', $user, 'create');
  $subject = 0;
  if( Engine_Api::_()->core()->hasSubject() ) {
    // Get subject
    $subject = Engine_Api::_()->core()->getSubject();
    if($subject && $subject->getType() == 'group') {
      $allowed_create = Engine_Api::_()->authorization()->isAllowed('group', $user, 'photo');
    }
  }
  if(!$allowed_create) return;
?>
<?php
  $this->headTranslate(array(
    'Overall Progress ({total})', 'File Progress', 'Uploading "{name}"',
    'Upload: {bytesLoaded} with {rate}, {timeRemaining} remaining.', '{name}',
    'Remove', 'Click to remove this entry.', 'Upload failed',
    '{name} already added.',
    '{name} ({size}) is too small, the minimal file size is {fileSizeMin}.',
    '{name} ({size}) is too big, the maximal file size is {fileSizeMax}.',
    '{name} could not be added, amount of {fileListMax} files exceeded.',
    '{name} ({size}) is too big, overall filesize of {fileListSizeMax} exceeded.',
    'Server returned HTTP-Status <code>#{code}</code>',
    'Security error occurred ({text})',
    'Error caused a send or load operation to fail ({text})',
  ));
  $attachMax = $this->composerType == 'activity' ? Engine_Api::_()->authorization()->getPermission($this->viewer()->level_id, 'album', 'attach_max') : 1;
?>

<script type="text/javascript">
  en4.core.runonce.add(function() {
      var type = 'wall';
      <?php if($subject && ($subject->getType() == 'group' || $subject->getType() =="event")) {  ?>
        type = '<?php echo $subject->getType(); ?>'
      <?php } ?>
      if (composeInstance.options.type) type = composeInstance.options.type;
      composeInstance.addPlugin(new Composer.Plugin.Photo({
        title : '<?php echo $this->string()->escapeJavascript($this->translate('Add Photo')) ?>',
        lang : {
          'Add Photo' : '<?php echo $this->string()->escapeJavascript($this->translate('Add Photo')) ?>',
          'Select File' : '<?php echo $this->string()->escapeJavascript($this->translate('Select File')) ?>',
          'cancel' : '<?php echo $this->string()->escapeJavascript($this->translate('cancel')) ?>',
          'Loading...' : '<?php echo $this->string()->escapeJavascript($this->translate('Loading...')) ?>',
          'Unable to upload photo. Please click cancel and try again': '<?php echo $this->string()->escapeJavascript($this->translate('Unable to upload photo. Please click cancel and try again')) ?>'
        },
        requestOptions : {
          'url'  : en4.core.baseUrl + 'album/album/compose-upload/type/'+type,
          'removePhotoURL' : "<?php echo $this->url(array('controller' => 'photo','action' => 'remove-photo'), 'album_extended', true); ?>",
        },
        fancyUploadOptions : {
          'url'  : en4.core.baseUrl + 'album/album/compose-upload/format/json/type/'+type,
          'path' : en4.core.basePath + 'externals/fancyupload/Swiff.Uploader.swf',
          'limitFiles': <?php echo $attachMax ?>
        }
      }));
  }); 
</script>
