<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: deleted-comment.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>
<?php
if (!empty($this->commentCount)) {
  $commentcount = $this->translate(array('%s comment', '%s comments', $this->commentCount), $this->locale()->toNumber($this->commentCount));
?>
<?php echo $commentcount; ?>
<?php } die; ?>