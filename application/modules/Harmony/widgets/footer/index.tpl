<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: index.tpl 2024-03-11 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Harmony
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>
<?php $settings = Engine_Api::_()->getApi('settings', 'core'); ?>
<div class="harmony_footer">
  <div class="container">
    <div class="row">
      <div class="col-md-5">
        <div class="harmony_footer_left">
          <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('harmony.footer.enablelogo', 1)) { ?>
            <div class="harmony_footer_logo">
              <?php echo $this->content()->renderWidget("core.menu-logo",array("logo"=> $settings->getSetting('harmony.footer.logo'))); ?>
            </div>
            <?php if($this->accessibility_option){ ?>
              <div class="harmony_footer_logo_contrast">
                  <?php echo $this->content()->renderWidget("core.menu-logo",array("logo"=> $this->footerlogocontrast)); ?>
              </div> 
            <?php } ?>
          <?php } ?>
          <?php if($settings->getSetting('harmony.description')) { ?>
            <p class="footer_desc"><?php echo nl2br($settings->getSetting('harmony.description', 'Inspire creativity, community, and awareness! SocialEngine PHP - the best choice for community social networking software.')); ?></p>
          <?php } ?>
          <?php if($settings->getSetting('harmony.socialenable') && engine_count($this->socialnavigation) > 0) { ?>
            <div class="footer_social_links">
              <h6><?php echo $this->translate("Follow Us"); ?></h6>
              <ul class="navigation">
                <?php foreach( $this->socialnavigation as $link ): ?>
                  <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
                    <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"
                      <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >
                      <?php if($link->get('icon') == 'fa-twitter') { ?>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>
                      <?php } else { ?>
                        <i class="<?php echo $link->get('icon') ? $link->get('icon') : 'fa fa-star' ?>"></i>
                      <?php } ?>
                      <span><?php echo $this->translate($link->getlabel()) ?></span>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php } ?>
        </div>
      </div>
      <?php if(engine_count($this->aboutLinksMenu) && $settings->getSetting('harmony.aboutlinksenable', '1')) { ?>
        <div class="col-md-2">
          <div class="harmony_footer_link">
            <h3 class="custom_footer_heading"><?php echo $this->translate("Explore"); ?></h3>
            <ul class="footer_link_bottom">
              <?php foreach( $this->aboutLinksMenu as $link ): 
                $attribs = array_diff_key(array_filter($link->toArray()), array_flip(array(
                  'reset_params', 'route', 'module', 'controller', 'action', 'type',
                  'visible', 'label', 'href'
                )));
              ?>
                <li>
                  <?php if($link->get('icon')) { ?>
                    <i class="fa <?php echo $link->get('icon') ? $link->get('icon') : 'fa-star' ?>"></i>
                  <?php } ?>
                  <?php echo $this->htmlLink($link->getHref(), $this->translate($link->getLabel()), $attribs) ?>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      <?php } ?>
      <?php if(engine_count($this->quickLinksMenu) && $settings->getSetting('harmony.quicklinksenable', '1')) { ?>
        <div class="col-md-2">
          <div class="harmony_footer_link">
            <h3 class="custom_footer_heading"><?php echo $this->translate("Quick Links"); ?></h3>
            <ul class="footer_link_bottom">
              <?php foreach( $this->quickLinksMenu as $link ): 
              $attribs = array_diff_key(array_filter($link->toArray()), array_flip(array(
                'reset_params', 'route', 'module', 'controller', 'action', 'type',
                'visible', 'label', 'href'
              )));
              ?>
              <li>
                <?php if($link->get('icon')) { ?>
                  <i class="fa <?php echo $link->get('icon') ? $link->get('icon') : 'fa-star' ?>"></i>
                <?php } ?>
                <?php echo $this->htmlLink($link->getHref(), $this->translate($link->getLabel()), $attribs) ?>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>  
        </div>  
      <?php } ?>
      <div class="col-md-3">
        <div class="harmony_footer_contact">
          <?php if($settings->getSetting('harmony.rightcolhdinglocation') || $settings->getSetting('harmony.rightcolhdingemail') || $settings->getSetting('harmony.rightcolhdingphone')) { ?>
            <h3 class="custom_footer_heading"><?php echo $this->translate("About Us"); ?></h3>
            <ul class="harmony_footer_contact_inner">
              <?php if($settings->getSetting('harmony.rightcolhdinglocation')) { ?>
                <li><i class="fas fa-map-marker-alt" aria-hidden="true"></i> <span> <?php echo $settings->getSetting('harmony.rightcolhdinglocation', 'Los Angeles, USA'); ?> </span></li>
              <?php } ?>
              <?php if($settings->getSetting('harmony.rightcolhdingemail')) { ?>
                <li><i class="fas fa-envelope" aria-hidden="true"></i> <span><?php echo $settings->getSetting('harmony.rightcolhdingemail', 'info@abc.com'); ?> </span></li>
              <?php } ?>
              <?php if($settings->getSetting('harmony.rightcolhdingphone')) { ?>
                <li><i class="fas fa-phone-alt" aria-hidden="true"></i> <span><?php echo $settings->getSetting('harmony.rightcolhdingphone', '1234567890'); ?> </span></li>
              <?php } ?>
            </ul>
          <?php } ?>
        </div>  
      </div>
    </div>  
  </div> 
  
  <div class="harmony_footer_bottom">
    <div class="container">
      <div class="harmony_footer_bottom_inner">
        <div class="harmony_footer_bottom_left">
          <span class="footer_copyright"><?php echo $this->translate('Copyright &copy;%s', date('Y')) ?></span>
          <?php if($settings->getSetting('harmony.helpenable', '1')) { ?>
            <?php foreach( $this->navigation as $item ):
              $attribs = array_diff_key(array_filter($item->toArray()), array_flip(array(
              'reset_params', 'route', 'module', 'controller', 'action', 'type',
              'visible', 'label', 'href'
              )));
              ?>
            <?php echo $this->htmlLink($item->getHref(), $this->translate($item->getLabel()), $attribs) ?>
            <?php endforeach; ?>
          <?php } ?>
        </div>
        <div class="harmony_footer_bottom_right">
          <?php echo $this->partial('_languages.tpl', 'core', array('languageNameList' => $this->languageNameList)); ?>
        </div>
      </div>
    </div>
    <?php if(!empty($this->viewer_id)) { ?>
      <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.sell.info', 1)): ?>
        <div class="container">
          <div class="footer_donotsell">
            <input type="checkbox" id="donosellinfo" onclick="donotSellInfo()" <?php if($this->viewer->donotsellinfo == 1) { ?> checked <?php } ?>> <?php echo $this->translate("Do Not Sell My Personal Information."); ?>
          </div>
        </div>
      <?php endif; ?>
      <script>
          function donotSellInfo() {
            var checkBox = document.getElementById("donosellinfo");
            (scriptJquery.ajax({
            method: 'post',
            url: en4.core.baseUrl + 'core/index/donotsellinfo/',
            dataType: 'json',
            data: {
                format: 'json',
                donotsellinfo: checkBox.checked,
            },
            success: function(responseHTML) {
            }
            }));
            return false;
          }
      </script>
    <?php } ?>
  </div>
</div>  
<style>
#global_footer{
  background-image:url(<?php echo $this->footerbgimage ? Engine_Api::_()->core()->getFileUrl($this->footerbgimage) : './application/modules/Harmony/externals/images/footer_bg.png'; ?>);
}
.dark_mode #global_footer{
  background-image:url(<?php echo $this->footerbgphotocontrast ? Engine_Api::_()->core()->getFileUrl($this->footerbgphotocontrast) : './application/modules/Harmony/externals/images/footer_bg.png'; ?>);
}
</style>
