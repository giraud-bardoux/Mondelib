<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _jsAdmin.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<script type="text/javascript">

  var fieldType = '<?php echo $this->fieldType ?>';
  var topLevelFieldId = '<?php echo sprintf('%d', $this->topLevelFieldId) ?>';
  var topLevelOptionId = '<?php echo sprintf('%d', $this->topLevelOptionId) ?>';
  var logging = true;
  var sortablesInstance;
  var urls = {
    option : {
      create : '<?php echo $this->url(array('action' => 'option-create')) ?>',
      edit : '<?php echo $this->url(array('action' => 'option-edit')) ?>',
      remove : '<?php echo $this->url(array('action' => 'option-delete')) ?>'
    },
    field : {
      create : '<?php echo $this->url(array('action' => 'field-create')) ?>',
      edit : '<?php echo $this->url(array('action' => 'field-edit')) ?>',
      remove : '<?php echo $this->url(array('action' => 'field-delete')) ?>'
    },
    map : {
      create : '<?php echo $this->url(array('action' => 'map-create')) ?>',
      remove : '<?php echo $this->url(array('action' => 'map-delete')) ?>'
    },
    type : {
      create : '<?php echo $this->url(array('action' => 'type-create')) ?>',
      edit : '<?php echo $this->url(array('action' => 'type-edit')) ?>',
      remove : '<?php echo $this->url(array('action' => 'type-delete')) ?>'
    },
    heading : {
      create : '<?php echo $this->url(array('action' => 'heading-create')) ?>',
      edit : '<?php echo $this->url(array('action' => 'heading-edit')) ?>',
      remove : '<?php echo $this->url(array('action' => 'heading-delete')) ?>'
    },
    order : '<?php echo $this->url(array('action' => 'order')) ?>',
    index : '<?php echo $this->url(array('action' => 'index')) ?>'
  };

  window.addEventListener('DOMContentLoaded', function() {
    registerEvents();
  });

  // Register all events
  var registerEvents = function() {

    // Attach change profile type
    if(scriptJquery('#profileType') ) {
      scriptJquery('#profileType').off().on('change', uiChangeProfileType);
    }

    // Attach create field (top level)
    scriptJquery('.admin_fields_options_addquestion').off().on('click', uiSmoothTopFieldCreate);

    // Attach create heading (top level)
    scriptJquery('.admin_fields_options_addheading').off().on('click', uiSmoothTopHeadingCreate);

    // Attach create option (top level)
    scriptJquery('.admin_fields_options_addtype').off().on('click', uiSmoothTopOptionCreate);

    // Attach edit option (top Level)
    scriptJquery('.admin_fields_options_renametype').off().on('click', uiSmoothTopOptionEdit);

    // Attach delete option (top level)
    scriptJquery('.admin_fields_options_deletetype').off().on('click', uiSmoothTopOptionDelete);


    // Attach options activator
    scriptJquery('.field_extraoptions > a').off().on('click', uiToggleOptions);

    // Attach create options input
    scriptJquery('.field_extraoptions_add > input').off().on('keypress', uiTextOptionCreate);

    // Attach edit options activator
    scriptJquery('.field_extraoptions_choices_options > a:first-child').off().on('click', uiSmoothOptionEdit);

    // Attach delete options activator
    scriptJquery('.field_extraoptions_choices_options > a + a').off().on('click', uiConfirmOptionDelete);

    // Attach toggle dependent fields
    scriptJquery('.field_option_select > span + span').off().on('click', uiToggleOptionDepFields);
    scriptJquery('.dep_hide_field_link').off().on('click', uiToggleOptionDepFields);

    // Attach create field in option
    scriptJquery('.dep_add_field_link').off().on('click', uiSmoothCreateField);

    // Attach edit field
    scriptJquery('.field > .item_options > a:first-child').off().on('click', uiSmoothEditField);

    // Attach delete field
    scriptJquery('.field > .item_options > a + a').off().on('click', uiConfirmDeleteField);

    // Attach heading edit
    scriptJquery('.heading > .item_options > a:first-child').off().on('click', uiSmoothEditHeading);

    // Attach heading edit
    scriptJquery('.heading > .item_options > a:last-child').off().on('click', uiConfirmDeleteField);


    // Attach over text
    //scriptJquery('.field_extraoptions_add input').each(function(el){ new OverText(el); });


    // Attach sortables
    if( !sortablesInstance ) {
      SortablesInstance = scriptJquery('.admin_fields, .field_extraoptions_choices').sortable({
        helper: "clone",
        handle : '.item_handle',
        stop: function( event, ui ) {
					saveOrder();
          //showSaveOrderButton(event, ui )
        }
      });
    }
  }

  // Read the parent-option-child identifiers
  var readIdentifiers = function(string, throwException) {
    var m;

    // Find in ID
    m = string.match(/([0-9]+)_([0-9]+)_([0-9]+)(_([0-9]+))?/);
    if( $type(m) && $type(m[2]) ) {
      var dat = new Hash({
        parent_id : m[1],
        option_id : m[2],
        child_id : m[3]
      });
      if( $type(m[5]) ) {
        dat.set('suboption_id', m[5]);
      }
      return dat;
    }

    // Find in CLASS
    m = string.match(/parent_([0-9]+).+option_([0-9]+).+child_([0-9]+)/);
    if( $type(m) && $type(m[2]) ) {
      return new Hash({
        parent_id : m[1],
        option_id : m[2],
        child_id : m[3]
      });
    }

    // Not found
    if( !$type(throwException) || throwException ) {
      throw '<?php echo $this->string()->escapeJavascript($this->translate("Unable to find identifiers in text:")) ?> ' + string;
    } else {
      return false;
    }
  }

  var consoleLog = function() {
    //if( logging && typeof(console) != 'undefined' && console != null ) {
    if( logging ) {
      //if( typeof(console) !== 'undefined' && console != null ) {
      //  console.log(arguments);
        //console.log.apply(null, arguments);
      //}
    }
  }

  var genericUpdateKeys = function(htmlArr) {
    
    Object.entries(htmlArr).forEach(function([key, html]) {
      var oldEl = scriptJquery('#admin_field_' + key);
      var newEl = scriptJquery(html);
      if( oldEl.length && !html) { // Remove
        
        oldEl.remove();
      } else if( oldEl.length && html ) { // Replace
        
        oldEl.replaceWith(newEl);
      } else if( !oldEl.length && html ) { // Add
        
        // This could cause future replaces
        var ids = readIdentifiers(key);

        if( ids.option_id == topLevelOptionId ) {
          var targetEl = scriptJquery('.admin_fields').eq(0);
          if( !targetEl.length ) {
            //throw '<?php echo $this->string()->escapeJavascript($this->translate("could not find target element")) ?>';
          } else {
            newEl.appendTo(targetEl);
          }
        } else {
          var selector =
            '.admin_field_dependent_field_wrapper_' + ids.option_id +
            ' .admin_fields';
          var targetEl = scriptJquery(selector).eq(0);
          if( !targetEl.length ) {
            //throw '<?php echo $this->string()->escapeJavascript($this->translate("could not find target element")) ?>';
          } else {
            targetEl.append(newEl);
          }
        }
      }
    });
    registerEvents();
  }

  var showSaveOrderButton = function() {
    scriptJquery('.admin_fields_options_saveorder').css('display', '').off().on('click', function() {
      saveOrder();
    });
  }

  var saveOrder = function() {
    scriptJquery('.admin_fields_options_saveorder').hide();

    // Generate order structure
    var fieldOrder = [];
    var optionOrder = [];
    scriptJquery('#global_content').append("<div class='admin_loading_icon' id='admin_loading_icon'><img src='application/modules/Core/externals/images/large-loading.gif' /></div>");
    // Fields (maps) order
    scriptJquery('.admin_field').each(function(e) {
      var el = scriptJquery(this);
      var ids = readIdentifiers(el.attr('id'));
      fieldOrder.push(ids.getClean());
    });

    // Options order
    scriptJquery('.field_option_select').each(function(e) {
      var el = scriptJquery(this);
      var ids = readIdentifiers(el.attr('id'));
      optionOrder.push(ids.getClean());
    });
    scriptJquery.ajax({
      url: urls.order,
      type: "POST",
      dataType: 'json',
      data : {
        format : 'json',
        fieldType : fieldType,
        fieldOrder : fieldOrder,
        optionOrder : optionOrder
      },
      success: function(responseJSON){
        scriptJquery("#admin_loading_icon").remove();
      }
    });

  }

  /* --------------------------- OPTION - GENERAL --------------------------- */

  var uiToggleOptions = function(spec, forceClose) {
    var element = scriptJquery.isWindow(this) ? scriptJquery(spec) : scriptJquery(this);
    element = element.parents('.admin_field:first').find('.field_extraoptions:first');
    var targetState = !element.hasClass('active');
    if( $type(forceClose) && !forceClose ) targetState = false;
    !targetState ? element.removeClass('active') : element.addClass('active');
    //OverText.update();
  }

  var uiToggleOptionDepFields = function(event) {
    let depelement = scriptJquery(this);
    let element;
    if(depelement.closest('.field_option_select').length > 0) {
      element = depelement.closest('.field_option_select')
    } else {
      element = depelement.closest('.admin_field_dependent_field_wrapper');
    }

    var ids = readIdentifiers(element.attr('id'));
    var wrapper = element.parents('.admin_field').find('.admin_field_dependent_field_wrapper_' + ids.suboption_id);
    var hadClass = wrapper.hasClass('active');
    scriptJquery('.admin_field_dependent_field_wrapper').removeClass('active');
    hadClass ? wrapper.removeClass('active') : wrapper.addClass('active');
    uiToggleOptions(element, false);

    // Make sure parents stay open
    var tmpEl = element;
    while( 0 !=(tmpEl.parents('.admin_field_dependent_field_wrapper').length) &&  (tmpEl = tmpEl.parents('.admin_field_dependent_field_wrapper')) ) {
      tmpEl.addClass('active');
    }
  }

  var uiChangeProfileType = function(event) {
    var option_id = scriptJquery(this).val();
    var url = new URL(window.location);
    url.searchParams.set('option_id',option_id);
    window.location = url;
  }

  /* --------------------------- OPTION - CREATE --------------------------- */

  // Handle the ui stuff for creating an option using a text input
  var uiTextOptionCreate = function(event) {
    if( event.key.toLowerCase() != 'enter' ) {
      return;
    }
    var element = scriptJquery(this);
    var ids = readIdentifiers(element.parents('.field_extraoptions').attr('id'));
    doOptionCreate(ids.child_id, element.val());
    element.val('');
    element.blur();
  }

  // Handle ui stuff for creating an option using a smoothbox
  var uiSmoothOptionCreate = function(field_id) {
    var url = urls.option.create;
    url += '/field_id/' + field_id + '/format/smoothbox';
    Smoothbox.open(url);
  }

  var uiSmoothTopOptionCreate = function(spec) {
    var url = urls.type.create;
    url += '/field_id/' + topLevelFieldId + '/format/smoothbox';
    Smoothbox.open(url);
  }

  // Handle data for option creation
  var doOptionCreate = function(field_id, label) {
    var url = urls.option.create;
    scriptJquery.ajax({
      url: url,
      type: "POST",
      dataType: 'json',
      data : {
        format : 'json',
        fieldType : fieldType,
        field_id : field_id,
        label : label
      },
      success: function(responseJSON){
        onOptionCreate(responseJSON.option, responseJSON.htmlArr);
      }
    });
  }            

  var onOptionCreate = function(option, htmlArr) {
    genericUpdateKeys(htmlArr);
  }

  var onTypeCreate = function(option) {
    (scriptJquery.crtEle('option', {
      'label' : option.label,
      'value' : option.option_id
    })).html(option.label).appendTo(scriptJquery('#profileType'));
  }

  /* ---------------------------- OPTION - EDIT ---------------------------- */

  // Handle ui stuff for creating an option using a smoothbox
  var uiSmoothOptionEdit = function(option) {
    var el = scriptJquery(this);
    var ids = readIdentifiers(el.parents('.field_option_select').attr('id'));
    if( !$type(ids.suboption_id) ) {
      throw "no option id found";
    }
    option = ids.suboption_id;
    uiToggleOptions(el);
    var url = urls.option.edit;
    url += '/option_id/' + option + '/format/smoothbox';
    Smoothbox.open(url);
  }

  var uiSmoothTopOptionEdit = function(spec) {
    var url = urls.type.edit;
    url += '/option_id/' + topLevelOptionId + '/format/smoothbox';
    Smoothbox.open(url);
  }

  var onOptionEdit = function(option, htmlArr) {
    genericUpdateKeys(htmlArr);
  }

  var onTypeEdit = function(option) {
    scriptJquery('#profileType').children().each(function(el){
      var el = scriptJquery(this);
      if( el.val() == option.option_id ) {
        el.attr('label', option.label);
        el.html(option.label);
      }
    });
  }

  /* --------------------------- OPTION - DELETE --------------------------- */

  var uiConfirmOptionDelete = function(spec) {
    element = scriptJquery(this);
    element = element.parents('.field_option_select');
    var ids = readIdentifiers(element.attr('id'));
    if( !$type(ids.suboption_id) ) {
      throw '<?php echo $this->string()->escapeJavascript($this->translate("unable to find option_id")) ?>';
    }

    if( confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to delete this option?")) ?>') ) {
      doOptionDelete(ids.suboption_id);
    }
  }

  var uiSmoothTopOptionDelete = function(spec) {
    var url = urls.type.remove;
    url += '/option_id/' + topLevelOptionId + '/format/smoothbox';
    Smoothbox.open(url);
  }

  var doOptionDelete = function(option_id) {
    scriptJquery('.field_option_select_' + option_id).remove();
    scriptJquery('.admin_field_dependent_field_wrapper_' + option_id).remove();
    var url = urls.option.remove;
    scriptJquery.ajax({
      url: url,
      type: "POST",
      dataType: 'json',
      data : {
        format : 'json',
        fieldType : fieldType,
        option_id : option_id,
      },
      success: function(responseJSON){
      }
    });
  }

  var onTypeDelete = function() {
    window.location = urls.index;
  }

  /* ---------------------------- FIELD - CREATE ---------------------------- */

  var uiSmoothCreateField = function(spec) {
    var element = scriptJquery(this);
    var parentEl = element.parents('.admin_field_dependent_field_wrapper');
    var ids = readIdentifiers(parentEl.attr('id'));
    var url = urls.field.create;
    url += '/option_id/' + ids.suboption_id + '/parent_id/' + ids.parent_id + '/format/smoothbox';
    Smoothbox.open(url);
  }

  var uiSmoothTopFieldCreate = function(spec) {
    var url = urls.field.create;
    url += '/option_id/' + topLevelOptionId + '/parent_id/' + topLevelFieldId + '/format/smoothbox';
    Smoothbox.open(url);
  }

  var onFieldCreate = function(field, htmlArr) {
    genericUpdateKeys(htmlArr);
  }

  /* ----------------------------- FIELD - EDIT ----------------------------- */

  var uiSmoothEditField = function(spec) {
    var element = scriptJquery(this);
    var parentEl = element.parents('.admin_field');
    var ids = readIdentifiers(parentEl.attr('id'));
    var url = urls.field.edit;
    url += '/field_id/' + ids.child_id + '/format/smoothbox';
    Smoothbox.open(url);
  }

  var onFieldEdit = function(field, htmlArr) {
    genericUpdateKeys(htmlArr);
  }

  /* ---------------------------- FIELD - DELETE ---------------------------- */

  var uiConfirmDeleteField = function(spec) {
    var element = scriptJquery(this);
    var parentEl = element.parents('.admin_field');
    var ids = readIdentifiers(parentEl.attr('id'));
    var url = urls.field.edit;
    if( confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to delete this field?")) ?>') ) {
      //doFieldDelete(ids.child_id);
      doFieldUnMap(ids.parent_id, ids.option_id, ids.child_id);
    }
  }

  var doFieldDelete = function(field_id) {
    scriptJquery('.admin_field_child_' + field_id).remove();
    var url = urls.field.remove;
    scriptJquery.ajax({
      url: url,
      type: "POST",
      dataType: 'json',
      data : {
        format : 'json',
        fieldType : fieldType,
        field_id : field_id,
      },
      success: function(responseJSON){
      }
    });
  }

  var doFieldUnMap = function(parent_id, option_id, child_id) {
    scriptJquery('.admin_field_child_' + child_id).remove();
    var url = urls.map.remove;
    scriptJquery.ajax({
      url: url,
      type: "POST",
      dataType: 'json',
      data : {
        format : 'json',
        fieldType : fieldType,
        parent_id : parent_id,
        option_id : option_id,
        child_id : child_id
      },
      success: function(responseJSON){
      }
    });
  }

  /* --------------------------- HEADING - CREATE --------------------------- */

  var uiSmoothTopHeadingCreate = function(spec) {
    var url = urls.heading.create;
    url += '/option_id/' + topLevelOptionId + '/parent_id/' + topLevelFieldId + '/format/smoothbox';
    Smoothbox.open(url);
  }

  var uiSmoothEditHeading = function(spec) {
    var element = scriptJquery(this);
    var parentEl = element.parents('.admin_field');
    var ids = readIdentifiers(parentEl.attr('id'));
    var url = urls.heading.edit;
    url += '/field_id/' + ids.child_id + '/format/smoothbox';
    Smoothbox.open(url);
  }

  var onHeadingCreate = function(field, htmlArr) {
    genericUpdateKeys(htmlArr);
  }

  /* ---------------------------- HEADING - EDIT ---------------------------- */

  var onHeadingEdit = function(field, htmlArr) {
    genericUpdateKeys(htmlArr);
  }

</script>
