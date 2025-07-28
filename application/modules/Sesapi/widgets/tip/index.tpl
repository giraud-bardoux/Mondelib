<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: index.tpl 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>

<?php if($this->iosid){ ?>
  <?php $this->headMeta()->setProperty('apple-itunes-app', 'app-id='.$this->iosid); ?>
<?php } ?>
<?php if($this->androidid){ ?>
  <?php $this->headMeta()->setProperty('google-play-app', 'google-play-app='.$this->androidid); ?>
<?php } ?>
<script type="application/javascript">
jquerysesapi(function() {
  var android = location.href.match(/#android$/) || navigator.userAgent.match(/Android/i) != null;
  jquerysesapi.smartbanner({ 
    layer:true,
    force: android ? 'android' : 'ios',
    author: '<?php echo $this->description; ?>',
    icon:'<?php echo $this->image; ?>',
    daysHidden: <?php echo $this->daysHidden; ?>, 
    daysReminder: <?php echo $this->daysReminder; ?>, 
    title:'<?php echo $this->title; ?>',
    button:'<?php echo $this->buttoninstall; ?>',
    price:"FREE",
    /* onInstall: function(e) {
       alert('Click install');
    },
    onClose: function(e) {
      alert('Click close');
    }  */
   });
});
</script>