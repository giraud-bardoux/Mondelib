<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<h3><?php echo $this->translate("Import Tools")?></h3>
<p><?php echo $this->translate("Below is a list of supported import tools that can be utilized for importing your data into SocialEngine.")?></p>
<p><?php echo $this->translate("More info: ")?><a href="https://community.socialengine.com/blogs/597/125/phpfox-importer" target="_blank"><?php echo $this->translate("See KB article")?></a>.</p>

<ul class="import_tools_list">
  <?php foreach( $this->importers as $importer ): ?>
    <li>
      <a class="buttonlink" href="<?php echo $this->url($importer['url']) ?>">
        <?php echo $this->translate($importer['title']); ?>
      </a>
      <p class="buttontext">
        <?php echo $this->translate($importer['description']); ?>
      </p>
    </li>
  <?php endforeach; ?>
</ul>
