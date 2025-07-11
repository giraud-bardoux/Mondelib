<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _composeLink.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<?php
$request = Zend_Controller_Front::getInstance()->getRequest();
$requestParams = $request->getParams();

// if (($requestParams['action'] == 'compose' || $requestParams['action'] == 'view') && $requestParams['module'] == 'messages' && $requestParams['controller'] == 'messages') {
//   return;
// }

$this->headTranslate(array('PLAY', 'CANCEL'));
?>
<script type="text/javascript">
  en4.core.runonce.add(function () {
    composeInstance.addPlugin(new Composer.Plugin.Link({
      title: '<?php echo $this->string()->escapeJavascript($this->translate('Add Link')) ?>',
      lang: {
        'Add Link': '<?php echo $this->string()->escapeJavascript($this->translate('Add Link')); ?>',
        'cancel': '<?php echo $this->string()->escapeJavascript($this->translate('cancel')) ?>',
        'Last': '<?php echo $this->string()->escapeJavascript($this->translate('Previous')) ?>',
        'Next': '<?php echo $this->string()->escapeJavascript($this->translate('Next')) ?>',
        'Attach': '<?php echo $this->string()->escapeJavascript($this->translate('Attach')) ?>',
        'Loading...': '<?php echo $this->string()->escapeJavascript($this->translate('Loading...')) ?>',
        'Don\'t show an image': '<?php echo $this->string()->escapeJavascript($this->translate('Don\'t show an image')) ?>',
        'Choose Image:': '<?php echo $this->string()->escapeJavascript($this->translate('Choose Image:')) ?>',
        '%d of %d': '<?php echo $this->string()->escapeJavascript($this->translate('%d of %d')) ?>'
      },
      requestOptions: {
        'url': en4.core.baseUrl + 'core/link/preview'
      }
    }));
  });
</script>
