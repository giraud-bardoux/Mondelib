<?php
class Moduleflou_View_Helper_FlouThumb extends Zend_View_Helper_Abstract
{
  /**
   * Rend une image potentiellement floutée selon l'état de connexion.
   *
   * @param mixed $photo URL string, array(['url'=>...]) ou objet avec getPhotoUrl()
   * @param int $blurPx Intensité du flou en pixels (CSS)
   * @param array $attrs Attributs HTML (alt, class, etc.)
   * @return string HTML <img>
   */
  public function flouThumb($photo, $blurPx = 8, array $attrs = array())
  {
    // Résoudre l'URL
    $url = null;
    if (is_string($photo)) {
      $url = $photo;
    } elseif (is_array($photo) && isset($photo['url'])) {
      $url = $photo['url'];
    } elseif (is_object($photo) && method_exists($photo, 'getPhotoUrl')) {
      $url = $photo->getPhotoUrl();
    }

    if (!$url) {
      return '';
    }

    // Déterminer si le viewer est connecté
    $viewer = Engine_Api::_()->user()->getViewer();
    $isGuest = !$viewer || !$viewer->getIdentity();

    // Préparer attributs
    $attrs = array_merge(array('alt' => ''), $attrs);
    $class = isset($attrs['class']) ? $attrs['class'] . ' ' : '';
    if ($isGuest) {
      $class .= 'moduleflou-thumb--blur';
      $attrs['style'] = (isset($attrs['style']) ? $attrs['style'] . '; ' : '') . '--moduleflou-blur: ' . intval($blurPx) . 'px;';
    }
    $attrs['class'] = trim($class);

    // Construire l'HTML
    $htmlAttrs = '';
    foreach ($attrs as $k => $v) {
      $htmlAttrs .= ' ' . htmlspecialchars($k, ENT_QUOTES, 'UTF-8') . '="' . htmlspecialchars($v, ENT_QUOTES, 'UTF-8') . '"';
    }

    return '<img src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"' . $htmlAttrs . ' />';
  }
}
