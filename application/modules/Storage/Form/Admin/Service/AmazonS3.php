<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: S3.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

// Include the AWS SDK
require_once 'application/libraries/Aws/aws-autoloader.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;

class Storage_Form_Admin_Service_AmazonS3 extends Storage_Form_Admin_Service_Generic
{
  public function init()
  {
    // Element: accessKey
    $this->addElement('Text', 'accessKey', array(
      'label' => 'Api Access Key',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
    ));

    // Element: secretKey
    $this->addElement('Text', 'secretKey', array(
      'label' => 'Api Secret Key',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
    ));
    
    $this->addElement('Text', 'region', array(
      'label' => 'Region',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
    ));

    // Element: bucket
    $this->addElement('Text', 'bucket', array(
      'label' => 'Bucket Name',
//       'description' => 'If the bucket does not exist, we will attempt to ' .
//           'create it. Please note the following restrictions on bucket names:<br />' .
//           '-Must start and end with a number or letter<br />' .
//           '-Must only contain lowercase letters, numbers, and dashes [a-z0-9-]<br />' .
//           '-Must be between 3 and 255 characters long',
      'required' => true,
      'allowEmpty' => false,
      'validators' => array(
        array('StringLength', true, array(3, 255)),
        array('Regex', true, array('/^[a-z0-9][a-z0-9-]+[a-z0-9]$/')),
      ),
    ));
    $this->getElement('bucket')->getDecorator('description')->setOption('escape', false);
    
    // Element: baseUrl
    $this->addElement('Text', 'base_url', array(
      'label' => 'Base URL',
      'description' => 'It\'s used to view files.',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
    ));
    
    // Element: baseUrl
    $this->addElement('Text', 'endpoint', array(
      'label' => 'Endpoint Url',
      //'required' => true,
      //'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
    ));

    //Element: path
    $this->addElement('Text', 'path', array(
      'label' => 'Path Prefix',
      'description' => 'This is prepended to the file path. Defaults to "public".',
      'filters' => array(
        'StringTrim',
      ),
    ));

    // Element: baseUrl
    $this->addElement('Text', 'baseUrl', array(
      'label' => 'CloudFront Domain',
      'description' => 'If you are using Amazon CloudFront for this bucket, ' .
          'enter the domain here.',
      'filters' => array(
        'StringTrim',
      ),
    ));

    parent::init();
  }

  public function isValid($data) {
  
		$valid = parent::isValid($data);
		$secretKey = $data['secretKey'];
		$serviceIdentity = Zend_Controller_Front::getInstance()->getRequest()->getParam('service_id', 0);
		if($serviceIdentity && empty($data['secretKey'])) {
			$serviceTable = Engine_Api::_()->getDbtable('services', 'storage');
			$service = $serviceTable->find($serviceIdentity)->current();
			if(!empty($service->config)) {
				$config = Zend_Json::decode($service->config);
				$secretKey = $config['secretKey'];
			}
		}
		// Custom valid
		if( $valid ) {
			// Check auth
			try {
				$testService = new Zend_Service_Amazon_S3($data['accessKey'], $secretKey, $data['region']);
				$buckets = $testService->getBuckets();
				if( $buckets === false ) {
					$this->addError('Please double check your S3 Credentials.');
					return false;
				}
			} catch( Exception $e ) {
				$this->addError('Please double check your access keys.');
				return false;
			}
			// Check bucket
			try {
				if( !in_array($data['bucket'], $buckets) ) {
					if( !$testService->createBucket($data['bucket'], $data['region']) ) {
							throw new Exception('Could not create or find bucket');
					}
				}
			} catch( Exception $e ) {
					$this->addError('Bucket name is already taken and could not be created.');
					return false;
			}
		}
		return $valid;
  }
}
