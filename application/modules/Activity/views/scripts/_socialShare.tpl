<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _socialShare.tpl 2024-10-28 00:00:00Z 
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
$settings = Engine_Api::_()->getApi('settings', 'core');
$item = $this->item;
$URL = ((!empty($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == 'on') ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $item->getHref();
$urlencode = urlencode($URL);
?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'externals/jQuery/owlcarousel/jquery.js'); ?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'externals/jQuery/owlcarousel/owl.carousel.js'); ?>

<div class="copy_link_container">
  <div class="copy_link_field">
    <input disabled="disabled" type="type" value="<?php echo $URL; ?>" id="copy_item_url" />
    <button type="button" class="copy_link copy_itemurl" data-bs-toggle="tooltip" data-bs-placement="top"
      data-bs-title="<?php echo $this->translate("Copy"); ?>">
      <i class="far fa-copy"></i>
    </button>
  </div>
  <?php if ($settings->getSetting('core.socialshare.enable', 1)) { ?>
    <?php $socialshare_allow = (array) json_decode($settings->getSetting('core.socialashare.allow', '["facebook","twitter","pinterest","linkedin","gmail","tumblr","flipboard","skype","vk","whatsapp"]')); ?>
    <div class="social_share_icons">
      <?php if (engine_in_array('facebook', $socialshare_allow)) { ?>
        <div class="social_share_icon">
          <a class="facebook" target="_blank" href="<?php echo 'http://www.facebook.com/sharer/sharer.php?u=' . $URL; ?>">
            <i class="fab fa-facebook-f"></i>
          </a>
        </div>
      <?php } ?>
      <?php if (engine_in_array('twitter', $socialshare_allow)) { ?>
        <div class="social_share_icon">
          <a class="twitter" target="_blank"
            href="<?php echo 'https://twitter.com/intent/tweet?url=' . $URL . '&text=%0a'; ?>">
            <i><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <path
                  d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z" />
              </svg></i>
          </a>
        </div>
      <?php } ?>
      <?php if (engine_in_array('pinterest', $socialshare_allow)) { ?>
        <div class="social_share_icon">
          <a class="pinterest" target="_blank" href="<?php echo 'http://pinterest.com/pin/create/button/?url=' . $URL; ?>">
            <i class="fab fa-pinterest-p"></i>
          </a>
        </div>
      <?php } ?>
      <?php if (engine_in_array('linkedin', $socialshare_allow)) { ?>
        <div class="social_share_icon">
          <a class="linkedin" target="_blank"
            href="<?php echo 'https://www.linkedin.com/shareArticle?mini=true&url=' . $URL; ?>">
            <i class="fab fa-linkedin-in"></i>
          </a>
        </div>
      <?php } ?>
      <?php if (engine_in_array('whatsapp', $socialshare_allow)) { ?>
        <div class="social_share_icon">
          <a class="whatsapp" target="_blank" href="<?php echo 'https://api.whatsapp.com/send?text=' . $URL; ?>">
            <i class="fab fa-whatsapp"></i>
          </a>
        </div>
      <?php } ?>
      <?php if (engine_in_array('gmail', $socialshare_allow)) { ?>
        <div class="social_share_icon">
          <a class="gmail" target="_blank"
            href="<?php echo 'https://mail.google.com/mail/u/0/?view=cm&fs=1&to&body=' . $URL . '&ui=2&tf=1'; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="23px" height="23px">
              <image width="23px" height="23px"
                xlink:href="data:img/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAYCAMAAACsjQ8GAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAABEVBMVEUAAAD/AADmGxnlHBnlHBnlHBnjHBf/uwD/uwD/vAD/vAD/vADkGxnlHBnmHBn/uwD/vADlGxn1NCj/QCv/ch3/vADlHBn/QTH/QTH/PjL/QzH/YCX/vAD/QTH/QDD/QTH/QTH/QTKIR3P/QTGYtB/eICH/YiT2vAMdeNvvPjbxWCsgrEIOge7/QjH/QTH/QTAVokwAh/j/QTH/QTH/QTH/QjAAqE3/RDP/QTH/QTH/RDX/QED/VSsAhfcAqUsAhfcAqUsAhPYAhfcAqEnlHBn/vAD4Nyv/QTH/YyTkHBr+vAAAhfdGZbPVIyjuugVPrzQAqUsbeN2yNErMuA8iq0EEg/N6TYCZtR4GqUlVsDL////l4lB2AAAARHRSTlMAAXDe+bgtLbn53W9x9mVl9t+tDKbe/tkvKdb5/vdvXvWk8NTw+v36/c/K/UmI/plKRk7t8VVGHsXLIgQG/v7W1TjVOMg2vQwAAAABYktHRFoDu6WiAAAAB3RJTUUH6AQTEi8fAVkveAAAAPtJREFUKM+NzelWwjAUBOBxXxBRiwsKSt3BBREVhKKorRGDGsX1/V/EtKm3lNYc5ldy5jt3MDQ8Mjo2jphMTE5NJ4AZWyY5G+1Tc45MAvO2lwUjXBtpx8silhS4X17p7TOrawpkYfuA5daDfiPPHhRwAsCY6c8YpvwQ2GwTYGoms8UIPG6Dd54IeDPyPIHnFwHOX98IyBnTf7ig+y5cwD8+2wQoEnx9CwU47+xEwO7ejxAE+H6hHxQPRC/gh0fHob50IsKgjNNKUJ+d4yICUK399Zd1xAE0/JmShXgAb0aex79AzrjnNQCWBT2gDA6aenCFaz1o4eZWB1p3v0I8j3jDGELXAAAAAElFTkSuQmCC" />
            </svg>
          </a>
        </div>
      <?php } ?>
      <?php if (engine_in_array('tumblr', $socialshare_allow)) { ?>
        <div class="social_share_icon">
          <a class="tumblr" target="_blank" href="<?php echo 'http://www.tumblr.com/share/link?url=' . $URL; ?>">
            <i><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                <path
                  d="M309.8 480.3c-13.6 14.5-50 31.7-97.4 31.7-120.8 0-147-88.8-147-140.6v-144H17.9c-5.5 0-10-4.5-10-10v-68c0-7.2 4.5-13.6 11.3-16 62-21.8 81.5-76 84.3-117.1 .8-11 6.5-16.3 16.1-16.3h70.9c5.5 0 10 4.5 10 10v115.2h83c5.5 0 10 4.4 10 9.9v81.7c0 5.5-4.5 10-10 10h-83.4V360c0 34.2 23.7 53.6 68 35.8 4.8-1.9 9-3.2 12.7-2.2 3.5 .9 5.8 3.4 7.4 7.9l22 64.3c1.8 5 3.3 10.6-.4 14.5z" />
              </svg></i>
          </a>
        </div>
      <?php } ?>
      <?php if (engine_in_array('skype', $socialshare_allow)) { ?>
        <div class="social_share_icon">
          <a class="skype" target="_blank" href="<?php echo 'https://web.skype.com/share?url=' . $URL . '&lang=en' ?>">
            <i class="fab fa-skype"></i>
          </a>
        </div>
      <?php } ?>
      <?php if (engine_in_array('flipboard', $socialshare_allow)) { ?>
        <div class="social_share_icon">
          <a class="flipboard" target="_blank"
            href="<?php echo 'https://share.flipboard.com/bookmarklet/popout?v=2&url=' . $URL ?>">
            <i><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                <path d="M0 32v448h448V32H0zm358.4 179.2h-89.6v89.6h-89.6v89.6H89.6V121.6h268.8v89.6z" />
              </svg></i>
          </a>
        </div>
      <?php } ?>
      <?php if (engine_in_array('vk', $socialshare_allow)) { ?>
        <div class="social_share_icon">
          <a class="vk" target="_blank" href="<?php echo 'https://vk.com/share.php?url=' . $URL ?>">
            <i class="fab fa-vk"></i>
          </a>
        </div>
      <?php } ?>
    </div>
  <?php } ?>

</div>

<script type="text/javascript">
  AttachEventListerSE('click', '.copy_itemurl', function (e) {
    if (scriptJquery('#copy_item_url').val().length) {
      scriptJquery("<textarea/>").appendTo("body").val(scriptJquery('#copy_item_url').val()).select().each(function () {
        document.execCommand('copy');
      }).remove();
      showSuccessTooltip('<i class="fas fa-check-circle"></i><span>' + ('<?php echo $this->translate("Link Copied Successfully."); ?>')+'</span>');
    }
  });

  owlJqueryObject(".social_share_icons").owlCarousel({
    nav: true,
    dots: false,
    margin: 8,
    loop: false,
    responsiveClass: true,
    autoWidth: true,
  });
  owlJqueryObject(".owl-prev").html('<i class="fa fa-angle-left"></i>');
  owlJqueryObject(".owl-next").html('<i class="fa fa-angle-right"></i>');
</script>