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

class Storage_Form_Admin_Service_S3 extends Storage_Form_Admin_Service_Generic
{
  public function init()
  {
    // Element: accessKey
    $this->addElement('Text', 'accessKey', array(
      'label' => 'Access Key',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
    ));

    // Element: secretKey
    $this->addElement('Text', 'secretKey', array(
      'label' => 'Secret Key',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
    ));

    // Element: region
    $this->addElement('Select', 'region', array(
      'label' => 'Region',
      'required' => true,
      'allowEmpty' => false,
      'multiOptions' => array(
        'us-east-1' => 'US East (N. Virginia)',
        'us-east-2' => 'US East (Ohio)',
        'us-west-1' => 'US West (Northern California)',
        'us-west-2' => 'US West (Oregon)',
        'ap-south-1' => 'Asia Pacific (Mumbai)',
        'ap-northeast-3' => 'Asia Pacific (Osaka)',
        'ap-northeast-2' => 'Asia Pacific (Seoul)',
        'ap-southeast-1' => 'Asia Pacific (Singapore)',
        'ap-southeast-2' => 'Asia Pacific (Sydney)',
        'ap-northeast-1' => 'Asia Pacific (Tokyo)',
        'ca-central-1' => 'Canada (Central)',
        'eu-central-1' => 'Europe (Frankfurt)',
        'eu-west-1' => 'Europe (Ireland)',
        'eu-west-2' => 'Europe (London)',
        'eu-west-3' => 'Europe (Paris)',
        'eu-north-1' => 'Europe (Stockholm)',
        'sa-east-1' => 'South America (Sao Paulo)',
      ),
    ));

    // Element: bucket
    $this->addElement('Text', 'bucket', array(
      'label' => 'Bucket',
      'description' => 'If the bucket does not exist, we will attempt to ' .
          'create it. Please note the following restrictions on bucket names:<br />' .
          '-Must start and end with a number or letter<br />' .
          '-Must only contain lowercase letters, numbers, and dashes [a-z0-9-]<br />' .
          '-Must be between 3 and 255 characters long',
      'required' => true,
      'allowEmpty' => false,
      'validators' => array(
        array('StringLength', true, array(3, 255)),
        array('Regex', true, array('/^[a-z0-9][a-z0-9-]+[a-z0-9]$/')),
      ),
    ));
    $this->getElement('bucket')->getDecorator('description')->setOption('escape', false);

    // Element: path
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
