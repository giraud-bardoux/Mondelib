<?php

class Sitetranslator_Bootstrap extends Engine_Application_Bootstrap_Abstract {

    public function _bootstrap($resource = null) {
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new Sitetranslator_Plugin_Core);
    }

}
