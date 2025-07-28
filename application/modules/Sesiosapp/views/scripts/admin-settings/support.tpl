<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: support.tpl 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>
<?php include APPLICATION_PATH .  '/application/modules/Sesiosapp/views/scripts/dismiss_message.tpl';?>
<h2>
  <?php echo $this->translate("Native iOS Mobile App") ?>
</h2>
<?php if( engine_count($this->navigation) ): ?>
  <div class='sesiosapp-admin-navgation'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<div class="sesiosapp_support_links">
  <a href="https://help.socialnetworking.solutions" target="_blank">
    <img src="application/modules/Sesiosapp/externals/images/support/faq.png" />
    <h4>FAQs</h4>
    <p>Get answers to all your random queries about this plugin from our Help Center. Click to read the tutorials and FAQs.</p>
  </a>
  <a href="https://socialnetworking.solutions/support/" target="_blank">
    <img src="application/modules/Sesiosapp/externals/images/support/support.png" />
    <h4>Support</h4>
    <p>If you face any issues with the plugin or you do not find answer to your query in our Help Center, then file a support ticket from here.</p>
  </a>
  <a href="https://socialenginesolutions.us16.list-manage.com/subscribe?u=70b1c9baba63dcf30fec2c66d&id=3c72ee2249" target="_blank">
    <img src="application/modules/Sesiosapp/externals/images/support/info.png" />
    <h4>Subscribe Newsletter</h4>
    <p>Do you want know more about "Questions & Answers Plugin" and it's features? If Yes, then click here to subscribe to our newsletter and stay updated.</p>
  </a>
  <a href="http://www.socialenginesolutions.com/contact-us/" target="_blank">
    <img src="application/modules/Sesiosapp/externals/images/support/contact.png" />
    <h4>Contact Us</h4>
    <p>Do you have a feature request, feedback or anything to discuss with our expert professionals directly, just Contact us freely.</p>
  </a>
  <a href="http://www.socialenginesolutions.com/blog/" target="_blank">
    <img src="application/modules/Sesiosapp/externals/images/support/update.png" />
    <h4>Blog Updates</h4>
    <p>We regularly post blogs about new releases & announcements. To read the new updates, check out our Blog Posts.</p>
  </a>
  <a href="javascript:;">
    <img src="application/modules/Sesiosapp/externals/images/support/review.png" style="filter:invert(0);" />
    <h4>Write a Review</h4>
    <p>Do you like this theme and wish to leave your feedback or review? You just have to "Write a Review" for it.</p>
  </a>
</div>
