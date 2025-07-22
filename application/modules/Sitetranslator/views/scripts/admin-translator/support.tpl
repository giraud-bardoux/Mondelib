<h2>Language Translator Plugin</h2>

<?php if (engine_count($this->navigation)): ?>
<div class='tabs seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
</div>
<?php endif; ?>



<p>To use this plugin\'s features at maximum extent, below are some helpful videos along with other content. Go through this section to get information about the Google Translator API Key generation, configuration of Google Translator API, Google price chart for translating the files in different languages and get to know about a few more features of this plugin in much more detailed manner.</p>
<br/>
<ul class="admin_sitetranslator_files sitetranslator_faq mtop15">
    <li>
        <p class="sitetranslator_bold">How this plugin works?</p>

        <div class="faq">
            To know about the functionality of this plugin in detail, please <a href="https://www.socialengineaddons.com/socialengine-language-translator-plugin#HowItWorks" target="_blank">click here</a>
         </div>
    </li>
    <li>
        <p class="sitetranslator_bold">How can I generate Google Translator API Key?</p>

        <div class="faq">
            1. Go to: <a href="https://console.developers.google.com/apis/library" target="_blank">https://console.developers.google.com/apis/library</a> <br />
            2.  Go to ‘Select a project’. Click on ‘+’ to create a project. [Note: Don’t include space and special characters in the name of the project.] <br />
            3. Enable ‘Translation API’ for the project you have created. To do so, follow below steps: <br />
            &nbsp;&nbsp;&nbsp;&nbsp;3.1. Search ‘Translation API’ from the list of all APIs. Click on ‘Translation API’.<br />
            &nbsp;&nbsp;&nbsp;&nbsp;3.2. Click on ‘Enable’ → ‘Enable Billing’ → ‘Create Billing Account’. [Note: If you have not registered your Billing Account for any other Google services then only this process needs to be configured otherwise it will use already configured Billing Account.]<br />
            &nbsp;&nbsp;&nbsp;&nbsp;3.3. Complete the configuration of your Billing Account. <br />
            4. Go to ‘Credentials’ on the left panel of your screen. <br />
            5. Now, click on ‘Create Credentials’ and select ‘API Key’. [Note: Make sure you are creating these credentials for the project you have created above.] <br />
            6. Select and copy the ‘API Key’ and paste it in the ‘Global Settings’ → ‘Google Translator API Key’ of the admin panel section of this plugin.<br />
            
            <br /><iframe width="650" height="350" src="https://www.youtube.com/embed/4DpEz1hgXYY" frameborder="0" allowfullscreen></iframe>
        </div>
    </li>
    <li>
        <p class="sitetranslator_bold">What is the configuration of Google Translator API?</p>

        <div class="faq">
            1. Configuration of Google Translator API should be as follows: <br />
            &nbsp;&nbsp;&nbsp;&nbsp;Characters per day: 30,00,000 minimum <br />
            &nbsp;&nbsp;&nbsp;&nbsp;Characters per 100 seconds per user: 1,00,000 minimum<br /><br />

            2. To set up the above configuration, follow below steps:<br />

            &nbsp;&nbsp;&nbsp;&nbsp;1. Go to https://console.developers.google.com/apis/library.<br />
            &nbsp;&nbsp;&nbsp;&nbsp;2. Go to ‘Dashboard’ on the left panel of your screen.<br />
            &nbsp;&nbsp;&nbsp;&nbsp;3. Click on ‘Google Cloud Translation API’ → ‘Quotas’.<br />
            &nbsp;&nbsp;&nbsp;&nbsp;4. Set ‘Characters per day’ and ‘Characters per 100 seconds per user’.<br />
               
            <br /><iframe width="650" height="350" src="https://www.youtube.com/embed/aZGx33ADaJA" frameborder="0" allowfullscreen></iframe>
        </div>
    </li>
     <li>
        <p class="sitetranslator_bold">What is the price chart for translating the files in different languages?'</p>

        <div class="faq">
            Google provides $300 worth Google Cloud Platform services free. Google translation service is one of them. Try to use this efficiently to translate files on your site from one language to another.<br /><br />

            If you have utilised the $300 worth of Google Cloud Platform services then to continue using these services you have to start your billing cycle. For details about the pricing, please <a href="https://cloud.google.com/translate/pricing#prices_per_month" target="_blank">click here.</a>

         </div>
    </li>
     <li>
        <p class="sitetranslator_bold">From where I can get answers to some other questions that I have related to this plugin?</p>

        <div class="faq">
            Go to <a href="<?php echo $this->baseUrl() ?>/admin/sitetranslator/settings/faqs"> FAQ </a> section available in the admin panel of this plugin to get all the information related to this plugin.  
            
        </div>
    </li>
    <li>
        <p class="sitetranslator_bold">I want detailed information about different features and functionality of Language Translator plugin, from where I can get the same?</p>

        <div class="faq">
            To get detailed information about Language Translator plugin, go to : <br />
            <a href="https://www.socialapps.tech/socialengine-language-translator-plugin" target="_blank">https://www.socialapps.tech/socialengine-language-translator-plugin</a>  
            
        </div>
    </li>
    <li>
        <p class="sitetranslator_bold">My queries has not been resolved by above questions, what should I do?</p>

        <div class="faq">
            If you still have any other queries left, please file a support ticket from the "Support" section of your Client Area on SocialApps.tech (<a href="http://www.socialapps.tech/user/login" target="_blank">http://www.socialapps.tech/user/login</a>) so that our support team could look into this. Purchase of this Software, entitles the Licensee of 60 days technical support from SocialApps.tech. If your support duration has expired, then please subscribe to our <a href="http://www.ssocialapps.tech/subscriptions" target="_blank">"Basic"</a> or <a href="http://www.ssocialapps.tech/subscriptions" target="_blank">"Plus"</a> Subscription Plans. <br />
        </div>
    </li>


</ul>