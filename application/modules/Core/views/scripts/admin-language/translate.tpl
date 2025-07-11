<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: translate.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<?php // attach a "powered by Google" branding ?>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
  google.load("language", "1");
  en4.core.runonce.add(function() {
    google.language.getBranding(scriptJquery('.global_form')[0].find('h3'));//'branding');
  });
</script>
<style type="text/css">
  .gBranding {
    display:inline-block;
    padding-left: 10px;
  }
</style>

<?php if( $this->form ): ?>

  <script type="text/javascript">
    var url = '<?php echo $this->url(array('action' => 'translate-phrase')) ?>';
    var testTranslation = function() {
      (scriptJquery.ajax({
        url : url,
        dataType : 'json',
        method : 'post',
        data : {
          format: 'json',
          source : scriptJquery('#source').val(),
          target : scriptJquery('#target').val(),
          text : scriptJquery('#test').val()
        },
        success : function(responseJSON) {
          if(scriptJquery('#test-translation') ) {
            scriptJquery('#test-translation').html(responseJSON.targetPhrase);
          } else {
            (scriptJquery.crtEle('p', {
              'id' : 'test-translation',
            })).html(responseJSON.targetPhrase).insertAfter(scriptJquery('#test'));
          }
        }
      }));
    }
    en4.core.runonce.add(function() {
      (scriptJquery.crtEle('a', {
        'href' : 'javascript:void(0);',
        'html' : ,
        'events' : {
          'click' : function() {
            testTranslation();
          }
        }
      })).html('Translate').appendTo(scriptJquery('#test-element').find('p').html(''));
      
    });
  </script>

  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>

<?php endif; ?>

<?php if( $this->values ): ?>
  <div id="admin_language_translate_log">
    <ul>
      
    </ul>
  </div>

<?php endif; ?>
