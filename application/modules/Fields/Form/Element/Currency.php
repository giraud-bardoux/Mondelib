<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Currency.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */
class Fields_Form_Element_Currency extends Engine_Form_Element_Float
{
  protected $_fieldMeta;

  public function setFieldMeta($field)
  {
    $this->_fieldMeta = $field;
    return $this;
  }

  public function init()
  {
    parent::init();

    $this->addFilter('Callback', array(array($this, 'filterRound')));
  }

  public function render(Zend_View_Interface $view = null)
  {
    if( $this->_fieldMeta instanceof Fields_Model_Meta) {
      $currencyCode = Engine_Api::_()->payment()->defaultCurrency();
      $currencyData = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrency($currencyCode);
      $currencyName = $currencyData->title;
      
      $this->loadDefaultDecorators();
      $this->getDecorator('Label')
        ->setOption('optionalSuffix', ' - ' . $currencyCode)
        ->setOption('requiredSuffix', ' - ' . $currencyCode);

      if( $currencyName && !$this->getDescription() ) {
        $this->setDescription($currencyName);
        $this->getDecorator('Description')->setOption('placement', 'APPEND');
      }
    }
    
    return parent::render($view);
  }

  public function filterRound($value)
  {	
		if(!is_numeric($value)) 
			return false;
    if( empty($value) ) {
      return false;
    }
    return round($value, 2);
  }
}
