<?php
/**
 * Photoblur Module
 *
 * @category   Application_Extensions
 * @package    Photoblur
 */
class Photoblur_Plugin_Core
{
  public function onRenderLayoutDefault($event)
  {
    $view = $event->getPayload();
    if (!$view instanceof Zend_View) {
      return;
    }
    
    // Vérifier que l'utilisateur est connecté
    $viewer = Engine_Api::_()->user()->getViewer();
    if (!$viewer || !$viewer->getIdentity()) {
      return;
    }
    
    // Vérifier si nous sommes sur une page de photo
    $request = Zend_Controller_Front::getInstance()->getRequest();
    if ($request->getModuleName() != 'album' || 
        $request->getControllerName() != 'photo' || 
        $request->getActionName() != 'view') {
      return;
    }
    
    // Récupérer l'ID de la photo
    $photo_id = $request->getParam('photo_id');
    if (!$photo_id) {
      return;
    }
    
    // Générer le lien de floutage
    $router = Zend_Controller_Front::getInstance()->getRouter();
    $blurUrl = $router->assemble(array(
      'module' => 'photoblur',
      'controller' => 'photo',
      'action' => 'blur',
      'photo_id' => $photo_id
    ), 'photoblur_photo', true);
    
    // Injecter le script JavaScript
    $script = <<<EOT
<script type="text/javascript">
en4.core.runonce.add(function() {
  // Attendre que le DOM soit chargé
  setTimeout(function() {
    // Trouver le menu dropdown des options
    var dropdownMenu = document.querySelector('.dropdown-option-menu');
    
    if (dropdownMenu) {
      // Créer le séparateur
      var separator = document.createElement('li');
      separator.setAttribute('role', 'separator');
      separator.className = 'dropdown-divider';
      
      // Créer le lien de floutage
      var blurItem = document.createElement('li');
      var blurLink = document.createElement('a');
      blurLink.href = '{$blurUrl}';
      blurLink.className = 'dropdown-item icon_blur';
      blurLink.innerHTML = '<i class="fa fa-adjust"></i> Flouter cette photo';
      blurItem.appendChild(blurLink);
      
      // Ajouter au menu
      dropdownMenu.appendChild(separator);
      dropdownMenu.appendChild(blurItem);
    }
  }, 500);
});
</script>
<style type="text/css">
.dropdown-item.icon_blur:before {
  content: "\\f042";
  font-family: FontAwesome;
  margin-right: 8px;
}
</style>
EOT;
    
    // Ajouter le script directement dans le body
    echo $script;
  }
}