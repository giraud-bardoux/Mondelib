<script type="text/javascript">
//<![CDATA[
  scriptJquery().ready(function() {
    scriptJquery('#sort').on('change', function(){
      scriptJquery(this).parents('form').trigger("submit");
    });
    
    var category_id = scriptJquery('#category_id');
    if( category_id.length){
      category_id.on('change', function(){
        scriptJquery(this).parents('form').trigger("submit");
      });
    }
  })
//]]>
</script>
<div class="sidebar_search_form">
  <?php echo $this->searchForm->render($this) ?>
</div>