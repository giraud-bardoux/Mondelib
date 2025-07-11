<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _emoji.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<?php $staticBaseUrl = $this->layout()->staticBaseUrl; ?>
<div class="emoji_contents">
  <?php
  if ($this->edit)
    $class = "edit";
  else
    $class = '';
  $emojis = Engine_Api::_()->activity()->getEmoticons(); ?>
  <div>
    <ul class="_simemoji custom_scrollbar">
      <?php
      foreach ($emojis as $key => $emoji) { ?>
        <li rel="<?php echo $key; ?>"><a href="javascript:;"
            class="select_emoji_adv<?php echo $class; ?>"><?php echo "<img src=\"" . $staticBaseUrl . "application/modules/Activity/externals/emoticons/images/$emoji\" border=\"0\"/>" ?><?php //echo $emoji; ?></a>
        </li>
      <?php
      } ?>
    </ul>
  </div>
  <?php if (!$this->edit) { ?>
    <script type="application/javascript">
      AttachEventListerSE('click', '.select_emoji_adv > img', function (e) {
        var code = scriptJquery(this).parent().parent().attr('rel');
        var html = scriptJquery('.compose-content').html();
        if (html == '<br>')
          scriptJquery('.compose-content').html('');
        composeInstance.setContent(composeInstance.getContent() + ' ' + code);
      });
    </script>
  <?php } ?>
</div>