<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<h2>
  <?php echo $this->translate('Statistics') ?>
</h2>
<div class="admin_home_quick_stas">
  <ul>
    <?php $i = 1; ?>
    <?php foreach( $this->statistics as $key => $statistic ): ?>
      <li>
        <div class="admin_home_quick_inner">
          <div class="admin_quick_heading">
            <h5>
              <?php echo $this->translate($statistic['label']) ?>
              <?php if($key == 'users.online') { ?>
                <span id="label_<?php echo $i; ?>"> | <?php echo $this->translate('Now') ?> </span>
              <?php } else { ?>
                <span id="label_<?php echo $i; ?>"> | <?php echo $this->translate('Today') ?> </span>
              <?php } ?>
            </h5>
            <div class="dropdwon_section" <?php if($key == 'users.online') { ?> style="display:none" <?php } ?>>
              <a href="javascript:void(0)" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-ellipsis-h"></i>
              </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><a href="javascript:void(0)" onclick="showHideStats('today', '<?php echo $i; ?>')"><?php echo $this->translate('Today') ?> </a></li>
                  <li><a href="javascript:void(0)" onclick="showHideStats('total', '<?php echo $i; ?>')"><?php echo $this->translate('Total') ?> </a></li>
                </ul>
              </div>
            </div>
            <div class="admin_quick_bottom" id="today_<?php echo $i; ?>" <?php if($key == 'users.online') { ?> style="display:none" <?php } ?>>
              <h4><?php echo $this->locale()->toNumber((int)$statistic['today']) ?></h4>
            </div>
            <div class="admin_quick_bottom" id="total_<?php echo $i; ?>" style="display:<?php if($key == 'users.online') { ?> block <?php } else { ?> none <?php } ?>;">
              <h4><?php echo $this->locale()->toNumber((int)$statistic['total']) ?></h4>
            </div>
          </div>
      </li>
      <?php $i++; ?>
    <?php endforeach; ?>
  </ul>
</div>
</div>
<script type="text/javascript">
  function showHideStats(value, key) {
    if(value == 'today') {
      scriptJquery('#today_'+key).show();
      scriptJquery('#total_'+key).hide();
      scriptJquery('#label_'+key).html(" | <?php echo $this->translate('Today') ?> ");
    } else { 
      scriptJquery('#today_'+key).hide();
      scriptJquery('#total_'+key).show();
      scriptJquery('#label_'+key).html(" | <?php echo $this->translate('Total') ?> ");
    }
  }
</script>
