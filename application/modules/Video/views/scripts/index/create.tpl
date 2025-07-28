<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: create.tpl 10110 2013-10-31 02:04:11Z andres $
 * @author     Jung
 */
?>
<?php echo $this->partial('_location.tpl', 'core', array('modulename' => 'video')); ?>
<script type="text/javascript">
  en4.core.runonce.add(function() {
    var tagsUrl = '<?php echo $this->url(array('controller' => 'tag', 'action' => 'suggest'), 'default', true) ?>';
    var validationErrorMessage = "<?php echo $this->translate("We could not find a video there - please check the URL and try again."); ?>";
    var checkingUrlMessage = '<?php echo $this->string()->escapeJavascript($this->translate('Checking URL...')) ?>';
    try {
      var updateTextFields = window.updateTextFields = function() {
        var video_element = document.getElementById("type");
        var url_element = document.getElementById("url-wrapper");
        var file_element = document.getElementById("Filedata-wrapper");
        var submit_element = document.getElementById("upload-wrapper");
        var rotation_element = document.getElementById("rotation-wrapper");
        // clear url if input field on change
        scriptJquery('#upload-wrapper').css("display","none");
        // If video source is empty
        if(video_element.value == 0 ) {
          scriptJquery('#url').val("");
          file_element.style.display = "none";
          url_element.style.display = "none";
          rotation_element.style.display = "none";
          return;
        } else if(scriptJquery('#code').val() && scriptJquery('#url').val()) {
          scriptJquery('#type-wrapper').css("display","none");
          file_element.style.display = "none";
          rotation_element.style.display = "none";
          submit_element.style.display = "block";
          return;
        } else if( video_element.value == 'iframely' ) {
          // If video source is youtube or vimeo
          scriptJquery('#url').val('');
          scriptJquery('#code').val('');
          scriptJquery('#id').val('');
          file_element.style.display = "none";
          rotation_element.style.display = "none";
          url_element.style.display = "block";
          return;
        } else if(scriptJquery('#id').val()) {
          // if there is video_id that means this form is returned from uploading
          // because some other required field
          scriptJquery('#url-wrapper').css("display","none");
          scriptJquery('#type-wrapper').css("display","none");
          rotation_element.style.display = "block";
          file_element.style.display = "none";
          submit_element.style.display = "block";
          return;
        } else if( video_element.value == 'upload' ) {
          // If video source is from computer
          scriptJquery('#url').value = '';
          scriptJquery('#code').value = '';
          file_element.style.display = "block";
          rotation_element.style.display = "block";
          url_element.style.display = "none";
          scriptJquery('#upload_file').css("display","block");
          scriptJquery('#upload-wrapper').css("display","");
          return;
        }
      }
      
      var video = window.video = {
        active : false,
        debug : false,
        currentUrl : null,
        currentTitle : null,
        currentDescription : null,
        currentImage : 0,
        currentImageSrc : null,
        imagesLoading : 0,
        images : [],
        maxAspect : (10 / 3), //(5 / 2), //3.1,
        minAspect : (3 / 10), //(2 / 5), //(1 / 3.1),
        minSize : 50,
        maxPixels : 500000,
        monitorInterval: null,
        monitorLastActivity : false,
        monitorDelay : 500,
        maxImageLoading : 5000,
        attach : function() {
          var bind = this;
          scriptJquery('#url').on('keyup', function() {
            bind.monitorLastActivity = (new Date).valueOf();
          });
          var url_element = scriptJquery("#url-element");
          var myElement = scriptJquery.crtEle("p",{});
          myElement.html("test");
          myElement.addClass("description");
          myElement.attr("id","validation");
          myElement.css("display","none");
          url_element.append(myElement);
          var body = scriptJquery('#url');
          var lastBody = '';
          var video_element = scriptJquery('#type');
          var setVideoInterval = setInterval(function() {
            // Ignore if no change or url matches
            if( body.val() == lastBody || bind.currentUrl || video_element.val() != 'iframely') {
              return;
            }
            // Ignore if delay not met yet
            if( (new Date).valueOf() < bind.monitorLastActivity + bind.monitorDelay ) {
              return;
            }
            video.iframely(body.val());
            lastBody = body.val();
          },250);
          en4.core.shutdown.add(function() {
            clearInterval(setVideoInterval);
          });
        }, 
        iframely : function(url) {
            scriptJquery('#validation').css("display","block");
            scriptJquery('#validation').html(checkingUrlMessage);
            scriptJquery('#upload-wrapper').css("display","none");
            (scriptJquery.ajax({
              type:'POST',
              method:'post',
              dataType:'json',
              'url' : '<?php echo $this->url(array('module' => 'video', 'controller' => 'index', 'action' => 'get-iframely-information'), 'default', true) ?>',
              'data' : {
                'format': 'json',
                'uri' : url,
              },
              success : function(response) {
                if( response.valid ) {
                  scriptJquery('#upload-wrapper').css("display","block");
                  scriptJquery('#validation').css("display","none");
                  scriptJquery('#code').val(response.iframely.code);
                  scriptJquery('#title').val(response.iframely.title);
                  scriptJquery('#description').val(response.iframely.description);
                  scriptJquery('#validation').css("display","none");
                } else {
                  scriptJquery('#upload-wrapper').css("display","none");
                  scriptJquery('#validation').html(validationErrorMessage);
                  scriptJquery('#code').val('');
                }
              }
            }));
        }
      }
      // Run stuff
      updateTextFields();
      video.attach();
      
      scriptJquery('#tags').selectize({
        maxItems: 10,
        valueField: 'label',
        labelField: 'label',
        searchField: 'label',
        create: true,
        load: function(query, callback) {
            if (!query.length) return callback();
            scriptJquery.ajax({
              url: tagsUrl,
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
    } catch(err){ console.log(err); }
  });
  
  var modulename = 'video';
  var category_id = '<?php echo $this->category_id; ?>';
  var subcat_id = '<?php echo $this->subcat_id; ?>';
  var subsubcat_id = '<?php echo $this->subsubcat_id; ?>';

  en4.core.runonce.add(function() {
    if(category_id && category_id != 0) {
      showSubCategory(category_id, subcat_id);
    } else {
      if(scriptJquery('#category_id').val() != 0) {
        showSubCategory(scriptJquery('#category_id').val());
      } else {
        if(document.getElementById('subcat_id-wrapper'))
          document.getElementById('subcat_id-wrapper').style.display = "none";
      }
    }

    if(subsubcat_id) {
      if(subcat_id && subcat_id != 0) {
        showSubSubCategory(subcat_id, subsubcat_id);
      } else {
        if(document.getElementById('subsubcat_id-wrapper'))
          document.getElementById('subsubcat_id-wrapper').style.display = "none";
      }
    } else if(subcat_id) {
      showSubSubCategory(subcat_id);
    }
    else {
      if(document.getElementById('subsubcat_id-wrapper'))
        document.getElementById('subsubcat_id-wrapper').style.display = "none";
    }
  });
</script>
<?php if (($this->current_count >= $this->quota) && !empty($this->quota)):?>
  <div class="tip">
    <span>
      <?php echo $this->translate('You have already uploaded the maximum number of videos allowed.');?>
      <?php echo $this->translate('If you would like to upload a new video, please <a href="%1$s">delete</a> an old one first.', $this->url(array('action' => 'manage'), 'video_general'));?>
    </span>
  </div>
  <br/>
<?php else:?>
  <div class="video_create_form global_form_wrap"><?php echo $this->form->render($this);?></div>
<?php endif; ?>
<script type="text/javascript">
  scriptJquery('.core_main_video').parent().addClass('active');
</script>