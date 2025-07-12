<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Alex
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_stats", 'childMenuItemName' => 'core_admin_main_stats_logs')); ?>

<div class="admin_common_top_section">
  <h2 class="page_heading"><?php echo $this->translate("Log Browser") ?></h2>
  <p><?php echo $this->translate("CORE_VIEWS_SCRIPTS_ADMINSYSTEM_LOG_DESCRIPTION") ?></p>
  <?php
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if( $settings->getSetting('user.support.links', 0) == 1 ) {
      echo 'More info: <a href="https://community.socialengine.com/blogs/597/83/log-browser" target="_blank">See KB article</a>.';
    } 
  ?>	
</div>
<script type="text/javascript">
  en4.core.runonce.add(function() {
    var el = scriptJquery('.admin_logs');
    if( el.length ) {
      el.scrollTop(0, el.offset().top);
    }
    scriptJquery('#clear').on('click', function() {
      if(scriptJquery('#file').val().trim() == '' ) {
        return;
      }
      var url = '<?php echo $this->url() ?>?clear=1';
      url += '&file=' + encodeURI(scriptJquery('#file').val());
      scriptJquery('#filter_form')
        .attr('action', url)
        .attr('method', 'POST')
        .trigger("submit");
        ;
    });
    scriptJquery('#download').on('click', function() {
      if(scriptJquery('#file').val().trim() == '' ) {
        return;
      }
      var url = '<?php echo $this->url(array('action' => 'download')) ?>';
      url += '?file=' + encodeURI(scriptJquery('#file').val());
      (scriptJquery.crtEle('iframe',{
        src : url,
      })).css({
          display : 'none'
      }).appendTo(scriptJquery(document.body));
    });
  });
</script>

<?php if( !empty($this->formFilter) ): ?>
  <div class="admin_search">
    <div class="search">
      <?php echo $this->formFilter->render($this) ?>
    </div>
  </div>
<?php endif; ?>
<?php if( $this->error ): ?>
  <ul class="form-notices">
    <li>
      <?php echo $this->error ?>
    </li>
  </ul>
<?php endif; ?>
<?php if( !empty($this->logText) ): ?>
  <div class="admin_logs_container">
    <div class="admin_logs_info">
      <?php echo $this->translate(
        'Showing the last %1$s lines, %2$s bytes from the end. The file\'s size is %3$s bytes.',
        $this->locale()->toNumber($this->logLength),
        $this->locale()->toNumber($this->logSize - $this->logOffset),
        $this->locale()->toNumber($this->logSize)
      ) ?>
    </div>
    <div class="admin_logs_nav">
      <span class="admin_logs_nav_next">
        <?php if( $this->logEndOffset > 0 ): ?>
          <a href="<?php echo $this->url() ?>?<?php echo http_build_query(array(
            'file' => $this->logName,
            'length' => $this->logLength,
            'offset' => $this->logEndOffset,
          )) ?>">
            Next
          </a>
        <?php endif; ?>
      </span>
      <?php if( $this->logOffset < $this->logSize ): ?>
        <span class="admin_logs_nav_previous">
          <a href="<?php echo $this->url() ?>?<?php echo http_build_query(array(
            'file' => $this->logName,
          )) ?>">
            Back
          </a>
        </span>
      <?php endif; ?>
    </div>
    <div class="admin_logs">
      <pre><?php echo $this->logText ?></pre>
    </div>
  </div>
<?php endif; ?>
<script type="application/javascript">
  scriptJquery('.core_admin_main_stats').parent().addClass('active');
  scriptJquery('.core_admin_main_stats_logs').addClass('active');
</script>
