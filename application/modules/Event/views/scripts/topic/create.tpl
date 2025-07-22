<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: create.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */
?>
<div class="layout_middle">
  <div class="generic_layout_container">
    <div class="breadcrumb_wrap">
      <div class="event_breadcrumb">
        <p><?php echo $this->event->__toString()." ".$this->translate("&#187; Discussions") ?></p>
      </div>
    </div>
  </div>
  <div class="generic_layout_container">
    <div class="topic_create global_form_wrap">
      <?php echo $this->form->render($this) ?>
    </div>
  </div>
</div>
