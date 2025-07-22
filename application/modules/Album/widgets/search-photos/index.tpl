<script type="text/javascript">
//<![CDATA[
  en4.core.runonce.add(function() {
    scriptJquery('#sort').on('change', function(){
      scriptJquery(this).parents('form').eq(0).trigger("submit");
    });
  })
//]]>
</script>
<div class="sidebar_search_form core_search_form">
  <?php echo $this->searchForm->render($this) ?>
</div>
