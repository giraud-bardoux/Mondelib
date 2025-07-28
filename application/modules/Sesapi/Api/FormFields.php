<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: FormFields.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
class Sesapi_Api_FormFields extends Core_Api_Abstract {
  public function generateFormFields($form = null,$getProfileType = false){
    if(!$form)
      return "";
    $formFields = array();
    $elements = $form->getElements();
    $counter = 0;
      $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;

    // location work
    if($form->getElement('location') && Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0)) {
      $order = 56982369;
      $form->addElement('Hidden', 'country', array(
        'label' => 'Country',
        'order' => !empty($order) ? $order +1 : '',
      ));
      $form->addElement('Hidden', 'state', array(
        'label' => 'State',
        'order' => !empty($order) ? $order +1 : '',
      ));
      $form->addElement('Hidden', 'city', array(
        'label' => 'City',
        'order' => !empty($order) ? $order +1 : '',
      ));
      $form->addElement('Hidden', 'zip', array(
        'label' => 'Zip',
        'order' => !empty($order) ? $order +1 : '',
      ));
      $form->addElement('Hidden', 'lat', array(
        'label' => 'Latitude',
        'id' => 'latSes',
        'order' => !empty($order) ? $order +1 : '',
      ));
      $form->addElement('Hidden', 'lng', array(
        'label' => 'Longitude',
        'id' => 'lngSes',
        'order' => !empty($order) ? $order +1 : '',
      ));
    }
    
    if($form->getElement('location')) {
      if($form->getElement('map-canvas'))
      $form->removeElement('map-canvas');
      if($form->getElement('ses_location'))
      $form->removeElement('ses_location');
    }


    foreach($elements as $element){
      $helper = get_object_vars($element);
      $type = !empty($helper['helper']) ? str_replace('form','',$helper['helper']) : "";
      if($type == "Heading")
        continue;
      if($getProfileType){
        $profileType = current(explode('_',$element->getName()));
        if(intval($profileType) > 0){
          $formFields[$counter]['profile_type_id'] = $profileType;
        }
      }
      if($element->getName() == "profile_type")
        $formFields[$counter]['changeFields']  = 1;
      $multiple = !empty($helper['multiple']) ? $helper['multiple'] : "";
      
     // if($type == "Checkbox")
       // continue;
      
      $formFields[$counter]['type'] = (string) $type;
      $formFields[$counter]['multiple'] = (string) $multiple;
      $formFields[$counter]['name'] = (string) $element->getName();
      $formFields[$counter]['label'] = (string) $element->getLabel();
      $descripiton = "";
      try{
        if($type != "Checkbox"){
          $descripiton = trim(strip_tags(str_replace('&nbsp;',"",($element->getDescription()))));  
        }
      }catch(Exception $e){
        //silence  
      }
      
      $formFields[$counter]['description'] = $view->translate($descripiton);
      $formFields[$counter]['isRequired'] = (int)$element->isRequired();
      try{
      if(($form->{ $element->getName()}) && is_array($form->{ $element->getName()}->getValue()))
        $formFields[$counter]['value'] = (array)  $form->{ $element->getName()}->getValue();
      else if($type == "Checkbox"){
        $formFields[$counter]['value'] = (int) $element->checked ? 1 : 0;
      }
      else if($form->{ $element->getName()}) {
        
        $formFields[$counter]['value'] = (string) $form->{$element->getName()}->getValue();
      }
      }catch(Exception $e){
        //silence 
      }
      
      if($type == 'Select' || $type == 'Radio' || strpos($type,'Multi') !== false){
        $options = $element->getMultiOptions();
        $multiOptions = array();
        foreach($options as $key=>$option){
            $multiOptions[$key] = $option; //strip_tags($option);
        }
        $formFields[$counter]['multiOptions'] =  $multiOptions;
      }

      if($formFields[$counter]['type'] == "Textarea" && _SESAPI_PLATFORM_SERVICE == 1){
          //if($formFields[$counter]['name'] == "description")
            //$formFields[$counter]['name'] = (string) $element->getName()."_keydesciption";
          
      }
      
      $counter++;
    }
    return $formFields;
  }
  public function validateFormFields($form = null){
    if(!$form)
      return "";
    $formFields = array();
    $elements = $form->getElements();
    $counter = 0;
    foreach($elements as $key => $element){
      if(!$element->hasErrors())
        continue;
      $type = !empty($helper['helper']) ? str_replace('form','',$helper['helper']) : "";
      $formFields[$counter]['type'] = $type;
      $formFields[$counter]['name'] = $element->getName();
      $formFields[$counter]['label'] = $element->getLabel();
      $errorMessage = '';
      foreach($element->getMessages() as $message){
        $errorMessage .= $element->getLabel().': '.$message;  
      }
      $formFields[$counter]['errorMessage'] = $element->getLabel().': '.$message;
      $formFields[$counter]['isRequired'] = $element->isRequired();
      $formFields[$counter]['value'] = (string) $form->{ $element->getName()}->getValue();
      
      $counter++;
    }
    return $formFields;
  }
}
