<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: select.tpl 10181 2014-04-29 23:52:32Z andres $
 * @author     John
 */
?>

<?php
  $baseUrl = rtrim(str_replace('\\', '/', dirname($this->baseUrl())), '/');
  $this->headLink()
    ->appendStylesheet($baseUrl . '/externals/uploader/uploader.css');
?>

<script type="text/javascript">
  var post_max_size = "<?php echo 10000000000; ?>";
  var uploadedFiles = [];
  var pendingExtraction = [];
  var currentlyUploading = false;
  var currentlyExtracting = false;
  <?php if( !empty($this->toExtractPackages) ): ?>
    pendingExtraction = <?php echo Zend_Json::encode($this->toExtractPackages) ?>;
  <?php endif; ?>
  var uploadError = false;
  var uploadFile = function() {
    document.getElementById('upload_file').click();
  }
  var BaseFileUpload = {
    alreadyUploaded: function (file) {
      if (uploadedFiles.length === 0) {
        return false;
      }
      return uploadedFiles.every(function (uploadedFile) {
          return uploadedFile === file.name;
      });
    },

    uploadFile: function (obj, file, iteration, total) {
      //Check upload file size
      //var FileSize = obj.files[0].size / 1024 / 1024;

      uploadError = false;
      if (BaseFileUpload.alreadyUploaded(file)) {
        return BaseFileUpload.processUploadError(file['name'] + ' already added.');
      }
      var url = scriptJquery(obj).attr('data-url');
      var xhr = new XMLHttpRequest();
      var fd = new FormData();
      uploadedFiles.push(file.name);
      xhr.open("POST", url, true);
      scriptJquery('#upload-status-current').css('display', '');
      scriptJquery('#upload-status-overall').css('display', '');

      // progress bar
      xhr.upload.addEventListener("progress", function(e) {
        var currentFileProgress = -400 + ((e.loaded/e.total) * 100 * 2.5);
        //scriptJquery('div#upload-status-current img')[0].css('background-position', currentFileProgress + 'px 0px');
        var overAllFileProgress = -400 + ((iteration/total) * 100 * 2.5);
        //scriptJquery('div#upload-status-overall img')[0].css('background-position', overAllFileProgress + 'px 0px');
      }, false);

      xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
          try {
            var res = JSON.parse(xhr.responseText);
          } catch (err) {
            BaseFileUpload.processUploadError('An error occurred.');
            return false;
          }

          if (res['error'] !== undefined) {
            BaseFileUpload.processUploadError(res['error']);
            return false;
          }
          pendingExtraction.push(res.file);
          checkCanContinue();

          if (iteration === total) {
            scriptJquery('#upload-status-current').css('display', 'none');
            scriptJquery('#upload-status-overall').css('display', 'none');
            currentlyUploading = false;
            checkCanContinue();
          }
        }
      };
      fd.append('ajax-upload', 'true');
      fd.append(scriptJquery(obj).attr('name'), file);
      fd.append('format', 'json');
      xhr.send(fd);
    },

    processUploadError: function (errorMessage) {
      new Element('li', {
        'class': 'file-upload-error',
        href: 'javascript:void(0);',
        html: errorMessage,
        events: {
          click: function() {
            this.destroy();
          }
        }
      }).inject($('upload_error_list'), 'top');
    }
  };

  en4.core.runonce.add(function() {
    checkCanContinue();
    scriptJquery('#upload_file').each(function (el) {
    
      scriptJquery(document).on('change',function(obj){
        if(scriptJquery('#package_select_noselection_message'))
          scriptJquery('#package_select_noselection_message').hide();
      //el.addEvent('change', function (obj) {
        currentlyUploading = true;
        var files = scriptJquery('#upload_file').prop('files');
        var total = files.length;
        var iteration = 0;
        var valid = true;
        for(var i = 0; i < files.length; i++) {
          var FileSize = files[i].size / 1024 / 1024;
          if(FileSize <= post_max_size) {
            iteration++;
            BaseFileUpload.uploadFile(scriptJquery("#upload_file")[0], scriptJquery("#upload_file")[0].files[i], iteration, total);
          }else{
            valid = false;
          }
        }
        if(!valid){
            alert("The size of the file exceeds the limits set on the server.");
            return false;
        }else{
          checkCanContinue();
        }
      });
      scriptJquery(document).on('click',function(){
        this.value = '';
      });
    });
  });
  scriptJquery(document).on('click',function(event){
    var element = scriptJquery(event.target);
    if( element.get('tag') == 'input' && element.get('type') == 'checkbox' ) {
      checkCanContinue();
    }
  });

  var checkCanContinue = function() {
    // Check for selection
    var hasChecked = false;
    scriptJquery('input[type=checkbox]').each(function(el) {
      if( scriptJquery(this).is(":checked")) {
        hasChecked = true;
      }
    });
    // Check for pending extraction
    checkPendingExtraction();

    // Do the message stuff
    if( currentlyUploading || currentlyExtracting || pendingExtraction.length > 0 ) {
      //scriptJquery('#package_select_continue').addClass('package_select_error_uploading');
    } else if( !hasChecked ) {
      scriptJquery('#package_select_continue').addClass('package_select_error_noselection');
    } else {
      scriptJquery('#package_select_continue').addClass('package_select_okay');
    }
  }

  var checkPendingExtraction = function() {
    if( currentlyExtracting || pendingExtraction.length == 0 ) {
      return;
    }

    currentlyExtracting = true;
    // Start extracting
    var url = '<?php echo $this->url(array('action' => 'extract')) ?>';
    var uploadMessageEl = scriptJquery.crtEle('span', {
      'class' : 'file-message'
    });
    
    var uploadEl = scriptJquery.crtEle('li', {
      'class': 'file file-success'
    }).clone(
      scriptJquery.crtEle('span', {
        'class': 'file-name',
        'html': pendingExtraction[0]
      }),
      scriptJquery.crtEle('span', {
        'class': 'file-info'
      }),
      scriptJquery.crtEle('a', {
        'class': 'file-package-remove',
        href: 'javascript:void(0);',
        html: 'remove',
        events: {
          click: function() {
            this.getParent().destroy();
          }
        }
      })
    ).appendTo(scriptJquery('#upload-list'), 'top');
    uploadMessageEl.appendTo(scriptJquery(uploadEl).get('.file-info') || uploadEl);
    uploadMessageEl.css('html', 'Extracting ...').addClass('file-loading');
    var request = scriptJquery.ajax({
      dataType: 'json',
      url: url,
      data : {
        'package' : pendingExtraction[0],
        'format' : 'json'
      },
      success : function(responseJSON, responseText) {
        pendingExtraction.shift();
        currentlyExtracting = false;

        uploadMessageEl.removeClass('file-loading');

        // Bad response
        if( !$type(responseJSON) ) {
          uploadMessageEl.set('html', 'Extract error: Bad response');

        // Error
        } else if( $type(responseJSON.error) ) {
          uploadMessageEl.set('html', 'Extract error: ' + responseJSON.error);

        // Okay
        } else if( $type(responseJSON.status) ) {
          replacePackageListItems(responseJSON.packagesInfo, uploadEl);

        // Wth
        } else {
          uploadMessageEl.set('html', 'Unknown extract error: ' + responseText);
        }

        // Check for more extraction
        checkCanContinue();
      }
    });
    //request.send();
  }

  var replacePackageListItems = function(packages, element) {
    packages.map(info => {
      var guid = info.data.type + '-' + info.data.name;
      var key = info.key;
      scriptJquery('.package_' + guid).remove();
      scriptJquery('.package_' + key).remove();

      scriptJquery(info.html).insertAfter(element);
    });
    element.remove();
  }

  var removePackage = function(packageKey, el) {
    var url = '<?php echo $this->url(array('action' => 'select-delete')) ?>';
    var linkParent = scriptJquery(el).parent('span').parent();
    scriptJquery(el).parent('span').innerHTML = "removing...";
    var request = scriptJquery.ajax({
      dataType: 'json',
      url : url,
      data : {
        format : 'json',
        'package' : packageKey
      },
      success : function(responseJSON) {
        if( $type(responseJSON) && $type(responseJSON.error) ) {
          alert('An error has occurred: ' + responseJSON.error);
        } else if( !$type(responseJSON) || !$type(responseJSON.status) || !responseJSON.status ) {
          alert('An unknown error has occurred.');
        } else {
          //success
          uploadedFiles = uploadedFiles.filter(function( obj ) {
            return obj !== packageKey + '.tar';
          });
          linkParent.remove();
          var hasCheckedRemove = false;
          scriptJquery('input[type=checkbox]').each(function(el) {
            if( scriptJquery(this).is(":checked")) {
              hasCheckedRemove = true;
            }
          });
          if(hasCheckedRemove == false)
            scriptJquery('#package_select_continue').removeClass('package_select_okay');
          checkCanContinue();
        }
      }
    });
    //request.send();
  }
</script>

<h3>
  Install Packages
</h3>

<?php
  // Navigation
  echo $this->render('_installMenu.tpl')
?>

<br />

<p>
  <?php echo $this->translate("Let's get started with installing your new packages. First, you will need to upload
  the packages you want to install. Click the 'Upload Packages' link below to select one or
  multiple packages from your computer to upload them to the server."); ?>
  <br />
   <?php echo $this->translate("Note: The packages are extracted on upload, so the progress bar will pause at 100% for
  up to several minutes (depending on the size of the package).");?>
</p>

<br />

<div class="package_uploader" id='package_uploader'>
  <div class="package_uploader_main">
    <div id="file-status">
      <ul id="uploaded-file-list"></ul>
      <div id="base-uploader-progress"></div>
      <div class="base-uploader">
          <input class="file-input" id="upload_file" type="file" multiple="multiple" data-url="<?php echo $this->url(array('action' => 'upload'))?>" data-form-id="#form-upload" name='Filedata' accept=".tar">
      </div>
    </div>
    <div id="upload-status" >
      <div class="upload-buttons" id="upload-buttons">
        <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Add Packages'), array('id' => 'demo-browse', 'class' => 'buttonlink package_uploader_choosepackages', 'onclick' => 'uploadFile();')) ?>
          -
        <a href="javascript:void(0);" id="select-check-all" onclick="scriptJquery('input[type=checkbox]').attr('checked', true);"><?php echo $this->translate("Check All");?></a> /
        <a href="javascript:void(0);" id="select-check-all" onclick="scriptJquery('input[type=checkbox]').attr('checked', false);"><?php echo $this->translate("Uncheck All");?></a>
      </div>
      <div class="upload-status-overall" id="upload-status-overall" style="display:none">
        <div class="overall-title"></div>
        <img class="progress overall-progress" alt="" src="<?php echo $baseUrl . '/externals/fancyupload/assets/progress-bar/bar.gif' ?>" />
      </div>
      <div class="upload-status-current" id="upload-status-current" style="display: none">
        <div class="current-title"></div>
        <img class="progress current-progress" alt="" src="<?php echo $baseUrl . '/externals/fancyupload/assets/progress-bar/bar.gif' ?>" />
      </div>
      <div class="current-text"></div>
    </div>
  </div>
</div>
<ul class="upload-error-list" id="upload_error_list">
</ul>

<form action="<?php echo $this->url(array('action' => 'prepare')) ?>" method="post">
  <ul class="upload-list selected-packages-list" id="upload-list">
  </ul>
  <ul class="selected-packages-list extracted-packages-list">
    <?php foreach( (array) $this->toExtractPackages as $toExtractPackage ): ?>
      <?php echo $this->packageSelectSimple($toExtractPackage) ?>
    <?php endforeach; ?>
    <?php foreach( $this->extractedPackages as $package ): ?>
      <?php echo $this->packageSelect($package) ?>
    <?php endforeach; ?>
  </ul>

  <br />

  <div id="package_select_continue">

    <div class="package_select_uploading_message">
      <?php echo $this->translate("Please wait until the upload finishes or while archives are extracted.");?>
    </div>
    
    <div class="package_select_noselection_message" id="package_select_noselection_message">
      <?php echo $this->translate("Please upload or select a package.");?>
    </div>
    
    <div class="package_select_continue_message">
      <p>
        <?php echo $this->translate("If you're ready to install the packages checked above, click the button below.
        In the next step, we will check to make sure your server has everything it needs
        to complete the installation.");?>
      </p>

      <br />
      
      <div>
        <button type="submit"><?php echo $this->translate("Continue");?></button>
      <?php echo $this->translate(" or ");?><a href="./"><?php echo $this->translate("cancel installation");?></a>
      </div>
    </div>
  </div>
</form>
