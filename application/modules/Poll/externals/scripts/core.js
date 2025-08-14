
/* $Id: core.js 9572 2011-12-27 23:41:06Z john $ */



(function() { // START NAMESPACE
var $ = 'id' in document ? document.id : window.$;
en4.poll = {

  urls : {
    vote : 'polls/vote/',
    login : 'login'
  },

  data : {},

  addPollData : function(identity, data) {
    if($type(data) != 'object' ) {
      data = {};
    }
    data = new Hash(data);
    this.data[identity] = data;
    return this;
  },

  getPollDatum : function(identity, key, defaultValue) {
    if( !defaultValue ) {
      defaultValue = false;
    }
    if( !(identity in this.data) ) {
      return defaultValue;
    }
    if( !(key in this.data[identity]) ) {
      return defaultValue;
    }
    return this.data[identity][key];
  },

  toggleResults : function(identity) {
    var pollContainer = scriptJquery('#poll_form_' + identity);
    if( 'none' == pollContainer.find('.poll_options div.poll_has_voted').css('display') ) {
      pollContainer.find('.poll_options div.poll_has_voted').show();
      pollContainer.find('.poll_options div.poll_not_voted').hide();
      pollContainer.find('.poll_toggleResultsLink').text(en4.core.language.translate('Show Questions'));
    } else {
      pollContainer.find('.poll_options div.poll_has_voted').hide();
      pollContainer.find('.poll_options div.poll_not_voted').show();
      pollContainer.find('.poll_toggleResultsLink:first').text(en4.core.language.translate('Show Results'));
    }
  },

  renderResults : function(identity, answers, votes) {
    if( !answers || 'object' != $type(answers) ) {
      return;
    }
    var pollContainer = scriptJquery('#poll_form_' + identity);
    Object.entries(answers).forEach(function([key,option]) {
      var div = scriptJquery('#poll-answer-' + option.poll_option_id);
      var pct = votes > 0
              ? Math.floor(100*(option.votes / votes))
              : 1;
      if (pct < 1)
          pct = 1;
      // set width to 70% of actual width to fit text on same line
      div[0].style.width = (.7*pct)+'%';
      div.next('div.poll_answer_total')
         .text(option.votesTranslated + ' (' + en4.core.language.translate('%1$s%%', (option.votes ? pct : '0')) + ')');
      if( !this.getPollDatum(identity, 'canVote') ||(!this.getPollDatum(identity, 'canChangeVote') && this.getPollDatum(identity, 'hasVoted')) || this.getPollDatum(identity, 'isClosed')) {
        pollContainer.getElement('.poll_radio input').set('disabled', true);
      }
    }.bind(this));
  },

  vote: function(identity, option) {
    if(!en4.user.viewer.id) {
      window.location.href = this.urls.login + '?return_url=' + encodeURIComponent(window.location.href);
      return;
    }
    //if( en4.core.subject.type != 'poll' ) {
    //  return;
    //}

    // if(scriptJquery(option).prototype != scriptJquery.prototype) {
    //   return;
    // }
    option = scriptJquery(option);

    var pollContainer = scriptJquery('#poll_form_' + identity);
    var value = option.val();

    scriptJquery('#poll_radio_' + value).toggleClass('poll_radio_loading');
    var token = this.data[identity].csrfToken;
    var self = this;
    scriptJquery.ajax({
      url: this.urls.vote + '/' + identity,
      method: 'post',
      dataType: 'json',
      data : {
        'format' : 'json',
        'poll_id' : identity,
        'option_id' : value,
        'token': token
      },
      success: function(responseJSON) {
        scriptJquery('#poll_radio_' + value).removeClass('poll_radio_loading');
        if( $type(responseJSON) == 'object' && responseJSON.error ) {
          Smoothbox.open(scriptJquery.crtEle('div', {
            'html' : responseJSON.error
              + '<br /><br /><button onclick="parent.Smoothbox.close()">'
              + en4.core.language.translate('Close')
              + '</button>'
          }));
        } else {
          pollContainer.find('.poll_vote_total:first')
            .text(en4.core.language.translate(['%1$s vote', '%1$s votes', responseJSON.votes_total], responseJSON.votes_total));
          self.renderResults(identity, responseJSON.pollOptions, responseJSON.votes_total);
          self.toggleResults(identity);
          self.data[identity].csrfToken = responseJSON.token;
        }
        if(!self.getPollDatum(identity, 'canChangeVote') ) {
          pollContainer.find('.poll_radio input').attr('disabled', true);
        }
      }.bind(this)
    });
  }
};

})(); // END NAMESPACE
