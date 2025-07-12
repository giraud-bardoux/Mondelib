<?php


class User_Form_Admin_Settings_Telegram extends Engine_Form {



    public function init() {



    

        $description = $this->getTranslator()->translate(

        'Here, you can integrate SocialEngine to Telegram for allowing users to login into your website using their Telegram accounts. To do so, create an Application through the ');

        $moreinfo = $this->getTranslator()->translate('<a href="%1$s" target="_blank"> Telegram Developers</a> page.<br />');

        $noteText = 'NOTE: The settings for button\'s styling and theming in widgets of this plugin are not supported for "Login with Telegram" option as the data being fetched and displayed from the same is in iframe.';

        $description = vsprintf($description.$moreinfo.$noteText, array('https://web.telegram.org'
        ));

        $this->loadDefaultDecorators();

        $this->getDecorator('Description')->setOption('escape', false);

    

        $settings = Engine_Api::_()->getApi('settings', 'core');

        $this->setTitle('Telegram Integration')

                ->setDescription($description);

 

        $this->addElement('Text', "telegram_username", array(

            'label' => 'Telegram Username',

            'value' => $settings->getSetting('telegram.username', ''),

            'required' => true,

            'allowEmpty' => false,

        ));

        $this->addElement('Text', "telegram_token", array(

            'label' => 'Telegram Token',

            'value' => $settings->getSetting('telegram.token', ''),

            'required' => true,

            'allowEmpty' => false,

        ));



        $this->addElement('Radio', "telegram_enable", array(
            'label' => 'Enable Login',
            'description' => 'Do you want to enable login on your website through this provider?',
            'allowEmpty' => true,
            'required' => false,
            'multiOptions' => array(1 => 'Yes', '0' => 'No'),
            'value' => $settings->getSetting('telegram.enable', 0),

        ));

        $this->addElement('Button', 'submit', array(

            'label' => 'Save Changes',

            'type' => 'submit',

            'ignore' => true

        ));

    }



}

