<h2><?php echo $this->translate('Language Translator Plugin') ?></h2>

<?php if (engine_count($this->navigation)): ?>
<div class='tabs seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
</div>
<?php endif; ?>
<div> <?php echo $this->translate("This page list down all the phrases which were missed during the translation process of a file. Entering criteria into the filter fields will help you find specific missed translated phrases."); ?> </div>
<br/>
<br />
<?php if(empty(Engine_Api::_()->getApi('settings','core')->getSetting('sitetranslator.google.api.key'))) : ?>
<div class='clear sitetranslator_settings_form'>
    <div class="tip">
        <span>  To start translation process, please generate and configure ‘Google Translator API Key. Please <a href="<?php echo $this->baseUrl()?>/admin/sitetranslator/translator/support" target="_blank"> click here</a> to know the steps.
        </span>
    </div>
</div>
<?php endif; ?>
<?php if(!empty($this->missedFiles)):  ?>
<div id="dismissintegration_modules">
            <div class="sitetranslator_notice">
                <div class="sitetranslator_notice-icon">
                    <img src="<?php echo $this->layout()->staticBaseUrl ?>application/modules/Sitetranslator/externals/images/notice.png" alt="Notice" />
                </div>
                <div style="float:right;">
                     <?php $url = $this->url(array('module' => 'sitetranslator', 'controller' => 'translator','action'=>'index','target'=>$this->target_language,'selected' => 1)); ?>
                    <button onclick="document.location='<?php echo $url ?>'">Translate</button>
                </div>
                <div class="sitetranslator_notice-text ">
                    Phrases belonging to single or multiple files are not translated completely. To complete the translation of these phrases, please click on Translate button.
                </div>  
            </div>
        </div>
<?php endif; ?>
<div class='filter'>
    <?php echo $this->form->render($this) ?>
</div>


<script type="text/javascript">
    function changeLanguages(source, target, file) {
        window.location.href = en4.core.baseUrl + 'admin/sitetranslator/translator/phrases?source_language=' + source + '&target_language=' + target + '&csv_files=' + file;
    }
    function phrasesShowHide(element, id) {
        if (scriptJquery('#'+id).css('display') == 'block') {
            scriptJquery('#'+id).css('display','none');
            element.removeClass("sitetranslator_collapse");
        } else {
            scriptJquery('#'+id).css('display','block');
            element.addClass("sitetranslator_collapse");
        }
    }
    function phrasesFileShowHide(element, id) {
        if (scriptJquery('#'+id).css('display') == 'block') {
            scriptJquery('#'+id).css('display','none');
        } else {
            scriptJquery('#'+id).css('display','block');
        }
    }
</script>
<style type="text/css">
  .sitetranslator_admin_order_list .sitetranslator_ul > li, 
  .sitetranslator_admin_order_list .sitetranslator_ul > li > div {
    float: none;
  }
  .sitetranslator_admin_order_list .sitetranslator_ul > li:hover {
    background-color: transparent;
  }
  .sitetranslator_admin_order_list ul.sitetranslator_ul > li > div {
    float: none;
  }
</style>
<br /><br /><br />
<?php $phrases = engine_array_filter($this->phrases); ?>
<?php if(empty($phrases)): ?>
<div class="tip">
    <span> All phrases has been translated successfully!
    </span> 
</div>
<?php else: ?>
  <?php if( !empty($this->missedFiles) ): ?>
    <div class="sitetranslator_completely_missed_files">
      <div class="list_head" onclick="phrasesFileShowHide(this, 'phrases_missing_files')" >
        <div style="width:40%">
          <a href="javascript:void(0)" style="color: #fff; font-weight: bold;">Completely Missed Files (<?php echo engine_count($this->missedFiles) ?>)</a>
        </div>
      </div>

      <ul id='phrases_missing_files' style="display: none">
        <?php foreach( $this->missedFiles as $fileName ) : ?>
          <li class='admin_table_bold'>
            <div class="head_title">
              <?php echo $fileName ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <br /><br /><br />
  <?php endif; ?>

  <div class="sitetranslator_admin_order_list">
    <div class="list_head">
      <div style="width:40%; text-align: left">
        Missed Phrases
      </div>
    </div>
    <ul class="sitetranslator_ul">
        <?php $ii =0; foreach( $this->phrases as $file => $fileValue ) : ?>
          <?php if( empty($fileValue) ) : continue;
          endif;
          ?>
          <li style="color: #dccece" class='admin_table_bold'>
            <div class="head_title" onclick="phrasesShowHide(this, 'phrases_of_<?php echo $file ?>')">
              <div style="width:33%">
                <?php echo $file ?>
              </div>
            </div>
              <ul id='phrases_of_<?php echo $file ?>' style="display: none">
                <?php
                $count = 1;
                foreach( $fileValue as $key => $value ) :
                  ?>
                  <li class='admin_table_bold'>
                   <span class='sitetranslator_phrase'>
                      <span>
                        <?php if( is_array($value) ): ?>
                            <?php echo htmlentities($value[0]) . ";" . htmlentities($value[1]) . "" ?>
                          <?php else: ?>
                            <?php echo htmlentities($value) ?>
                          <?php endif; ?>
                      </span>
                     <span class=""><?php echo htmlentities($key) ?></span>
                    </span>
                  </li>
              <?php endforeach; ?>
              </ul> 
          </li>
      <?php endforeach; ?>
      </ul>

  </div>
<?php endif; ?>