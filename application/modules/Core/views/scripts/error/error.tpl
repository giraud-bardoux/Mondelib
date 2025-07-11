<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: error.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<script type="text/javascript">
  var goToContactPageAfterError = function() {
    var url = '<?php echo $this->url(array('controller' => 'help', 'action' => 'contact'), 'default', true) ?>';
    var name = '<?php echo urlencode(base64_encode($this->errorName)) ?>';
    var loc = '<?php echo urlencode(base64_encode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'])) ?>';
    var time = '<?php echo urlencode(base64_encode(time())) ?>';
    loadAjaxContentApp(url + '?name=' + name + '&loc=' + loc + '&time=' + time);
  }
</script>


<div class="error_page_main">
<div id="content">
  <div class="error_page_img">
    <img src="./application/modules/Core/externals/images/error/error-img.png" alt="Error">
  </div>
  <div class="error_page_right">
    <h3> We're sorry!</h3>                  
    <span class="caption">We are currently experiencing some technical issues. </br> Please try again later.</span>
    <div id="error-code">
      <p class="error_code"><?php printf($this->translate('Error Code: %s'), $this->error_code); ?></p>
      <?php if( isset($this->error) && 'development' == APPLICATION_ENV ): ?>
        <pre><?php echo $this->error; ?></pre>
      <?php endif; ?>
    </div>
  </div>
  </div>
</div>



<style type="text/css">
  #global_content{
    padding:0;
    width:calc(100% - 30px);
  }
  .error_page_main *{
    font-family: roboto, arial, sans-serif;
    margin: 0px;
    padding: 0px;
  }
  .error_page_main{
    background-image:url(./application/modules/Core/externals/images/error/error-bg.jpg);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
    background-attachment: fixed;
    padding:50px 20px;
  }      
  #content{
    max-width:1400px;
    width:100%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap:wrap;
    margin:auto;
  }
  .error_page_img{
    width: 50%;
    padding-right: 30px;
  }
  .error_page_img img{
    max-width: 100%;
    -webkit-animation: mover 2s infinite  alternate;
    animation: mover 2s infinite  alternate;
  }
  @-webkit-keyframes mover {
    0% { transform: translateY(0); }
    100% { transform: translateY(-15px); }
  }
  @keyframes mover {
    0% { transform: translateY(0); }
    100% { transform: translateY(-15px); }
  }
  .error_page_right{
    width:50%;
  }
  .error_page_right h3{
    font-size: 35px;
    margin-bottom: 10px;
    color: #1D3666;
  }
  .error_page_right .caption{
    display: block;
    font-size: 22px;
    color: #2B2A2A;
    margin-bottom: 15px;
    line-height:1.5;
  }
  .error_page_right #error-code{
    color: #1d3666;
    word-break: break-word;
    line-height: 150%;
  }
  .error_page_right #error-code .error_code{
    font-weight:bold;
    margin-bottom:10px;
  }
  .error_page_right #error-code pre{
    max-height:350px;
    overflow: auto;
  }
  @media (max-width:1199px){
    #content{
      width: 100%;
    }
    .error_page_main{
      padding:20px 15px;
    }
  }
  @media (max-width:991px){
    #content{
      flex-direction: column;
    }
    .error_page_img{
      width: 100%;
      text-align: center;
      margin-bottom: 25px;
      margin-right: 0;
    }
    .error_page_img img{
      margin: 0 auto;
      max-width:400px;
    }
    .error_page_right{
      width:100%;
    }
    .error_page_right h3,
    .error_page_right .caption,
    .error_page_right #error-code .error_code{
      text-align:center !important;
    }
  }
  @media (max-width:767px){
    .error_page_right h3{
      font-size: 25px;
      margin-bottom: 6px;
    }
    .error_page_right .caption{
      font-size: 18px;
      line-height: 25px;
      margin-bottom: 10px;
    }
  }
</style>