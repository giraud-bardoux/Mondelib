<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<ul class="popular_tags">
  <?php
  $url = $this->url(array(),'core_hashtags','true')."?search=";
  for ($i = 0; $i < engine_count($this->hashtags); $i++): ?>
    <li>
      <a href='<?php echo $url.urlencode(trim($this->hashtags[$i]['tagName'], '#')); ?>'>
        <?php echo $this->hashtags[$i]['tagName']; ?>
      </a>
      <span class="font_color_light"><?php echo $this->translate(array('%s people talking about this.', '%s peoples talking about this.', $this->hashtags[$i]['tagCount']), $this->locale()->toNumber($this->hashtags[$i]['tagCount']))?></span>
    </li>
  <?php endfor;?>
</ul>
