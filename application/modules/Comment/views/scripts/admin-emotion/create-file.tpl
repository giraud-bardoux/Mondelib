<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: create-file.tpl 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
 
?>
<script type="text/javascript">
  var contentAutocomplete =  'tags';
  en4.core.runonce.add(function() {
    var cache = {};
    scriptJquery('#tags').autocomplete({
      source: function (request, response) { 
        scriptJquery.ajax({
          url: '<?php echo $this->url(array('controller' => 'tag', 'action' => 'suggest'), 'default', true) ?>',
          data: { text: request.term },
          success: function (transformed) {
            response(transformed);
          },
          error: function () {
              response([]);
          }
        });
      },
      select: function(event, ui) { 
      },
    });
  });
</script>
<div class='settings'>
  <?php echo $this->form->render($this); ?>
</div>
