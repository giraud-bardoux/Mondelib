<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: manage.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<div class="sdk manage" id="content4">
  
  <div class="tip" id="package-created"><span><?php echo $this->translate("Package(s) successfully created. Download below.") ?></span></div>
  

  <h3><?php echo $this->translate("Manage Package Files") ?></h3>

  <p>
    <?php echo $this->translate("These are the built package files we found on your system at") ?>
    <i><?php echo $this->translate("temporary/package/sdk") ?></i>
  </p>

  <?php if( empty($this->packages) ): ?>

    <div class="tip">
      <?php echo $this->translate("No packages were found.") ?>
    </div>
  
  <?php else: ?>
    <script type="text/javascript">
      function selectAll(obj){
        scriptJquery('.checkbox').each(function(){
          scriptJquery(this).prop("checked",scriptJquery(obj).prop("checked"))
        });
      }
    </script>
    <form action="<?php echo $this->url() ?>" method="get" id="sdk_manage_form">    
      <table class="sdk_table manage">
        <thead>
          <tr>
            <th><input type='checkbox' class='checkbox' onclick="selectAll(this);" /></th>
            <th class="package-file"><?php echo $this->translate("Package File") ?></th>
            <th class="package-date"><?php echo $this->translate("Date Built") ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach( $this->packages as $index => $package ): ?>
            <tr>
              <td>
                <input type='checkbox' class='checkbox' name="actions[]" value="<?php echo basename($this->packageFiles[$index]) ?>">
              </td>
              <td>
                <a href="<?php echo $this->url(array('action' => 'download')) ?>?file=<?php echo urlencode(basename($this->packageFiles[$index])) ?>" class="buttonlink sdk-download">
                  <?php echo basename($this->packageFiles[$index]) ?>
                </a>
              </td>
              <td>
                <?php echo $package ? $package->getMeta()->getDate() : '' ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </form>
    <div class="button-container">
      <button onclick="scriptJquery('#sdk_manage_form').attr('action', '<?php echo $this->url(array('action' => 'combine')) ?>').submit();"><?php echo $this->translate("Combine") ?></button>
      <button onclick="scriptJquery('#sdk_manage_form').attr('action', '<?php echo $this->url(array('action' => 'delete')) ?>').submit();"><?php echo $this->translate("Delete") ?></button>
    </div>

  <?php endif; ?>
</div>
