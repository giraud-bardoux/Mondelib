<?php
/**
 * SocialEngine - Search Widget Smarty Template
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2012 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Matthew
 */
?>
<?php $randomNumber = rand(4, 160); ?>
<div id='global_search_form_container' class="core_search_form">
  <a href="javascript:void(0)" id='search_mobile_btn_<?php echo $randomNumber; ?>' class="search_mobile_btn d-none"><i class="fas fa-search"></i></a>
  <div class="core_search_form_wrap">
    <form id="global_search_form" action="<?php echo $this->url(array('controller' => 'search'), 'default', true) ?>" method="get">
      <input class="global_search_field" autocomplete="off" type='text' class='text suggested' name='query' id='global_search_field' size='20' maxlength='100' alt='<?php echo $this->translate('Search') ?>'  placeholder='<?php echo $this->translate('Search') ?>'/>
      <button type="submit"><i class="fas fa-search"></i></button>
    </form>
    <div id="recent_search_data" class="recent_search_data header_search_recent notifications_donotclose" style="display:none;">
      <?php if($this->viewer()->getIdentity()) { ?>
        <div id="user_search_query_all">
          <div class="header_search_recent_head header_search_recent_head_recent" id="header_search_recent_head" <?php if(engine_count($this->getResults) == 0) { ?> style="display:none;" <?php } ?> >
            <span><?php echo $this->translate("Recent");?></span>
            <a href="javascript:void(0);" class="user_recent_search_remove font_small"><?php echo $this->translate("Clear"); ?></a>
          </div>
          <ul class="header_search_recent_list header_search_recent_list_recent" id="header_search_recent_list">
            <?php foreach($this->getResults as $result) { ?>
              <li class="search_query_<?php echo $result->recentsearch_id; ?>" id="search_query_<?php echo $result->recentsearch_id; ?>" >
              <a href="<?php echo $this->url(array('controller' => 'search'), 'default', true). '?query='.$result->query.'&type='.$result->type; ?>" class="header_search_recent_list_item">
                  <div class="_thumb">
                    <?php if($result->id && $result->type) { ?>
                      <?php $item = Engine_Api::_()->getItem(strtolower($result->type), $result->id); if(!$item) continue; ?>
                      <?php echo $this->itemPhoto($item, 'thumb.icon'); ?>
                    <?php } else { ?>
                      <i class="fa-regular fa-clock"></i>
                    <?php } ?>
                  </div>
                  <div class="_info">
                    <p class="m-0 _title"><?php echo $result->query; ?></p>
                  </div>
                </a>
                <a href="javascript:void(0);" class="user_recent_search_remove _clear link_inherit center_item rounded-circle" data-id="<?php echo $result->recentsearch_id; ?>" data-query="<?php echo $result->query; ?>">
                  <i class="icon_cross"></i>
                </a>
              </li>
            <?php } ?>
          </ul>
        </div>
      <?php } ?>
      <?php if(engine_count($this->hashtags) > 0) { ?>
        <div class="header_search_recent_head header_search_recent_head_trending">
          <span><?php echo $this->translate("Trending Hashtags");?></span>
        </div>
        <ul class="header_search_recent_list header_search_recent_list_trending">
          <?php $url = $this->url(array(),'core_hashtags','true')."?search="; ?>
          <?php for ($i = 0; $i < engine_count($this->hashtags); $i++): ?>
            <li>
              <a href='<?php echo $url.urlencode(trim($this->hashtags[$i]['tagName'], '#')); ?>' class="header_search_recent_list_item">
                <div class="_thumb">
                  <i class="fa-solid fa-hashtag"></i>
                </div>
                <div class="_info">
                  <p class="m-0 _title"><?php echo $this->hashtags[$i]['tagName']; ?></p>
                </div>
              </a>
            </li>
          <?php endfor;?>
        </ul>
      <?php } ?>
    </div>
  </div>
</div>
<script type="application/javascript">
en4.core.runonce.add(function() {

  AttachEventListerSE('click', '#search_mobile_btn_<?php echo $randomNumber; ?>', function (e) {
    var item = scriptJquery(this).parent();
    var target = item.find(".core_search_form_wrap");
    if(target.hasClass('search_mobile_btn_active')) {
      item.find(".core_search_form_wrap").removeClass("search_mobile_btn_active");
    } else {
      item.find(".core_search_form_wrap").addClass("search_mobile_btn_active");
    }
  });

  AutocompleterRequestJSON('global_search_field', "<?php echo $this->url(array('module' => 'core', 'controller' => 'search', 'action' => 'global-search'), 'default', true) ?>", function(selecteditem) {

    scriptJquery.ajax({
      method: 'post',
      'url':  en4.core.baseUrl + 'core/search/add',
      'data': {
        format: 'json',
        query: selecteditem.value,
        type:selecteditem.resource_type,
        id:selecteditem.resource_id,
      },
      success: function(response) {
        var response = jQuery.parseJSON(response);
        if(response.error) {
          alert(en4.core.language.translate('Something went wrong,please try again later'));
        } else {
          //showSuccessTooltip('<i class="fa fa-heart"></i><span>'+(response.message)+'</span>');
        }
      }
    });

    scriptJquery('#global_search_field').val('');

    if(scriptJquery('#global_search_field').val() != '') {
      var randomId = Math.floor(Math.random() * 1000000000);
      var searchDataResource = '<li class="search_query_'+randomId+'" id="search_query_'+randomId+'"><a href="search/index/query/'+selecteditem.value+'/type/'+selecteditem.resource_type+'" class="header_search_recent_list_item"><div class="_thumb">'+selecteditem.photo+'</div><div class="_info"><p class="m-0 _title">'+selecteditem.value+'</p></div></a><a href="'+selecteditem.url+'" class="user_recent_search_remove _clear link_inherit center_item rounded-circle" data-id="'+randomId+'" data-query="'+selecteditem.value+'"><i class="icon_cross"></i></a></li>';
      scriptJquery('body').find('.header_search_recent_list').prepend(searchDataResource);
    }
    loadAjaxContentApp(selecteditem.url);
  }, [], {'class':'header_mini_search'});
});
</script>
