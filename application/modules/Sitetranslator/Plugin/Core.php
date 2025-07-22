<?php

class Sitetranslator_Plugin_Core extends Zend_Controller_Plugin_Abstract {

    public function routeShutdown(Zend_Controller_Request_Abstract $request) {
        //CHECK IF ADMIN
        if (substr($request->getPathInfo(), 1, 5) == "admin") {
            $translate = Zend_Registry::get('Zend_Translate');
            $translate->setLocale('en');
        }
    }
    public function onRenderLayoutDefaultSimple($event) {
        // Forward
        return $this->onRenderLayoutDefault($event);
    }

    public function onRenderLayoutMobileSMDefault($event) {
        // Forward
        return $this->onRenderLayoutDefault($event);
    }


    public function onRenderLayoutDefault($event, $mode = null) {

        $view = $event->getPayload();
        if (!($view instanceof Zend_View_Interface)) {
            return;
        }

        $translate = Zend_Registry::get('Zend_Translate');
        $pageLocale = $translate->getLocale();

        $translateList = array();
        foreach ($translate->getList() as $key => $value) {
            $translateList[$key] = str_replace("_","-",$value);
        }
        $languageList = implode(",", $translateList);
        $isMobile = Engine_Api::_()->sitetranslator()->isSiteMobileModeEnabled();

        if ($pageLocale == 'en' || !Engine_Api::_()->getApi("settings", "core")->getSetting('sitetranslator.content.translator.widget.enable', 0)) {
            return;
        }

        $domainName = basename($_SERVER['HTTP_HOST']); 
        $domainName = preg_replace("/www./", "", $domainName);
        $pageLocale = str_replace("_","-",$pageLocale);

        setrawcookie("googtrans", "/en/$pageLocale", time() + 3600, "/", $domainName);
        setrawcookie("googtrans", "/en/$pageLocale", time() + 3600);

        if (!$isMobile) {
               $script = <<<EOF
     en4.core.runonce.add(function () {
     if(document.getElementsByTagName("body")[0]){
        var translateElement = document.createElement("div");
            translateElement.setAttribute("id","google_translate_element");
            translateElement.style.display = "none";
        document.getElementsByTagName("body")[0].appendChild(translateElement);
        var e = document.createElement('script');
            e.type = 'text/javascript';
            e.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
        var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(e, s);
            
        }
        });

    function googleTranslateElementInit() {
        var pageLocale = 'en';
        var listLanguages = '$languageList';
        new google.translate.TranslateElement({ 
            pageLanguage: pageLocale, 
            includedLanguages: listLanguages,
            autoDisplay: false 
        }, "google_translate_element");
    }
    en4.core.runonce.add(function(){
        if($("language")){
           $("language").addClass("notranslate");
        }
     
    });
EOF;
        $view->headScript()->appendScript($script);
        } 

        else {

            $script = <<<EOF

    $(function(){
        translationScriptInit();
        if($("#language")){
           $("#language").addClass("notranslate");
        }
    });

    function googleTranslateElementInit() {
        var pageLocale = 'en';
        var listLanguages = '$languageList';
        new google.translate.TranslateElement({ 
            pageLanguage: pageLocale, 
            includedLanguages: listLanguages,
            autoDisplay: false 
        }, "google_translate_element");
    }

    function translationScriptInit() {
        if(document.getElementsByTagName("body")[0]){
            var translateElement = document.createElement("div");
                translateElement.setAttribute("id","google_translate_element");
                translateElement.style.display = "none";
            document.getElementsByTagName("body")[0].appendChild(translateElement);
            var e = document.createElement('script');
                e.type = 'text/javascript';
                e.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
            var s = document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(e, s);
        }
    }
EOF;

        $view->headScriptSM()->appendScript($script);
        $view->headLinkSM()->appendStylesheet('application/modules/Sitetranslator/externals/styles/main.css');
    }
     
    }

    public function getAdminNotifications($event) {
        $db = Engine_Db_Table::getDefaultAdapter();
        $initialTranslateAdapter = $db->select()
                ->from('engine4_core_settings', 'value')
                ->where('`name` = ?', 'core.translate.adapter')
                ->query()
                ->fetchColumn();
        $translateArray = (int) ($initialTranslateAdapter == 'array');
        if (!$translateArray) {
            $url = Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'core', 'controller' => 'settings', 'action' => 'performance'), 'admin_default', true);
            $tips = '<div class="tip sitetranslator_tips"><span>To increase the speed of translation process, please enable ‘Translation Performance’ from <a href="' . $url . '">here</a>.</span></div>';
            $event->addResponse($tips);
        }
    }

}
