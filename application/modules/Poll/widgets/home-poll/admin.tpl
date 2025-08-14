<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: admin.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<div style="padding: 10px;">

  <?php if( !$this->values ): ?>
    
    <script type="text/javascript">
      en4.core.runonce.add(function() {
        var params = parent.pullWidgetParams();
        var info = parent.pullWidgetTypeInfo();
        
        // Populate params
        Object.entries(params).forEach(function([key, value]) {
          if( $type(value) == 'object' ) {
            Object.entries(value).forEach(function([skey, svalue]){
              if(scriptJquery('#'+key + '-' + svalue).length) {
                scriptJquery('#'+key + '-' + svalue).prop('checked', true);
              }
            });
          } else if(scriptJquery('#'+key).length) {
            scriptJquery('#'+key).val(value);
          } else if(scriptJquery('#'+key + '-' + value).length) {
            scriptJquery('#'+key + '-' + value).prop('checked', true);
          }
        });
        scriptJquery('.form-description').html(info.description);

        // Has a poll selected already
        if( 'poll_id' in params && params.poll_id ) {
          scriptJquery('#poll-home-poll-search').css('display', 'none');
          selectPoll(params.poll_id);
        } else {
          
        }
        
      });

      var searchPoll = function(query) {
        scriptJquery.ajax({
          url : '<?php echo $this->url(array('module' => 'poll', 'controller' => 'manage', 'action' => 'suggest'), 'admin_default', true) ?>',
          method : 'post',
          dataType : 'json',
          data : {
            format : 'json',
            query : query
          },
          success : function(responseJSON) {
            scriptJquery('#poll-home-poll-search-results').css('display', '');
            Object.entries(responseJSON.data).forEach(function([id,title]) {
              (scriptJquery.crtEle('a', {})).html(title).click(function(event) {
                    selectPoll(id);
              }).appendTo(
                (scriptJquery.crtEle('li', {
                })).appendTo(
                  scriptJquery('#poll-home-poll-search-results').find('ul')
                )
              );
            });
            parent.Smoothbox.instance.doAutoResize();
          }
        });
      }

      var deselectPoll = function() {
        scriptJquery('#poll_id').val();
        scriptJquery('#poll-home-poll-search').css('display', '');
        scriptJquery('#poll-home-poll-search-results').css('display', 'none');
        scriptJquery('#poll-home-poll-form').css('display', 'none');
        scriptJquery('#poll-home-poll-selected').css('display', 'none');
      }
      
      var selectPoll = function(poll_id, info) {
        if( !info ) {
          getPollInfo(poll_id, selectPoll);
          return;
        }
        scriptJquery('#poll_id').val(poll_id);
        scriptJquery('#title').val(info.title);
        scriptJquery('#poll-home-poll-search').css('display', 'none');
        scriptJquery('#poll-home-poll-search-results').css('display', 'none');
        scriptJquery('#poll-home-poll-form').css('display', '');
        scriptJquery('#poll-home-poll-selected').css('display', '');

        scriptJquery('#poll-home-poll-selected').attr('');

        // Create photo
        if( 'photo' in info ) {
          (scriptJquery.crtEle('img', {
            src : info.photo
          })).appendTo(
            (scriptJquery.crtEle('div', {
              'class' : 'photo'
            })).appendTo(
              scriptJquery('#poll-home-poll-selected')
            )
          );
        }
        // Create info wrapper
        var infoWrapper = scriptJquery.crtEle('div', {
          'class' : 'info'
        });
        infoWrapper.appendTo(scriptJquery('#poll-home-poll-selected'));

        // Create title
        (scriptJquery.crtEle('a', {
          href : info.href,
          target : '_blank'
        })).appendTo(info.title).appendTo(
          (scriptJquery.crtEle('div', {
            'class' : 'title'
          })).appendTo(
            infoWrapper
          )
        );

        // Create description
        (scriptJquery.crtEle('div', {
          'class' : 'description'
        })).html(info.description).appendTo(
          infoWrapper
        );
        parent.Smoothbox.instance.doAutoResize();
      }

      var getPollInfo = function(poll_id, callback) {
        scriptJquery.ajax({
          url : '<?php echo $this->url(array('module' => 'poll', 'controller' => 'manage', 'action' => 'info'), 'admin_default', true) ?>',
          method : 'post',
          dataType : 'json',
          data : {
            format : 'json',
            poll_id : poll_id
          },
          success : function(responseJSON) {
            if('status' in responseJSON && responseJSON.status ) {
              callback(poll_id, responseJSON);
            }
          }
        });
      }
    </script>

    <div id="poll-home-poll-form" style="display:none;">
      <?php echo $this->form->render($this) ?>
    </div>

    <div id="poll-home-poll-search">
      <h3>
        <?php echo $this->translate('Home Poll') ?>
      </h3>
      <p>
        <?php echo $this->translate('Search for a poll to display using the text box below.') ?>
      </p>
      <div>
        <input type="text" name="query" id="query" />
      </div>
      <div>
        <button onclick="searchPoll(document.getElementById('query').value)">Search</button>
      </div>
    </div>
    <div id="poll-home-poll-search-results" style="display:none;">
      <ul>
      </ul>
    </div>
    <div id="poll-home-poll-selected" style="display:none;">
    </div>
  <?php else: ?>
    <script type="text/javascript">
      parent.setWidgetParams(<?php echo Zend_Json::encode($this->values) ?>);
      parent.Smoothbox.close();
    </script>
  <?php endif; ?>
</div>
