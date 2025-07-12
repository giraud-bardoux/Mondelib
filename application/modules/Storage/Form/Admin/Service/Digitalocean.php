<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Wasabi.php 9747 2012-07-26 02:08:08Z john $
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

class Storage_Form_Admin_Service_Digitalocean extends Storage_Form_Admin_Service_Generic
{
  public function init()
  {
    // Element: accessKey
    $this->addElement('Text', 'accessKey', array(
      'label' => 'Digital Ocean Access Key',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
    ));

    // Element: secretKey
    $this->addElement('Text', 'secretKey', array(
      'label' => 'Digital Ocean Secret Key',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
    ));
    
    $this->addElement('Text', 'region', array(
      'label' => 'Digital Ocean Space Region',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
    ));
    
    $this->addElement('Text', 'spacename', array(
      'label' => 'Digital Ocean Space Name',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        'StringTrim',
      ),
    ));

    // Element: bucket
    $this->addElement('Text', 'bucket', array(
      'label' => 'Digital Ocean Bucket Name',
      'description' => 'Enter the bucket name.',
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
      'description' => 'Enter the domain here.',
      'filters' => array(
        'StringTrim',
      ),
    ));

    parent::init();
  }

  public function isValid($data)
  {
    if (!parent::isValid($data)) {
      return false;
    }

//     try {
//       $raw_credentials = array(
//         'credentials' => [
//           'key'    => $data['accessKey'],
//           'secret' => $data['secretKey'],
//         ],
//         'endpoint' => (_ENGINE_SSL ? 'https://' : 'http://') .$data['region'].'.digitaloceanspaces.com',
//         'region' => $data['region'],
//         'version' => 'latest',
//         //'use_path_style_endpoint' => true
//       );
//       $s3Client = S3Client::factory($raw_credentials);
//       $s3Client->createBucket(array(
//           'Bucket' => $data['bucket']
//       ));
//     } catch (S3Exception $e) {
//       $this->addError($e->getAwsErrorMessage());
//       return false;
//     } catch (AwsException $e) {
//       $this->addError($e->getAwsErrorMessage());
//       return false;
//     }

    return true;
  }
}
