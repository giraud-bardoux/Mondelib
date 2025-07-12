<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Api_Core extends Core_Api_Abstract
{
    /**
     * @var Core_Model_Item_Abstract|mixed The object that represents the subject of the page
     */
    protected $_subject;

    /**
     * Set the object that represents the subject of the page
     *
     * @param Core_Model_Item_Abstract|mixed $subject
     * @return Core_Api_Core
     */
    public function setSubject($subject)
    {
        if( null !== $this->_subject ) {
            throw new Core_Model_Exception("The subject may not be set twice");
        }

        if( !($subject instanceof Core_Model_Item_Abstract) ) {
            throw new Core_Model_Exception("The subject must be an instance of Core_Model_Item_Abstract");
        }

        $this->_subject = $subject;
        return $this;
    }

    /**
     * Get the previously set subject of the page
     *
     * @return Core_Model_Item_Abstract|null
     */
    public function getSubject($type = null)
    {
        if( null === $this->_subject ) {
            throw new Core_Model_Exception("getSubject was called without first setting a subject.  Use hasSubject to check");
        } else if( is_string($type) && $type !== $this->_subject->getType() ) {
            throw new Core_Model_Exception("getSubject was given a type other than the set subject");
        } else if( is_array($type) && !engine_in_array($this->_subject->getType(), $type) ) {
            throw new Core_Model_Exception("getSubject was given a type other than the set subject");
        }

        return $this->_subject;
    }

    /**
     * Checks if a subject has been set
     *
     * @return bool
     */
    public function hasSubject($type = null)
    {
        if( null === $this->_subject ) {
            return false;
        } else if( null === $type ) {
            return true;
        } else {
            return ( $type === $this->_subject->getType() );
        }
    }

    public function clearSubject()
    {
        $this->_subject = null;
        return $this;
    }

    public function getCaptchaOptions(array $params = array())
    {
        $spamSettings = Engine_Api::_()->getApi('settings', 'core')->core_spam;
        $recaptchaVersionSettings = Engine_Api::_()->getApi('settings', 'core')->core_spam_recaptcha_version;

        if($recaptchaVersionSettings == 1) {
            // Image captcha
            return array_merge(array(
                'label' => 'Human Verification',
                'description' => 'Please type the characters you see in the image.',
                'captcha' => 'image',
                'required' => true,
                'captchaOptions' => array(
                    'wordLen' => 6,
                    'fontSize' => '30',
                    'timeout' => 300,
                    'imgDir' => APPLICATION_PATH . '/public/temporary/',
                    'imgUrl' => Zend_Registry::get('Zend_View')->baseUrl() . '/public/temporary',
                    'font' => APPLICATION_PATH . '/application/modules/Core/externals/fonts/arial.ttf',
                ),
            ), $params);
        } else if($recaptchaVersionSettings == 0  && !empty($spamSettings['recaptchaprivatev3']) && !empty($spamSettings['recaptchapublicv3'])) {
            $script = "en4.core.runonce.add(function() {
            scriptJquery('#captcha-wrapper').hide();
              scriptJquery('<input>').attr({ 
                  name: 'recaptcha_response', 
                  id: 'recaptchaResponse', 
                  type: 'hidden', 
              }).appendTo('.global_form'); 
            });";
            $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
            $view->headScript()->appendScript($script);
            
            // Recaptcha v3
            return array_merge(array(
                'captcha' => 'ReCaptcha3',
                'required' => true,
                'captchaOptions' => array(
                    'privkey' => $spamSettings['recaptchaprivatev3'],
                    'pubkey' => $spamSettings['recaptchapublicv3'],
                    'ssl' => constant('_ENGINE_SSL'),   // Fixed Captcha does not work well when ssl is enabled on website
                ),
            ), $params);
        }
    }

    public function smileyToEmoticons($string = null)
    {
        $emoticonsTag = Engine_Api::_()->activity()->getEmoticons(true);
        if (empty($emoticonsTag)) {
            return $string;
        }

        $string = str_replace("&lt;:o)", "<:o)", $string);
        $string = str_replace("(&amp;)", "(&)", $string);

        return strtr($string, $emoticonsTag);
    }
    public  function floodCheckMessage($data = array()){
        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        if(engine_count($data)){
            $duration = $data[0];
            $type = $data[1];
            //$time = $duration.' '.($duration == 1 ? $type : $type."s");
            $time =  "1 ".$type;
            return $view->translate('You have reached maximum limit of posting in %s. Try again after this duration expires.',$time);
        }
        return "";
    }
    public function clearLogs() {

      $logfileSize = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.logfile.size', 50);

      if(file_exists(APPLICATION_PATH . '/temporary/log/main.log')) {
        $logSize = filesize(APPLICATION_PATH . '/temporary/log/main.log');
        $logSize = number_format($logSize / 1048576, 2);
        if($logfileSize < $logSize) {
            file_put_contents(APPLICATION_PATH . '/temporary/log/main.log', '');
        }
      }

      if(file_exists(APPLICATION_PATH . '/temporary/log/warnings.log')) {
        $logSize = filesize(APPLICATION_PATH . '/temporary/log/warnings.log');
        $logSize = number_format($logSize / 1048576, 2);
        if($logfileSize < $logSize  || !_ENGINE_ENABLE_LOG) {
            file_put_contents(APPLICATION_PATH . '/temporary/log/warnings.log', '');
        }
      }

      if(file_exists(APPLICATION_PATH . '/temporary/log/install.log')) {
        $logSize = filesize(APPLICATION_PATH . '/temporary/log/install.log');
        $logSize = number_format($logSize / 1048576, 2);
        if($logfileSize < $logSize ) {
            file_put_contents(APPLICATION_PATH . '/temporary/log/install.log', '');
        }
      }

      if(file_exists(APPLICATION_PATH . '/temporary/log/task.log')) {
        $logSize = filesize(APPLICATION_PATH . '/temporary/log/task.log');
        $logSize = number_format($logSize / 1048576, 2);
        if($logfileSize < $logSize) {
            file_put_contents(APPLICATION_PATH . '/temporary/log/task.log', '');
        }
      }

      if(file_exists(APPLICATION_PATH . '/temporary/log/translate.log')) {
        $logSize = filesize(APPLICATION_PATH . '/temporary/log/translate.log');
        $logSize = number_format($logSize / 1048576, 2);
        if($logfileSize < $logSize) {
            file_put_contents(APPLICATION_PATH . '/temporary/log/translate.log', '');
        }
      }
    }
    public function getFileUrl($image) {
        $table = Engine_Api::_()->getDbTable('files', 'core');
        $result = $table->select()
                    ->from($table->info('name'), 'storage_file_id')
                    ->where('storage_path =?', $image)
                    ->query()
                    ->fetchColumn();
        if(!empty($result)) {
          $storage = Engine_Api::_()->getItem('storage_file', $result);
          return $storage->map();
        } else {
          return $image;
        }
    }
    public function isMobile()
    {
        // No UA defined?
        if( !isset($_SERVER['HTTP_USER_AGENT']) ) {
          return false;
        }

        // Windows is (generally) not a mobile OS
        if( false !== stripos($_SERVER['HTTP_USER_AGENT'], 'windows') &&
            false === stripos($_SERVER['HTTP_USER_AGENT'], 'windows phone os')) {
          return false;
        }

        // Sends a WAP profile header
        if( isset($_SERVER['HTTP_PROFILE']) ||
            isset($_SERVER['HTTP_X_WAP_PROFILE']) ) {
          return true;
        }

        // Accepts WAP as a valid type
        if( isset($_SERVER['HTTP_ACCEPT']) &&
            false !== stripos($_SERVER['HTTP_ACCEPT'], 'application/vnd.wap.xhtml+xml') ) {
          return true;
        }

        // Is Opera Mini
        if( isset($_SERVER['ALL_HTTP']) &&
            false !== stripos($_SERVER['ALL_HTTP'], 'OperaMini') ) {
          return true;
        }

        if( preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android)/i', $_SERVER['HTTP_USER_AGENT']) ) {
          return true;
        }

        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
        $mobile_agents = array(
          'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird',
          'blac', 'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric',
          'hipt', 'inno', 'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c',
          'lg-d', 'lg-g', 'lge-', 'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi',
          'mot-', 'moto', 'mwbp', 'nec-', 'newt', 'noki', 'oper', 'palm', 'pana',
          'pant', 'phil', 'play', 'port', 'prox', 'qwap', 'sage', 'sams', 'sany',
          'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar', 'sie-', 'siem', 'smal',
          'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-', 'tosh', 'tsm-',
          'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp', 'wapr',
          'webc', 'winw', 'winw', 'xda ', 'xda-'
        );

        if( engine_in_array($mobile_ua, $mobile_agents) ) {
          return true;
        }
        return false;
    }
    
    /**
    * Decode emoji in text
    * @param string $text text to decode
    */
    public function DecodeEmoji($text) {
      return $this->convertEmoji($text,"DECODE");
    }
    
    public function encode($text) {
      return $this->convertEmoji($text, 'ENCODE');
    }
    
    /**
    * Decode emoji in text
    * @param string $text text to decode
    */
    public function decode($text) {
      return $this->convertEmoji($text, 'DECODE');
    }
    
    public function convertEmoji($text,$op) {
    
      if($op=="ENCODE") {
        return preg_replace_callback('/([0-9|#][\x{20E3}])|[\x{00ae}|\x{00a9}|\x{203C}|\x{2047}|\x{2048}|\x{2049}|\x{3030}|\x{303D}|\x{2139}|\x{2122}|\x{3297}|\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{1F000}-\x{1FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F9FF}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F9FF}][\x{1F000}-\x{1FEFF}]?/u',array('self',"encodeEmojis"),$text);
      } else {
        return preg_replace_callback('/(\\\u[0-9a-f]{4})+/',array('self',"decodeEmojis"),$text);
      }
    }
    
    private static function encodeEmojis($match) {
      return str_replace(array('[',']','"'),'',json_encode($match));
    }
    
    private static function decodeEmojis($text) {
      if(!$text) return '';
      $text = $text[0];
      $decode = json_decode($text,true);
      if($decode) return $decode;
      $text = '["' . $text . '"]';
      $decode = json_decode($text);
      if(engine_count($decode) == 1){
        return $decode[0];
      }
      return $text;
    }
    
    public function dateFormatCalendar() {
      $localeObject = Zend_Registry::get('Locale');
      $dateLocaleString = $localeObject->getTranslation('long', 'Date', $localeObject);
      $dateLocaleString = preg_replace('~\'[^\']+\'~', '', $dateLocaleString);
      $dateLocaleString = strtolower($dateLocaleString);
      $dateLocaleString = preg_replace('/[^ymd]/i', '', $dateLocaleString);
      $dateLocaleString = preg_replace(array('/y+/i', '/m+/i', '/d+/i'), array('yy/', 'mm/', 'dd/'), $dateLocaleString);
      return trim($dateLocaleString, '/');
    }
    
	/**
	* This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
	* 
	* @param string $sSize
	* @return integer The value in bytes
	*/
	public function convertPHPSizeToBytes($sSize) {
		$sSuffix = strtoupper(substr($sSize, -1));
		if (!engine_in_array($sSuffix,array('P','T','G','M','K'))){
				return (int)$sSize;  
		} 
		$iValue = substr($sSize, 0, -1);
		switch ($sSuffix) {
			case 'P':
				$iValue *= 1024;
			case 'T':
				$iValue *= 1024;
			case 'G':
				$iValue *= 1024;
			case 'M':
				$iValue *= 1024;
			case 'K':
				$iValue *= 1024;
				break;
		}
		return (int)$iValue;
	}

	public function isViewPermission($resource_id) {
	
		$viewer = Engine_Api::_()->user()->getViewer();
		if( null !== $viewer && $viewer->getIdentity() ) {
			$level_id = $viewer->level_id;
		} else {
			$level_id = '5';
		}
		$allowTable = Engine_Api::_()->getDbTable('allow', 'authorization');
		return $allowTable->select()
							->from($allowTable->info('name'), 'value')
							->where('resource_type = ?', 'forum')
							->where('resource_id = ?', $resource_id)
							->where('action = ?', 'view')
							->where('role_id = ? OR role_id = 0', $level_id)
							->query()
							->fetchColumn();
	}
	
	public function isAcpProSetting() {
		$show = true;
    if(Engine_Api::_()->user()->getViewer()->isOnlyAdmin() && !Engine_Api::_()->getApi('settings', 'core')->getSetting('acppro.smtp', 1)) {
			$show = false;
    }
    return $show;
	}
	
	public function saveTinyMceImages($body, $item) {
    $doc = new DOMDocument();
    $doc->loadHTML($body);
    $xml = simplexml_import_dom($doc);
    $images = $xml->xpath('//img');
    $storageDbTable = Engine_Api::_()->getDbTable('files', 'storage');
    $imageIds = array();
    foreach ($images as $img) {
      $img = end(explode('/', $img['src']));
      $storageData = $storageDbTable->getStorageData($img);
      
      if($storageData) {
        $storageData->resource_type = $item->getType();
        $storageData->resource_id = $item->getIdentity();
        $storageData->save();
        
        $imageIds[] = $storageData->file_id;
      }
    }

    //Delete images after upload new or delete images from tinymce
    $select = $storageDbTable->select()
              ->where('resource_type = ?', $item->getType())
              ->where('resource_id = ?', $item->getIdentity());
    if(engine_count($imageIds) > 0) {
      $select->where('file_id NOT In (?)', $imageIds);
    }
    foreach( $storageDbTable->fetchAll($select) as $file ) {
      try {
        Engine_Api::_()->storage()->deleteExternalsFiles($file->file_id);
        $file->delete();
      } catch( Exception $e ) {
        if( !($e instanceof Engine_Exception) ) {
          $log = Zend_Registry::get('Zend_Log');
          $log->log($e->__toString(), Zend_Log::WARN);
        }
      }
    }
	}
	
  public function getModuleItem($moduleName) {
    $itemType = array();
    $filePath =  APPLICATION_PATH . DIRECTORY_SEPARATOR ."application" . DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR . ucfirst($moduleName) . DIRECTORY_SEPARATOR . "settings" . DIRECTORY_SEPARATOR . "manifest.php";
    if (is_file($filePath)) {
      $manafestFile = include $filePath;
      if (is_array($manafestFile) && isset($manafestFile['items'])) {
        foreach ($manafestFile['items'] as $item) {
          $itemType[] = $item;
        }
      }
    }
    return $itemType;
  }
  
  public function contentApprove($item, $content_text) {

    $viewer = Engine_Api::_()->user()->getViewer();

    if (!$item->approved) {
      //Send to admins only
      $admins = Engine_Api::_()->getDbTable('users', 'user')->getAllAdmin();
      foreach ($admins as $admin) {
        Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($admin, $viewer, $item, 'content_waitingapprovalforadmin', array('content_text' => $content_text,  'sender_title' => $item->getOwner()->getTitle(), 'object_title' => $item->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));
      }

      //Send to content owner
      Engine_Api::_()->getDbTable('notifications', 'activity')->addNotification($viewer, $viewer, $item, 'content_waitingapprovalforowner', array('content_text' => $content_text, 'object_title' => $item->getTitle(), 'object_link' => $item->getHref(), 'host' => $_SERVER['HTTP_HOST']));
    } else {
      $item->resubmit = 1;
      $item->save();
    }
  }
  
  public function getContantValueXML($key) {
    $filePath = APPLICATION_PATH . "/application/settings/constants.xml";
    $results = simplexml_load_file($filePath);
    $xmlNodes = $results->xpath('/root/constant[name="' . $key . '"]');
    $nodeName = @$xmlNodes[0];
    $value = @$nodeName->value;
    return $value;
  }
  
  public function facebookShareUrl($href = '', $subject = '') {
    if (!$href)
      return 'javascript:;';
    $href = (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $href;
    return 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($href) . '&t=' . $subject->getTitle();
  }

  public function twitterShareUrl($href = '', $subject = '') {
    if (!$href)
      return 'javascript:;';
    $urlencode = urlencode(((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $href);
    return 'https://twitter.com/share?url=' . $urlencode . '&text=' . htmlspecialchars(urlencode(html_entity_decode($subject->getTitle('encode'), ENT_COMPAT, 'UTF-8')), ENT_COMPAT, 'UTF-8') . "%0a";
  }
  
  public function LinkedinShareUrl($href = '', $subject = '') {
    if (!$href)
      return 'javascript:;';
    $href = (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $href;
    return 'https://www.linkedin.com/shareArticle?mini=true&url=' . $href;
  }
  public function validateFormFields($form = null) {
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
      $formFields[$counter]['errorMessage'] = $message;
      $formFields[$counter]['isRequired'] = $element->isRequired();
      $formFields[$counter]['value'] = (string) $form->{ $element->getName()}->getValue();
      $counter++;
    }
    return $formFields;
  }
  
  public function readWriteXML($keys, $value, $default_constants = null) {

    $filePath = APPLICATION_PATH . "/application/settings/constants.xml";
    $results = simplexml_load_file($filePath);

    if (!empty($keys) && !empty($value)) {
        $contactsThemeArray = array($keys => $value);
    } elseif (!empty($keys)) {
        $contactsThemeArray = array($keys => '');
    } elseif ($default_constants) {
        $contactsThemeArray = $default_constants;
    }

    foreach ($contactsThemeArray as $key => $value) {
      $xmlNodes = $results->xpath('/root/constant[name="' . $key . '"]');
      $nodeName = @$xmlNodes[0];
      $params = json_decode(json_encode($nodeName));
      $paramsVal = @$params->value;
      if ($paramsVal && $paramsVal != '' && $paramsVal != null) {
          $nodeName->value = $value;
      } else {
          $entry = $results->addChild('constant');
          $entry->addChild('name', $key);
          $entry->addChild('value', $value);
      }
    }
    return $results->asXML($filePath);
  }
  
  public function timeZone() {
    return array(
      'US/Pacific' => '(UTC-8) Pacific Time (US & Canada)',
      'US/Mountain' => '(UTC-7) Mountain Time (US & Canada)',
      'US/Central' => '(UTC-6) Central Time (US & Canada)',
      'US/Eastern' => '(UTC-5) Eastern Time (US & Canada)',
      'America/Halifax' => '(UTC-4)  Atlantic Time (Canada)',
      'America/Anchorage' => '(UTC-9)  Alaska (US & Canada)',
      'Pacific/Honolulu' => '(UTC-10) Hawaii (US)',
      'Pacific/Samoa' => '(UTC-11) Midway Island, Samoa',
      'Etc/GMT-12' => '(UTC-12) Eniwetok, Kwajalein',
      'Canada/Newfoundland' => '(UTC-3:30) Canada/Newfoundland',
      'America/Buenos_Aires' => '(UTC-3) Brasilia, Buenos Aires, Georgetown',
      'Atlantic/South_Georgia' => '(UTC-2) Mid-Atlantic',
      'Atlantic/Azores' => '(UTC-1) Azores, Cape Verde Is.',
      'Europe/London' => 'Greenwich Mean Time (Lisbon, London)',
      'Europe/Berlin' => '(UTC+1) Amsterdam, Berlin, Paris, Rome, Madrid',
      'Europe/Athens' => '(UTC+2) Athens, Helsinki, Istanbul, Cairo, E. Europe',
      'Europe/Moscow' => '(UTC+3) Baghdad, Kuwait, Nairobi, Moscow',
      'Iran' => '(UTC+3:30) Tehran',
      'Asia/Dubai' => '(UTC+4) Abu Dhabi, Kazan, Muscat',
      'Asia/Kabul' => '(UTC+4:30) Kabul',
      'Asia/Yekaterinburg' => '(UTC+5) Islamabad, Karachi, Tashkent',
      'Asia/Calcutta' => '(UTC+5:30) Bombay, Calcutta, New Delhi',
      'Asia/Katmandu' => '(UTC+5:45) Nepal',
      'Asia/Omsk' => '(UTC+6) Almaty, Dhaka',
      'Indian/Cocos' => '(UTC+6:30) Cocos Islands, Yangon',
      'Asia/Krasnoyarsk' => '(UTC+7) Bangkok, Jakarta, Hanoi',
      'Asia/Hong_Kong' => '(UTC+8) Beijing, Hong Kong, Singapore, Taipei',
      'Asia/Tokyo' => '(UTC+9) Tokyo, Osaka, Sapporto, Seoul, Yakutsk',
      'Australia/Adelaide' => '(UTC+9:30) Adelaide, Darwin',
      'Australia/Sydney' => '(UTC+10) Brisbane, Melbourne, Sydney, Guam',
      'Asia/Magadan' => '(UTC+11) Magadan, Solomon Is., New Caledonia',
      'Pacific/Auckland' => '(UTC+12) Fiji, Kamchatka, Marshall Is., Wellington',
    );
  }

  function generateJsCss(){

    $view = Zend_Registry::get('Zend_View');
    $baseUrl = $view->baseUrl().'/';
    
    //remove js
    $files = glob(APPLICATION_PATH."/externals/scripts/*"); // get all file names
    foreach($files as $file){ // iterate files
      if(is_file($file)) {
        unlink($file); // delete file
      }
    }
    // remove css
    $files = glob(APPLICATION_PATH."/externals/styles/*"); // get all file names
    foreach($files as $file){ // iterate files
      if(is_file($file)) {
        unlink($file); // delete file
      }
    }

    // read manifest
    $jsFile = array();
    $cssFile = array();
    foreach (Zend_Registry::get('Engine_Manifest') as $key => $data) {
      if (empty($data['loadDefault'])) {
        continue;
      }
      foreach ($data['loadDefault'] as $key => $file) {
        if($key == "css"){
          $cssFile = array_merge($cssFile, $file);
        }else if($key == "js"){
          $jsFile = array_merge($jsFile, $file);
        }
      }
    }
    
    // remove duplicate files
    array_unique($jsFile);
    array_unique($cssFile);
    
    $jsFiles = array_chunk($jsFile, 30);
    $cssFiles = array_chunk($cssFile, 40);
    $counterJsKey = 0;
    $counterCssKey = 0;
    foreach($jsFiles as $key=>$files){
      $js = "";
      foreach($files as $file){
        $js .= file_get_contents(APPLICATION_PATH.DS.$file);
        $js .= ";
        
        ";
      }
      $counterJsKey = $key+1;
      @file_put_contents(APPLICATION_PATH."/externals/scripts/script_$counterJsKey.js", $this->minify_js($js));
    }

    foreach($cssFiles as $key=>$files){
      $css = "";
      foreach($files as $file){
        $css .= str_replace("~/",$baseUrl,file_get_contents(APPLICATION_PATH.DS.$file));
      }
      $counterCssKey = $key+1;
      @file_put_contents(APPLICATION_PATH."/externals/styles/styles_$counterCssKey.css", $this->minify_css($css));
    }

    Engine_Api::_()->getApi('settings','core')->setSetting("core.scripts.counter",$counterJsKey);
    Engine_Api::_()->getApi('settings','core')->setSetting("core.styles.counter",$counterCssKey);


    $css = "";
    $js = file_get_contents(APPLICATION_PATH.'/application/modules/Core/externals/scripts/core.js');
    $js .= file_get_contents(APPLICATION_PATH.'/externals/jQuery/core.js');
    $js .= file_get_contents(APPLICATION_PATH.'/application/modules/User/externals/scripts/core.js');
    $js .= file_get_contents(APPLICATION_PATH.'/externals/mdetect/mdetect.js');
    $js .= file_get_contents(APPLICATION_PATH.'/externals/smoothbox/smoothbox4.js');
    $modulesEnable = Engine_Api::_()->getDbTable('modules', 'core')->getEnabledModuleNames();
    foreach($modulesEnable as $module){
      $moduleName = ucfirst($module);
      if(file_exists(APPLICATION_PATH.'/application/modules/'.$moduleName.'/externals/styles/main.css')){
        $css .= file_get_contents(APPLICATION_PATH.'/application/modules/'.$moduleName.'/externals/styles/main.css');
      }
      if(file_exists(APPLICATION_PATH.'/application/modules/'.$moduleName.'/externals/scripts/core.js') && $moduleName != "Core" && $moduleName != "User"){
        $js .= file_get_contents(APPLICATION_PATH.'/application/modules/'.$moduleName.'/externals/scripts/core.js');
      }
    }
    
    @file_put_contents(APPLICATION_PATH.'/externals/styles/styles.css', str_replace("~/",$baseUrl,$this->minify_css($css)));
    @file_put_contents(APPLICATION_PATH.'/externals/scripts/script.js', $this->minify_js($js));
  }
  

function minify_css($input) {
  if(trim($input) === "") return $input;
  return preg_replace(
      array(
          // Remove comment(s)
          '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
          // Remove unused white-space(s)
          '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~]|\s(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
          // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
          '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
          // Replace `:0 0 0 0` with `:0`
          '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
          // Replace `background-position:0` with `background-position:0 0`
          '#(background-position):0(?=[;\}])#si',
          // Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
          '#(?<=[\s:,\-])0+\.(\d+)#s',
          // Minify string value
          '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
          '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
          // Minify HEX color code
          '#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
          // Replace `(border|outline):none` with `(border|outline):0`
          '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
          // Remove empty selector(s)
          '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
      ),
      array(
          '$1',
          '$1$2$3$4$5$6$7',
          '$1',
          ':0',
          '$1:0 0',
          '.$1',
          '$1$3',
          '$1$2$4$5',
          '$1$2$3',
          '$1:0',
          '$1$2'
      ),
  $input);
}
function removeComments($string){
  //Takes a string of code, not an actual function.
  $pattern = '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/';
  return preg_replace($pattern, '', $string);
}
// JavaScript Minifier
function minify_js($input) {
  return $input;
  return $input;
  // return $input;
    if(trim($input) === "") return $input;
    $input = preg_replace('/([-\+])\s+\+([^\s;]*)/', '$1 (+$2)', $input);
    // condense spaces
    $input = preg_replace("/\s*\n\s*/", "\n", $input); // spaces around newlines
    $input = preg_replace("/\h+/", " ", $input); // \h+ horizontal white space
    // remove unnecessary horizontal spaces around non variables (alphanumerics, underscore, dollar sign)
    $input = preg_replace("/\h([^A-Za-z0-9\_\$])/", '$1', $input);
    $input = preg_replace("/([^A-Za-z0-9\_\$])\h/", '$1', $input);
    // remove unnecessary spaces around brackets and parentheses
    $input = preg_replace("/\s?([\(\[{])\s?/", '$1', $input);
    $input = preg_replace("/\s([\)\]}])/", '$1', $input);
    // remove unnecessary spaces around operators that don't need any spaces (specifically newlines)
    $input = preg_replace("/\s?([\.=:\-+,])\s?/", '$1', $input);
    // unnecessary characters 
    $input = preg_replace("/;\n/", ";", $input); // semicolon before newline
    $input = preg_replace('/;}/', '}', $input); // semicolon before end bracket
    // return $input;
    echo $input;die;
    $input =  preg_replace(
        array(
            // Remove comment(s)
            '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
            // Remove white-space(s) outside the string and regex
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
            // Remove the last semicolon
            '#;+\}#',
            // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
            '#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
            // --ibid. From `foo['bar']` to `foo.bar`
            '#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'
        ),
        array(
            '$1',
            '$1$2',
            '}',
            '$1$3',
            '$1.$3'
        ),
      $this->removeComments($input));

      
      return $input;
  }

  function fileTypes($type) {
  
    $counter = 0;
    $types = array(
    // Image formats
    'image_'.$counter++ => 'image/jpeg',
    'image_'.$counter++ => 'image/gif',
    'image_'.$counter++ => 'image/png',
    'image_'.$counter++ => 'image/bmp',
    'image_'.$counter++ => 'image/tiff',
    'image_'.$counter++ => 'image/x-icon',
    // Video formats
    'video_'.$counter++ => 'video/x-ms-asf',
    'video_'.$counter++ => 'video/x-ms-wmv',
    'video_'.$counter++ => 'video/x-ms-wmx',
    'video_'.$counter++ => 'video/x-ms-wm',
    'video_'.$counter++ => 'video/avi',
    'video_'.$counter++ => 'video/divx',
    'video_'.$counter++ => 'video/x-flv',
    'video_'.$counter++ => 'video/quicktime',
    'video_'.$counter++ => 'video/mpeg',
    'video_'.$counter++ => 'video/mp4',
    'video_'.$counter++ => 'video/ogg',
    'video_'.$counter++ => 'video/webm',
    'video_'.$counter++ => 'video/x-matroska',
    // Text formats
    'text_'.$counter++ => 'text/plain',
    'code_'.$counter++ => 'application/octet-stream',
    'csv_'.$counter++ => 'text/csv',
    'text_'.$counter++ => 'text/tab-separated-values',
    'calander_'.$counter++ => 'text/calendar',
    'text_'.$counter++ => 'text/richtext',
    'code_'.$counter++ => 'text/css',
    'code_'.$counter++ => 'text/html',
    // Audio formats
    'audio_'.$counter++ => 'audio/mpeg',
    'audio_'.$counter++ => 'audio/x-realaudio',
    'audio_'.$counter++ => 'audio/wav',
    'audio_'.$counter++ => 'audio/amr',
      'audio_'.$counter++ => 'audio/mp3',
    'audio_'.$counter++ => 'audio/ogg',
    'audio_'.$counter++ => 'audio/midi',
    'audio_'.$counter++ => 'audio/x-ms-wma',
    'audio_'.$counter++ => 'audio/x-ms-wax',
    'audio_'.$counter++ => 'audio/x-matroska',
    // Misc application formats
    'file_'.$counter++ => 'application/rtf',
    'code_'.$counter++ => 'application/javascript',
    'pdf_'.$counter++ => 'application/pdf',
    'file_'.$counter++ => 'application/x-shockwave-flash',
    'file_'.$counter++ => 'application/java',
    'archive_'.$counter++ => 'application/x-tar',
    'archive_'.$counter++ => 'application/zip',
    'archive_'.$counter++ => 'application/x-gzip',
    'archive_'.$counter++ => 'application/rar',
    'file_'.$counter++ => 'application/x-7z-compressed',
    'exe_'.$counter++ => 'application/x-msdownload',
    // MS Office formats
    'document_'.$counter++ => 'application/msword',
    'document_'.$counter++ => 'application/vnd.ms-powerpoint',
    'document_'.$counter++ => 'application/vnd.ms-write',
    'document_'.$counter++ => 'application/vnd.ms-excel',
    'document_'.$counter++ => 'application/vnd.ms-access',
    'document_'.$counter++ => 'application/vnd.ms-project',
    'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'document_'.$counter++ => 'application/vnd.ms-word.document.macroEnabled.12',
    'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
    'document_'.$counter++ => 'application/vnd.ms-word.template.macroEnabled.12',
    'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'document_'.$counter++ => 'application/vnd.ms-excel.sheet.macroEnabled.12',
    'document_'.$counter++ => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
    'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
    'document_'.$counter++ => 'application/vnd.ms-excel.template.macroEnabled.12',
    'document_'.$counter++ => 'application/vnd.ms-excel.addin.macroEnabled.12',
    'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'document_'.$counter++ => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
    'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
    'document_'.$counter++ => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
    'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.presentationml.template',
    'document_'.$counter++ => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
    'document_'.$counter++ => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
    'document_'.$counter++ => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
    'document_'.$counter++ => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
    'document_'.$counter++ => 'application/onenote',
    // OpenOffice formats
    'file_'.$counter++ => 'application/vnd.oasis.opendocument.text',
    'file_'.$counter++ => 'application/vnd.oasis.opendocument.presentation',
    'file_'.$counter++ => 'application/vnd.oasis.opendocument.spreadsheet',
    'file_'.$counter++ => 'application/vnd.oasis.opendocument.graphics',
    'file_'.$counter++ => 'application/vnd.oasis.opendocument.chart',
    'file_'.$counter++ => 'application/vnd.oasis.opendocument.database',
    'file_'.$counter++ => 'application/vnd.oasis.opendocument.formula',
    // WordPerfect formats
    'file_'.$counter++ => 'application/wordperfect',
    // iWork formats
    'file_'.$counter++ => 'application/vnd.apple.keynote',
    'file_'.$counter++ => 'application/vnd.apple.numbers',
    'file_'.$counter++ => 'application/vnd.apple.pages',
    );
    if(false !== $key = array_search($type, $types)) {
      return $key;
    } else {
      return "";
    }
  }
  
  public function saveThemeVariables($values, $form, $themeName) {

    unset($values['contrast_mode']);
    unset($values['theme_color']);

    $theme = APPLICATION_PATH . '/application/themes/'.$themeName;
    @chmod($theme, 0777);
    $filename = $theme . '/theme-variables.css';
    if (!is_readable($theme)) {
      $error = Zend_Registry::get('Zend_Translate')->_("You do not have read permission on below file path. So, please give chmod 777 recursive permission to continue this process. Path Name: %s", $theme);
      $form->addError($error);
      return;
    }

    $fileExists = @file_exists($filename);
    if (!empty($fileExists)) {
      @chmod($theme, 0777);
      if (!is_writable($theme)) {
        $error = Zend_Registry::get('Zend_Translate')->_("You do not have writable permission on below file path. So, please give chmod 777 recursive permission to continue this process.  Path Name: $theme");
        $form->addError($error);
        return;
      }
      
      $fh = @fopen($filename, 'w');
      $constant = '';
      $constant .= ':root {';
      $constant .= PHP_EOL ;
      foreach($values as $key => $value) {
        $key = str_replace('_', '-', $key);
        $constant .= '--'.$key.':'.$value.';' . PHP_EOL;
      }
      $constant .= '}';
      @fwrite($fh, $constant);
      @chmod($filename, 0777);
      @fclose($fh);
      @chmod($filename, 0777);
      @chmod($filename, 0777);
    } else {
      $fh = @fopen($filename, 'w');
      $constant = '';
      $constant .= ':root {';
      $constant .= PHP_EOL ;
      foreach($values as $key => $value) {
        $key = str_replace('_', '-', $key);
        $constant .= '--'.$key.':'.$value.';' . PHP_EOL;
      }
      $constant .= '}';
      @fwrite($fh, $constant);
      @chmod($filename, 0777);
      @fclose($fh);
      @chmod($filename, 0777);
      @chmod($filename, 0777);
    }
  }

  public function getGoogleFonts($param) {

    $url = "https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyDczHMCNc0JCmJACM86C7L8yYdF9sTvz1A";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $data = curl_exec($ch);
    curl_close($ch);

    $results = json_decode($data,true);

    $googleFontArray = $googleFontVariants = array();

    foreach($results['items'] as $re) {
      $googleFontArray['"'.$re["family"].'"'] = $re['family'];
      $googleFontVariants['"'.$re["family"].'"'] = $re['variants'];
    }

    if($param == 'fontfamily') {
      return $googleFontArray;
    } else if($param == 'variants') {
      return $googleFontVariants;
    }
  }

  public function convertImageToWebp($extension) {

    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.convertwebp', 1) && engine_in_array(strtolower($extension), array('jpg','jpeg','png'))) {
      return 'webp';
    }
    return $extension;
  }

  //For link fetch work
  public function handleIframelyInformation($uri) {

    $iframelyDisallowHost = Engine_Api::_()->getApi('settings', 'core')->getSetting('video_iframely_disallow');
    if (parse_url($uri, PHP_URL_SCHEME) === null) {
        $uri = "http://" . $uri;
    }
    
    $uriHost = Zend_Uri::factory($uri)->getHost();
    if ($iframelyDisallowHost && engine_in_array($uriHost, $iframelyDisallowHost)) {
        return;
    }

    if(Engine_Api::_()->getApi('settings', 'core')->getSetting('video.youtube.apikey') && engine_in_array($uriHost, array('youtube.com','www.youtube.com','youtube', 'youtu.be'))){
      
      return $this->YoutubeVideoInfomation($uri);
    } else {
      $config = Engine_Api::_()->getApi('settings', 'core')->core_iframely;
      $iframely = Engine_Iframely::factory($config)->get($uri, 'video');
    }
    
    if (!engine_in_array('player', array_keys($iframely['links'])))
      return;

    if (engine_in_array('player', array_keys($iframely['links']))) {
        if(empty($iframely['links']['player']))
          return;
    }

    $information = array('thumbnail' => '', 'title' => '', 'description' => '', 'duration' => '');
    if (!empty($iframely['links']['thumbnail'])) {
        $information['thumbnail'] = $iframely['links']['thumbnail'][0]['href'];
        if (parse_url($information['thumbnail'], PHP_URL_SCHEME) === null) {
            $information['thumbnail'] = str_replace(array('://', '//'), '', $information['thumbnail']);
            $information['thumbnail'] = "http://" . $information['thumbnail'];
        }
    }
    if (!empty($iframely['meta']['title'])) {
        $information['title'] = $iframely['meta']['title'];
    }
    if (!empty($iframely['meta']['description'])) {
        $information['description'] = $iframely['meta']['description'];
    }
    if (!empty($iframely['meta']['duration'])) {
        $information['duration'] = $iframely['meta']['duration'];
    }else if($iframely['meta']['site'] == 'YouTube') {
      $video_id = explode("?v=", $iframely['meta']['canonical']);
      $video_id = $video_id[1];
      $information['duration'] = $this->YoutubeVideoInfo($video_id);
      $information['duration'] = Engine_Date::convertISO8601IntoSeconds($information['duration']);
    }else if($iframely['meta']['site'] == 'Dailymotion') {
      $video_id = explode("/video/", $iframely['meta']['canonical']);
      $information = $this->handleInformation(4,$video_id[1]);
      $information['duration'] = $information['duration'];
    }
    else{
      $information['duration'] = 0;
    }
    $information['status'] = 1;
    $information['code'] = $iframely['html'];
    if($iframely['meta']['site'])
      $information['site'] = $iframely['meta']['site'];
    return $information;
  }
  
  public function getYoutubeIdFromUrl($url) {
    $parts = parse_url($url);
    if(isset($parts['query'])) {
      parse_str($parts['query'], $qs);
      if(isset($qs['v'])){
        return $qs['v'];
      } else if(isset($qs['vi'])){
        return $qs['vi'];
      }
    }
    if(isset($parts['path'])){
      $path = explode('/', trim($parts['path'], '/'));
      return $path[count($path)-1];
    }
    return false;
  }
  
  public function YoutubeVideoInfomation($uri) {
   
    $video_id = $this->getYoutubeIdFromUrl($uri);
    $key = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.youtube.apikey');
    if(empty($key)){
        return;
    }
    $url = 'https://www.googleapis.com/youtube/v3/videos?id='.$video_id.'&key='.$key.'&part=snippet,player,contentDetails';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_REFERER']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    curl_close($ch);
    $response_a = json_decode($response,TRUE);
    
    $iframely =  $response_a['items'][0];
    if (!engine_in_array('player', array_keys($iframely))) {
        return;
    }
    $information = array('thumbnail' => '', 'title' => '', 'description' => '', 'duration' => '');
    if (!empty($iframely['snippet']['thumbnails'])) {
        $information['thumbnail'] = $iframely['snippet']['thumbnails']['high']['url'];
        if (parse_url($information['thumbnail'], PHP_URL_SCHEME) === null) {
            $information['thumbnail'] = str_replace(array('://', '//'), '', $information['thumbnail']);
            $information['thumbnail'] = "http://" . $information['thumbnail'];
        }
    }
    if (!empty($iframely['snippet']['title'])) {
        $information['title'] = $iframely['snippet']['title'];
    }
    if (!empty($iframely['snippet']['description'])) {
        $information['description'] = $iframely['snippet']['description'];
    }
    if (!empty($iframely['contentDetails']['duration'])) {
        $information['duration'] =  Engine_Date::convertISO8601IntoSeconds($iframely['contentDetails']['duration']);
    }
    $information['code'] = $iframely['player']['embedHtml'];
    return $information; 
  }
  
  public function YoutubeVideoInfo($video_id) {
    $key = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.youtube.apikey');
      $url = 'https://www.googleapis.com/youtube/v3/videos?id='.$video_id.'&key='.$key.'&part=snippet,contentDetails';
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      $response = curl_exec($ch);
      curl_close($ch);
      $response_a = json_decode($response);
      return  $response_a->items[0]->contentDetails->duration; //get video duaration
  }
  
  // retrieves infromation and returns title + desc
  public function handleInformation($type, $code) {
    switch ($type) {
      //youtube
      case "1":
        $key = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.youtube.apikey');
        if (function_exists('curl_init')){
          $data =  ($this->url_get_contents("https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id=$code&key=$key"));
        } else
          $data = file_get_contents("https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id=$code&key=$key");
          
        if (empty($data)) {
          return;
        }
        
        $data = Zend_Json::decode($data);
        $information = array();
        $youtube_video = $data['items'][0];
        $information['title'] = $youtube_video['snippet']['title'];
        $information['description'] = $youtube_video['snippet']['description'];
        //$information['duration'] = Engine_Date::convertISO8601IntoSeconds($youtube_video['contentDetails']['duration']);
        return $information;
      //vimeo
      case "2":
        //thumbnail_medium
        $data = simplexml_load_file("http://vimeo.com/api/v2/video/" . $code . ".xml");
        $thumbnail = $data->video->thumbnail_medium;
        $information = array();
        $information['title'] = $data->video->title;
        $information['description'] = $data->video->description;
        $information['duration'] = $data->video->duration;
        return $information;
      case "4":
        if (function_exists('curl_init')) {
          $data =  ($this->url_get_contents("https://api.dailymotion.com/video/$code?fields=allow_embed,description,duration,thumbnail_url,title"));
        } else
          $data = file_get_contents("https://api.dailymotion.com/video/$code?fields=allow_embed,description,duration,thumbnail_url,title");
        $data = json_decode($data, true);
        $information['title'] = $data['title'];
        $information['description'] = $data['description'];
        $information['duration'] = $data['duration'];
        return $information;
    }
  }
  
  function url_get_contents($Url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $Url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
  }
}
