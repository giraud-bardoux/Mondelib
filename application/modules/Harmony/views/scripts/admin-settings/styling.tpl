<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: styling.tpl 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<?php include APPLICATION_PATH .  '/application/modules/Harmony/views/scripts/_adminHeader.tpl';?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'externals/jscolor/jscolor.js'); ?>
<?php $settings = Engine_Api::_()->getApi('settings', 'core'); ?>

<div class='clear'>
  <div class='settings harmony_color_schemes_form'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<?php $theme_color = $settings->getSetting('harmony.theme.color', 1); ?>
<script type="text/javascript">

  hashSign = '#';

  en4.core.runonce.add(function() {
    scriptJquery('#theme_color-<?php echo $theme_color ?>').parent().addClass('colored-border');
    changeThemeColor("<?php echo $theme_color; ?>");
  });
  
  function getThemeColor(value) {
    var URL = en4.core.baseUrl+'admin/harmony/settings/getcustomthemecolors/';
    (scriptJquery.ajax({
      //method: 'post',
      url: URL ,
      //dataType : 'html', 
      data: {
        format: 'html',
        customtheme_id: value,
      },
      success: function(responseHTML) {
        var customthevalyearray = scriptJquery.parseJSON(responseHTML);
        for(i=0;i<customthevalyearray.length;i++){
          var splitValue = customthevalyearray[i].split('||');
          scriptJquery('#'+splitValue[0]).val(splitValue[1]);
          if(scriptJquery('#'+splitValue[0]).hasClass('SEcolor')){
            if(splitValue[1] == ""){
              splitValue[1] = "#FFFFFF";
            }
            try{
              document.getElementById(splitValue[0]).color.fromString('#'+splitValue[1]);
              document.getElementById(splitValue[0]).style.color = '#'+splitValue[1];
            } catch(err) {
              document.getElementById(splitValue[0]).color.fromString('#'+splitValue[1]);
              document.getElementById(splitValue[0]).style.color = '#'+splitValue[1];
            }
          }
        }
      }
    }));
  }

	function changeThemeColor(value) {
    getThemeColor(value);
	  if(value == 1 || value == 2 || value == 3) {
		  if(scriptJquery('#header_settings-wrapper').length)
				scriptJquery('#header_settings-wrapper').css("display",'none');
	    if(scriptJquery('#footer_settings-wrapper').length)
				scriptJquery('#footer_settings-wrapper').css("display",'none');
		  if(scriptJquery('#body_settings-wrapper').length)
				scriptJquery('#body_settings-wrapper').css("display",'none');
			if(scriptJquery('#header_settings_group').length)
			  scriptJquery('#header_settings_group').css("display",'none');
			if(scriptJquery('#footer_settings_group').length)
			  scriptJquery('#footer_settings_group').css("display",'none');
			if(scriptJquery('#body_settings_group').length)
			  scriptJquery('#body_settings_group').css("display",'none');
      if(scriptJquery('#custom_themes').length)
				scriptJquery('#custom_themes').css("display",'block');
      if(scriptJquery('#edit_custom_themes').length)
        scriptJquery('#edit_custom_themes').css("display",'none');
      if(scriptJquery('#delete_custom_themes').length)
        scriptJquery('#delete_custom_themes').css("display",'none');
      if(scriptJquery('#deletedisabled_custom_themes').length)
        scriptJquery('#deletedisabled_custom_themes').css("display",'none');
      if(scriptJquery('#submit').length)
        scriptJquery('#submit').css("display",'none');
	  } else {
		  if(scriptJquery('#header_settings-wrapper').length)
				scriptJquery('#header_settings-wrapper').css("display",'block');
	    if(scriptJquery('#footer_settings-wrapper').length)
				scriptJquery('#footer_settings-wrapper').css("display",'block');
			if(scriptJquery('#body_settings-wrapper').length)
				scriptJquery('#body_settings-wrapper').css("display",'block');
			if(scriptJquery('#header_settings_group').length)
			  scriptJquery('#header_settings_group').css("display",'block');
			if(scriptJquery('#footer_settings_group').length)
			  scriptJquery('#footer_settings_group').css("display",'block');
			if(scriptJquery('#body_settings_group').length)
			  scriptJquery('#body_settings_group').css("display",'block');
				
      if(value > 3) {
        if(scriptJquery('#submit').length)
          scriptJquery('#submit').css("display",'inline-block');
        if(scriptJquery('#edit_custom_themes').length)
          scriptJquery('#edit_custom_themes').css("display",'inline-block');
        if(scriptJquery('#delete_custom_themes').length)
          scriptJquery('#delete_custom_themes').css("display",'inline-block');

        <?php if(empty($this->customtheme_id)): ?>
          history.pushState(null, null, 'admin/harmony/settings/styling/customtheme_id/'+value);
          
          scriptJquery('#edit_custom_themes').attr('href', scriptJquery('#edit_custom_themes').attr('href') + '/customtheme_id/'+value);
          scriptJquery('#delete_custom_themes').attr('href', scriptJquery('#delete_custom_themes').attr('href')+'/customtheme_id/'+value);
        <?php else: ?>
        
          scriptJquery('#edit_custom_themes').attr('href', scriptJquery('#edit_custom_themes').attr('href') + '/customtheme_id/'+value);

          var activatedTheme = '<?php echo $this->activatedTheme; ?>';
          if(activatedTheme == value) {
            scriptJquery('#delete_custom_themes').css("display",'none');
            scriptJquery('#deletedisabled_custom_themes').css("display",'inline-block');
          } else {
            if(scriptJquery('#deletedisabled_custom_themes').length)
              scriptJquery('#deletedisabled_custom_themes').css("display",'none');
            scriptJquery('#delete_custom_themes').attr('href', scriptJquery('#delete_custom_themes').attr('href')+'/customtheme_id/'+value);
          }
        <?php endif; ?>
      } else {
        if(scriptJquery('#edit_custom_themes').length)
          scriptJquery('#edit_custom_themes').css("display",'none');
        if(scriptJquery('#delete_custom_themes').length)
          scriptJquery('#delete_custom_themes').css("display",'none');
        if(scriptJquery('#deletedisabled_custom_themes').length)
          scriptJquery('#deletedisabled_custom_themes').css("display",'none');
        if(scriptJquery('#submit').length)
          scriptJquery('#submit').css("display",'none');
      }
	  }
	}
	
	en4.core.runonce.add(function() {
    scriptJquery('#theme_color-element .form-options-wrapper > li').click(function(){
      if ( scriptJquery(this).hasClass('colored-border') ) {
          scriptJquery(this).removeClass('colored-border');
      } else {
          scriptJquery('#theme_color-element .form-options-wrapper > li.colored-border').removeClass('colored-border');
          scriptJquery(this).addClass('colored-border');    
      }
    });
	});
</script>
<script type="application/javascript">
  scriptJquery('.core_admin_main_harmony').parent().addClass('active');
</script>
