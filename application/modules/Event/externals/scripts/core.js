/* $Id: core.js 9984 2013-03-20 00:00:04Z john $ */

function eventWidgetRequestSend(action, event_id, notification_id, rsvp) {
  var url;
  if( action == 'accept' )
  {
    url = en4.core.baseUrl + 'event/member/accept';
  }
  else if( action == 'reject' )
  {
    url = en4.core.baseUrl + 'event/member/reject';
  }
  else
  {
    return false;
  }

  (scriptJquery.ajax({
    url : url,
    dataType : 'json',
    method : 'post',
    data : {
      event_id : event_id,
      format : 'json',
      rsvp : rsvp
      //'token' : '<?php //echo $this->token() ?>'
    },
    success : function(responseJSON)
    {
      if( !responseJSON.status ) {
        document.getElementById('notifications_' + notification_id).innerHTML = '<div class="request_success">' + responseJSON.error + '</div>';
      } else {
        document.getElementById('notifications_' + notification_id).innerHTML = '<div class="request_success">' + responseJSON.message + '</div>';
      }
    }
  }));
}
