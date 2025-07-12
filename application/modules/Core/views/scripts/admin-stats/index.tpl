<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9861 2013-02-12 02:25:28Z john $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_stats", 'childMenuItemName' => 'core_admin_main_stats_statistics')); ?>

<div class="admin_common_top_section">
  <h2 class="page_heading"><?php echo $this->translate("Site-wide Statistics") ?></h2>
  <p>
    <?php echo $this->translate("CORE_VIEWS_SCRIPTS_ADMINSTATS_INDEX_DESCRIPTION") ?>
  </p>
  <?php
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if( $settings->getSetting('user.support.links', 0) == 1 ) {
      echo 'More info: <a href="https://community.socialengine.com/blogs/597/79/site-wide-statistics" target="_blank">See KB article</a>.';
    } 
  ?>	
</div>  
<div class="admin_search">
  <div class="search">
    <?php echo $this->filterForm->render($this) ?>
  </div>
</div>
<div id="admin_statistics" class="admin_statistics">
  <div class="admin_statistics_nav">
    <a id="admin_stats_offset_previous" onclick="processStatisticsPage(-1);"><?php echo $this->translate("Previous") ?></a>
    <a id="admin_stats_offset_next" onclick="processStatisticsPage(1);" style="display: none;"><?php echo $this->translate("Next") ?></a>
  </div>

  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <script type="text/javascript">
    var currentArgs = {};
    var processStatisticsFilter = function(formElement) {
      var vals = {};
      formElement.serializeArray().forEach((item)=>{
        vals[item.name] = item.value;
      });
      vals.offset = 0;
      buildChart(vals);
      return false;
    }
    var processStatisticsPage = function(count) {
      args = scriptJquery.extend({},currentArgs);
      args.offset += count;
      buildChart(args);
    }
    var updateFormOptions = function() {
      var periodEl = scriptJquery('form').find('#period');
      var chunkEl = scriptJquery('form').find('#chunk');
      switch( periodEl.val()) {
        case 'ww':
          var children = chunkEl.children().eq(0);
          for( var i = 0, l = children.length; i < l; i++ ) {
            if( ['dd'].indexOf(children.eq(i).val()) == -1 ) {
              children.eq(i).css('display', 'none');
              if( children.eq(i).prop('selected') ) {
                children.eq(i).prop('selected', false);
              }
            } else {
              children.eq(i).css('display', '');
            }
          }
          break;
        case 'MM':
          var children = chunkEl.children();
          for( var i = 0, l = children.length; i < l; i++ ) {
            if( ['dd', 'ww'].indexOf(children.eq(i).val()) == -1 ) {
              children.eq(i).css('display', 'none');
              if( children.eq(i).prop('selected') ) {
                children.eq(i).prop('selected', false);
              }
            } else {
              children.eq(i).css('display', '');
            }
          }
          break;
        case 'y':
          var children = chunkEl.children();
          for( var i = 0, l = children.length; i < l; i++ ) {
            if( ['dd', 'ww', 'MM'].indexOf(children.eq(i).val()) == -1 ) {
              children.eq(i).css('display', 'none');
              if( children.eq(i).prop('selected') ) {
                children.eq(i).prop('selected', false);
              }
            } else {
              children.eq(i).css('display', '');
            }
          }
          break;
        default:
          break;
      }
    }
    var buildChart = function(args) {
      currentArgs = args;
      scriptJquery('#admin_stats_offset_next').css('display', (args.offset < 0 ? '' : 'none'));

      var url = new URL('<?php echo (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']
        . $this->url(array('action' => 'chart-data')) ?>');
      var searchData = scriptJquery.extend({
            format : 'json',
          },args); 
      while (scriptJquery('#my_chart').children(':first').length) {
        scriptJquery('#my_chart').children(':first').remove();
      }
      scriptJquery('#loading').css('display', '').appendTo(scriptJquery('#my_chart'));
      setTimeout(function() {
        scriptJquery.ajax({
          url : url.toString(),
          method : 'post',
          data : searchData,
          success : function(responseJSON) {
            scriptJquery('#loading').css('display', 'none').appendTo(scriptJquery('#admin_statistics'));
            google.charts.setOnLoadCallback(drawChart(responseJSON));
          }
        });
      },250);
    }

    window.addEventListener('load', function() {
      updateFormOptions();
      scriptJquery('#period').on('change', function(event) {
        updateFormOptions();
      });
      buildChart({
        'type' : 'core.views',
        'mode' : 'normal',
        'chunk' : 'dd',
        'period' : 'ww',
        'start' : 0,
        'offset' : 0
      });
    });

    google.charts.load('current', {'packages':['corechart']});
    function drawChart(response) {
      var data = [];
      for (var key in response.data) {
        if (response.data.hasOwnProperty(key)) {
          data.push([key, response.data[key]]);
        }
      }
      var data = google.visualization.arrayToDataTable(data);

      var options = {
        title: response.title,
        legend: { 
          position: 'bottom',
        },
        <?php if(!empty($_COOKIE['adminmode_theme']) && $_COOKIE['adminmode_theme'] == 'dark'): ?>
          titleTextStyle: {
            color: '#fff',
          },
          backgroundColor: {
            'fill': '#1D1D1F',
          },
        <?php endif; ?>
      };
      var chart = new google.visualization.PieChart(document.getElementById('my_chart'));
      chart.draw(data, options);
    }
  </script>
  <div id="my_chart" class="my_chart"></div>
  <div id="loading" style="display: none"></div>
</div>
<style>
  .my_chart {
    height:450px;
    width:100%
  }
  [dir="rtl"] .my_chart *{
    text-align:left;
    direction:ltr;
  }
  #loading {
    width: inherit;
    height: inherit;
    background-position: center 10%;
    background-repeat: no-repeat;
    background-image: url(application/modules/Core/externals/images/large-loading.gif);
  }
  html.dark_mode div#my_chart svg g rect + g text{
    fill:#fff !important;
  }

</style>
