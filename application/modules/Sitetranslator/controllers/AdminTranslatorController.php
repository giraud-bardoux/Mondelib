<?php

class Sitetranslator_AdminTranslatorController extends Core_Controller_Action_Admin {

    /**
     * Translate the source language into given target language
     * @param source,target,csv files array
     * 
     */
    public function indexAction() {

        //MAKE NAVIGATION
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
                ->getNavigation('sitetranslator_admin_main', array(), 'sitetranslator_admin_main_translator');
        $googleApiKey = Engine_Api::_()->getApi('settings','core')->getSetting('sitetranslator.google.api.key');
        if(empty($googleApiKey))  {
          return;
        }
        $this->view->translationSuccess = $this->_getParam('translationSuccess');
        $this->view->locale = $this->_getParam('locale');
        $target_language = $this->_getParam('target');
        $selected = $this->_getParam('selected');
        $session = new Zend_Session_Namespace('missed_files');
        if(!empty($session->missed_files)){
            $selected_files = $session->missed_files;
        }
        //GENERATE FORM
        $this->view->form = $form = new Sitetranslator_Form_Admin_Translator();
        $this->view->source = $form->getValue('source_language');
        if (!empty($target_language) && !empty($selected) && !empty($selected_files)) {
            $form->populate(array('target_language' => $target_language, 'sitetranslator_csv_files' => $selected_files,'overwrite'=>0));
            $form->submit->setLabel("Continue to translate");
        }
        $i = 0;
        $language_path = APPLICATION_PATH . "/application/languages/";
        foreach (glob($language_path . 'en/' . '*.csv') as $csv_filename) {
            $size[$i++] = filesize($csv_filename);
        }
        $this->view->size = $size;
        $characterLimit = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitetranslator.google.api.character.limit', 0);
        $this->view->sleep = $sleep = (int) (50 / ($characterLimit / 100000));
        if (!$this->getRequest()->isPost()) {
            return;
        }
        //FORM VALIDATION
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }
        $values = $form->getValues();
        $selectedFiles = $values['sitetranslator_csv_files'];
        $sourceCode = $values['source_language'];
        $targetCodes = $values['target_language'];

        ini_set('max_execution_time', 0);
        set_time_limit(0);
        foreach ($targetCodes as $targetCode) {
            if ($sourceCode == $targetCode) {
                continue;
            }
            $source_path = $language_path . $sourceCode;
            $target_path = $language_path . $targetCode;
            if (!is_dir($target_path)) {
                mkdir($target_path);
                chmod($target_path, 0777);
            }
            $targe_phpFile = $target_path.'/'.$targetCode.'.php';
            if(!is_file($targe_phpFile)){
                touch($targe_phpFile);
                chmod($targe_phpFile, 0777);
                $phpContent = "<?php return array(); ?>";
                $fp = fopen($targe_phpFile, 'w');
                fwrite($fp, $phpContent);
                fclose($fp);
            }
            if (empty($values['overwrite'])) {
                $translated_big_array = $this->sitetranslator_csv_folder_to_array($target_path, $selectedFiles);
            }
            $big_array = $this->sitetranslator_csv_folder_to_array($source_path, $selectedFiles);


            $data = array();
            $variables = $this->_csv_to_array(APPLICATION_PATH . "/application/modules/Sitetranslator/settings/variables.csv");
            $replaceBefore = array_keys($variables);
            $replaceAfter = array_values($variables);
            $seprater = "<Z_X_Y>";
            $joiner = "<Z_X_Y>";
            foreach ($big_array as $file => $phases) {

                if (!empty($translated_big_array[$file])) {
                    $phases = engine_array_diff_key($phases, $translated_big_array[$file]);
                }

                $bigData = array();
                $data = array();
                foreach ($phases as $key => $value) {
                    if (empty($value)) {
                        continue;
                    }
                    if (is_array($value)) {
                        $data[] = str_replace($replaceBefore, $replaceAfter, $value[0]);
                        $data[] = str_replace($replaceBefore, $replaceAfter, $value[1]);
                    } else {
                        $data[] = str_replace($replaceBefore, $replaceAfter, $value);
                    }
                    if (strlen(join($seprater, $data)) > 20000) {
                        $bigData[] = join($seprater, $data);
                        $data = array();
                    }
                }

                $bigData[] = join($seprater, $data);
                $translatedArray = array();
                foreach ($bigData as $key => $value) {
                    $responseData = Engine_Api::_()->sitetranslator()->getTranslatedResponse($value, $sourceCode, $targetCode);
                    if ($responseData['responseCode'] != 200) {
                        $form->addError('Fetching translation failed! Server response code:' . $responseData['responseCode'] . '<br /> Error description: ' . $responseData['responseData']);
                        return;
                    } else {
                        $translatedArray = !empty($translatedArray) ? array_merge($translatedArray, explode($joiner, $responseData["responseData"])) : explode($joiner, $responseData["responseData"]);
                    }

                    sleep($sleep);
                }

                $translatedDataArray = array();
                $key = 0;
                $data = array();
                foreach ($phases as $pKey => $value) {
                    if (empty($value)) {
                        continue;
                    }
                    if (is_array($value)) {
                        $content = array(
                            str_replace('"', '""', $translatedArray[$key++]),
                            str_replace('"', '""', $translatedArray[$key++])
                        );
                    } else {
                        $content = str_replace('"', '""', $translatedArray[$key++]);
                    }
                    $data[str_replace('"', '""', $pKey)] = $content;
                }

                $translatedDataArray[$file] = $data;
                $translatedContent = '';
                foreach ($data as $key => $eachTranslatedData) {
                    if (!is_array($eachTranslatedData)) {
                        $translatedContent .= '"' . $key . '";"' . trim(str_ireplace($replaceAfter, $replaceBefore, $eachTranslatedData)) . "\"\n";
                    } else {
                        $multiValues = join('";"', $eachTranslatedData);
                        $translatedContent .= '"' . $key . '";"' . trim(str_ireplace($replaceAfter, $replaceBefore, $multiValues)) . "\"\n";
                    }
                }

                if (!empty($translated_big_array[$file])) {
                    $this->sitetranslator_array_to_csv($target_path . "/$file", $translatedContent, 1);
                } else {
                    $this->sitetranslator_array_to_csv($target_path . "/$file", $translatedContent, 0);
                }
                sleep(20);
            }
            $this->csv_folder_to_array($language_path . "/$targetCode", $targetCode);
        }

        $this->_helper->redirector->gotoRoute(array('module' => 'sitetranslator', 'controller' => 'translator', 'translationSuccess' => 1, 'locale' => json_encode($targetCodes)), 'admin_default', true);
        $form->addNotice('You have successfully translated the language pack.');
    }

    /**
     * To add custom phrases in custom.csv
     * @param phrase key, phrase value, target language
     * 
     */
    public function addCustomPhraseAction() {

        //MAKE NAVIGATION
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
                ->getNavigation('sitetranslator_admin_main', array(), 'sitetranslator_admin_main_add_custom_phrase');

        $googleApiKey = Engine_Api::_()->getApi('settings','core')->getSetting('sitetranslator.google.api.key');
        if(empty($googleApiKey))  {
          return;
        }
        //GENERATE FORM
        $this->view->form = $form = new Sitetranslator_Form_Admin_AddCustomPhrase();
        if (!$this->getRequest()->isPost()) {
            return;
        }
        //FORM VALIDATION
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }
        $values = $form->getValues();
        $customFilePath = APPLICATION_PATH . "/application/languages/" . $values["target_language"] . "/custom.csv";

        if (is_file($customFilePath)) {
            $phrases = $this->_csv_to_array($customFilePath);
        } else {
            touch($customFilePath);
            chmod($customFilePath, 0777);
        }
        
        if (!empty($phrases) && engine_in_array($values['sitetranslator_phrase_key'], array_keys($phrases))) {
            $form->addError("Phrase key [ " . $values['sitetranslator_phrase_key'] . " ] already exists.");
            return;
        }
        $cont = file_get_contents($customFilePath);
        $content = $cont . '"' . $values['sitetranslator_phrase_key'] . '";"' . $values['sitetranslator_phrase_value'] . '"' . PHP_EOL;
        file_put_contents($customFilePath, $content);
        $this->view->form = $form = new Sitetranslator_Form_Admin_AddCustomPhrase();
        $form->addNotice('You have successfully added new phrase [ ' . $values['sitetranslator_phrase_key'] . ' ].');
    }

    /**
     * List down all the languages with their character length
     * @param null
     * return list of languages at view
     */
    public function listLanguagesAction() {
        //MAKE NAVIGATION
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
                ->getNavigation('sitetranslator_admin_main', array(), 'sitetranslator_admin_main_list_languages');

        $language_path = APPLICATION_PATH . "/application/languages/";
        $size = 0;
        $lists = scandir($language_path);

        foreach ($lists as $list) {
            if ($list === '.' or $list === '..') {
                continue;
            }
            if (is_dir($language_path . '/' . $list)) {
                foreach (glob($language_path . $list . '/' . '*.csv') as $csv_filename) {
                    $size += filesize($csv_filename);
                }
                $data[$list] = $size;
                $size = 0;
            }
        }
        // Prepare default langauge
        $translate = Zend_Registry::get('Zend_Translate');
        $this->view->defaultLanguage = $translate->getLocale();
        $languageList = $translate->getList();
        $localeObject = Zend_Registry::get('Locale');
        $defaultLanguage = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en');
        if( !engine_in_array($defaultLanguage, $languageList) ) {
          if( $defaultLanguage == 'auto' && isset($languageList['en']) ) {
            $defaultLanguage = 'en';
          } else {
            $defaultLanguage = null;
          }
        }
        $languages = Zend_Locale::getTranslationList('language', $localeObject);
        $territories = Zend_Locale::getTranslationList('territory', $localeObject);

        $localeMultiOptions = array();
        foreach( $languageList as $key ) {
          $languageName = null;
          if( !empty($languages[$key]) ) {
            $languageName = $languages[$key];
          } else {
            $tmpLocale = new Zend_Locale($key);
            $region = $tmpLocale->getRegion();
            $language = $tmpLocale->getLanguage();
            if( !empty($languages[$language]) && !empty($territories[$region]) ) {
              $languageName = $languages[$language] . ' (' . $territories[$region] . ')';
            }
          }

          if( $languageName ) {
            $localeMultiOptions[$key] = $languageName . ' [' . $key . ']';
          } else {
            $localeMultiOptions[$key] = $this->view->translate('Unknown') . ' [' . $key . ']';
          }
        }
        $localeMultiOptions = array_merge(array($defaultLanguage => $defaultLanguage), $localeMultiOptions);
        $this->view->languages = $localeMultiOptions;
        $this->view->listLanguages = $data;
    }

    /**
     * Filter out the missing phrases
     * @param String,String
     * return filtered phrases at view
     */
    public function phrasesAction() {
        //MAKE NAVIGATION
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
                ->getNavigation('sitetranslator_admin_main', array(), 'sitetranslator_admin_main_phrases');
        //GENERATE FORM
        $this->view->form = $form = new Sitetranslator_Form_Admin_Phrases();

        $values = $this->_getAllParams();
        $path = APPLICATION_PATH . "/application/languages/";
        if (isset($values["source_language"])) {
          $source = $values["source_language"];
          $this->view->target_language = $target = $values["target_language"];
        } else {
          $source = $values["source_language"]="en";
          $this->view->target_language = $target = $values["target_language"]="en";
          $values["csv_files"] = "all";
        }
        $form->populate($values);
       
        if ($values['csv_files'] == 'all') {
            $sourceFileOptions = $this->csvFiles($source);
            $targetFileOptions = $this->csvFiles($target);
        } else {
            $sourceFileOptions = $targetFileOptions = array($values['csv_files']);
        }

        $customFilePath = $path . $values["target_language"] . "/custom.csv";
        if (file_exists($customFilePath) && is_file($customFilePath)) {
            $customPhrases = $this->_csv_to_array($customFilePath);
        }

        $source_big_array = $this->sitetranslator_csv_folder_to_array($path . $source . DIRECTORY_SEPARATOR, $sourceFileOptions);
        $target_big_array = $this->sitetranslator_csv_folder_to_array($path . $target . DIRECTORY_SEPARATOR, $targetFileOptions);

        $diff_phrases_array = array();
        $i = 0;
        foreach ($source_big_array as $file => $phrases) {
          if( empty($target_big_array[$file]) ) {
            $diff_phrases_array[$file] = $phrases;
          } else {
            $diff_phrases[$file] = engine_array_diff_key($phrases, $target_big_array[$file]);
            foreach( $diff_phrases[$file] as $key => $phrase ) {
              if( !empty($phrase) && empty($customPhrases[$key]) ) {
                $diff_phrases_array[$file][$key] = $phrase;
              }
            }
          }
        }
        
        $this->view->phrases = $diff_phrases_array;
        $this->view->missedFiles = array_keys($diff_phrases_array);
        $session = new Zend_Session_Namespace('missed_files');
        $session->missed_files = $this->view->missedFiles; 
    }

    function mobileTranslatorAction() {
        //MAKE NAVIGATION
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
                ->getNavigation('sitetranslator_admin_main', array(), 'sitetranslator_admin_main_mobile_translator');
        $this->view->siteandroidapp = Engine_Api::_()->hasModuleBootstrap('siteandroidapp');
        $this->view->siteiosapp = Engine_Api::_()->hasModuleBootstrap('siteiosapp');
    }

    function supportAction() {
        //MAKE NAVIGATION
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
                ->getNavigation('sitetranslator_admin_main', array(), 'sitetranslator_admin_main_support');
    }

    function translateAction() {
        $params = $this->_getAllParams();
        $response = Engine_Api::_()->sitetranslator()->getTranslatedResponse($params["phrase"], $params["source"], $params["target"]);
        return $this->_helper->json($response);
    }

    /**
     * Generate the associative array of all files to phrases at folder path
     * @param String,array
     * return array
     */
    public function sitetranslator_csv_folder_to_array($folder_path, $selectedFiles) {
        $folder_path .= DIRECTORY_SEPARATOR;
        // Gather Folder's CSV Files
        $csv_file_array = array();
        foreach (glob($folder_path . '*.csv') as $csv_filename) {
            $csv_file_array[] = $csv_filename;
        }
        $csv_file_count = engine_count($csv_file_array);
        $big_array = array();
        for ($i = 0; $i < $csv_file_count; $i++) {
            $name = substr($csv_file_array[$i], strrpos($csv_file_array[$i], "/") + 1, strlen($csv_file_array[$i]));
            if (!engine_in_array($name, $selectedFiles)) {
                continue;
            }
            $big_array[$name] = $this->_sitetranslator_csv_to_array($csv_file_array[$i]);
        }
        return $big_array;
    }

    /**
     * Generate the array of phrases of given csv file
     * @param String
     * return array
     */
    protected function _sitetranslator_csv_to_array($csv_file) {
        $file_array = array();
        if (( $data = fopen($csv_file, 'r')) !== FALSE) {
            // ignore first characters of file until double quotes are found (")
            while (( $phrase = @fgetcsv($data, 0, ';', '"', '#') ) !== FALSE) {

                // If First and Last charachters are " double quotes, remove them
                if (strpos($phrase[0], '"') == 0 && strrpos($phrase[0], '"') == (strlen($phrase[0]) - 1 )) {
                    $phrase[0] = substr($phrase[0], 1, -1);
                }

                $phrase_count = engine_count($phrase);
                if ($phrase_count == 2) {
                    // Add Singular Phrases
                    $file_array[$phrase[0]] = $phrase[1];
                } elseif ($phrase_count > 2) {
                    // Add Pluralized Phrases
                    $plural_array = array();
                    for ($c = 1; $c < $phrase_count; $c++) {
                        $plural_array[] = $phrase[$c];
                    }
                    $file_array[$phrase[0]] = $plural_array;
                } else {
                    // Do nothing for Blank Phrases
                }
            }
        }

        return $file_array;
    }

    /**
     * Write the given content into targeted path
     * @param String,String,int
     * return boolean
     */
    function sitetranslator_array_to_csv($target_path, $content, $set = 0) {
        if ($set) {
            $fp = fopen($target_path, 'a');
            fwrite($fp, $content);
            fclose($fp);
        } else {
            touch($target_path);
            chmod($target_path, 0777);
            $fp = fopen($target_path, 'w');
            fwrite($fp, $content);
            fclose($fp);
        }

        return true;
    }
    /**
     * Generate array of all .csv files at source location
     * @param String
     * return array
     */
    function csvFiles($source) {
        $folder = APPLICATION_PATH . "/application/languages/$source" . DIRECTORY_SEPARATOR;
        $sourceFiles = glob($folder . '*.csv');
        $sourceFileOptions = engine_array_map(function($file) {
            return substr($file, strripos($file, '/') + 1);
        }, $sourceFiles);
        return $sourceFileOptions;
    }
    
}
