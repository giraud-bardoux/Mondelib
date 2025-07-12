<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: manage-emoticons.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'childMenuItemName' => 'core_admin_main_settings_emoticons')); ?>

<div class="settings">
  <?php if ($this->hasPermission): ?>
    <p>Click <a class='smoothbox' href='<?php echo $this->url(array('action' => 'add-emoticon'));?>'>
      <?php echo $this->translate("here") ?>
    </a> to add new emoticons. </p>
  <?php else:?>
    <div class="tip">
      <span>
        Please provide chmod 777 permission to directory "/application/modules/Activity/externals/emoticons" to modify emoticons.
      </span>
    </div>
  <?php endif;?>
  <table class='admin_table admin_responsive_table'>
    <thead>
      <tr>
        <th style="width: 1%"><?php echo $this->translate("Emoticon") ?></th>
        <th style="width: 1%"><?php echo $this->translate("Symbol") ?></th>
        <th class="admin_table_centered" style="width: 5%"><?php echo $this->translate("Icon") ?></th>
        <th class='admin_table_options' style="width: 3%"><?php echo $this->translate("Options") ?></th>
      </tr>
    </thead>
    <tbody>
      <?php if( engine_count($this->emoticons) ): ?>
        <?php foreach( $this->emoticons as $symbol => $icon): ?>
          <tr>
            <td data-label="<?php echo $this->translate("Emoticon") ?>" style="width: 1%"><?php $ext = pathinfo($icon, PATHINFO_EXTENSION);
                echo basename($icon, ".".$ext); ?></td>
            <td data-label="<?php echo $this->translate("Symbol") ?>" style="width: 1%" class='admin_table_bold'><?php echo $symbol;?></td>
            <td data-label="<?php echo $this->translate("Icon") ?>" class="admin_table_centered" style="width: 5%"><?php echo "<img src=\"" . $this->layout()->staticBaseUrl .
                    "application/modules/Activity/externals/emoticons/images/$icon\" border=\"0\" alt=\"$2\" />" ;?></td>
            <td class='admin_table_options' style="width: 3%">
              <?php if ($this->hasPermission): ?>
                <a class='smoothbox' href='<?php echo $this->url(array('action' => 'edit-emoticon', 'symbol' => $symbol));?>'>
                  <?php echo $this->translate("edit") ?>
                </a>
               |
              <?php endif;?>
              <a class='smoothbox' href='<?php echo $this->url(array('action' => 'delete-emoticon', 'symbol' => $symbol));?>'>
                <?php echo $this->translate("delete") ?>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
