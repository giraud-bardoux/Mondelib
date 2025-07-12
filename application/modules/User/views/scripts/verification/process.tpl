<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: process.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>
<div class="generic_layout_container layout_main">
	<div class="generic_layout_container layout_middle">
  	<div class="generic_layout_container layout_core_content">
			<?php if(method_exists($this->gateway->getPlugin(),'getGatewayUserForm')): ?>
				<?php 
					$form = $this->gateway->getPlugin()->getGatewayUserForm();
					$form->setAction($this->returnUrl);
					echo $form->render();
				?>
				<?php if($form->getSettings()['receipt']): ?>
					<script type="text/javascript">
						window.addEventListener('load', function(){
							scriptJquery('#file').attr('required',true);
						});
					</script>
				<?php endif; ?>
			<?php else: ?>
        <div class="payment_process_loading text-center">
					<i class="fas fa-spinner fa-spin fa-3x fa-fw"></i>
          <span><?php echo $this->translate("Please do not close tab while we are processing."); ?></span>
        </div>
				<script type="text/javascript">
					function jsonToQueryString(json) {
						return '?' + 
							Object.keys(json).map(function(key) {
									return encodeURIComponent(key) + '=' +
											encodeURIComponent(json[key]);
							}).join('&');
					}

					en4.core.runonce.add(function() {
						var url = '<?php echo $this->transactionUrl ?>';
						var data = <?php echo Zend_Json::encode($this->transactionData) ?>;

						loadAjaxContentApp(url +jsonToQueryString(data));
					});
				</script>
			<?php endif; ?>
		</div>
	</div>
</div>
