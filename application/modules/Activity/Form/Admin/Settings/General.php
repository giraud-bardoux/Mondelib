<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: General.php 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
class Activity_Form_Admin_Settings_General extends Engine_Form
{

  public function init()
  {

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->addElement('Text', 'length', array(
      'label' => 'Overall Feed Length',
      'description' => 'ACTIVITY_FORM_ADMIN_SETTINGS_GENERAL_LENGTH_DESCRIPTION',
      'value' => 15,
      'required' => true,
      'allowEmpty' => false,
      'validators' => array(
        array('Int', true),
        array('Between', true, array(1, 50, true)),
        //array('GreaterThan', true, array(0)),
      ),
    ));

    $this->addElement('Text', 'postLength', array(
      'label' => 'Post Feed Character Limit',
      'description' => 'ACTIVITY_FORM_ADMIN_SETTINGS_GENERAL_POSTLENGTH_DESCRIPTION',
      'value' => 1000,
      'required' => true,
      'allowEmpty' => false,
      'maxlength' => 4,
      'validators' => array(
        array('Int', true),
        array('Between', true, array(1, 9999, true)),
        array('GreaterThan', true, array(-1)),
      ),
    ));

    $this->addElement('Select', 'content', array(
      'label' => 'Feed Content',
      'description' => 'ACTIVITY_FORM_ADMIN_SETTINGS_GENERAL_CONTENT_DESCRIPTION',
      'value' => 'everyone',
      'multiOptions' => array(
        'everyone' => 'All Members',
        'networks' => 'My Networks',
        'friends' => 'My Friends'
      )
    ));

    // $this->addElement('Select', 'publish', array(
    //   'label' => 'Item Publishing Option',
    //   'description' => 'ACTIVITY_FORM_ADMIN_SETTINGS_GENERAL_PUBLISH_DESCRIPTION',
    //   'value' => 1,
    //   'multiOptions' => array(
    //     1 => 'Yes, members may specify which of their item types will not be published.',
    //     0 => 'No, members may not specify which of their item types will not be published.'
    //   )
    // ));

    $this->addElement('MultiCheckbox', 'statusboxsettings', array(
      'label' => "Status Box Options Settings",
    ));

    $this->addElement('MultiCheckbox', 'view_privacy', array(
      'label' => 'Posts Privacy Options',
      'description' => 'Your users can choose from any of the options checked below when they decide who can see their posts.',
      'multiOptions' => array(
        'everyone'  => 'Everyone',
        'networks'  => 'Friends or Networks',
        'friends'   => 'Friends Only',
        'onlyme'    => 'Just Me',
      ),
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting(
        'activity.view.privacy',
        array('everyone', 'registered', 'network', 'member', 'owner')
      ),
    ));

    if (Engine_Api::_()->getApi('settings', 'core')->getSetting('network.enable', 1)) {
      $this->addElement('Select', 'network_privacy', array(
        'label' => 'Allow Network Selection as a post privacy option ?',
        'multiOptions' => array(
          2 => 'Yes, show all the available networks.',
          1 => 'Yes, show only those networks that have been joined by the user.',
          0 => 'No'
        ),
        'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.network.privacy', 0),
      ));
    }

    $href = Zend_Registry::get('Zend_View')->url(array('action' => 'manage-emoticons'));
    $description = sprintf(
      "%1sClick here%2s to add custom emoticons. ",
      "<a href='$href' target='_blank'>",
      "</a>"
    );
    // $this->addElement('MultiCheckbox', 'composer_options', array(
    //   'description' => 'Select options to be enabled in Status Post Box for activity feeds.',
    //   'label' => 'Status Post Box Options',
    //   'escape' => false,
    //   'multiOptions' => array(
    //     //'emoticons' => 'Emoticons / Smileys ( Enabling this will add an "Insert Emoticons" icon in the status post box and will allow users to insert attractive Emoticons / Smileys in their status updates. Symbols for smileys entered in status updates as well as comments of activity feeds will also be displayed as respective emoticons. ' . $description . ')',
    //     'userTags' => 'User Mentions ( Users will be able to mention / tag their friends in the status updates and comments. Tagged / Mentioned friends will receive a notification for this.)',
    //     'hashtags' => 'Hashtags ( Enabling this will allow users to post hashtags in the status post box. Users can also be able to view top trending hashtags. )'
    //   ),
    //   'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options'),
    // ));

    // Assign the composing values
    $composePartials = array('tagUseActivity' => 'Tag People (Members will be able to tag their friends to their status posts.)', 'smilesActivity' => 'Emoticons / Smileys ( Enabling this will add an "Insert Emoticons" icon in the status post box and will allow users to insert attractive Emoticons / Smileys in their status updates. Symbols for smileys entered in status updates as well as comments of activity feeds will also be displayed as respective emoticons. ' . $description . ')', 'locationactivity' => 'Check In (Members will be able to add their location to their posts.)', 'shedulepost' => 'Schedule Post (Members will be able to choose publish date and time for their posts.)', 'stickers' => 'Post Stickers', 'activityfeedgif' => "GIF", 'feelingssctivity' => "Feelings");

    if (Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('elivestreaming')) {
      $composePartials = array_merge(array('elivestreaming' => "elive_activity_settings_general"), $composePartials);
    }

    foreach (Zend_Registry::get('Engine_Manifest') as $data) {
      if (empty($data['composer'])) {
        continue;
      }
      $title = $data['package']['title'];
      foreach ($data['composer'] as $type => $config) {
        if ($type == 'link' || $type == 'tag')
          continue;

        if ($type == 'albumvideo') {
          if (!Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('album') && !Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('video'))
            continue;
        }

        $addType = 'Add ' . ucfirst(str_replace('ses', '', $type));
        if ($type == 'activitylink') {
          $addType = 'Add Link';
          $titleA = 'Core';
        } else if ($type == 'fileupload')
          $addType = 'Add File';
        else if ($type == 'buysell')
          $addType = 'Sell Something';
        if (!empty($titleA)) {
          $titleO = $title;
          $title = $titleA;
        }

        if($type == "intopenaiimage"){
          $addType = Zend_Registry::get('Zend_Translate')->_('Add Generate Image ');
        }
        if($type == "intopenaiword"){
          $addType = Zend_Registry::get('Zend_Translate')->_('Add Generate Post ');
        }

        $composePartials[$type] = $addType.'('.$title.')';
        if(!empty($titleO)){
          $title = $titleO;
          $titleO = $titleA = '';
        }
      }
    }

    $composerSettings = $settings->getSetting('activity.composeroptions', 1);
    $composerArray = array();
    foreach ($composerSettings as $composerSetting) {
      if (isset($composePartials[$composerSetting]))
        $composerArray[$composerSetting] = $composePartials[$composerSetting];
    }

    //get diff
    foreach ($composePartials as $key => $composePartial) {
      if (!array_key_exists($key, $composerArray)) {
        $composerArray[$key] = $composePartial;
      }
    }

    

    $this->addElement('MultiCheckbox', 'composeroptions', array(
      'label' => 'Status Box Attachments',
      'description' => 'Select the attachments which will be available in status box.',
      'multiOptions' => $composerArray,
      'escape' => false,
      'value' => $settings->getSetting('activity.composeroptions', 1),
    ));

    $this->addElement('Text', "giphyapi", array(
      'label' => 'GIPHY API Key',
      'description' => "Enter the GIPHY API key. <a target='_blank' href='https://developers.giphy.com/docs/api#quick-start-guide'>Click Here</a> to get the guidelines on how to create the key. If you already know, then simply <a target='_blank' href='https://developers.giphy.com'>get started</a>.",
      'value' => $settings->getSetting('activity.giphyapi', ''),
    ));
    $this->getElement('giphyapi')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));


    //ads code
    $this->addElement('Radio', 'adsenable', array(
      'label' => 'Enable Ad Campaigns',
      'description' => 'Do you want to enable the display of ads from SocialEngine Ad Campaign in activity feeds?',
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No'
      ),
      'onchange' => 'ads(this.value)',
      'value' => $settings->getSetting('activity.adsenable', 1),
    ));
    $this->getElement('adsenable')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));

    $campaigns = Engine_Api::_()->getDbtable('adcampaigns', 'core')->fetchAll();

    if (engine_count($campaigns) > 0) {
      // Element: adcampaign_id
      $this->addElement('Select', 'adcampaignid', array(
        'label' => 'Choose Ad Campaign',
        'description' => 'Choose an ad campaign from below.',
      ));
      foreach ($campaigns as $campaign) {
        $this->adcampaignid->addMultiOption($campaign->adcampaign_id, $campaign->name);
      }
      $this->adcampaignid->setValue($settings->getSetting('activity.adcampaignid', ''));
    } else {
      $description = "<div class='tip'><span>" . Zend_Registry::get('Zend_Translate')->_('You have not created any Ads Campaign yet, <a href="admin/ads/create" target="_blank">Create New Campaign</a>') . "</span></div>";
      //Add Element: Dummy
      $this->addElement('Dummy', 'noadcampaignid', array(
        'label' => 'Campaign Id',
        'description' => $description,
      ));
      $this->noadcampaignid->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));
    }

    $this->addElement('Radio', 'adsrepeatenable', array(
      'label' => 'Display Ads For Next Feed Count',
      'description' => 'Do you want to display the ads after each feed count cycle? For Example: (If you have choosen Yes and entered 2 in the below setting then after a cycle of 2 feeds, ads will show in activity feed.)',
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No'
      ),
      //'onchange' => 'repeatAds(this.value)',
      'value' => $settings->getSetting('activity.adsrepeatenable', 0),
    ));

    $this->addElement('Text', 'adsrepeattimes', array(
      'label' => "Show 'Ad' After Feed Count",
      'description' => 'Enter the number of feeds after which the ads will display.',
      'validators' => array(
        array('Int', true),
        array('GreaterThan', true, array(0)),
      ),
      'value' => $settings->getSetting('activity.adsrepeattimes', 15),
    ));
    //end ads code

    $this->addElement('Select', 'enablenactivityupdownvote', array(
      'label' => 'Enable Up/Down vote in Activity feed',
      'description' => 'Do you want to enable Up/Down vote in Activity feed? This feature is only for the posts in the activity feed.',
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No'
      ),
      'value' => $settings->getSetting('activity.enablenactivityupdownvote', 0),
    ));

    $commentsOptions = array('photos' => 'Attach Photos', 'videos' => 'Attach Videos', 'stickers' => 'Post Stickers', 'gif' => "Post GIF");
    $commentsOptions['emotions'] = "Post Emoticons";

    $this->addElement('MultiCheckbox', 'enableattachement', array(
      'label' => 'Choose Attachments',
      'description' => 'Choose from below the attachments which you want to enable in the comments and replies on your website.',
      'multiOptions' => $commentsOptions,
      'escape' => false,
      'value' => $settings->getSetting('activity.enableattachement', 1),
    ));
    $this->getElement('enableattachement')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));


    $this->addElement('Select', 'enabletooltip', array(
      'label' => 'Enable Tooltip',
      'description' => "Do you want to enable tooltip to display information of members and contents when someone mouse over on their names? This tooltip will be shown in activity feed only.",
      'multiOptions' => array(
        '1' => 'Yes',
        '0' => 'No'
      ),
      'value' => $settings->getSetting('activity.enabletooltip', 1),
    ));

    $this->addElement('Text', 'feedbgmax', array(
      'label' => 'Feed Background Images Count',
      'description' => 'Enter the number of background images to be shown in the status box to the users. (Maximum 12 background images are recommended.)',
      'validators' => array(
        array('Int', true),
        array('Between', false, array('min' => 2, 'max' => 50))
      ),
      'value' => $settings->getSetting('activity.feedbgmax', 12),
    ));

    $this->addElement('Select', 'commentreverseorder', array(
      'label' => 'Comment Sorting Order',
      'description' => 'Choose the default comments order in which you want to show the comments on your site. Users can change the comments order from the comments section.',
      'value' => 1,
      'multiOptions' => array(
        1 => 'Reverse chronological (Newest)',
        0 => 'Chronological (Oldest)'
      ),
    ));

    $this->addElement('Select', 'translate', array(
      'label' => 'Show Translate Option',
      'description' => 'Do you want to show Translate option in feeds? [If you choose Yes, then all the feeds which includes the texts will have a translate option to translate the text.]',
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No'
      ),
      'onclick' => 'showLanguage(this.value);',
      'value' => $settings->getSetting('activity.translate', 1),
    ));

    $localeObject = Zend_Registry::get('Locale');

    $languages = Zend_Locale::getTranslationList('language', $localeObject);
    $territories = Zend_Locale::getTranslationList('territory', $localeObject);

    $localeMultiOptions = array();
    foreach (array_keys(Zend_Locale::getLocaleList()) as $key) {
      $languageName = null;
      if (!empty($languages[$key])) {
        $languageName = $languages[$key];
      } else {
        $tmpLocale = new Zend_Locale($key);
        $region = $tmpLocale->getRegion();
        $language = $tmpLocale->getLanguage();
        if (!empty($languages[$language]) && !empty($territories[$region])) {
          $languageName =  $languages[$language] . ' (' . $territories[$region] . ')';
        }
      }
      if ($languageName) {
        if (strpos($key, '_') === false)
          $localeMultiOptions[$key] = $languageName . ' [' . $key . ']';
      }
    }

    $this->addElement('Select', 'language', array(
      'label' => 'Default Language',
      'description' => 'Choose the language in which you want to translate the feeds. Users will be able to change the language from the available language options.',
      'multiOptions' => $localeMultiOptions,
      'value' => $settings->getSetting('activity.language', 'en'),
    ));


    $count = 0;
    $this->addElement('Select', 'visiblesearchfilter', array(
      'label' => 'Filter Count',
      'description' => 'How many filters do you want to show in the activiy feed? "More" will display after this number and remaining filters will show on clicking it. This setting will work on the member home page only.',
      'multiOptions' => array(
        ++$count => $count,
        ++$count => $count,
        ++$count => $count,
        ++$count => $count,
        ++$count => $count,
        ++$count => $count,
        ++$count => $count,
        ++$count => $count,
        ++$count => $count,
        ++$count => $count,
      ),
      'value' => $settings->getSetting('activity.visiblesearchfilter', 4),
    ));

    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}
