<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: MysqlServer.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Sanity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John Boehr <j@webligo.com>
 */
class Engine_Sanity_Test_MysqlServer extends Engine_Sanity_Test_Abstract
{
  protected $_messageTemplates = array(
    'badAdapter' => 'Unable to check. No database adapter was provided.',
    'tooLowVersion' => 'Requires at least version %min_version%',
    'tooHighVersion' => 'Requires no greater than %max_version%',
    
    'tooLowMariaDBVersion' => 'Requires at least MariaDB version %min_mariadb_version%',
    'tooHighMariaDBVersion' => 'Requires no greater MariaDB than %max_mariadb_version%',
  );

  protected $_messageVariables = array(
    'min_version' => '_minVersion',
    'max_version' => '_maxVersion',
    'actual_version' => '_actualVersion',
    
    'min_mariadb_version' => '_minMariaDBVersion',
    'max_mariadb_version' => '_maxMariaDBVersion',
    'actual_mariadb_version' => '_actualMariaDBVersion',
  );
  
  protected $_adapter;

  protected $_minVersion;

  protected $_maxVersion;

  protected $_actualVersion;
  
  protected $_minMariaDBVersion;

  protected $_maxMariaDBVersion;

  protected $_actualMariaDBVersion;

  public function setMinVersion($minVersion)
  {
    $this->_minVersion = $minVersion;
    return $this;
  }

  public function getMinVersion()
  {
    return $this->_minVersion;
  }

  public function setMaxVersion($maxVersion)
  {
    $this->_maxVersion = $maxVersion;
    return $this;
  }

  public function getMaxVersion()
  {
    return $this->_maxVersion;
  }
  
  
  public function setMinMariaDBVersion($minMariaDBVersion)
  {
    $this->_minMariaDBVersion = $minMariaDBVersion;
    return $this;
  }

  public function getMinMariaDBVersion()
  {
    return $this->_minMariaDBVersion;
  }

  public function setMaxMariaDBVersion($maxMariaDBVersion)
  {
    $this->_maxMariaDBVersion = $maxMariaDBVersion;
    return $this;
  }

  public function getMaxMariaDBVersion()
  {
    return $this->_maxMariaDBVersion;
  }

  public function setAdapter($adapter)
  {
    if( $adapter instanceof Engine_Db_Adapter_Mysql ||
        $adapter instanceof Zend_Db_Adapter_Mysqli ||
        $adapter instanceof Zend_Db_Adapter_Pdo_Mysql ) {
      $this->_adapter = $adapter;
    }
    return $this;
  }

  public function getAdapter()
  {
    if( null === $this->_adapter ) {
      if( null !== ($defaultAdapter = Engine_Sanity::getDefaultDbAdapter()) ) {
        $this->_adapter = $defaultAdapter;
      }
    }
    return $this->_adapter;
  }

  public function execute()
  {
    $adapter = $this->getAdapter();
    $minVersion = $this->getMinVersion();
    $maxVersion = $this->getMaxVersion();
    
    $minMariaDBVersion = $this->getMinMariaDBVersion();
    $maxMariaDBVersion = $this->getMaxMariaDBVersion();

    if( !$adapter ) {
      return $this->_error('badAdapter');
    }

    try {
      $this->_actualVersion = $actualVersion = $adapter->getClientVersion();
    } catch( Exception $e ) {
      return $this->_error('badAdapter');
    }

    if( !$actualVersion ) {
      return $this->_error('badAdapter');
    }

    if( !empty($minVersion) && version_compare($actualVersion, $minVersion, '<') ) {
      $this->_error('tooLowVersion');
    }

    if( !empty($maxVersion) && version_compare($actualVersion, $maxVersion, '>') ) {
      $this->_error('tooHighVersion');
    }
    
    if(strpos($adapter->isMariaDb(), 'MariaDB') !== false) {
      try {
        $this->_actualMariaDBVersion = $actualMariaDBVersion = $adapter->getServerVersion();
      } catch( Exception $e ) {
        return; //$this->_error('badAdapter');
      }

      if( !$actualMariaDBVersion ) {
        return $this->_error('badAdapter');
      }

      if( !empty($minMariaDBVersion) && version_compare($actualMariaDBVersion, $minMariaDBVersion, '<') ) {
        $this->_error('tooLowMariaDBVersion');
      }

      if( !empty($maxMariaDBVersion) && version_compare($actualMariaDBVersion, $maxMariaDBVersion, '>') ) {
        $this->_error('tooHighMariaDBVersion');
      }
    }
  }
}
