<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: script.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<script type="text/javascript">

var topLevelId = '0';

function changeFields(element, force)
{
  // We can call this without an argument to start with the top level fields
  if( !$type(element) )
  {
    scriptJquery('.parent_'+topLevelId).each(function(element)
    {
      changeFields(element);
    });
    return;
  }

  // Detect if this is an input or the container
  if(element.hasClass('field_container') )
  {
    element = element.find('.field_input').eq(0);
  }

  // If this cannot have dependents, skip
  if( !$type(element) || !$type(element.onchange) )
  {
    return;
  }

  // Get the input and params
  var params = element.id.split(/[-_]/);
  if( params.length > 3 )
  {
    params.shift();
  }
  force = ( $type(force) ? force : false );

  // Now look and see
  var option_id = element.value;

  // Iterate over children
  scriptJquery('.parent_'+params[2]).each(function(e)
  {
    childElement = scriptJquery(this);
    // Forcing hide
    var nextForce;
    if( force == 'hide' || force == 'show' )
    {
      childElement.css("display",( force == 'hide' ? 'none' : '' ));
      nextForce = force;
    }

    // Hide fields not tied to the current option (but propogate hiding)
    else if( !childElement.hasClass('option_'+option_id) )
    {
      childElement.style.display = 'none';
      nextForce = 'hide';
    }

    // Otherwise show field and propogate (nothing, show?)
    else
    {
      childElement.style.display = '';
      nextForce = undefined;
    }

    changeFields(childElement, nextForce);
  });
}
window.addEventListener('DOMContentLoaded', function()
{
  changeFields();
});

</script>
