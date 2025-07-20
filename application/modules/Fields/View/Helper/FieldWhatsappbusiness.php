<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: FieldWhatsappbusiness.php 9747 2017-11-10 02:08:08Z john $
 * @author     Donna
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     Donna
 */
class Fields_View_Helper_FieldWhatsappbusiness extends Fields_View_Helper_FieldAbstract
{
  public function fieldWhatsappbusiness($subject, $field, $value)
  {
    $whatsappUrl = 'https://wa.me/' .  trim($value->value);

    return $this->view->htmlLink($whatsappUrl, $value->value, array(
      'target' => '_blank',
      'ref' => 'nofollow',
    ));
  }
}
