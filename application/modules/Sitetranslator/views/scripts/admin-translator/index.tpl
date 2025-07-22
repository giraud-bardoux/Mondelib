<h2>Language Translator Plugin</h2>

<?php if (engine_count($this->navigation)): ?>
    <div class='tabs seaocore_admin_tabs'>
        <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
    </div>
<?php endif; ?>
<?php if(empty(Engine_Api::_()->getApi('settings','core')->getSetting('sitetranslator.google.api.key'))) : ?>
<div class='clear sitetranslator_settings_form'>
    <div class="tip">
      <span>   To start language pack creation & translation process, please generate and configure 'Google Translator' API Key. To know about the steps, please <a href="<?php echo $this->baseUrl()?>/admin/sitetranslator/translator/support" target="_blank"> click here</a> to know the steps.
        <br><br>If you only want to create a new language pack then you do not need to configure 'Google Translator' API Key. You can simply create a new language pack from <a href="<?php echo $this->baseUrl('/admin/language/create')?>" target="_blank">here</a> . 
        </span>
    </div>
</div>
<?php return; ?>
<?php elseif(empty($this->source)) : ?>
 <div class="tip mtip10">
            <span> There are some problem, please check your google translator api key in Global Settings .
            </span> 
 </div>
<?php else: ?>
<div class='clear sitetranslator_settings_form'>
    <div class='settings'>
        <?php echo $this->form->render($this) ?>
    </div>
</div>
<?php endif; ?>

<script type="text/javascript">
    var sizearray = [];
    var i = 0;
    var size =0;
    var sleep = <?php echo $this->sleep ?>;
    var locale = [];
    <?php foreach($this->size as $size): ?>
            sizearray[i++] = <?php echo $size ?>;
    <?php endforeach; ?>
    var j = 0;
    <?php if ($this->locale):?>
      <?php $locales = json_decode($this->locale);
      foreach($locales as $locale): ?>
        locale[j++] = '<?php echo $locale ?>';
      <?php endforeach; ?>
    <?php endif; ?>
    
    function getCheckedItem(element){
        var count = 0;
        var files = document.getElementById(element).querySelectorAll("input[type='checkbox']");
        locale = [];
        for(var i = 0; i < files.length; i++) { 
                    if(files[i].checked) { 
                          count++;
                          if(sizearray[i]>40000){ console.log((sizearray[i]/40000));
                              size= size + sleep*(Math.ceil(sizearray[i]/40000)) - 20;
                          } else {
                              size+=sleep;
                          }
                          locale[i] = files[i].value;
                    }
                    
        }
        size+=count*20;
        console.log(locale);
        return count;
    }
    function showProgressBar(e){
            getCheckedItem('sitetranslator_csv_files-wrapper');
            var sleepPerLanguage = size; 
            var selectedLanguages = getCheckedItem('target_language-wrapper');
            var delay = (sleepPerLanguage*selectedLanguages)/100;
            if(sleepPerLanguage==0){
                return 0;
            }
            Smoothbox.open("<h3>Translation Processing</h3><span> Please wait till translation process is not complete. </span> <br /><div id='myProgress'><div id='myBar'></div></div> <br />  Approximate Remaining Time :  <span id='rest_time' style='display: none'></span> <span id='rest_time_show'></span> seconds");
            document.getElementById("rest_time").innerHTML = selectedLanguages*sleepPerLanguage;
            document.getElementById("rest_time_show").innerHTML = selectedLanguages*sleepPerLanguage;
            var sleepTimer = setInterval(function(){ 
                width=document.getElementById("myBar").style.width.split("%");
                width = Number(width[0]) + 1;
                console.log(Math.ceil(Number((document.getElementById("rest_time").innerHTML)- Number(delay))));
                document.getElementById("myBar").style.width = width.toString()+"%";
                document.getElementById("rest_time").innerHTML = (Number((document.getElementById("rest_time").innerHTML)- Number(delay)));
                document.getElementById("rest_time_show").innerHTML = Math.floor(document.getElementById("rest_time").innerHTML) > 0 ? Math.floor(document.getElementById("rest_time").innerHTML) : 1;
                }, delay*1000);
            setTimeout(function(){
                clearInterval(sleepTimer);
            },(sleepPerLanguage*selectedLanguages)*1000);
            
        
            console.log(selectedLanguages);
            console.log(sleepPerLanguage);
        }
    function changeSoucreFiles(source){
        window.location.href= en4.core.baseUrl+'admin/sitetranslator/translator?source='+source;
    }
    function downloadLanguage(){
        window.location.href= en4.core.baseUrl+'admin/language/export/locale/'+locale[0];
        //for(var i=0; i<locale.length; i++){
        //    window.location.href= en4.core.baseUrl+'admin/language/export/locale/'+locale[i];
        //}
        //window.location.href= en4.core.baseUrl+'admin/sitetranslator/translator/list-languages';
        //return ;
    
    }
    en4.core.runonce.add( function(){ 
        scriptJquery('#'+sitetranslator_csv_files-all).on('click', function() {
            var files = document.getElementById("sitetranslator_csv_files-wrapper").querySelectorAll("input[type='checkbox']");
            if(this.checked){
                for(var i = 0; i < files.length; i++) {
                    files[i].checked = true;   
                }
            } else {
                for(var i = 0; i < files.length; i++) {
                    files[i].checked = false;   
                }
            }
        });
        scriptJquery('#'+target_language-all').on('click', function() {
            var files = document.getElementById("target_language-wrapper").querySelectorAll("input[type='checkbox']");
            if(this.checked){
                for(var i = 0; i < files.length; i++) {
                    files[i].checked = true;   
                }
            } else {
                for(var i = 0; i < files.length; i++) {
                    files[i].checked = false;   
                }
            }
        });
        
      <?php if(!empty($this->translationSuccess)): ?>
                var baseUrl = '<?php echo $this->baseUrl() ?>'+'/admin/sitetranslator/translator';
                var load = "document.location=\""+baseUrl+"\"";
                var load_language = "document.location=\""+'<?php echo $this->baseUrl() ?>'+'/admin/language'+"/\"";
                console.log(locale.length);
                if(locale.length > 1){
                    var content = "<a onclick='parent.Smoothbox.close()' href='javascript:void(0)' class='sitetranslator_close_popup' title='Close' ></a><div style='text-align:center; font-size:16px; margin-top:20px'> You have successfully translated new language pack.  <br /> <br /> <button onclick='"+load_language+"; parent.Smoothbox.close()'> View In Language </button><br /><br /><button onclick='"+load+"; parent.Smoothbox.close()'> Start New Translation </button></div>";
                } else {
                    var content = "<a onclick='parent.Smoothbox.close()' href='javascript:void(0)' class='sitetranslator_close_popup' title='Close' ></a><div style='text-align:center; font-size:16px; margin-top:20px'> You have successfully translated new language pack.  <br /> <br /> <button onclick='downloadLanguage(); parent.Smoothbox.close()'> Download To See </button> <button onclick='"+load_language+"; parent.Smoothbox.close()'> View In Language </button><br /><br /><button onclick='"+load+"; parent.Smoothbox.close()'> Start New Translation </button></div>";
                }
                Smoothbox.open(content); 
      <?php endif; ?>
    });
</script>