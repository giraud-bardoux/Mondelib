<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Global.php 10213 2014-05-13 17:37:19Z andres $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Video_Form_Admin_Global extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Global Settings')
      ->setDescription('These settings affect all members in your community.');

    $this->addElement('Text', 'video_ffmpeg_path', array(
      'label' => 'Path to FFMPEG',
      'description' => 'Please enter the full path to your FFMPEG installation. (Environment variables are not present)',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('video.ffmpeg.path', ''),
    ));

    $this->addElement('Textarea', 'video_iframely_disallow', array(
      'label' => 'Disallow these Sources',
      'description' => 'Enter the domains of the sites (separated by commas) that you do not want to allow for Video'
        . ' Source. Example: example1.com, example2.com. Note: We\'ve integrated Iframely API with this module. By '
        . 'default URLs that return a \'player\' are allowed, such as music based websites like Soundcloud. You can '
        . 'use this setting to control which sites should not be allowed in this section.',
      'filters' => array(
        'StringTrim',
      ),
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('video.iframely.disallow', ''),
    ));
    $this->video_iframely_disallow->getDecorator('Description')->setOption('escape', false);

    $description = 'Enter YouTube API Key for creating videos on your website used from YouTube as a source. For this, create an Application Key through the <a href="https://console.developers.google.com" target="_blank">Google Developers Console</a> page. <br>For more information, see: <a href="https://developers.google.com/youtube/v3/getting-started" target="_blank">YouTube Data API</a>.';

    $this->addElement('Text', 'video_youtube_apikey', array(
       'label' => 'Youtube API Key',
       'description' => $description,
       'filters' => array(
           'StringTrim',
       ),
       'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('video.youtube.apikey'),
    ));
    if (_ENGINE_ADMIN_NEUTER) {
      $this->video_youtube_apikey->setValue('*********');
    } 
    $this->video_youtube_apikey->getDecorator('Description')->setOption('escape', false);

    $this->addElement('Text', 'video_jobs', array(
      'label' => 'Encoding Jobs',
      'description' => 'How many jobs do you want to allow to run at the same time?',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('video.jobs', 2),
    ));

    $this->addElement('Text', 'video_page', array(
      'label' => 'Videos Per Page',
      'description' => 'How many videos will be shown per page? (Enter a number between 1 and 999)',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('video.page', 12),
    ));

    $this->addElement('Select', 'video_allow_unauthorized', array(
      'label' => 'Make unauthorized videos searchable?',
      'description' => 'Do you want to make a unauthorized videos searchable? (If set to no, videos that are not authorized for the current user will not be displayed in the video search results and widgets.)',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('video.allow.unauthorized', 0),
      'multiOptions' => array(
        '1' => 'Yes',
        '0' => 'No',
      ),
    ));

    $this->addElement('Select', 'video_embeds', array(
      'label' => 'Allow Embedding of Videos?',
      'description' => 'Enabling this option will give members the ability to '
        . 'embed videos on this site in other pages using an iframe (like YouTube).',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('video.embeds', 1),
      'multiOptions' => array(
        '1' => 'Yes, allow embedding of videos.',
        '0' => 'No, do not allow embedding of videos.',
      ),
    ));

    $this->addElement('Select', 'video_enable_rating', array(
      'label' => 'Enable Rating',
      'description' => 'Do you want to enable rating for the videos on your website?',
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('video.enable.rating', 1),
      'multiOptions' => array(
        '1' => 'Yes',
        '0' => 'No',
      ),
      'onchange' => 'showHideRatingSetting(this.value)', 
    ));
    
    $this->addElement('Text', 'video_ratingicon', array(
      'label' => 'Font Icon for Rating',
      'description' => 'Enter font icon for rating. You can choose font icon from <a href="https://fontawesome.com/v5/search?m=free&s=solid" target="_blank"> here</a>. Example: fas fa-star',
      'requried' => true,
      'allowEmpty' => false,
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('video.ratingicon', 'fas fa-star'),
    ));
    $this->video_ratingicon->addDecorator('Description', array('placement' => Zend_Form_Decorator_Abstract::PREPEND, 'escape' => false));

    $this->addElement('Select', 'video_enable_location', array(
      'label' => 'Enable Location',
      'description' => 'Do you want to enable location for the videos on your website?',
      'multiOptions' => array(
        '1' => 'Yes',
        '0' => 'No',
      ),
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('video.enable.location', 0),
    ));

    $this->addElement('Select', 'video_enable_videoondemad', array(
      'label' => 'Enable Video on Demand',
      'description' => 'Do you want to enable video on demand on your website? If you choose yes then it will create 2 more video which size is 360*360 and 720*720. In this case you need to increase your server  storage.',
      'multiOptions' => array(
        '1' => 'Yes',
        '0' => 'No',
      ),
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('video_enable_videoondemad', 0),
    ));

    // Add submit button
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}
