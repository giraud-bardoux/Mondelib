<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sesbasic
 * @package    Sesbasic
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: _customFields.tpl 2015-10-11 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<script type="text/javascript">
//en4.core.runonce.add(function() {

  var topLevelId = '<?php echo sprintf('%d', (int) @$this->topLevelId) ?>';
  var topLevelValue = '<?php echo sprintf('%d', (int) @$this->topLevelValue) ?>';
  var elementCache = {};

  function getFieldsElements(selector) {
    if( selector in elementCache || $type(elementCache[selector]) ) {
      return elementCache[selector];
    } else {
      return elementCache[selector] = scriptJquery(selector);
    }
  }
  
  function updateFieldValue(element, value) {
  
    if( (element.prop('tagName') ?? "").toLowerCase() == 'option' ) {
      element = element.parents('select:first');
    } else if(element.attr('type') == 'checkbox' || element.attr('type') == 'radio' ) {
      element.prop('checked', Boolean(value));
      return;
    }
    if (element.prop("tagName") == 'SELECT') {
      if (element.attr('multiple')) {
        element.find('option').each(function(subEl){
          scriptJquery(this).prop('selected', false);
        });
      }
    }
    if( element ) {
      element.val(value);
    }
  }

	var valueNotChanged;
	var changeFields = window.changeFields = function(element, force, isLoad,valueNotChanged,resets) {

    if(element)
			element = scriptJquery(element);
		
		// We can call this without an argument to start with the top level fields
    if(!element || !element.length) {
      scriptJquery('.parent_' + topLevelId).each(function(element) {
        element = scriptJquery(this);
        let parent_field_id = element.attr('class').match(/option_([\d]+)/i)[1];
        changeFields(element, force, isLoad,parent_field_id);
      });
      return;
    }

    // If this cannot have dependents, skip
    if( !$type(element) || !$type(element.attr("onchange")) ) {
      return;
    }
    
    // Get the input and params
    var field_id = element.attr('class').match(/field_([\d]+)/i)[1];
    var parent_field_id = element.attr('class').match(/parent_([\d]+)/i)[1];
    var parent_option_id = element.attr('class').match(/option_([\d]+)/i)[1];
    
    if( !field_id || !parent_option_id || !parent_field_id ) {
      return;
    }

    force = ( $type(force) ? force : false );
    
    // Now look and see
    // Check for multi values
    var option_id = [];
    var isRadio = true;
    if( $type(element.attr("name")) && element.attr("name").indexOf('[]') > -1 ) {
      if(element.attr("type") == 'checkbox' ) { // MultiCheckbox
        scriptJquery('.field_' + field_id).each(function(multiEl) {
          multiEl = scriptJquery(this);
          if( multiEl.prop('checked')) {
            option_id.push(multiEl.val());
          }
        });
      } else if( element.prop("tagName") == 'SELECT' && element.attr("multiple") ) { // Multiselect
        element.children().each(function(multiEl) {
          if(scriptJquery(this).prop('selected')) {
            option_id.push(this.value);
          }
        });
      }
    } else if( element.attr("type") == 'radio' ) {
      if(element.prop('checked')) {
        option_id = [element[0].value];
      } else {
        isRadio = false;
      }
    } else {
      option_id = [element[0].value];
    }

    var executed = false;
   
		// Iterate over children
    scriptJquery('.parent_' + field_id).each(function(childElement) {

      childElement = scriptJquery(this);
      var childContainer  = [];
      if(childElement.closest('form').hasClass('field_search_criteria')) {
        childContainer = childElement.parent().prop("tagName") == 'LI' && childElement.parent().parent().parent().prop("tagName") == 'LI' ? childElement.parent().parent().parent() : childElement.closest('li');
      }
      if(childContainer.length == 0 ) {
        childContainer = childElement.closest('div.form-wrapper-heading');
      }
      if(childContainer.length == 0) {
         childContainer = childElement.closest('div.form-wrapper');
      }
      if( childContainer.length == 0) {
        childContainer = childElement.closest('li');
      }

      var childOptions = childElement.attr('class').match(/option_([\d]+)/gi);
      for(var i = 0; i < childOptions.length; i++) {
        for(var j = 0; j < option_id.length; j++) {
          if(childOptions[i] == "option_" + option_id[j]) {
            var childOptionId = option_id[j];
            break;
          }
        }
      }

      var childIsVisible = ( 'none' != childContainer.css('display') );
      var skipPropagation = false;

      // Forcing hide
			if(isLoad != 'yes'){
        
				if(typeof valueNotChanged == 'string' || typeof valueNotChanged == 'number'){
				if(typeof valueNotChanged == 'number')
					var valueId = [valueNotChanged];
				else
					var valueId = valueNotChanged.split(',');
					for(var i =0 ; i<valueId.length;i++){           
						if(scriptJquery(childElement).hasClass('option_'+valueId[i])){
              if(scriptJquery(childElement).parent().hasClass('form-wrapper-heading')){
                scriptJquery(childElement).parent().show();
              }else{
								  scriptJquery(childElement).closest('div').parent().css('display','block');
              }
              executed = true;
              updateFieldsProfileSES(childElement);
						  return;
						}	
					}
				} else if(scriptJquery(childElement).hasClass('option_'+valueNotChanged)){
						if(scriptJquery(childElement).hasClass('option_'+valueId[i])){
								if(scriptJquery(childElement).parent().hasClass('form-wrapper-heading'))
									scriptJquery(childElement).parent().show();
								else{
									scriptJquery(childElement).closest('div').parent().css('display','block');
								}
								executed = true;
								updateFieldsProfileSES(childElement);
								return;
						}
				}
			}
     
      if(scriptJquery(element).attr('id') == '0_0_1')
        updateFieldsProfileSES(childElement,resets);

			if(scriptJquery(childElement).parent().hasClass('form-wrapper-heading')){
				scriptJquery(childElement).parent().hide();
			} else {
				scriptJquery(childElement).closest('div').parent().css('display','none');
			}
			updateFieldValue(childElement, null,valueNotChanged);	
    });
    
    if(scriptJquery(element).attr('id') != '0_0_1'){
      updateFieldsProfileSES(element,resets);
    }
    scriptJquery(window).trigger('onChangeFields');
  }

	function updateFieldsProfileSES(childElement,resets){
		if(typeof resets != 'undefined')
			updateFieldValue(childElement,null);
		var field_id_child =  childElement.attr('class').match(/field_([\d]+)/i)[1];
		
		if(typeof childElement.name != 'undefined' && childElement.name.indexOf('[]') > 0 ) {
			var checked = scriptJquery('.field_' + field_id_child).is(':checked');
			if( childElement.prop("type") == 'checkbox' ) {
				scriptJquery("input[name='"+scriptJquery(childElement).attr('name')+"']").each(function(index, element) {
					// MultiCheckbox        
					var checked = scriptJquery(this).is(':checked');
				if(scriptJquery('.parent_'+field_id_child).parent().hasClass('form-wrapper-heading')){
					if(!checked)
						scriptJquery('.parent_'+field_id_child).parent().hide();
					else
						scriptJquery('.option_'+scriptJquery(childElement).val()+'.parent_'+field_id_child).parent().show();
				}else{
						var elemClass =  scriptJquery(childElement).attr('class');
						if(typeof elemClass != 'undefined'){
							var fieldId = elemClass.match(/field_([\d]+)/i)[1];
							if(scriptJquery('.parent_'+fieldId).parent().hasClass('form-wrapper-heading')){
								if(!checked)
									scriptJquery('.parent_'+fieldId).parent().hide();
								else
									scriptJquery('.parent_'+fieldId).parent().show();
							}else{
									if(!checked && scriptJquery('.parent_'+field_id_child).hasClass('option_'+scriptJquery(this).val())){
									scriptJquery('.option_'+scriptJquery(this).val()+'.parent_'+fieldId).closest('div').parent().each(function(){
										scriptJquery(this).css('display','none');
										updateFieldValue(scriptJquery(this),null);
										if(scriptJquery('.parent_'+field_id_child).length){
											scriptJquery('.parent_'+field_id_child).each(function(){
												updateFieldsProfileSES(scriptJquery(this),resets);
											})
										}else
											return;
									})
									}else if(scriptJquery('.parent_'+field_id_child).hasClass('option_'+scriptJquery(this).val())){
									scriptJquery('.option_'+scriptJquery(this).val()+'.parent_'+fieldId).closest('div').parent().show();
									}
							}
						}
				}
				});
			} else if( childElement.get('type') == 'select' && childElement.multiple ) { // Multiselect
					scriptJquery(childElement).find("option").each(function(index, item) {
					var checked = scriptJquery(item).is(':selected');
					if(scriptJquery('.parent_'+field_id_child).parent().hasClass('form-wrapper-heading')){
							if(!checked && scriptJquery('.parent_'+field_id_child).hasClass('option_0')){
							scriptJquery('.parent_'+field_id_child).parent().hide();
							}
						else if(scriptJquery('.parent_'+field_id_child).hasClass('option_'+scriptJquery(childElement).val()))
							scriptJquery('.option_'+scriptJquery(childElement).val()+'.parent_'+field_id_child).parent().show();
					}else{
							var elemClass =  scriptJquery(childElement).attr('class');
							if(typeof elemClass != 'undefined'){
								var fieldId = elemClass.match(/field_([\d]+)/i)[1];
									if(scriptJquery('.parent_'+fieldId).parent().hasClass('form-wrapper-heading')){
										if(!checked && scriptJquery('.parent_'+field_id_child).hasClass('option_0')){
											scriptJquery('.parent_'+fieldId).parent().hide();
										}
										else if(scriptJquery('.parent_'+field_id_child).hasClass('option_'+scriptJquery(this).val()))
											scriptJquery('.parent_'+fieldId).parent().show();
									}else{
										if(!checked && scriptJquery('.parent_'+field_id_child).hasClass('option_'+scriptJquery(this).val())){
										scriptJquery('.option_'+scriptJquery(this).val()+'.parent_'+fieldId).closest('div').parent().each(function(){
											scriptJquery(this).css('display','none');
											updateFieldValue(scriptJquery(this),null);
											if(scriptJquery('.parent_'+field_id_child).length){
												scriptJquery('.parent_'+field_id_child).each(function(){
													updateFieldsProfileSES(scriptJquery(this),resets);
												})
											}else
												return;
										})
										} else if(scriptJquery('.parent_'+field_id_child).hasClass('option_'+scriptJquery(this).val())){
										scriptJquery('.option_'+scriptJquery(this).val()+'.parent_'+fieldId).closest('div').parent().show();
										}
									}
								}
							}
				});
			}
		} else if( childElement.prop("type") == 'radio' ) {
			scriptJquery('input[name='+scriptJquery(childElement).attr('name')+']').each(function(index, item){
			var checked = scriptJquery(item).is(':checked');
			if(scriptJquery('.parent_'+field_id_child).parent().hasClass('form-wrapper-heading')){
					if(!checked && scriptJquery('.parent_'+field_id_child).hasClass('option_0'))
						scriptJquery('.parent_'+field_id_child).parent().hide();
					else if(scriptJquery('.parent_'+field_id_child).hasClass('option_'+scriptJquery(childElement).val()))
						scriptJquery('.option_'+scriptJquery(childElement).val()+'.parent_'+field_id_child).parent().show();
				}else{
						var elemClass =  scriptJquery(childElement).attr('class');
						if(typeof elemClass != 'undefined'){
							var fieldId = elemClass.match(/field_([\d]+)/i)[1];
							if(scriptJquery('.parent_'+fieldId).parent().hasClass('form-wrapper-heading')){
									if(!checked && scriptJquery('.parent_'+field_id_child).hasClass('option_0'))
									scriptJquery('.parent_'+fieldId).parent().hide();
								else if(scriptJquery('.parent_'+field_id_child).hasClass('option_'+scriptJquery(this).val()))
									scriptJquery('.parent_'+fieldId).parent().show();
							}else{
								if(!checked && scriptJquery('.parent_'+field_id_child).hasClass('option_'+scriptJquery(this).val())){
									scriptJquery('.option_'+scriptJquery(this).val()+'.parent_'+fieldId).closest('div').parent().each(function(){
										scriptJquery(this).css('display','none');
										updateFieldValue(scriptJquery(this),null);
										if(scriptJquery('.parent_'+field_id_child).length){
											scriptJquery('.parent_'+field_id_child).each(function(){
												updateFieldsProfileSES(scriptJquery(this),resets);
											})
										}else
											return;
									})
									}else if(scriptJquery('.parent_'+field_id_child).hasClass('option_'+scriptJquery(this).val())){
									scriptJquery('.option_'+scriptJquery(this).val()+'.parent_'+fieldId).closest('div').parent().show();
									}
							}
						}
						
				}
				}); 
		} else if(childElement.prop("type") == 'select-one') {
			scriptJquery('#'+scriptJquery(childElement).attr('id')+' option').each(function() {
				
				var checked = scriptJquery(this).is(':selected');
				if(scriptJquery('.parent_'+field_id_child).parent().hasClass('form-wrapper-heading')){
					if(!checked && scriptJquery('.parent_'+field_id_child).hasClass('option_0')){
						scriptJquery('.parent_'+field_id_child).parent().hide();
					}
					else if(scriptJquery('.parent_'+field_id_child).hasClass('option_'+scriptJquery(childElement).val())){
						scriptJquery('.option_'+scriptJquery(childElement).val()+'.parent_'+field_id_child).parent().show();
					}
				}else{
						var elemClass =  scriptJquery(childElement).attr('class');
						if(typeof elemClass != 'undefined'){
							var fieldId = elemClass.match(/field_([\d]+)/i)[1];
							if(scriptJquery('.parent_'+fieldId).parent().hasClass('form-wrapper-heading')){
								if(!checked && scriptJquery('.parent_'+field_id_child).hasClass('option_0')){
									scriptJquery('.parent_'+fieldId).parent().hide();
								}
								else if(scriptJquery('.parent_'+field_id_child).hasClass('option_'+scriptJquery(this).val()))
									scriptJquery('.parent_'+fieldId).parent().show();
							}else{
									if(!checked && scriptJquery('.parent_'+field_id_child).hasClass('option_'+scriptJquery(this).val())){
									scriptJquery('.option_'+scriptJquery(this).val()+'.parent_'+fieldId).closest('div').parent().each(function(){
										scriptJquery(this).css('display','none');
										updateFieldValue(scriptJquery(this),null);
										if(scriptJquery('.parent_'+field_id_child).length){
											scriptJquery('.parent_'+field_id_child).each(function(){
												updateFieldsProfileSES(scriptJquery(this),resets);
											})
										} else
											return;
									});
									} else if(scriptJquery('.parent_'+field_id_child).hasClass('option_'+scriptJquery(this).val())) {
										scriptJquery('.option_'+scriptJquery(this).val()+'.parent_'+fieldId).closest('div').parent().show();
									}
							}
						}
					}
			});
		} else {
			if(scriptJquery('.parent_'+field_id_child).parent().hasClass('form-wrapper-heading')){
				//scriptJquery('.parent_'+field_id_child).parent().hide();
			} else {
				var elemClass =  scriptJquery('.parent_'+field_id_child).attr('class');
				if(typeof elemClass != 'undefined'){
					var fieldId = elemClass.match(/field_([\d]+)/i)[1];
					if(scriptJquery('.parent_'+fieldId).parent().hasClass('form-wrapper-heading')){
						//scriptJquery('.parent_'+fieldId).parent().hide();
					}else{
						updateFieldValue(scriptJquery('.parent_'+fieldId),null);
						scriptJquery('.parent_'+fieldId).closest('div').parent().css('display','none');
					}
				}
				scriptJquery('.parent_'+field_id_child).val('');
				scriptJquery('.parent_'+field_id_child).closest('div').parent().css('display','none');
			}
		}
	}
//});
</script>
