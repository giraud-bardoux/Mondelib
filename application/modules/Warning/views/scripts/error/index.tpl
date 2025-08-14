<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: index.tpl 2024-01-24 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>

<div class="warning_private_page_main_container">
  <?php if($this->privatepagephotoID) { ?>
    <?php $bgPhotoUrl = Engine_Api::_()->core()->getFileUrl($this->privatepagephotoID);  ?>
  <?php } else { ?>
    <?php $bgPhotoUrl = 'application/modules/Warning/externals/images/private/'.$this->default_activate.'.png'; ?>
  <?php } ?>

  <?php if($this->default_activate == 1): ?>
    <div class="warning_private_page_one_container" style="background-image:url(application/modules/Warning/externals/images/private/bg_1.png);">
      <div class="warning_private_page_row">
        <div class="warning_private_page_content">
          <div class="warning_private_page_image">
            <img src="<?php echo $bgPhotoUrl; ?>">
          </div>
          <div class="warning_main_title">
            <h2><?php echo $this->translate("Private Page"); ?></h2>
          </div>
          <div class="warning_small_title">
            <p><?php echo $this->translate("Sorry, You've landed on a private page."); ?></p>
          </div>
          <div class="warning_discrtiption">
            <p><?php echo $this->translate("If you are seeking something specific, feel free to refine your search or do not hesitate to reach out to us for assistance."); ?></p>
          </div>
          <?php if($this->showsearch): ?>
            <form class="searchform" id="global_search_form" action="<?php echo $this->url(array('controller' => 'search'), 'default', true) ?>" method="get">
              <div class="warning_private_page_form">
                <span><input id="global_search_field" type="search" data-required="required" name="query" placeholder="Search" value=""></span>
                <button type="submit"><i class="fa fa-search"></i></button>
              </div>
            </form>
          <?php endif; ?>
          <?php if($this->showhomebutton || $this->showbackbutton): ?>
            <div class="warning_footer_section">
              <?php if($this->showhomebutton): ?>
                <div class="warning_home_button">
                  <a href=""><?php echo $this->translate("Go to Home"); ?></a>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php elseif($this->default_activate == 2): ?> 
    <div class="warning_private_page_two_container" style="background-image:url(application/modules/Warning/externals/images/private/bg_2.png);">
      <div class="warning_private_page_row">
        <div class="warning_private_page_content">
          <div class="warning_private_page_image">
            <img src="<?php echo $bgPhotoUrl; ?>">
          </div>
          <div class="warning_main_title">
            <h2><?php echo $this->translate("Private Page"); ?></h2>
          </div>
          <div class="warning_small_title">
            <p><?php echo $this->translate("Sorry, You've landed on a private page."); ?></p>
          </div>
          <div class="warning_discrtiption">
            <p><?php echo $this->translate("If you are seeking something specific, feel free to refine your search or do not hesitate to reach out to us for assistance."); ?></p>
          </div>
          <?php if($this->showsearch): ?>
            <form class="searchform" id="global_search_form" action="<?php echo $this->url(array('controller' => 'search'), 'default', true) ?>" method="get">
              <div class="warning_private_page_form">
                <span><input id="global_search_field" type="search" data-required="required" name="query" placeholder="Search" value=""></span>
                <button type="submit"><i class="fa fa-search"></i></button>
              </div>
            </form>
          <?php endif; ?>
          <?php if($this->showhomebutton || $this->showbackbutton): ?>
            <div class="warning_footer_section">
              <?php if($this->showhomebutton): ?>
                <div class="warning_home_button">
                  <a href=""><?php echo $this->translate("Go to Home"); ?></a>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php elseif($this->default_activate == 3): ?>
    <div class="warning_private_page_three_container" style="background-image:url( application/modules/Warning/externals/images/private/bg_3.png);">
      <div class="warning_private_page_row">
        <div class="warning_private_page_content">
          <div class="warning_private_page_image">
            <img src="<?php echo $bgPhotoUrl; ?>">
          </div>
          <div class="warning_main_title">
            <h2><?php echo $this->translate("Private Page"); ?></h2>
          </div>
          <div class="warning_small_title">
            <p><?php echo $this->translate("Sorry, You've landed on a private page."); ?></p>
          </div>
          <div class="warning_discrtiption">
            <p><?php echo $this->translate("If you are seeking something specific, feel free to refine your search or do not hesitate to reach out to us for assistance."); ?></p>
          </div>
          <?php if($this->showsearch): ?>
            <form class="searchform" id="global_search_form" action="<?php echo $this->url(array('controller' => 'search'), 'default', true) ?>" method="get">
              <div class="warning_private_page_form">
                <span><input id="global_search_field" type="search" data-required="required" name="query" placeholder="Search" value=""></span>
                <button type="submit"><i class="fa fa-search"></i></button>
              </div>
            </form>
          <?php endif; ?>
          <?php if($this->showhomebutton || $this->showbackbutton): ?>
            <div class="warning_footer_section">
              <?php if($this->showhomebutton): ?>
                <div class="warning_home_button">
                  <a href=""><?php echo $this->translate("Go to Home"); ?></a>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div> 
  <?php elseif($this->default_activate == 4): ?>
    <div class="warning_private_page_four_container" style="background-image:url();">
      <div class="warning_private_page_row">
        <div class="warning_private_page_image">
          <img src="<?php echo $bgPhotoUrl; ?>">
        </div>
        <div class="warning_private_page_content">
          <div class="warning_main_title">
            <h2><?php echo $this->translate("Private Page"); ?></h2>
          </div>
          <div class="warning_small_title">
            <p><?php echo $this->translate("Sorry, You've landed on a private page."); ?></p>
          </div>
          <div class="warning_discrtiption">
            <p><?php echo $this->translate("If you are seeking something specific, feel free to refine your search or do not hesitate to reach out to us for assistance."); ?></p>
          </div>
          <?php if($this->showsearch): ?>
            <form class="searchform" id="global_search_form" action="<?php echo $this->url(array('controller' => 'search'), 'default', true) ?>" method="get">
              <div class="warning_private_page_form">
                <span><input id="global_search_field" type="search" data-required="required" name="query" placeholder="Search" value=""></span>
                <button type="submit"><i class="fa fa-search"></i></button>
              </div>
            </form>
          <?php endif; ?>
          <?php if($this->showhomebutton || $this->showbackbutton): ?>
            <div class="warning_footer_section">
              <?php if($this->showhomebutton): ?>
                <div class="warning_home_button">
                  <a href=""><?php echo $this->translate("Go to Home"); ?></a>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div> 
  <?php elseif($this->default_activate == 5): ?>
    <div class="warning_private_page_five_container" style="background-image:url(application/modules/Warning/externals/images/private/5.png);">
      <div class="warning_private_page_row">
        <div class="warning_private_page_content">
          <div class="warning_main_title">
            <h2><?php echo $this->translate("Private Page"); ?></h2>
          </div>
          <div class="warning_small_title">
            <p><?php echo $this->translate("Sorry, You've landed on a private page."); ?></p>
          </div>
          <div class="warning_discrtiption">
            <p><?php echo $this->translate("If you are seeking something specific, feel free to refine your search or do not hesitate to reach out to us for assistance."); ?></p>
          </div>
          <?php if($this->showsearch): ?>
            <form class="searchform" id="global_search_form" action="<?php echo $this->url(array('controller' => 'search'), 'default', true) ?>" method="get">
              <div class="warning_private_page_form">
                <span><input id="global_search_field" type="search" data-required="required" name="query" placeholder="Search" value=""></span>
                <button type="submit"><i class="fa fa-search"></i></button>
              </div>
            </form>
          <?php endif; ?>
          <?php if($this->showhomebutton || $this->showbackbutton): ?>
            <div class="warning_footer_section">
              <?php if($this->showhomebutton): ?>
                <div class="warning_home_button">
                  <a href=""><?php echo $this->translate("Go to Home"); ?></a>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php elseif($this->default_activate == 6): ?>
    <div class="warning_private_page_six_container">
      <div class="warning_private_page_row">
        <?php if($bgPhotoUrl): ?>
          <div class="warning_private_page_image">
            <img src="<?php echo $bgPhotoUrl; ?>">
            <?php else: ?>
            <img src="application/modules/Warning/externals/images/private/design_6.png">
            <?php endif; ?>
          </div>
        <div class="warning_private_page_content">
          <div class="warning_main_title">
            <h2><?php echo $this->translate("Private Page"); ?></h2>
          </div>
          <div class="warning_small_title">
            <p><?php echo $this->translate("Sorry, You've landed on a private page."); ?></p>
          </div>
          <div class="warning_discrtiption">
            <p><?php echo $this->translate("If you are seeking something specific, feel free to refine your search or do not hesitate to reach out to us for assistance."); ?></p>
          </div>
          <?php if($this->showsearch): ?>
            <form class="searchform" id="global_search_form" action="<?php echo $this->url(array('controller' => 'search'), 'default', true) ?>" method="get">
              <div class="warning_private_page_form">
                <span><input id="global_search_field" type="search" data-required="required" name="query" placeholder="Search" value=""></span>
                <button type="submit"><i class="fa fa-search"></i></button>
              </div>
            </form>
          <?php endif; ?>
          <?php if($this->showhomebutton || $this->showbackbutton): ?>
            <div class="warning_footer_section">
              <?php if($this->showhomebutton): ?>
                <div class="warning_home_button">
                  <a href=""><?php echo $this->translate("Go to Home"); ?></a>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php elseif($this->default_activate == 7): ?>
    <div class="warning_private_page_seven_container" style="background-image:url(<?php echo $bgPhotoUrl; ?>);">
      <div class="warning_private_page_row">
        <div class="warning_private_page_content">
          <div class="warning_main_title">
            <h2><?php echo $this->translate("Private Page"); ?></h2>
          </div>
          <div class="warning_small_title">
            <p><?php echo $this->translate("Sorry, You've landed on a private page."); ?></p>
          </div>
          <div class="warning_discrtiption">
            <p><?php echo $this->translate("If you are seeking something specific, feel free to refine your search or do not hesitate to reach out to us for assistance."); ?></p>
          </div>
          <?php if($this->showsearch): ?>
            <form class="searchform" id="global_search_form" action="<?php echo $this->url(array('controller' => 'search'), 'default', true) ?>" method="get">
              <div class="warning_private_page_form">
                <span><input id="global_search_field" type="search" data-required="required" name="query" placeholder="Search" value=""></span>
                <button type="submit"><i class="fa fa-search"></i></button>
              </div>
            </form>
          <?php endif; ?>
          <?php if($this->showhomebutton || $this->showbackbutton): ?>
            <div class="warning_footer_section">
              <?php if($this->showhomebutton): ?>
                <div class="warning_home_button">
                  <a href=""><?php echo $this->translate("Go to Home"); ?></a>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php elseif($this->default_activate == 8): ?>
    <div class="warning_private_page_eight_container" style="background-image:url(<?php echo $bgPhotoUrl; ?>);">
      <div class="warning_private_page_row">
        <div class="warning_private_page_content">
          <div class="warning_main_title">
            <h2><?php echo $this->translate("Private Page"); ?></h2>
          </div>
          <div class="warning_small_title">
            <p><?php echo $this->translate("Sorry, You've landed on a private page."); ?></p>
          </div>
          <div class="warning_discrtiption">
            <p><?php echo $this->translate("If you are seeking something specific, feel free to refine your search or do not hesitate to reach out to us for assistance."); ?></p>
          </div>
          <?php if($this->showsearch): ?>
            <form class="searchform" id="global_search_form" action="<?php echo $this->url(array('controller' => 'search'), 'default', true) ?>" method="get">
              <div class="warning_private_page_form">
                <span><input id="global_search_field" type="search" data-required="required" name="query" placeholder="Search" value=""></span>
                <button type="submit"><i class="fa fa-search"></i></button>
              </div>
            </form>
          <?php endif; ?>
          <?php if($this->showhomebutton || $this->showbackbutton): ?>
            <div class="warning_footer_section">
              <?php if($this->showhomebutton): ?>
                <div class="warning_home_button">
                  <a href=""><?php echo $this->translate("Go to Home"); ?></a>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>
<script type="text/javascript">
	en4.core.runonce.add(function() {
    AutocompleterRequestJSON('global_search_field', "<?php echo $this->url(array('module' => 'warning', 'controller' => 'index', 'action' => 'search'), 'default', true) ?>", function(selecteditem) {
      loadAjaxContentApp(selecteditem.url);
    });
  });
  en4.core.runonce.add(function() {
    scriptJquery('#global_search_field').keydown(function(e) {
      if (e.which === 13) {
        showAllSearchResultsError();
      }
    });
  });
  function showAllSearchResultsError() {
    if(document.getElementById('all')) {
      document.getElementById('all').removeEvents('click');
    }
    loadAjaxContentApp('<?php echo $this->url(array("controller" => "search"), "default", true); ?>' + "?query=" + document.getElementById('global_search_field').value);
  }
</script>
