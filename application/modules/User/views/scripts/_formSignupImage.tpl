<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _formSignupImage.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>
<div class="user_edit_photo_main">
  <?php 
    if (isset($_SESSION['TemporaryProfileImg'])){
      echo '<img src="'.$_SESSION['TemporaryProfileImg'].'" alt="" id="lassoImg"/>';
    }
    else
      echo '<span class="bg_item_photo bg_thumb_profile bg_item_photo_user bg_item_nophoto" id="lassoImg"></span>';
    ?>
</div>
<br/>
  <?php if (isset($_SESSION['TemporaryProfileImg'])){?>
    <div id="preview-thumbnail" class="preview-thumbnail">
      <?php echo '<img class ="thumb_icon item_photo_user thumb_icon" src="'.$_SESSION['TemporaryProfileImgSquare'].'" alt="Profile Photo" id="previewimage" />  <br />';?>  
    </div>
    <div id="thumbnail-controller" class="thumbnail-controller">
      <?php
        if (isset($_SESSION['TemporaryProfileImg'])){
          echo '<a href="javascript:void(0);" onclick="lassoStart();">'.$this->translate('Edit Profile Photo').'</a>';
        }
      ?>
    </div>
    <br/>
  <?php }    ?>
  <div>
    <?php 
      $settings = Engine_Api::_()->getApi('settings', 'core');
      
      if (isset($_SESSION['TemporaryProfileImg']) && $settings->getSetting('user.signup.photo', 0) == 1){
        echo '<button name="done" id="done" type="submit" onClick="javascript:finishForm();">Save Photo</button>';
      }
    ?>
  </div>
  <script type="text/javascript">
    var loader = scriptJquery.crtEle('img',{ src: en4.core.staticBaseUrl + 'application/modules/Core/externals/images/loading.gif'});;
    var orginalThumbSrc;
    var originalSize;
    var lassoCrop;

    var lassoSetCoords = function(coords)
    {
      scriptJquery('#coordinates').val(coords.x1 + ':' + coords.y1 + ':' + coords.width + ':' + coords.height);
    }
    var myLasso;
    var lassoStart = function()
    {
      if( !orginalThumbSrc ) orginalThumbSrc = scriptJquery('#previewimage').attr('src');

      scriptJquery('#lassoImg').cropper({
        preview : ".preview-thumbnail",
        done: lassoSetCoords
      });

      originalSize = scriptJquery("#lassoImg");
      var sourceImg = scriptJquery('#lassoImg').attr("src");
      scriptJquery('#previewimage').attr("src",scriptJquery('#lassoImg').attr("src"));
      scriptJquery('#coordinates').val(10 + ':' + 10 + ':' + 58+ ':' + 58);

      scriptJquery('#thumbnail-controller').html('<a href="javascript:void(0);" onclick="lassoEnd();"><?php echo $this->translate('Apply Changes');?></a>');
    }

    var uploadSignupPhoto =function(){
      scriptJquery('#uploadPhoto').val(true);
      scriptJquery('#thumbnail-controller').html("<div><img class='loading_icon' src='application/modules/Core/externals/images/loading.gif'/><?php echo $this->translate('Loading...');?></div>");
      scriptJquery('.signup_account_form').trigger("submit");
      scriptJquery('#Filedata-wrapper').html("");
    }
    var lassoEnd = function(){
      scriptJquery('#lassoImg').css('display', 'block');
      scriptJquery('#thumbnail-controller').html('<a href="javascript:void(0);" onclick="lassoStart();"><?php echo $this->string()->escapeJavascript($this->translate('Edit Profile Photo'));?></a>');
      scriptJquery('#lassoMask').remove();
    }

  </script>
