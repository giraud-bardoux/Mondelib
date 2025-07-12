<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: referrers.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_stats", 'childMenuItemName' => 'core_admin_main_stats_url')); ?>

<div class="admin_common_top_section">
  <h2 class="page_heading"><?php echo $this->translate("Top Referring Sites") ?></h2>
  <p><?php echo $this->translate("CORE_VIEWS_SCRIPTS_ADMINSTATS_REFERRERS_DESCRIPTION") ?></p>
  <?php
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if( $settings->getSetting('user.support.links', 0) == 1 ) {
      echo 'More info: <a href="https://community.socialengine.com/blogs/597/81/referring-urls" target="_blank">See KB article</a>.';
    } 
  ?>	
</div>

<script type="text/javascript">
  var clearReferrers = function() {
    if( !confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to clear the referrers?")) ?>') ) {
      return;
    }
    var url = '<?php echo $this->url(array('action' => 'clear-referrers')) ?>';
    var request = scriptJquery.ajax({
      url : url,
      data : {
        format : 'json'
      },
      success : function() {
        window.location.replace( window.location.href );
      }
    });
  }
</script>

<?php if( engine_count($this->referrers) > 0 ): ?>
  <div>
    <?php echo $this->htmlLink('javascript:void(0);', 'Clear Referrer List', array(
      'class' => ' admin_link_btn admin_referrers_clear',
      'onclick' => "clearReferrers();",
    )) ?>
  </div>
  <table class='admin_table stats_referrers'>
    <thead>
      <tr>
        <th><?php echo $this->translate("Hits") ?></th>
        <th><?php echo $this->translate("Referring URL") ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach( $this->referrers as $referrer ): ?>
        <tr>
          <td>
            <?php echo $this->locale()->toNumber($referrer->value) ?>
          </td>
          <td>
            <?php
              $href = $referrer->host . $referrer->path . ( $referrer->query ? '?' . $referrer->query : '' );
              echo $this->htmlLink((_ENGINE_SSL ? 'https://' : 'http://') . $href, (_ENGINE_SSL ? 'https://' : 'http://') . $href, array('target' => '_blank'));
            ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

<?php else: ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There have not been any referrers logged yet.") ?>
    </span>
  </div>

<?php endif; ?>
