<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */
?>

<?php if ($this->viewer_id): ?>

  <script type="text/javascript">
    en4.core.runonce.add(function(){
      scriptJquery('#rsvp_options input[type=radio]').on('click', function(){
        var option_id = scriptJquery(this).val();
        scriptJquery('#event_radio_' + option_id).addClass('event_radio_loading');
        scriptJquery.ajax({
            url: '<?php echo $this->url(array('module' => 'event', 'controller' => 'widget', 'action'=>'profile-rsvp', 'subject' => $this->subject()->getGuid()), 'default', true); ?>',
            method: 'post',
            dataType : 'json',
            data : {
              'format' : 'json',
              'option_id' : option_id,
              'subject' : '<?php echo $this->subject()->getGuid(); ?>',
            },
            success : function(responseJSON)
            {
              refreshEventStats();
              scriptJquery('#event_radio_' + option_id).addClass('event_radio');
              scriptJquery('#event_radio_' + option_id).removeClass('event_radio_loading');
              scriptJquery('#rsvp_options input').each(function(e){
                var radio = scriptJquery(e);
                radio.css("display",null);
                radio.blur();
              });
              if (responseJSON.error) {
                alert(responseJSON.error);
              } else {
                <?php if (!$this->canChangeVote): ?>
                  scriptJquery('.poll_radio input').attr('disabled', true);
                <?php endif ?>
              }
            }
        });
      });
    });

    var refreshEventStats = function() {
      en4.core.request.send(
        scriptJquery.ajax({
          url : en4.core.baseUrl + 'widget/index/content_id/' +
            <?php echo sprintf('%d', $this->profileInfoContentId) ?>,
          method: 'post',
          dataType : 'html',
          data : {
            format : 'html',
            subject : en4.core.subject.guid,
          }
        }),
        { 'element' : scriptJquery('#event_stats').parent() }
      );
    }
  </script>

  <h3>
    <?php echo $this->translate('Your RSVP');?>
  </h3>
  <form class="event_rsvp_form" action="<?php echo $this->url() ?>" method="post" onsubmit="return false;">
    <div class="events_rsvp" id="rsvp_options">
      <div class="event_radio" id="event_radio_2">
        <input id="rsvp_option_2" type="radio" class="rsvp_option" name="rsvp_options" <?php if ($this->rsvp == 2): ?>checked="true"<?php endif; ?> value="2" /><?php echo $this->translate('Attending');?>
      </div>
      <div class="event_radio" id="event_radio_1">
        <input id="rsvp_option_1" type="radio" class="rsvp_option" name="rsvp_options" <?php if ($this->rsvp == 1): ?>checked="true"<?php endif; ?> value="1" /><?php echo $this->translate('Maybe Attending');?>
      </div>
      <div class="event_radio" id="event_radio_0">
        <input id="rsvp_option_0" type="radio" class="rsvp_option" name="rsvp_options" <?php if ($this->rsvp == 0): ?>checked="true"<?php endif; ?> value="0" /><?php echo $this->translate('Not Attending');?>
      </div>
    </div>
  </form>

<?php endif; ?>
