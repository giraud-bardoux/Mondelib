<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Wasabi.php 10235 2014-05-23 19:00:11Z lucas $
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

class Storage_Service_Wasabi extends Storage_Service_Abstract
{
  // General

  protected $_type = 's3';

  protected $_path;

  protected $_baseUrl;

  /**
   * @var Zend_Service_Amazon_S3
   */
  protected $_internalService;

  protected $_bucket;
  protected $_region;

  protected $_streamWrapperName;

  public function __construct(array $config)
  {
    if( empty($config['bucket']) ) {
      throw new Storage_Service_Exception('No bucket specified');
    }
    $this->_bucket = $config['bucket'];
    $this->_region = $config['region'];
    try {
      $credentials = array(
        'credentials' => [
            'key'    => $config['accessKey'],
            'secret' => $config['secretKey'],
        ],
        'endpoint' => (_ENGINE_SSL ? 'https://' : 'http://') . 's3.'.$config['region'].'.wasabisys.com', // please refer to service end points for buckets in different regions
        'region' => $config['region'], // please refer to service end points for buckets in different regions
        'version' => 'latest',
        'use_path_style_endpoint' => true
      );
      $this->_internalService = S3Client::factory($credentials);
    } catch (S3Exception $e) {
      throw new Storage_Service_Exception($e->getAwsErrorMessage());
    } catch (AwsException $e) {
      throw new Storage_Service_Exception($e->getAwsErrorMessage());
    }

    if( !empty($config['path']) ) {
      $this->_path = $config['path'];
    } else {
      $this->_path = 'public';
    }

    if( !empty($config['baseUrl']) ) {
      $this->_baseUrl = $config['baseUrl'];
      unset($config['baseUrl']);
      // Add http:// if no protocol
      if( false === strpos($this->_baseUrl, '://') ) {
        $this->_baseUrl = (_ENGINE_SSL ? 'https://' : 'http://') . $this->_baseUrl;
      }
    }

    // Should we register the stream wrapper?
    $this->_streamWrapperName = 's3' . (int) @$config['service_id'];
    $this->_internalService->registerStreamWrapper($this->_streamWrapperName);

    parent::__construct($config);
  }

  public function getType()
  {
    return $this->_type;
  }

  /**
   * Returns a url that allows for external access to the file. May point to some
   * adapter which then retrieves the file and outputs it, if desirable
   *
   * @param Storage_Model_DbRow_File The file for operation
   * @return string
   */
  public function map(Storage_Model_File $model)
  {
    // Remove bucket from storage path? (b/c)
    $path = $model->storage_path;
    if( substr($path, 0, strlen($this->_bucket) + 1) == $this->_bucket . '/' ) {
      $path = ltrim(substr($path, strlen($this->_bucket) + 1), '/');
    }

    // Make url
    if( !$this->_baseUrl ) {
      // Map to S3 bucket directly
      return (_ENGINE_SSL ? 'https://' : 'http://') . 's3.'.$this->_region.'.wasabisys.com/'.$this->_bucket .'/' . $path;
    } else {
      // Map to baseUrl (cloudfront)
      return rtrim($this->_baseUrl, '/') . '/' . $path;
    }
  }

  /**
   * Stores a local file in the storage service
   *
   * @param Zend_Form_Element_File|array|string $file Temporary local file to store
   * @param array $params Contains iden
   * @return string Storage type specific path (internal use only)
   */
  public function store(Storage_Model_File $model, $file)
  {
    $path = $this->getScheme()->generate($model->toArray());
    try {
      $this->_internalService->putObject([
        'Bucket'       => $this->_bucket,
        'Key'          => $path,
        'ACL'          => 'public-read',
        'SourceFile'   => $file,
      ]);
    } catch (S3Exception $e) {
      throw new Storage_Service_Exception($e->getAwsErrorMessage());
    } catch (AwsException $e) {
      throw new Storage_Service_Exception($e->getAwsErrorMessage());
    }

    return $path;
  }

  /**
   * Returns the content of the file
   *
   * @param Storage_Model_DbRow_File $model The file for operation
   * @param array $params
   */
  public function read(Storage_Model_File $model)
  {
    try {
      $response = $this->_internalService->getObject([
        'Bucket'  => $this->_bucket,
        'Key'     => $model->storage_path,
      ]);
    } catch (S3Exception $e) {
      throw new Storage_Service_Exception($e->getAwsErrorMessage());
    } catch (AwsException $e) {
      throw new Storage_Service_Exception($e->getAwsErrorMessage());
    }

    return $response;
  }

  /**
   * Creates a new file from data rather than an existing file
   *
   * @param Storage_Model_DbRow_File $model The file for operation
   * @param string $data
   */
  public function write(Storage_Model_File $model, $data)
  {
    $path = $this->getScheme()->generate($model->toArray());
    try {
      $this->_internalService->putObject([
        'Bucket'       => $this->_bucket,
        'Key'          => $path,
        //'ACL'          => 'public-read',
        'SourceFile'   => $data,
      ]);
    } catch (S3Exception $e) {
      throw new Storage_Service_Exception($e->getAwsErrorMessage());
    } catch (AwsException $e) {
      throw new Storage_Service_Exception($e->getAwsErrorMessage());
    }

    return $path;
  }

  /**
   * Removes the file
   *
   * @param Storage_Model_DbRow_File $model The file for operation
   */
  public function remove(Storage_Model_File $model)
  {
    if (empty($model->storage_path)) {
      return;
    }

    try {
      $this->_internalService->deleteObject([
        'Bucket'  => $this->_bucket,
        'Key'     => $model->storage_path,
      ]);
    } catch (S3Exception $e) {
      throw new Storage_Service_Exception($e->getAwsErrorMessage());
    } catch (AwsException $e) {
      throw new Storage_Service_Exception($e->getAwsErrorMessage());
    }
  }

  /**
   * Creates a local temporary local copy of the file
   *
   * @param Storage_Model_DbRow_File $model The file for operation
   */
  public function temporary(Storage_Model_File $model)
  {
    try {
      $rfh = fopen($this->_streamWrapperName . '://' . $model->storage_path, 'r');
    } catch( Exception $e ) {
      throw $e;
    }

    $tmp_file = APPLICATION_PATH . '/public/temporary/' . basename($model['storage_path']);
    $fp = fopen($tmp_file, "w");
    stream_copy_to_stream($rfh, $fp);
    fclose($fp);
    @chmod($tmp_file, 0777);
    return $tmp_file;
  }

  public function removeFile($path)
  {
    if (empty($path)) {
      return;
    }

    try {
      $this->_internalService->deleteObject([
        'Bucket'  => $this->_bucket,
        'Key'     => $path,
      ]);
    } catch (S3Exception $e) {
      throw new Storage_Service_Exception($e->getAwsErrorMessage());
    } catch (AwsException $e) {
      throw new Storage_Service_Exception($e->getAwsErrorMessage());
    }
  }
}
