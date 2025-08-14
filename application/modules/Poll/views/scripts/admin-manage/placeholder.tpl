<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: placeholder.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */
?>

<h2><?php echo $this->translate("Poll Plugin") ?></h2>

<?php if( engine_count($this->navigation) ): ?>
  <div class='tabs'>
    <?php
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>

  <div class='clear'>
    <div class='settings'>

      <?php echo $this->message; ?>

    </div>
  </div>
