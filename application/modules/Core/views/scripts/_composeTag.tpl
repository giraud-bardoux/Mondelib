<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _composeTag.tpl 10245 2014-05-28 18:08:24Z lucas $
 * @author     John
 */
?>
<script type="text/javascript">  
 en4.core.runonce.add(function() {
    try {
      //console.log(Composer.Plugin.Tag,"Composer.Plugin.Tag");
      //console.log(composeInstance,"composeInstance");
        var plugin = new Composer.Plugin.Tag({
          enabled: true,
          suggestOptions : {
            'url' : '<?php echo $this->url(array(), 'default', true) . 'user/friends/suggest?includeSelf=1' ?>',
            'data' : {
              'format' : 'json',
              'includeSelf':true
            }
          },
          'suggestProto' : 'request.json',
          'suggestParam' : [],
        });
        <?php if($this->forEdit) : ?>
          <?php $tags = array(); ?>
          <?php if(!empty($this->action->params['tags'])):?>
            <?php  foreach($this->action->params['tags'] as $userId => $label): ?>
            <?php    $tags [] = array('user_id' => $userId, 'label' => $label); ?>
            <?php  endforeach; ?>
          <?php endif; ?>
          scriptJquery(document).data('editComposeInstanceActivity<?php echo $this->forEdit ?>').addPlugin(plugin);
          plugin.setHiddenTags(<?php echo $this->jsonInline($tags) ?>);
          scriptJquery(document).data('editComposeInstanceActivity<?php echo $this->forEdit ?>').extractTag();
        <?php else: ?>
          composeInstance.addPlugin(plugin);
        <?php endif; ?>


    } catch(err){ 
      console.log(err,"asfdsdfdf");
    } 
 });
</script>
