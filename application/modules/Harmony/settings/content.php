<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: content.php 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

$files = Engine_Api::_()->getDbTable('files', 'core')->getFilesAssoc();

return array(
		array(
      'title' => 'Harmony Landing Page Banner',
      'description' => 'Displays the banner on the Landing Page. Requires that you have at least one banner. Edit this widget to configure various settings.',
      'category' => 'Harmony',
      'type' => 'widget',
      'name' => 'harmony.landing-page-banner',
      "autoEdit" => true,
      'adminForm' => array(
        'elements' => array(
          array(
            'Text',
            'height',
            array(
                'label' => 'Enter the height of this Banner (in pixels).',
                'value' => 450,
                'validators' => array(
                    array('Int', true),
                    array('GreaterThan', true, array(0)),
                )
            ),
          ),
          array(
            'Text',
            'title',
            array(
              'label' => 'Enter caption to show in the banner.',
            ),
          ),
          array(
            'Text',
            'description',
            array(
              'label' => 'Enter description to show in the banner.',
            ),
          ),
          array(
            'Text',
            'btntext',
            array(
              'label' => 'Enter CTA Button Text',
            ),
          ),
          array(
            'Text',
            'btntextlink',
            array(
              'label' => 'Enter CTA Button Link',
            ),
          ),
          array(
            'Select',
            'photo1',
            array(
              'label' => 'Choose the Banner Image 1 to be shown in this widget. Note: You can add a new image from the "File & Media Manager" section.',
              'multiOptions' => $files,
              'value' => '',
            )
          ),
          array(
            'Select',
            'photo2',
            array(
              'label' => 'Choose the Banner Image 2 to be shown in this widget. Note: You can add a new image from the "File & Media Manager" section.',
              'multiOptions' => $files,
              'value' => '',
            )
          ),
          array(
            'Select',
            'photo3',
            array(
              'label' => 'Choose the Banner Image 3 to be shown in this widget. Note: You can add a new image from the "File & Media Manager" section.',
              'multiOptions' => $files,
              'value' => '',
            )
          ),
        )
      ),
    ),
		array(
      'title' => 'Harmony Landing Page Features',
      'description' => 'This widget displays the feature blocks on the Landing Page. Edit this widget to configure features.',
      'category' => 'Harmony',
      'type' => 'widget',
      'name' => 'harmony.landing-page-features',
      'adminForm' => 'Harmony_Form_Admin_Widget_LandingPageFeatures',
      "autoEdit" => true,
    ),
		array(
      'title' => 'Harmony Landing Page Services',
      'description' => 'This widget displays the services on the Landing Page. Edit this widget to configure services.',
      'category' => 'Harmony',
      'type' => 'widget',
      'name' => 'harmony.landing-page-service',
      'adminForm' => 'Harmony_Form_Admin_Widget_LandingPageService',
       "autoEdit" => true,     
    ),
		array(
      'title' => 'Harmony Landing Page Counter Section',
      'description' => 'This widget displays the counter section on the Landing Page. Edit this widget to configure the counters.',
      'category' => 'Harmony',
      'type' => 'widget',
      'name' => 'harmony.landing-page-counter-section',
      'adminForm' => 'Harmony_Form_Admin_Widget_LandingPageCounter',
       "autoEdit" => true,     
    ),    
		array(
      'title' => 'Harmony Landing Page CTA Section',
      'description' => 'This widget displays a CTA section on the landing Page. Edit this widget to configure various settings.',
      'category' => 'Harmony',
      'type' => 'widget',
      'name' => 'harmony.landing-page-cta-section',
      "autoEdit" => true,      
      'adminForm' => array(
        'elements' => array(
          array(
            'Text',
            'title',
            array(
              'label' => 'Enter caption of this widget.',
            ),
          ),
          array(
            'Text',
            'description',
            array(
              'label' => 'Enter description of this widget.',
            ),
          ),
          array(
            'Text',
            'btntext',
            array(
              'label' => 'Enter CTA button text',
            ),
          ),
          array(
            'Text',
            'btntextlink',
            array(
              'label' => 'Enter CTA button link',
            ),
          ),        
        )
      ),
    ),
		array(
      'title' => 'Harmony Landing Page How Our Community Works?',
      'description' => 'This widget displays a "How Our Community Works?" section on the landing page. Edit this widget to configure sections.',
      'category' => 'Harmony',
      'type' => 'widget',
      'name' => 'harmony.landing-page-why-choose',
      'adminForm' => 'Harmony_Form_Admin_Widget_LandingPageWhyChoose',
      "autoEdit" => true,      
    ),
		array(
      'title' => 'Harmony Landing Page Mobile Apps Section',
      'description' => 'This widget displays the mobile apps section the on Landing Page. Edit this widget to configure apps section.',
      'category' => 'Harmony',
      'type' => 'widget',
      'name' => 'harmony.landing-page-app-section',
      "autoEdit" => true,
      'adminForm' => array(
      'elements' => array(
	   array(
            'Text',
            'title',
            array(
              'label' => 'Enter title of this widget.',
            ),
          ),
        array(
          'Text',
          'description',
          array(
            'label' => 'Enter description of this widget.',
          ),
        ),
        array(
          'Text',
          'androidapplink',
          array(
            'label' => 'Enter play store link of Android App.',
          ),
        ),
        array(
          'Text',
          'iosapplink',
          array(
            'label' => 'Enter app store link of iOS App.',
          ),
        ),
        array(
          'Select',
          'apprightimage',
          array(
            'label' => 'Choose the image to be shown in this widget. Note: You can add a new image from the "File & Media Manager" section.',
            'multiOptions' => $files,
            'value' => '',
          )
        ),
      )
    ),
  ),
  array(
    'title' => 'Harmony Theme - Footer',
    'description' => 'This widget displays the Footer of the website.',
    'category' => 'Harmony',
    'type' => 'widget',
    'name' => 'harmony.footer',
    "autoEdit" => false,      
  ),  
  array(
    'title' => 'Harmony Theme - Header',
    'description' => 'This widget displays the header of the website.',
    'category' => 'Harmony',
    'type' => 'widget',
    'name' => 'harmony.header',
    "autoEdit" => false,      
  ),  
);
