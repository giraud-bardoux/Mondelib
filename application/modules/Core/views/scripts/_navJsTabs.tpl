<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _navJsTabs.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<script type='text/javascript'>
  var containerPrefix = '<?php echo ( !empty($this->container->containerPrefix) ? $this->container->containerPrefix : 'user_profile_index-main-middle-' ) ?>';
  function switchTab(identity)
  {
    var container = scriptJquery('#global_content').find('.layout_' + identity);
    
    scriptJquery('.tab_links').each(function(e)
    {
      var element = scriptJquery(this);
      var localIdentity = element.attr('id').replace('tab_link_', '');
      var localContainer = scriptJquery('#global_content').find('.layout_' + localIdentity)

      // If missing container
      if(localContainer.length) {
        localContainer.css('display', 'none');
        element.css('display', 'none');
        return;
      }
      
      // Show
      if( element.attr('id') == 'tab_link_' + identity )
      {
        if( !localContainer.hasClass('tab_container_active') )
        {
          localContainer.addClass('tab_container_active');
          localContainer.removeClass('tab_container_inactive');
        }
        if( !element.hasClass('tab_active') )
        {
          element.addClass('tab_active');
          element.removeClass('tab_inactive');
        }
      }

      // Hide
      else
      {
        if( !localContainer.hasClass('tab_container_inactive') )
        {
          localContainer.addClass('tab_container_inactive');
          localContainer.removeClass('tab_container_active');
        }
        if( !element.hasClass('tab_inactive') )
        {
          element.addClass('tab_inactive');
          element.removeClass('tab_active');
        }
      }
    });
  }

</script>

<div class='tabs'>
  <ul>
    <?php foreach( $this->container as $link ): ?>
      <li id="tab_link_<?php echo $link->getClass() ?>" class="tab_links tab_link_<?php echo $link->getClass().( $link->isActive() ? ' tab_active' : ' tab_inactive' ) ?>"><a href="<?php echo $link->getHref() ?>" onclick="switchTab('<?php echo $link->getClass()?>'); return false;"><?php echo $link->getLabel() ?></a></li>
    <?php endforeach; ?>
  </ul>
</div>
