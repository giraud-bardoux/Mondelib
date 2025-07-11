<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: build.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<div class="sdk" id="content3">
  <h3><?php echo $this->translate("Build Packages") ?></h3>
  <p>
    <?php echo $this->translate("These are the packages we found on your system. Choose the ones you want to
    build into distributable files.") ?>
  </p>

  <?php if( $this->status ): ?>

    <div class="tip">
      <?php echo $this->translate("Your package(s) have been built successfully.") ?>
    </div>

  <?php elseif( $this->error ): ?>
  
    <div class="error">
      <?php echo $this->error ?>
    </div>
    
  <?php endif; ?>

  <?php if( empty($this->buildPackages) ): ?>

    <div class="tip">
      <?php echo $this->translate("No packages were found.") ?>
    </div>

  <?php else: ?>
    <script type="text/javascript">
      function showHide(source) {
        if(scriptJquery('#'+source).css('display') == 'none')
        scriptJquery('#'+source).show();
        else
        scriptJquery('#'+source).hide();
      }
      function selectAll(obj){
        scriptJquery('.checkbox').each(function(){
          scriptJquery(this).prop("checked",scriptJquery(obj).prop("checked"))
        });
      }
    </script>
    <form action="<?php echo $this->url() ?>" method="post">
      <table class="sdk_table build" cellpading="0" cellspecing="0">
        <thead>
          <tr>
            <th><input onclick="selectAll(this)" type='checkbox' class='checkbox'></th>
            <th class="package"><?php echo $this->translate("Package") ?></th>
            <th class="version"><?php echo $this->translate("Version") ?></th>
            <th class="type"><?php echo $this->translate("Type") ?></th>
            <th class="author"><?php echo $this->translate("Author") ?></th>
            <th class="moreinfo">&nbsp;</th>
          </tr>
        </thead>
        <tbody>
          <?php $j = 1; ?>
          <?php foreach( $this->buildPackages as $package ): $i = !@$i; ?>

            <tr<?php if( !$i ) echo ' class="alt"'; ?>>
              <td>
                <input name='build[]' value='<?php echo $package['key'] ?>' type='checkbox' class='checkbox'>
              </td>
              <td>
                <span class="sdk_build_title">
                  <strong><?php echo $package['manifest']['package']['title'] ?></strong>
                </span>
                <div class="sdk_build_moreinfo_container" id="sdk_build_moreinfo_container_<?php echo $j; ?>">
                  <div class="sdk_build_location">
                    <i><?php echo $this->translate("Location:") ?></i>
                    <p>
                      <?php echo $package['manifest']['package']['path'] ?>
                    </p>
                  </div>
                  <div class="sdk_build_description">
                    <i><?php echo $this->translate("Description:") ?></i>
                    <p>
                      <?php echo $package['manifest']['package']['description'] ?>
                    </p>
                  </div>
                </div>
              </td>
              <td>
                <?php echo $package['manifest']['package']['version'] ?>
              </td>
              <td>
                <?php echo ucfirst($package['manifest']['package']['type']) ?>
              </td>
              <td>
                <?php echo @$package['manifest']['package']['author'] ?>
              </td>
              <td class="moreinfo">
                <a href="javascript:void(0);" onclick="showHide('sdk_build_moreinfo_container_<?php echo $j; ?>');">
                  <?php echo $this->translate("More info") ?>
                </a>
              </td>
            </tr>
            <?php $j++; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
      <button type="submit"><?php echo $this->translate("Build Packages") ?></button>
    </form>
  <?php endif; ?>
</div>
