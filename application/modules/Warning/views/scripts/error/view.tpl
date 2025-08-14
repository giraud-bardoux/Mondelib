<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: view.tpl 2024-01-24 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>
<div class="warning_pagenotfound_main">
  <div class="warning_pagenotfound_container">
    <?php if($this->default_activate == 1): ?>
      <div class="warning_pagenotfound_one_row">
        <div class="warning_pagenotfound_img">
          <?php if($this->pagenotfoundphotoID): ?>
            <?php $photo = Engine_Api::_()->core()->getFileUrl($this->pagenotfoundphotoID); ?>
            <img src="<?php echo $photo; ?>">
          <?php else: ?>
              <img src="application/modules/Warning/externals/images/pagenotfound/1.png" />
          <?php endif; ?>
        </div>
        <?php if($this->showsearch || $this->showbackbutton): ?>
          <div class="warning_pagenotfound_main_content">
            <div class="warning_pagenotfound_main_content_inner">
              <div class="warning_pagenotfound_main_title">
                <p><?php echo $this->translate("Oops! Error 404 occurred."); ?></p>
              </div>
              <div class="warning_pagenotfound_mini_title">
                <p><?php echo $this->translate("We can't seem to find the resource you're looking for."); ?></p>
              </div>
              <p class="small_title"><?php echo $this->translate("Please check that all spellings are correct."); ?></p>
              <?php if($this->showsearch): ?>
                <form class="searchform" id="global_search_form" action="<?php echo $this->url(array('controller' => 'search'), 'default', true) ?>" method="get">
                  <div class="warning_pagenotfound_form">
                    <span><input id="global_search_field" type="search" data-required="required" name="query" placeholder="Search" value=""></span>
                    <button type="submit" class=""><i class="fas fa-search"></i></button>
                  </div>
                </form>
              <?php endif; ?>
              <?php if($this->showbackbutton): ?>
                <p class="warning_pagenotfound_home_link">
                  <a href=""><?php echo $this->translate("Go to Home"); ?></a>
                </p>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    <?php elseif($this->default_activate == 2): ?>
      <div class="warning_pagenotfound_two_row">
        <div class="warning_pagenotfound_center">
          <div class="warning_pagenotfound_img">
            <?php if($this->pagenotfoundphotoID): ?>
              <?php $photo = Engine_Api::_()->core()->getFileUrl($this->pagenotfoundphotoID); ?>
              <img src="<?php echo $photo; ?>">
            <?php else: ?>
                <img src="application/modules/Warning/externals/images/pagenotfound/2.png" />
            <?php endif; ?>
          </div>
          <div class="warning_pagenotfound_main_title">
            <p><?php echo $this->translate("Oops! Error 404 occurred."); ?></p>
          </div>
          <div class="warning_pagenotfound_mini_title">
            <p><?php echo $this->translate("We can't seem to find the resource you're looking for."); ?></p>
          </div>
          <div class="warning_pagenotfound_discrtiption">
            <p><?php echo $this->translate("Please check that all spellings are correct."); ?></p>
          </div>
          <?php if($this->showsearch): ?>
            <form class="searchform" id="global_search_form" action="<?php echo $this->url(array('controller' => 'search'), 'default', true) ?>" method="get">
              <div class="warning_pagenotfound_form">
                <input id="global_search_field" type="search" data-required="required" name="query" placeholder="Search" value="">
                <button type="submit" class=""><i class="fas fa-search"></i></button>
              </div>
            </form>
          <?php endif; ?>
          <?php if($this->showbackbutton): ?>
            <p class="warning_pagenotfound_home_link">
              <a href=""><?php echo $this->translate("Go to Home"); ?></a>
            </p>
          <?php endif; ?>
        </div>
      </div>
    <?php elseif($this->default_activate == 3): ?>
      <div class="warning_pagenotfound_three_row">
        <div class="warning_pagenotfound_three_content">
          <div class="warning_pagenotfound_left_col">
            <div class="warning_pagenotfound_main_title">
              <p><?php echo $this->translate("Oops! Error 404 occurred."); ?></p>
            </div>
            <div class="warning_pagenotfound_discrtiption">
              <p><?php echo $this->translate("We can't seem to find the resource you're looking for."); ?></p>
            </div>
            <div class="warning_pagenotfound_form">
              <p><?php echo $this->translate("Please check that all spellings are correct."); ?></p>
              <?php if($this->showsearch): ?>
                <form class="searchform" id="global_search_form" action="<?php echo $this->url(array('controller' => 'search'), 'default', true) ?>" method="get">
                  <input id="global_search_field" type="search" data-required="required" name="query" placeholder="Search" value="">
                  <button type="submit" class=""><i class="fas fa-search"></i></button>
                </form>
              <?php endif; ?>
            </div>
            <?php if($this->showbackbutton): ?>
              <p class="warning_pagenotfound_home_link">
                <a href=""><?php echo $this->translate("Go to Home"); ?></a>
              </p>
            <?php endif; ?>
          </div>
          <div class="warning_pagenotfound_right_col">
            <div class="warning_pagenotfound_img">
              <?php if($this->pagenotfoundphotoID): ?>
                <?php $photo = Engine_Api::_()->core()->getFileUrl($this->pagenotfoundphotoID); ?>
                <img src="<?php echo $photo; ?>">
              <?php else: ?>
                  <img src="application/modules/Warning/externals/images/pagenotfound/3.png" />
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php elseif($this->default_activate == 4): ?>
      <div class="warning_pagenotfound_four_row">
        <div class="warning_pagenotfound_center">
          <div class="warning_pagenotfound_img">
            <?php if($this->pagenotfoundphotoID): ?>
              <?php $photo = Engine_Api::_()->core()->getFileUrl($this->pagenotfoundphotoID); ?>
              <img src="<?php echo $photo; ?>">
            <?php else: ?>
              <img src="application/modules/Warning/externals/images/pagenotfound/4.png" />
            <?php endif; ?>
          </div>
          <div class="warning_pagenotfound_main_title">
            <p><?php echo $this->translate("Oops! Error 404 occurred."); ?></p>
          </div>
          <div class="warning_pagenotfound_mini_title">
            <p><?php echo $this->translate("We can't seem to find the resource you're looking for."); ?></p>
          </div>
          <div class="warning_pagenotfound_discrtiption">
            <p><?php echo $this->translate("Please check that all spellings are correct."); ?></p>
          </div>
          <?php if($this->showsearch): ?>
            <form class="searchform" id="global_search_form" action="<?php echo $this->url(array('controller' => 'search'), 'default', true) ?>" method="get">
              <div class="warning_pagenotfound_form">
                <input id="global_search_field" type="search" data-required="required" name="query" placeholder="Search" value="">
                <button type="submit" class=""><i class="fas fa-search"></i></button>
              </div>
            </form>
          <?php endif; ?>
          <?php if($this->showbackbutton): ?>
            <p class="warning_pagenotfound_home_link">
              <a href=""><?php echo $this->translate("Go to Home"); ?></a>
            </p>
          <?php endif; ?>
        </div>
      </div>
    <?php elseif($this->default_activate == 5): ?>
      <div class="warning_pagenotfound_five_row">
        <?php if($this->pagenotfoundphotoID) { ?>
          <?php $bgPhotoUrl = Engine_Api::_()->core()->getFileUrl($this->pagenotfoundphotoID);  ?>
          <div class="warning_pagenotfound_img" style="background-image: url(<?php echo $bgPhotoUrl; ?>);">
        <?php } else { ?>
          <div class="warning_pagenotfound_img">
        <?php } ?>
          <span><?php echo $this->translate('404'); ?></span>
        </div>
        <?php if($this->showsearch || $this->showbackbutton): ?>
          <div class="warning_pagenotfound_main_content">
            <div class="warning_pagenotfound_main_content_inner">
              <div class="warning_pagenotfound_main_title">
                <p><?php echo $this->translate("Oops! Error 404 occurred."); ?></p>
              </div>
              <div class="warning_pagenotfound_mini_title">
                <p><?php echo $this->translate("We can't seem to find the resource you're looking for."); ?></p>
              </div>
              <p class="small_title"><?php echo $this->translate("Please check that all spellings are correct."); ?></p>
              <?php if($this->showsearch): ?>
                <form class="searchform" id="global_search_form" action="<?php echo $this->url(array('controller' => 'search'), 'default', true) ?>" method="get">
                  <div class="warning_pagenotfound_form">                    
                    <span><input id="global_search_field" type="search" data-required="required" name="query" placeholder="Search" value=""></span>
                    <button type="submit" class=""><i class="fas fa-search"></i></button>
                  </div>
                </form>
              <?php endif; ?>
              <?php if($this->showbackbutton): ?>
                <p class="warning_pagenotfound_home_link">
                  <a href=""><?php echo $this->translate("Go to Home"); ?></a>
                </p>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    <?php elseif($this->default_activate == 6): ?>
      <div class="warning_pagenotfound_six_row">
        <div class="warning_pagenotfound_center">
          <div class="warning_pagenotfound_img">
            <?php if($this->pagenotfoundphotoID): ?>
              <?php $photo = Engine_Api::_()->core()->getFileUrl($this->pagenotfoundphotoID); ?>
              <img src="<?php echo $photo; ?>">
            <?php else: ?>
                <img src="application/modules/Warning/externals/images/pagenotfound/6.png" />
            <?php endif; ?>
          </div>
          <div class="warning_pagenotfound_main_title">
            <p><?php echo $this->translate("Oops! Error 404 occurred."); ?></p>
          </div>
          <div class="warning_pagenotfound_mini_title">
            <p><?php echo $this->translate("We can't seem to find the resource you're looking for."); ?></p>
          </div>
          <div class="warning_pagenotfound_discrtiption">
            <p><?php echo $this->translate("Please check that all spellings are correct."); ?></p>
          </div>
          <?php if($this->showsearch): ?>
            <form class="searchform" id="global_search_form" action="<?php echo $this->url(array('controller' => 'search'), 'default', true) ?>" method="get">
              <div class="warning_pagenotfound_form">
                <input id="global_search_field" type="search" data-required="required" name="query" placeholder="Search" value="">
                <button type="submit" class=""><i class="fas fa-search"></i></button>
              </div>
            </form>
          <?php endif; ?>
          <?php if($this->showbackbutton): ?>
            <p class="warning_pagenotfound_home_link">
              <a href=""><?php echo $this->translate("Go to Home"); ?></a>
            </p>
          <?php endif; ?>
        </div>
      </div>
    <?php elseif($this->default_activate == 7): ?>
      <?php if($this->pagenotfoundphotoID) { ?>
        <?php $bgPhotoUrl = Engine_Api::_()->core()->getFileUrl($this->pagenotfoundphotoID);  ?>
      <?php } else { ?>
        <?php $bgPhotoUrl = ''; ?>
      <?php } ?>
      <div class="warning_pagenotfound_seven_row" style="background-image: url();">
        <div class="warning_pagenotfound_center">
          <div class="warning_pagenotfound_img">
            <img src="application/modules/Warning/externals/images/pagenotfound/7.png" />
            <div class="warning_pagenotfound_content">
              <div class="warning_pagenotfound_main_title">
                <p><?php echo $this->translate("Oops! Error 404 occurred."); ?></p>
              </div>
              <div class="warning_pagenotfound_mini_title">
                <p><?php echo $this->translate("We can't seem to find the resource you're looking for."); ?></p>
              </div>
              <div class="warning_pagenotfound_discrtiption">
                <p><?php echo $this->translate("Please check that all spellings are correct."); ?></p>
              </div>
            </div>
          </div>
          <div class="warning_pagenotfound_footer">
          <?php if($this->showsearch): ?>
            <form class="searchform" id="global_search_form" action="<?php echo $this->url(array('controller' => 'search'), 'default', true) ?>" method="get">
              <div class="warning_pagenotfound_form">
                <input id="global_search_field" type="search" data-required="required" name="query" placeholder="Search" value="">
                <button type="submit" class=""><i class="fas fa-search"></i></button>
              </div>
            </form>
          <?php endif; ?>
          <?php if($this->showbackbutton): ?>
            <p class="warning_pagenotfound_home_link">
              <a href=""><?php echo $this->translate("Go to Home"); ?></a>
            </p>
          <?php endif; ?>
          </div>
        </div>
      </div>
    <?php elseif($this->default_activate == 8): ?>
      <?php if($this->pagenotfoundphotoID) { ?>
        <?php $bgPhotoUrl = Engine_Api::_()->core()->getFileUrl($this->pagenotfoundphotoID);  ?>
      <?php } else { ?>
        <?php $bgPhotoUrl = 'application/modules/Warning/externals/images/pagenotfound/8.png'; ?>
      <?php } ?>
      <div class="warning_pagenotfound_eight_row" style="background-image: url();">
        <div class="warning_pagenotfound_center">
          <div class="warning_pagenotfound_content">
            <div class="warning_pagenotfound_main_title">
              <p><?php echo $this->translate("Oops! Error 404 occurred."); ?></p>
            </div>
            <div class="warning_pagenotfound_mini_title">
              <p><?php echo $this->translate("We can't seem to find the resource you're looking for."); ?></p>
            </div>
            <div class="warning_pagenotfound_discrtiption">
              <p><?php echo $this->translate("Please check that all spellings are correct."); ?></p>
            </div>
            <?php if($this->showsearch): ?>
            <form class="searchform" id="global_search_form" action="<?php echo $this->url(array('controller' => 'search'), 'default', true) ?>" method="get">
              <div class="warning_pagenotfound_form">
                <input id="global_search_field" type="search" data-required="required" name="query" placeholder="Search" value="">
                <button type="submit" class=""><i class="fas fa-search"></i></button>
              </div>
            </form>
            <?php endif; ?>
            <?php if($this->showbackbutton): ?>
              <p class="warning_pagenotfound_home_link">
                <a href=""><?php echo $this->translate("Go to Home"); ?></a>
              </p>
            <?php endif; ?>
          </div>
          <div class="warning_pagenotfound_img">
            <img src="<?php echo $bgPhotoUrl; ?>" />
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
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
