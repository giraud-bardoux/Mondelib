<?php

class Sitetranslator_AdminSettingsController extends Core_Controller_Action_Admin {

    public function indexAction() {

      //MAKE NAVIGATION
      $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('sitetranslator_admin_main', array(), 'sitetranslator_admin_main_settings');
      $settings = Engine_Api::_()->getApi('settings', 'core');
      $this->view->contentTranslator = $settings->getSetting('sitetranslator.content.translator.widget.enable', 1);
      //GENERATE FORM
      $variablesFilePath = APPLICATION_PATH . "/application/modules/Sitetranslator/settings/variables.csv";
      $variables = $this->_csv_to_array($variablesFilePath);
      $this->view->form = $form = new Sitetranslator_Form_Admin_Global(array("variables" => $variables));

      if (!$this->getRequest()->isPost()) {
        return;
      }

      //FORM VALIDATION
      if (!$form->isValid($this->getRequest()->getPost())) {
        return;
      }

      if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
        $values = $form->getValues();
        if ($values["sitetranslator_google_api_character_limit"] < 100000) {
          $form->addError("Please increase the character translation per 100 second limit[ at least 100000 ].");
          return;
        }


        if(!empty($values['sitetranslator_google_api_key']) && ($settings->getSetting('sitetranslator.google.api.key') != $values['sitetranslator_google_api_key'])){
          $settings->setSetting('sitetranslator.google.api.key', $values['sitetranslator_google_api_key']);
          $responseData = Engine_Api::_()->sitetranslator()->getTranslatedResponse(rawurlencode("Api Key Validation Test"), 'en', 'hi');
          if ($responseData['responseCode'] != 200) {
            $settings->removeSetting('sitetranslator.google.api.key');
            $form->addError('Please try again,Api key validation failed! Server response code:' . $responseData['responseCode'] . ' Error description: ' . $responseData['responseData']);
            return;
          } 
        } elseif(!empty ($settings->getSetting('sitetranslator.google.api.key')) && empty($values['sitetranslator_google_api_key'])) {
          $settings->removeSetting('sitetranslator.google.api.key');
        }
          
        foreach ($values as $key => $value) {
          if (!engine_in_array($key,array('sitetranslator_variables','sitetranslator_google_api_key'))) {
            $settings->setSetting($key, $value);
          }
        }

        if ($values['sitetranslator_variables'] != join(",",array_keys($variables))) {
          $variablesAdded = engine_array_diff(explode(",", $values['sitetranslator_variables']), array_keys($variables));
          $variablesRemoved = engine_array_diff(array_keys($variables), explode(",", $values['sitetranslator_variables']));

          foreach ($variablesRemoved as $r) {
            unset($variables[$r]);
          }
          $randomArray = array('z', 'x', 'y', 'v', 'w', 'q');
          foreach ($variablesAdded as $v) {
            if (empty($v) && strlen($v) > 1) {
              continue;
            }
            $rand = array_rand($randomArray, engine_count($randomArray) - 1);
            if (strlen($v) > 2) {
              $i = strlen($v) / 2;
              $preparedKey = $randomArray[$rand[0]] . "_" . strtolower($v[$i]) . "_" . $randomArray[$rand[3]] . "_" . strtolower($v[$i + 1]);
            } else {
              $preparedKey = $randomArray[$rand[0]] . "_" . strtolower($v[0]) . "_" . $randomArray[$rand[3]] . "_" . strtolower($v[1]);
            }

            $responseData = Engine_Api::_()->sitetranslator()->getTranslatedResponse(rawurlencode($preparedKey), 'en', 'hi');

            if ($responseData['responseCode'] != 200) {
              $form->addError('Please try again,Fetching translation failed! Server response code:' . $responseData['responseCode'] . ' <=> Error description: ' . $responseData['responseData']);
              return;
            } elseif ($responseData["responseData"] != $preparedKey) {
              $variablesAdded[] = $v;
              continue;
            }
            $variables[$v] = $preparedKey;
          }
          foreach ($variables as $key => $val) {
            $content = $content . '"' . $key . '";"' . $val . '"' . PHP_EOL;
            $specialVariable = $specialVariable ? $specialVariable . "," . $key : $key;
          }
          file_put_contents($variablesFilePath, $content);
        }
        $form->addNotice('Settings has been successfully saved.');
        $this->view->contentTranslator = $settings->getSetting('sitetranslator.content.translator.widget.enable', 1);
      }
    }

    function faqsAction() {
        //MAKE NAVIGATION
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
                ->getNavigation('sitetranslator_admin_main', array(), 'sitetranslator_admin_main_faqs');
        $this->view->showFAQ = $this->_getParam('showFAQ');
    }

}
