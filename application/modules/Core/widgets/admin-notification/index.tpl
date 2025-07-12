<?php
/**
* SocialEngine
*
* @category   Application_Core
* @package    Core
* @copyright  Copyright 2006-2021 Webligo Developments
* @license    http://www.socialengine.com/license/
* @version    $Id: index.tpl 9905 2021-11-09 $
* @author     John
*/
?>
  <?php if( !empty($this->notifications) || $this->paginator->getTotalItemCount() > 0 ): ?>
  <ul class="admin_home_dashboard_messages">
      <?php // Hook-based notifications ?>
      <?php if( !empty($this->notifications) ): ?>
      <?php foreach( $this->notifications as $notification ):
        if( is_array($notification) ) {
          $class = ( !empty($notification['class']) ? $notification['class'] : 'notification-notice priority-info' );
          $message = $notification['message'];
        } else {
          $class = 'notification-notice priority-info';
          $message = $notification;
        }
        ?>
      <li class="<?php echo $class ?>">
        <?php echo $message ?>
      </li>
      <?php endforeach; ?>
      <?php endif; ?>
      <?php // Database-based notifications ?>
      <?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
      <?php foreach( $this->paginator as $notification ):
        $class = 'notification-' . ( $notification->priority >= 5 ? 'notice' : ( $notification->priority >= 4 ? 'warning' : 'error') )
          . ' priority-' . strtolower($notification->priorityName);
        $message = $notification->message;
        if( !empty($notification->plugin) ) {
          // Load and execute plugin
          try {
            $class = $notification->plugin;
            Engine_Loader::loadClass($class);
            if( !method_exists($class, '__toString') ) continue;
            $instance = new $class($notification);
            $message = $instance->__toString();
            if( method_exists($instance, 'getClass') ) {
              $class .= ' ' . $instance->getClass();
            }
          } catch( Exception $e ) {
            if( APPLICATION_ENV == 'development' ) {
              echo $e->getMessage();
            }
            continue;
          }
        }
        ?>
      <li class="<?php echo $class ?>">
        <?php echo $message ?>
      </li>
      <?php endforeach; ?>
      <?php endif; ?>
  </ul>
  <?php endif; ?>

