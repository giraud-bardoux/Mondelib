<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: comingsoon.tpl 2024-01-24 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>
<?php if($this->default_activate == 1): ?>
  <?php if($this->comingsoonphotoID) { ?>
    <?php $bgPhotoUrl = Engine_Api::_()->core()->getFileUrl($this->comingsoonphotoID);  ?>
  <?php } else { ?>
    <?php $bgPhotoUrl = 'application/modules/Warning/externals/images/comingsoon/template_1.jpg'; ?>
  <?php } ?>
  <div class="warning_comingsoon_main_container">
    <div class="warning_comingsoon_one_container" style="background-image:url(<?php echo $bgPhotoUrl; ?>);">
      <div class="warning_comingsoon_middle_section">
        <div class="warning_comingsoon_content_section_inner">
          <?php if(!empty($this->logo)) { ?>
            <div class="warning_comingsoon_main_logo">
              <img src="<?php echo $this->logo; ?>">
            </div>
					<?php } ?>
          <div class="warning_comingsoon_main_title">
            <h2><?php echo $this->translate("We're Coming Soon!"); ?></h2>
          </div>
          <div id="simple_timer"></div>
          <div class="warning_comingsoon_small_title">
            <p><?php echo $this->translate("We have been spending long hours building our new website."); ?></p>
          </div>
          <div class="warning_comingsoon_discrtiption">
            <p><?php echo $this->translate("Please join our mailing list, or follow us on social media to stay updated."); ?></p>
          </div>
          <?php if($this->showcontactbutton): ?>
            <div class="warning_comingsoon_contect_button">
              <a href="javascript:;" onclick="openSmoothBoxInUrl('<?php echo $this->url(array('module' => 'warning', 'controller' => 'index', 'action' => 'contact'), 'default', true); ?>')"><?php echo $this->translate("Contact Us"); ?></a>
            </div>
          <?php endif; ?>
          <?php if($this->showsocialshare): ?>
            <div class="warning_comingsoon_social_icons">
              <ul>
                <?php foreach( $this->socialShareNavigation as $link ): ?>
                  <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
                    <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"
                      <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >
                      <i class="<?php echo $link->get('icon') ? $link->get('icon') : 'fa fa-star' ?>"></i>
                      <span><?php echo $this->translate($link->getlabel()) ?></span>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
<?php elseif($this->default_activate == 2): ?>
  <?php if($this->comingsoonphotoID) { ?>
    <?php $bgPhotoUrl = Engine_Api::_()->core()->getFileUrl($this->comingsoonphotoID);  ?>
  <?php } else { ?>
    <?php $bgPhotoUrl = 'application/modules/Warning/externals/images/comingsoon/template_2.jpg'; ?>
  <?php } ?>
  <div class="warning_comingsoon_main_container">
    <div class="warning_comingsoon_two_container" style="background-image:url(<?php echo $bgPhotoUrl; ?>);">
      <div class="warning_comingsoon_middle_section">
        <div class="warning_comingsoon_content_section_inner">
          <?php if(!empty($this->logo)) { ?>
            <div class="warning_comingsoon_main_logo">
              <img src="<?php echo $this->logo; ?>">
            </div>
					<?php } ?>
          <div id="simple_timer"></div>
          <div class="warning_comingsoon_main_title">
            <h2><?php echo $this->translate("We're Coming Soon!"); ?></h2>
          </div>
          <div class="warning_comingsoon_small_title">
            <p><?php echo $this->translate("We have been spending long hours building our new website."); ?></p>
          </div>
          <div class="warning_comingsoon_discrtiption">
            <p><?php echo $this->translate("Please join our mailing list, or follow us on social media to stay updated."); ?></p>
          </div>
          <?php if($this->showcontactbutton): ?>
            <div class="warning_comingsoon_contect_button">
              <a href="javascript:;" onclick="openSmoothBoxInUrl('<?php echo $this->url(array('module' => 'warning', 'controller' => 'index', 'action' => 'contact'), 'default', true); ?>')"><?php echo $this->translate("Contact Us"); ?></a>
            </div>
          <?php endif; ?>
          <?php if($this->showsocialshare ): ?>
            <div class="warning_comingsoon_social_icons">
              <ul>
                <?php foreach( $this->socialShareNavigation as $link ): ?>
                  <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
                    <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"
                      <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >
                      <i class="<?php echo $link->get('icon') ? $link->get('icon') : 'fa fa-star' ?>"></i>
                      <span><?php echo $this->translate($link->getlabel()) ?></span>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
        </div>        
      </div>
    </div>
  </div>
<?php elseif($this->default_activate == 3): ?>
  <?php if($this->comingsoonphotoID) { ?>
    <?php $bgPhotoUrl = Engine_Api::_()->core()->getFileUrl($this->comingsoonphotoID);  ?>
  <?php } else { ?>
    <?php $bgPhotoUrl = 'application/modules/Warning/externals/images/comingsoon/template_3.jpg'; ?>
  <?php } ?>
  <div class="warning_comingsoon_main_container">
    <div class="warning_comingsoon_three_container" style="background-image:url(<?php echo $bgPhotoUrl; ?>);">
      <div class="warning_comingsoon_middle_section">
        <div class="warning_comingsoon_content_section_inner">
          <?php if(!empty($this->logo)) { ?>
            <div class="warning_comingsoon_main_logo">
              <img src="<?php echo $this->logo; ?>">
            </div>
					<?php } ?>
          <div class="warning_comingsoon_main_title">
            <h2><?php echo $this->translate("We're Coming Soon!"); ?></h2>
          </div>
          <div class="warning_comingsoon_small_title">
            <p><?php echo $this->translate("We have been spending long hours building our new website."); ?></p>
          </div>
          <div id="simple_timer"></div>
          <div class="warning_comingsoon_discrtiption">
            <p><?php echo $this->translate("Please join our mailing list, or follow us on social media to stay updated."); ?></p>
          </div>
          <?php if($this->showcontactbutton): ?>
            <div class="warning_comingsoon_contect_button">
              <a href="javascript:;" onclick="openSmoothBoxInUrl('<?php echo $this->url(array('module' => 'warning', 'controller' => 'index', 'action' => 'contact'), 'default', true); ?>')"><?php echo $this->translate("Contact Us"); ?></a>
            </div>
          <?php endif; ?>
        </div>        
      </div>
      <div class="warning_comingsoon_footer_section">
        <?php if($this->showsocialshare ): ?>
          <div class="warning_comingsoon_social_icons">
            <ul>
              <?php foreach( $this->socialShareNavigation as $link ): ?>
                <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
                  <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"
                    <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >
                    <i class="<?php echo $link->get('icon') ? $link->get('icon') : 'fa fa-star' ?>"></i>
                    <span><?php echo $this->translate($link->getlabel()) ?></span>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php elseif($this->default_activate == 4): ?>
  <?php if($this->comingsoonphotoID) { ?>
    <?php $bgPhotoUrl = Engine_Api::_()->core()->getFileUrl($this->comingsoonphotoID);  ?>
  <?php } else { ?>
    <?php $bgPhotoUrl = 'application/modules/Warning/externals/images/comingsoon/template_4.jpg'; ?>
  <?php } ?>
  <div class="warning_comingsoon_main_container">
    <div class="warning_comingsoon_four_container" style="background-image:url(<?php echo $bgPhotoUrl; ?>);">
      <div class="warning_comingsoon_middle_section">
        <div class="warning_comingsoon_content_section_inner">
          <?php if(!empty($this->logo)) { ?>
            <div class="warning_comingsoon_main_logo">
              <img src="<?php echo $this->logo; ?>">
            </div>
					<?php } ?>
          <div id="simple_timer"></div>
          <div class="warning_comingsoon_main_title">
            <h2><?php echo $this->translate("We're Coming Soon!"); ?></h2>
          </div>
          <div class="warning_comingsoon_small_title">
            <p><?php echo $this->translate("We have been spending long hours building our new website."); ?></p>
          </div>
          <div class="warning_comingsoon_discrtiption">
            <p><?php echo $this->translate("Please join our mailing list, or follow us on social media to stay updated."); ?></p>
          </div>
          <?php if($this->showcontactbutton): ?>
            <div class="warning_comingsoon_contect_button">
              <a href="javascript:;" onclick="openSmoothBoxInUrl('<?php echo $this->url(array('module' => 'warning', 'controller' => 'index', 'action' => 'contact'), 'default', true); ?>')"><?php echo $this->translate("Contact Us"); ?></a>
            </div>
          <?php endif; ?>
          <?php if($this->showsocialshare ): ?>
            <div class="warning_comingsoon_social_icons">
              <ul>
                <?php foreach( $this->socialShareNavigation as $link ): ?>
                  <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
                    <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"
                      <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >
                      <i class="<?php echo $link->get('icon') ? $link->get('icon') : 'fa fa-star' ?>"></i>
                      <span><?php echo $this->translate($link->getlabel()) ?></span>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
        </div>        
      </div>
    </div>
  </div>
<?php elseif($this->default_activate == 5): ?>
  <?php if($this->comingsoonphotoID) { ?>
    <?php $bgPhotoUrl = Engine_Api::_()->core()->getFileUrl($this->comingsoonphotoID);  ?>
  <?php } else { ?>
    <?php $bgPhotoUrl = 'application/modules/Warning/externals/images/comingsoon/template_5.jpg'; ?>
  <?php } ?>
  <div class="warning_comingsoon_main_container">
    <div class="warning_comingsoon_five_container" style="background-image:url(<?php echo $bgPhotoUrl; ?>);">
      <div class="warning_comingsoon_container">
				<div class="warning_comingsoon_head_section">
          <?php if(!empty($this->logo)) { ?>
            <div class="warning_comingsoon_main_logo">
              <img src="<?php echo $this->logo; ?>">
            </div>
					<?php } ?>
				</div>
        <div class="warning_comingsoon_middle_section">
          <div class="warning_comingsoon_main_title">
            <h2><?php echo $this->translate("We're Coming Soon!"); ?></h2>
          </div>
          <div class="warning_comingsoon_small_title">
            <p><?php echo $this->translate("We have been spending long hours building our new website."); ?></p>
          </div>
          <div id="simple_timer"></div>
          <div class="warning_comingsoon_discrtiption">
            <p><?php echo $this->translate("Please join our mailing list, or follow us on social media to stay updated."); ?></p>
          </div>
          <?php if($this->showcontactbutton): ?>
          <div class="warning_comingsoon_contect_button">
            <a href="javascript:;" onclick="openSmoothBoxInUrl('<?php echo $this->url(array('module' => 'warning', 'controller' => 'index', 'action' => 'contact'), 'default', true); ?>')"><?php echo $this->translate("Contact Us"); ?></a>
          </div>
          <?php endif; ?>
        </div>
        <?php if($this->showcontactbutton || $this->showsocialshare): ?>
          <div class="warning_comingsoon_footer_section">
            <?php if($this->showsocialshare ): ?>
              <div class="warning_comingsoon_social_icons">
                <ul>
                  <?php foreach( $this->socialShareNavigation as $link ): ?>
                    <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
                      <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"
                        <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >
                        <i class="<?php echo $link->get('icon') ? $link->get('icon') : 'fa fa-star' ?>"></i>
                        <span><?php echo $this->translate($link->getlabel()) ?></span>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php elseif($this->default_activate == 6): ?>
  <div class="warning_comingsoon_main_container">
    <div class="warning_comingsoon_six_container">
      <div class="warning_comingsoon_left_section">
				<div class="warning_comingsoon_header_section">
          <?php if(!empty($this->logo)) { ?>
            <div class="warning_comingsoon_main_logo">
              <img src="<?php echo $this->logo; ?>">
            </div>
					<?php } ?>
				</div>
        <div class="warning_comingsoon_middle_section">
          <div class="warning_comingsoon_main_title">
            <h2><?php echo $this->translate("We're Coming Soon!"); ?></h2>
          </div>
          <div class="warning_comingsoon_small_title">
            <p><?php echo $this->translate("We have been spending long hours building our new website."); ?></p>
          </div>
          <div class="warning_comingsoon_discrtiption">
            <p><?php echo $this->translate("Please join our mailing list, or follow us on social media to stay updated."); ?></p>
          </div>
          <?php if($this->showcontactbutton): ?>
            <div class="warning_comingsoon_contect_button">
              <a href="javascript:;" onclick="openSmoothBoxInUrl('<?php echo $this->url(array('module' => 'warning', 'controller' => 'index', 'action' => 'contact'), 'default', true); ?>')"><?php echo $this->translate("Contact Us"); ?></a>
            </div>
          <?php endif; ?>
          <div id="simple_timer"></div>
        </div>
        <div class="warning_comingsoon_footer_section">
          <?php if($this->showsocialshare ): ?>
            <div class="warning_comingsoon_social_icons">
              <ul>
                <?php foreach( $this->socialShareNavigation as $link ): ?>
                  <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
                    <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"
                      <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >
                      <i class="<?php echo $link->get('icon') ? $link->get('icon') : 'fa fa-star' ?>"></i>
                      <span><?php echo $this->translate($link->getlabel()) ?></span>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="warning_comingsoon_right_section">
        <?php if($this->comingsoonphotoID) { ?>
          <?php $bgPhotoUrl = Engine_Api::_()->core()->getFileUrl($this->comingsoonphotoID);  ?>
        <?php } else { ?>
          <?php $bgPhotoUrl = 'application/modules/Warning/externals/images/comingsoon/template_6.jpg'; ?>
        <?php } ?>
        <div class="img_section" style="background-image:url(<?php echo $bgPhotoUrl; ?>);"></div>
      </div>
    </div>
  </div>
<?php elseif($this->default_activate == 7): ?>
  <?php if($this->comingsoonphotoID) { ?>
    <?php $bgPhotoUrl = Engine_Api::_()->core()->getFileUrl($this->comingsoonphotoID);  ?>
  <?php } else { ?>
    <?php $bgPhotoUrl = 'application/modules/Warning/externals/images/comingsoon/template_7.jpg'; ?>
  <?php } ?>
  <div class="warning_comingsoon_main_container">
    <div class="warning_comingsoon_seven_container" style="background-image:url(<?php echo $bgPhotoUrl; ?>);">
      <div class="warning_comingsoon_section_main">
        <?php if(!empty($this->logo)) { ?>
          <div class="warning_comingsoon_main_logo">
            <img src="<?php echo $this->logo; ?>">
          </div>
        <?php } ?>
        <div class="warning_comingsoon_content_section">
          <div class="warning_comingsoon_left_section">
            <div class="warning_comingsoon_main_title">
              <h2><?php echo $this->translate("We're Coming Soon!"); ?></h2>
            </div>
            <div class="warning_comingsoon_small_title">
              <p><?php echo $this->translate("We have been spending long hours building our new website."); ?></p>
            </div>
            <div class="warning_comingsoon_discrtiption">
              <p><?php echo $this->translate("Please join our mailing list, or follow us on social media to stay updated."); ?></p>
            </div>
            <?php if($this->showcontactbutton): ?>
              <div class="warning_comingsoon_contect_button">
                <a href="javascript:;" onclick="openSmoothBoxInUrl('<?php echo $this->url(array('module' => 'warning', 'controller' => 'index', 'action' => 'contact'), 'default', true); ?>')"><?php echo $this->translate("Contact Us"); ?></a>
              </div>
            <?php endif; ?>
          </div>
          <div class="warning_comingsoon_right_section">
            <div id="simple_timer"></div>
          </div>
        </div>
        <?php if($this->showsocialshare ): ?>
          <div class="warning_comingsoon_social_icons">
            <ul>
              <?php foreach( $this->socialShareNavigation as $link ): ?>
                <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
                  <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"
                    <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >
                    <i class="<?php echo $link->get('icon') ? $link->get('icon') : 'fa fa-star' ?>"></i>
                    <span><?php echo $this->translate($link->getlabel()) ?></span>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php elseif($this->default_activate == 8): ?>
  <?php if($this->comingsoonphotoID) { ?>
    <?php $bgPhotoUrl = Engine_Api::_()->core()->getFileUrl($this->comingsoonphotoID);  ?>
  <?php } else { ?>
    <?php $bgPhotoUrl = 'application/modules/Warning/externals/images/comingsoon/template_8.jpg'; ?>
  <?php } ?>
  <div class="warning_comingsoon_main_container">
    <div class="warning_comingsoon_eight_container" style="background-image:url(<?php echo $bgPhotoUrl; ?>);">
      <div class="warning_comingsoon_section_main">
        <?php if(!empty($this->logo)) { ?>
          <div class="warning_comingsoon_main_logo">
            <img src="<?php echo $this->logo; ?>">
          </div>
        <?php } ?>
        <div class="warning_comingsoon_content_section">
          <div class="warning_comingsoon_left_section">
            <div class="warning_comingsoon_main_title">
              <h2><?php echo $this->translate("We're Coming Soon!"); ?></h2>
            </div>
            <div class="warning_comingsoon_small_title">
              <p><?php echo $this->translate("We have been spending long hours building our new website."); ?></p>
            </div>
            <div class="warning_comingsoon_discrtiption">
              <p><?php echo $this->translate("Please join our mailing list, or follow us on social media to stay updated."); ?></p>
            </div>
            <?php if($this->showcontactbutton): ?>
              <div class="warning_comingsoon_contect_button">
                <a href="javascript:;" onclick="openSmoothBoxInUrl('<?php echo $this->url(array('module' => 'warning', 'controller' => 'index', 'action' => 'contact'), 'default', true); ?>')"><?php echo $this->translate("Contact Us"); ?></a>
              </div>
            <?php endif; ?>
          </div>
          <div class="warning_comingsoon_right_section">
            <div id="simple_timer"></div>
          </div>
        </div>
        <?php if($this->showsocialshare ): ?>
          <div class="warning_comingsoon_social_icons">
            <ul>
              <?php foreach( $this->socialShareNavigation as $link ): ?>
                <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
                  <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"
                    <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >
                    <i class="<?php echo $link->get('icon') ? $link->get('icon') : 'fa fa-star' ?>"></i>
                    <span><?php echo $this->translate($link->getlabel()) ?></span>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endif; ?>
<?php $datetime = new DateTime($this->warning_comingsoondate); ?>
<script type="text/javascript">
  en4.core.runonce.add(function() {
    scriptJquery('#simple_timer').syotimer({
      initialdate: '<?php echo $datetime->format('c') ;?>',
    });
  });
</script>
