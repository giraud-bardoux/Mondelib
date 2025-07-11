<?php ?>


<div class="user_cover_info_status">
  <div class="_status" id="status_edit_link">
    <?php echo nl2br($this->subject->status); ?>
    <?php if ($this->subject->isSelf($this->viewer())) { ?>
      <?php if ($this->subject->status) { ?>
        <a href="javascript:void(0);" class="_editicon" onclick="editStatus()" data-bs-toggle="tooltip" title="<?php echo $this->translate("Update Your Status"); ?>"><i class="icon_edit_pencil"></i></a>
      <?php } else { ?>
        <a href="javascript:void(0);" onclick="editStatus()" class="user_cover_info_status_link gap-1"><i class="icon_edit_pencil"></i><span><?php echo $this->translate("Set a Status"); ?></span></a>
      <?php } ?>
    <?php } ?>
  </div>
  <?php if ($this->subject->isSelf($this->viewer())) { ?>
    <div id="status_textarea" class="user_cover_info_status_update" style='display:none;'>
      <div class="_field">
        <input type="text" maxlength="25" id="status" style='display:block;' value="<?php echo $this->subject->status ? $this->subject->status : ''; ?>"></input>
        <a href="javascript:void(0);" onclick="saveStatus()" class="btn btn-primary btn-small"><?php echo $this->translate('Done'); ?></a></a>
        <div style='display:none;' id="loading_image" class="_loading">
          <img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Core/externals/images/loading.gif" alt="" />
        </div>
      </div>
    </div>

  <?php } ?>

</div>

<?php if ($this->subject->isSelf($this->viewer())) { ?>
  <script type="application/javascript">
    function saveStatus() {
      var str = document.getElementById('status').value.replace(/\n/g, '<br />');
      var status = document.getElementById('status').value;
      // scriptJquery("#status_textarea").hide();
      scriptJquery("#loading_image").show();
      en4.core.request.send(scriptJquery.ajax({
        url: en4.core.baseUrl + 'user/profile/status/',
        data: {
          format: 'html',
          status: status,
          id: '<?php echo $this->subject->user_id; ?>',
        },
        success: function (responseHTML) {
          if (str == '') {
            str = "<div class='user_cover_info_status'><div class='_status'><a href='javascript:void(0);' onclick='editStatus()' class='user_cover_info_status_link gap-1'><i class='icon_edit_pencil'></i>" + '<?php echo $this->string()->escapeJavascript($this->translate("Set a Status")) ?>' + "</a></div></div>";
            scriptJquery('#status_edit_link').html(str);
          } else {
            str = '<div class="user_cover_info_status"><div class="_status">' + str + '<a href="javascript:void(0);" class="_editicon" onclick="editStatus()" data-bs-toggle="tooltip" title="<?php echo $this->translate("Update Your Status"); ?>"><i class="icon_edit_pencil"></i></a></div></div>';

            scriptJquery('#status_edit_link').html(str);
          }
          scriptJquery("#status_edit_link").show();
          scriptJquery("#loading_image").hide();
          scriptJquery("#status_textarea").hide();
        }
      }));
    }

    function editStatus() {
      scriptJquery("#status_edit_link").hide();
      scriptJquery("#status_textarea").show();
      document.getElementById('status').focus();
    }
  </script>
<?php } ?>