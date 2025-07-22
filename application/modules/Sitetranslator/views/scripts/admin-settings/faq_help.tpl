
<script type="text/javascript">
    function faq_show(id) {
        if (scriptJquery('#'+id).css('display') == 'block') {
            scriptJquery('#'+id).css('display','none');
        } else {
            scriptJquery('#'+id).css('display','block');
        }
    }
    en4.core.runonce.add(function () {
    <?php if($this->showFAQ): ?>
    faq_show('<?php echo $this->showFAQ ?>');
    <?php endif; ?>
    });
    
</script>

<div class="admin_sitetranslator_files_wrapper">
    <ul class="admin_sitetranslator_files sitetranslator_faq">	

        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_3');">Which is the preferred language to be considered as base language for translation?</a>
            <div class='faq' style='display: none;' id='faq_3'>
                English is the preferred language to be considered as base language for translation as it contains all the phrases.
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_4');">When do I need to overwrite a file while doing language translation of any selected file?</a>
            <div class='faq' style='display: none;' id='faq_4'>
                Overwriting of file is required in below scenarios while doing language translation: <br /><br />

                1. After installation of new plugin.<br />
                2. After upgradation of already installed plugin. <br />
            </div>
        </li>

        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_5');">What are the various scenarios for which Language Translation of files can be done?</a>
            <div class='faq' style='display: none;' id='faq_5'>
                There are three scenarios for which Language Translation of files can be done: <br /><br />

                Case 1: New Language Pack Creation <br />
                When you want to create a new language pack for your files.<br /><br />

                Case 2: After New Plugin Installation<br />
                When a new plugin is installed on your site, you have to add .csv file in the existing language pack.<br /><br />

                Case 3: Enhancement in Already Installed Plugin
                When any new feature is added in the already installed plugin, you have to update the .CSV file for the existing plugin in the language pack. <br />

            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_6');">What is the limitation for translating the files from one language to multiple languages?</a>
            <div class='faq' style='display: none;' id='faq_6'>
                There is no limitation for translating the files from one language to multiple languages. But, there are few points one needs to consider before starting the translation process which are listed as follows as per two different scenarios:
                <br /><br />
                Scenario 1: Free Trial <br />
                Google provides translation of 150,000,00 words. So, you can translate as many files in any number of languages until you consume these words. <br />
                For example: You want to translate a file with 50,000,00 characters, the possible translation of this file from one language to another is 3. As after translation in 3 languages you will exhaust the allocated 150,000,00 words.<br />
                [Note: Google provides $300 worth Google Cloud Platform services free. Google translation service is one of them. Try to use this efficiently to translate files on your site from one language to another.]<br />
                <br />
                Scenario 2: Paid Version<br />
                There is no limitation for translating the files from one language to multiple languages in case of paid version. It entirely depends on you requirement and accordingly you can pay for the service. <br />For more details about the pricing, please <a href="https://cloud.google.com/translate/pricing#prices_per_month" >click here.</a>

            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_7');">What is the benefit of setting the character limit to 100000 or more?</a>
            <div class='faq' style='display: none;' id='faq_7'>
                To decrease the file translation time, it is better to set the character limit to 100000 or more. 
                Decrease in file translation time will speed up the process of file translation from one language to another.
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_8');">How can I translate the phrases which are not present in any file?
                <br />or<br />
                How can I translate the phrases which were missed during the language translation process?</a>
            <div class='faq' style='display: none;' id='faq_8'>
                To translate the phrases which are not present in any file or were missed during the language translation process, follow below steps:<br /><br />

                1. Go to ‘Add Custom Phrase’ in the admin panel of this plugin.<br />
                2. Select the ‘Source Language’ for the phrase which you want to translate.<br />
                3. Enter the phrase which you want to translate.<br />
                4. Select the ‘Target Language’ in which you want to translate above phrase.<br />
                5. Click on ‘Add Phrase’ to add the translation of the entered phrase.<br />

            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_10');">Some of the phrases of a file did not get translated. What might be the reason behind it?</a>
            <div class='faq' style='display: none;' id='faq_10'>
                <p> There are two scenarios when phrases don’t get translated, which are: <br /><br />

                    1. The phrases are not present in the file which was translated.<br />
                    2. The phrases have been added in the file after its translation.<br /><br />

                    To translate these phrases you can go through the steps mentioned in FAQ 6.            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_9');">Why few texts are not coming completely after performing translation in certain languages?</a>
            <div class='faq' style='display: none;' id='faq_9'>
                <p> After performing translation in certain languages, complete texts no longer appears. This is so because, sometimes a word in one language gets translated to more than one word in another language.
                    This entirely depends on the language in which you have translated the files.<br /><br />
                    The complete content on your site is being translated by ‘Google Translator’ widget and by you manually using this plugin. You don’t have any control over the words translated and provided by the ‘Google Translator’ widget. But, you can modify the words / texts translated using this plugin from their respective files. </p>
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_11');">I have lost the list of special variables. How can I get the list of special variables?</a>
            <div class='faq' style='display: none;' id='faq_11'>
                <p> If you have lost the lost of special variables then you can simply copy the list of special variables from below and paste it in the ‘Global Settings’ → ‘Special Variables’ section.<br /> 
                    Special variables list: <br /><br /> <?php echo htmlentities('[sender_title],[header],[footer],[message],[subject],{item:$subject},{var:$eventname},[sender_email],[sender_name],[page_title],[object_type_name],[COMMUNITY_NAME],[VIEWER_TITLE],[subject_title],[item_title],{item:$object},[name_name],[variable_value],[variable_value_val],{var:$label},{item:$object:$label},{var:$value},[email],[password],%d,<style>,<title>,[object_link],[subscription_title],[subscription_description],[subscription_terms],&#187;,&#171;'); ?>,$type,$count,\n
                </p>
            </div>
        </li>
        <li>
            <a href="javascript:void(0);" onClick="faq_show('faq_12');">I have successfully translated all the CSV files and checked that there are no missing phrases or files for the translation. Still, a few phrases are not translating at user end. What might be the reason behind it?</a>
            <div class='faq' style='display: none;' id='faq_12'>
                <p>Even after successful translation of CSV files, a few phrases do not get translated a user end. The reason behind it is that those phrases are not present in the parent language pack which you have used as source language to translate the CSV files.<br /><br />

                    If you want to translate these phrases then follow below steps:<br />

                    1) Add these phrases in the parent language pack. <br />
                    2) Go to ‘Manage Missing Phrases’ section of the admin panel.<br />
                    3) List down the missing phrases by filtering the source language and target language. New phrases added in the first step will be visible here as missing phrases.<br />
                    4) Start the translation of missing phrases from <a href="<?php echo $this->baseUrl()?>/admin/sitetranslator/translator/phrases">here</a>.<br /><br />

                    This way all the phrases will get translated at user end.

                </p>
            </div>
        </li>
    </ul>
</div>
