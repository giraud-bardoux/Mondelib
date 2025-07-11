<?php ?>



<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_monetization", 'parentMenuItemName' => 'core_admin_main_membership', 'lastMenuItemName' => 'Change Style')); ?>

<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'externals/jscolor/jscolor.js'); ?>

<h2 class="page_heading">
  <?php echo $this->translate("Membership") ?>
</h2>
<?php if( count($this->navigation) ): ?>
<div class='tabs'>
  <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
</div>
<?php endif; ?>

<div class="settings">
  <div class="admin_results">
     <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'payment', 'controller' => 'package', 'action' => 'index'), $this->translate("Back to Plans"), array('class' => 'icon_back buttonlink')); ?> 
  </div>
  <div class="payment_plan_style_form">
  	<?php echo $this->form->render($this) ?>
  </div>
</div>

<script>
  en4.core.runonce.add(function() {
    showLabel('<?php echo $this->show_label; ?>');
  });
  
  function showLabel(value){
    if(value == 1) {
      document.getElementById('label_text-wrapper').style.display = "inline-block";
      document.getElementById('label_color-wrapper').style.display = "inline-block";
      document.getElementById('label_text_color-wrapper').style.display = "inline-block";
      document.getElementById('label_position-wrapper').style.display = "inline-block";
    } else {
      document.getElementById('label_text-wrapper').style.display = "none";
      document.getElementById('label_color-wrapper').style.display = "none";
      document.getElementById('label_text_color-wrapper').style.display = "none";
      document.getElementById('label_position-wrapper').style.display = "none";
    }
  }

  scriptJquery('.core_admin_main_monetization').parent().addClass('active');
  scriptJquery('.core_admin_main_membership').addClass('active');
</script>
