<h2>Language Translator Plugin</h2>

<?php if (engine_count($this->navigation)): ?>
<div class='tabs seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
</div>
<?php endif; ?>
<?php if(empty(Engine_Api::_()->getApi('settings','core')->getSetting('sitetranslator.google.api.key'))) : ?>
<div class='clear sitetranslator_settings_form'>
    <div class="tip">
        <span> To start adding phrases from this section, please generate and configure 'Google Translator' API Key. To know about the steps, please <a href="<?php echo $this->baseUrl()?>/admin/sitetranslator/translator/support" target="_blank"> click here</a>. If you want to add phrases in the available language packs of your website then you do not need to configure 'Google Translator' API Key. You can simply add new phrases from ‘edit phrases’ option available <a href="<?php echo $this->baseUrl('/admin/language')?>" target="_blank"> here</a>.
        </span>
    </div>
</div>
<?php else: ?>
<div class='clear sitetranslator_settings_form'>
    <div class='settings'>
        <?php echo $this->form->render($this) ?>
    </div>
</div>

<script type="text/javascript">
    function translatePhrase() { 
        var phrase = document.getElementById('sitetranslator_phrase_key').value;
        if(!phrase){
            alert("Please enter a phrase before starting translation.");
            return ;
        }
        document.getElementById('translate_button').css('display,'none');
        document.getElementById('translate_loading').css('display','inline-block');
        
        var source = document.getElementById('source_language').val();
        var target = document.getElementById('target_language').val();
        //window.location.href= en4.core.baseUrl+'admin/sitetranslator/translator/add-custom-phrase?target='+source+'&phrase='+phrase;
        url = en4.core.baseUrl + 'admin/sitetranslator/translator/translate';
        var request = scriptJquery.ajax({
            'url': url,
            'method': 'post',
            'data': {
                'format': 'json',
                'phrase': phrase,
                'source': source,
                'target': target
            },
            success : function(responseJSON,responseHTML){
                document.getElementById('translate_button').css('display,'block');
                document.getElementById('translate_loading').css('display,'none');
                if(responseJSON.responseCode != 200){
                    alert("Problem in translation , Error Code: "+responseJSON.responseCode+" Error Message :"+responseJSON.responseData);
                    return ;
                }
                document.getElementById("sitetranslator_phrase_value").value = responseJSON.responseData;
                $("submit").style = "background-color:#619dbe;";
                console.log(responseJSON);
                console.log(responseHTML);
            }
        });
        request.send();
    }
    function isTranslated(event){
        if(!document.getElementById("sitetranslator_phrase_value").value || !document.getElementById("sitetranslator_phrase_key").value){
            alert("Phrase/Translated phrase can not be empty");
            event.preventDefault(); 
            return false;
        }
    }
en4.core.runonce.add(function(){ 
var translateButton = document.createElement("button");
    translateButton.setAttribute("onclick","translatePhrase()");
    translateButton.setAttribute("id","translate_button");
    translateButton.innerHTML = "Translate";
    translateButton.style = "float: right; margin-right: 80px; margin-top: 100px;";
    translateButton.type = "button";
var loading = document.createElement("img");
    loading.src = en4.core.baseUrl+"application/modules/Sitetranslator/externals/images/loading.gif";
    loading.setAttribute("id","translate_loading");
    loading.style = "margin-left: 15px;margin-top: -20px;display:none;";
    
    $("sitetranslator_phrase_value-element").appendChild(translateButton);
    $("sitetranslator_phrase_value-element").appendChild(loading);
    
    $("submit").style = "background-color:#7a7a7a;";
    if($("source_language").value === $("target_language").value){
       translateButton.style.display='none'; 
    }
    
});
</script> 
<?php endif; ?>
