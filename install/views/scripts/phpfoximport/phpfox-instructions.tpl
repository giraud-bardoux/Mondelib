<?php
/**
* SocialEngine
*
* @category   Application_Extensions
* @package    Phpfoximporter
* @copyright  Copyright 2006-2020 Webligo Developments
* @license    http://www.socialengine.com/license/
* @version    $Id: phpfox-instructions.tpl 2015-05-15 00:00:00Z john $
* @author     John
*/
?>
<div>
  <h3><?php echo $this->translate("PHPfox Import Instructions");?></h3>

  <p>
    <?php echo $this->translate("This PHPfox Import tool is designed to migrate content directly from a PHPfox installation into SocialEngine. It is intended to be used on a fresh install of SocialEngine PHP. It will remove any existing content on the network.");?>
  </p>

  <br />


  <?php if (!empty($this->dbHasContent)): ?>
  <div class="warning">
    <?php echo $this->translate("Your site already has content. The content will be removed if you use this
    import tool.");?>
  </div>
  <br />
  <br />
  <?php endif; ?>


  <ul>
    <li>
      <?php echo $this->translate("The following types of data");?>
      <b style="font-weight: bold;"><?php echo $this->translate("will be removed or overwritten from the existing ");?><em><?php echo $this->translate("SocialEngine PHP");?></em><?php echo $this->translate(" installation");?></b>:
      <ul style="margin-left: 20px;margin-bottom: 10px;padding-top: 4px;list-style: circle;">
        <li>
          <?php echo $this->translate("All admin and user accounts.");?>
        </li>
        <li>
          <?php echo $this->translate("User levels.");?>
        </li>
        <li>
          <?php echo $this->translate("All user content.");?>
        </li>
        <li>
          <?php echo $this->translate("All announcements.");?>
        </li>
        <li>
          <?php echo $this->translate("Files uploaded in the admin panel media manager.");?>
        </li>
        <li>
          <?php echo $this->translate("All admin created categoried (i.e. blog categories, video categories, etc)");?>
        </li>


      </ul>
    </li>
    <li>
      <?php echo $this->translate("The following types of data");?>
      <b style="font-weight: bold;"><?php echo $this->translate("will be removed or overwritten from the existing ");?><em><?php echo $this->translate("SocialEngine PHP");?></em><?php echo $this->translate(" installation");?></b>:
      <ul style="margin-left: 20px;margin-bottom: 10px;padding-top: 4px;list-style: circle;">
        <li>
          <?php echo $this->translate("Any installed plugins, themes, widgets, or language packs.");?>
        </li>
        <li>
          <?php echo $this->translate("Custom pages or changes to existing pages made in the layout editor.");?>
        </li>
      </ul>
    </li>
    <li>
      <?php echo $this->translate("The following types of data");?>
      <b style="font-weight: bold;"><?php echo $this->translate("may be removed or overwritten from the existing ");?><em><?php echo $this->translate("SocialEngine PHP");?></em><?php echo $this->translate(" installation");?></b>:
      <ul style="margin-left: 20px;margin-bottom: 10px;padding-top: 4px;list-style: circle;">
        <li>
          <?php echo $this->translate("Global settings");?>
        </li>
        <li>
          <?php echo $this->translate("Level settings");?>
        </li>
      </ul>
    </li>
  </ul>
  <div class="warning">
    <?php echo $this->translate("Note: In Order to proceed with the import process from phpfox, you need to enter the Youtube API Key in the Global Settings of the Video Plugin in your SocialEngine installation.");?>
  </div>
  <br />

  <button type="button" style="margin:10px 0px" id="continue" name="continue"
          onclick="window.location.href = '<?php echo $this->url(array('action' => 'phpfox')) ?>';
                      return false;">
    <?php echo $this->translate("Start Import");?>
  </button>

</div>
