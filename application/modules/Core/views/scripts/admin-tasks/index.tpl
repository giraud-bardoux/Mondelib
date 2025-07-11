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
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'childMenuItemName' => 'core_admin_main_settings_tasks')); ?>

<h2 class="page_heading"><?php echo $this->translate("Task Scheduler") ?></h2>

<?php if( engine_count($this->navigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>

<p>
  <?php echo (
    'CORE_VIEWS_SCRIPTS_ADMINTASKS_INDEX_DESCRIPTION' !== ($desc = $this->translate("CORE_VIEWS_SCRIPTS_ADMINTASKS_INDEX_DESCRIPTION")) ?
    $desc : '' ) ?>
</p>	
<p>
  <?php
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if( $settings->getSetting('user.support.links', 0) == 1 ) {
      echo 'More info: <a href="https://community.socialengine.com/blogs/597/39/task-scheduler" target="_blank">See KB article</a>.';
    } 
  ?>	
</p>

<div class='admin_search'>
  <?php echo $this->formFilter->render($this) ?>
</div>


<script type="text/javascript">
  // Auto refresh
  var doAutoRefresh = false;
  var tips;
  window.addEventListener('load', function() {
    doAutoRefresh = ( Cookie.read('en4_admin_tasks_autorefresh') == '1' ? true : false );
    (scriptJquery.crtEle('a', {
      'href' : 'javascript:void(0);',
    })).click(function() {
      if( doAutoRefresh ) {
				scriptJquery(this).html('<?php echo $this->translate('Enable Auto-Refresh') ?>');
        doAutoRefresh = false;
      } else {
        scriptJquery(this).html('<?php echo $this->translate('Disable Auto-Refresh') ?>');
        doAutoRefresh = true;
      }
      Cookie.write('en4_admin_tasks_autorefresh', (doAutoRefresh ? '1' : '0'));
    }).html(doAutoRefresh ? '<?php echo $this->translate('Disable Auto-Refresh') ?>' : '<?php echo $this->translate('Enable Auto-Refresh') ?>').appendTo(scriptJquery('#autorefresh-select'));
  });

  // Order
  var handleSort = function(order) {
    if(scriptJquery('#order').val() != order ) {
      scriptJquery('#order').val(order);
      scriptJquery('#direction').val('value');
    } else {
      scriptJquery('#direction').val('value', (scriptJquery('#direction').val() == 'ASC' ? 'DESC' : 'ASC'));
    }
    scriptJquery('#filter_form').trigger("submit");
  }

  // Selected
  var handleSelectedAction = function(action) {
    
    // Check action
    var url;
    switch( action ) {
      case 'run':
        url = '<?php echo $this->url(array('action' => 'run')) ?>';
        break;
      case 'reset':
        url = '<?php echo $this->url(array('action' => 'reset')) ?>';
        break;
      case 'unlock':
        url = '<?php echo $this->url(array('action' => 'unlock')) ?>';
        break;
      default:
        return;
        break;
    }

    var selections=document.getElementsByTagName("input");
    var checked = [];
    for (var i = 0; i<selections.length;i++) {
        if (selections[i].type == "checkbox") {
            if (selections[i].checked) {
                checked.push(selections[i]);
            }
        }
    }   

    // Check selection
    if( action != 'unlock' && checked.length <= 0 ) {
      return;
    }
    
    // Submit
    url += '?return=' + encodeURI(window.location.href);
    scriptJquery('#admin-tasks-form').attr('action', url);
    scriptJquery('#admin-tasks-form').trigger("submit");
  }

  // 
  // Counter
  var now = parseInt('<?php echo sprintf('%d', time()) ?>');
  var lastRun = parseInt('<?php echo sprintf('%d', $this->taskSettings['last']) ?>');
  var interval = parseInt('<?php echo sprintf('%d', $this->taskSettings['interval']) ?>');
  var timeout = parseInt('<?php echo sprintf('%d', $this->taskSettings['timeout']) ?>');
  var counter = 0;
  var refreshing = false;
  setInterval(function(){
    counter++;
    var sortOfNow = now + counter;
    var delta = sortOfNow - lastRun;
    if( delta > interval * 2 ) {
      //$clear(checkInterval);
      if( doAutoRefresh && !refreshing && counter > interval ) {
        refreshing = true;
        window.location.replace( window.location.href );
      }
    } else if( delta > interval ) {
      if(scriptJquery('#task_counter_container') ) {
        scriptJquery('#task_counter_container').html('<?php echo $this->translate('Tasks are ready to be run again.') ?>');
      }
      // Auto refresh?
      if( doAutoRefresh && !refreshing && sortOfNow > 10 ) {
        refreshing = true;
        window.location.replace( window.location.href );
      }
    } else {
      if(scriptJquery('#task_counter') ) {
        scriptJquery('#task_counter').html(interval - delta);
      }
    }
  },1000);
  

  en4.core.runonce.add(function(){
    scriptJquery('th.admin_table_short input[type=checkbox]').on('click', function(event){
      var el = scriptJquery(event.target);
      scriptJquery('input[type=checkbox]:not(:disabled)').prop('checked', el.prop('checked'));
    });
  });
</script>

<div>
  <?php if( time() - (int) $this->taskSettings['last'] > max($this->taskSettings['interval'] * 3, 60) ): ?>
    <ul class="form-errors">
      <li>
        <?php echo $this->translate('Tasks have not executed for more than %1$d seconds. Please check your configuration.', max($this->taskSettings['interval'] * 3, 60)) ?>
      </li>
    </ul>
  <?php endif; ?>

  <?php echo $this->translate('Tasks are checked every %1$s seconds.', $this->taskSettings['interval']) ?>
  <br />
  
  <?php echo $this->translate('Tasks are considered to have timed out after %1$d seconds.', $this->taskSettings['timeout']) ?>
  <br />

  <span>
    <span id="task_counter_container">
      <?php
        $next = ($this->taskSettings['last'] + $this->taskSettings['interval']) - time();
        if( $next <= 0 ):
      ?>
        <?php echo $this->translate('Tasks are ready to be run again.') ?>
      <?php else: ?>
        <?php echo $this->translate('Tasks can be run again in %1$s seconds.',
            '<span id="task_counter">' . (($this->taskSettings['last'] + $this->taskSettings['interval']) - time()) . '</span>'
        ) ?>
      <?php endif; ?>
    </span>
    <span id="autorefresh-select">
    </span>
  </span>
  <br />
  
</div>
<br />


<?php if( $this->tasks->count() > 1 ): ?>
  <?php echo $this->paginationControl($this->tasks) ?>
  <br />
<?php endif; ?>


<div class="admin_table_form">
  <form id="admin-tasks-form" method="post" action="<?php echo $this->url() ?>">

    <table class="admin_table admin_responsive_table">
      <thead>
        <tr>
          <th style="width: 1%;" class="admin_table_short">
            <input type="checkbox" class='checkbox' />
          </th>
          <th style="width: 1%;">
            <a href="javascript:void(0)" onclick="handleSort('task_id')">
              <?php echo $this->translate('ID') ?>
            </a>
          </th>
          <th>
            <a href="javascript:void(0)" onclick="handleSort('title')">
              <?php echo $this->translate('Name') ?>
            </a>
          </th>
          <th style="width: 1%;">
            <a href="javascript:void(0)" onclick="handleSort('timeout')">
              <?php echo $this->translate('Timeout') ?>
            </a>
          </th>
          <th style="width: 1%;">
            <?php echo $this->translate('Stats') ?>
          </th>
          <th style="width: 1%;">
            <?php echo $this->translate('Processes') ?>
          </th>
          <?php /*
          <th>
            <?php echo $this->translate('Options') ?>
          </th>
           *
           */ ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach( $this->tasks as $task ): ?>
          <tr>
            <td class="nowrap">
              <input type="checkbox" class='checkbox' name="selection[]" value="<?php echo $task->task_id ?>" />
            </td>
            <td data-label="ID" class="nowrap">
              <?php echo $this->locale()->toNumber($task->task_id) ?>
            </td>
            <td data-label="<?php echo $this->translate("Name") ?>" class="nowrap">
              <?php if( !empty($task->title) ): ?>
                <?php echo $task->title ?>
              <?php else: ?>
                <?php echo $task->plugin ?>
              <?php endif; ?>

              <?php if( !empty($this->taskProgress[$task->plugin]) ): ?>
                <br />
                <?php // Percent mode ?>
                <?php if( !empty($this->taskProgress[$task->plugin]['progress']) && !empty($this->taskProgress[$task->plugin]['total'])  ): ?>
                  <i>
                  <?php echo $this->translate(
                    '%1$s' . '%% complete',
                    $this->locale()->toNumber(round((int) @$this->taskProgress[$task->plugin]['progress'] / $this->taskProgress[$task->plugin]['total'] * 100, 1))
                  ) ?>
                  <br />
                  <?php echo $this->translate(
                    '%1$s of %2$s',
                    $this->locale()->toNumber((int) @$this->taskProgress[$task->plugin]['progress']),
                    $this->locale()->toNumber($this->taskProgress[$task->plugin]['total'])
                  ) ?>
                  </i>
                <?php // Queue mode ?>
                <?php elseif( !empty($this->taskProgress[$task->plugin]['total']) ): ?>
                  <i>
                  <?php echo $this->translate(
                    '%1$s in queue',
                    $this->locale()->toNumber($this->taskProgress[$task->plugin]['total'])
                  ) ?>
                  </i>
                <?php // Progress mode ?>
                <?php elseif( !empty($this->taskProgress[$task->plugin]['progress']) ): ?>
                  <i>
                  <?php echo $this->translate(
                    '%1$s processed',
                    $this->locale()->toNumber($this->taskProgress[$task->plugin]['total'])
                  ) ?>
                  </i>
                <?php endif; ?>
              <?php endif; ?>
            </td>
            <td data-label="<?php echo $this->translate("Timeout") ?>" class="nowrap">
              <?php echo $this->translate(array('%1$s second', '%1$s seconds', $task->timeout), $this->locale()->toNumber($task->timeout)) ?>
            </td>
            <td data-label="<?php echo $this->translate("Stats") ?>" class="nowrap">
              Succeeded:
              <?php if( $task->success_count > 0 ): ?>
                <?php echo $this->locale()->toNumber($task->success_count) ?>
                times, last
                <?php echo $this->timestamp($task->success_last) ?>
              <?php else: ?>
                never
              <?php endif; ?>
              <br />

              Failed:
              <?php if( $task->failure_count > 0 ): ?>
                <?php echo $this->locale()->toNumber($task->failure_count) ?>
                times, last
                <?php echo $this->timestamp($task->failure_last) ?>
              <?php else: ?>
                never
              <?php endif; ?>
              <br />

              <?php if( $task->started_count != $task->success_count + $task->failure_count ): ?>
                <?php if( $task->started_count > 0 ): ?>
                Started:
                  <?php echo $this->locale()->toNumber($task->started_count) ?>
                  times, last
                  <?php echo $this->timestamp($task->started_last) ?>
                <?php else: ?>
                  never
                <?php endif; ?>
                <br />
              <?php endif; ?>

              <?php if( $task->completed_count != $task->success_count + $task->failure_count ): ?>
                Completed:
                <?php if( $task->completed_count > 0 ): ?>
                  <?php echo $this->locale()->toNumber($task->completed_count) ?>
                  times, last
                  <?php echo $this->timestamp($task->completed_last) ?>
                <?php else: ?>
                  never
                <?php endif; ?>
                <br />
              <?php endif; ?>
            </td>
            <td data-label="<?php echo $this->translate("Processes") ?>" class="nowrap">
              <?php if( !empty($this->processIndex) && !empty($this->processIndex[$task->plugin]) ): ?>
                <?php foreach( $this->processIndex[$task->plugin] as $process ): ?>
                  <div>
                    <?php echo $this->htmlLink(array(
                      'reset' => false,
                      'action' => 'processes',
                      'pid' => $process['pid']
                    ), $process['pid']) ?>
                    <br />
                    <?php
                      $delta = time() - $process['started'];
                      echo $this->translate(array('Running for %d second', 'Running for %d seconds', $delta), $delta)
                    ?>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </td>
            <?php /*
            <td class="admin_table_options">
              <span class="sep">|</span>
              <?php echo $this->htmlLink('javascript:void(0);', $this->translate('run'), array('onclick' => 'runTasks(' . $task->task_id . ', $(this));')) ?>
              <span class="sep">|</span>
              <?php echo $this->htmlLink(array('reset' => false, 'action' => 'edit', 'task_id' => $task->task_id), $this->translate('edit')) ?>
              <span class="sep">|</span>
              <?php echo $this->htmlLink(array('reset' => false, 'action' => 'reset-stats', 'task_id' => $task->task_id), $this->translate('reset stats')) ?>

            </td>
             *
             */ ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <br />

    <div>
      <button onclick="handleSelectedAction('run'); return false;">Run Selected Now</button>
      <button onclick="handleSelectedAction('reset'); return false;">Reset Statistics</button>
    </div>

  </form>
</div>
<script type="application/javascript">
  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_settings_tasks').addClass('active');
</script>
