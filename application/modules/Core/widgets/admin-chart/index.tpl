<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9905 2013-02-14 02:46:28Z alex $
 * @author     John
 */
?>

<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Core/externals/scripts/apexcharts.min.js'); ?> 
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Core/externals/styles/apexcharts.css'); ?>

<div class="admin_home_dashboard_item">
  <div class="admin_quick_heading">
    <h5><?php echo $this->translate("Reports ") ?>
      <span><?php echo $this->translate("| Weekly") ?></span>
      </h5>
      <div class="dropdwon_section">
        <a href="<?php echo $this->url(array("controller" => 'stats'), 'admin_default', true); ?>" class="view_btn">
          <?php echo $this->translate("View All") ?>
        </a> 
      </div>
  </div>
  <div class="admin_chat_graph">
    <div id="reportsChart"></div>
  </div>
</div>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", () => {
  new ApexCharts(document.querySelector("#reportsChart"), {
    series: [
      {
        name: '<?php echo $this->translate("Views"); ?>',
        data: <?php echo $this->viewsData; ?>,
      },
      {
        name: '<?php echo $this->translate("Signups"); ?>',
        data: <?php echo $this->signupData; ?>
      }, 
      {
        name: 'Activity Feeds',
        data: <?php echo $this->actionsData; ?>
      },
      {
        name: '<?php echo $this->translate("Comments"); ?>',
        data: <?php echo $this->commentsData; ?>,
      },
    ],
    chart: {
      height: 350,
      type: 'area',
      toolbar: {
        show: false
      },
    },
    markers: {
      size: 4
    },
    colors: ['#4154f1', '#2eca6a', '#ff771d', '#f110de'],
    fill: {
      type: "gradient",
      gradient: {
        shadeIntensity: 1,
        opacityFrom: 0.3,
        opacityTo: 0.4,
        stops: [0, 90, 100]
      }
    },
    dataLabels: {
      enabled: false
    },
    stroke: {
      curve: 'smooth',
      width: 2
    },
    xaxis: {
      type: 'datetime',
      categories: <?php echo $this->dateArray; ?>
    },
    tooltip: {
      <?php if(!empty($_COOKIE['adminmode_theme']) && $_COOKIE['adminmode_theme'] == 'dark'):?>
        theme: 'dark',
      <?php else: ?>
        theme: 'light',
       <?php endif; ?>
      x: {
        format: 'dd/MM/yyyy'
      },
    }
  }).render();
});
</script>
