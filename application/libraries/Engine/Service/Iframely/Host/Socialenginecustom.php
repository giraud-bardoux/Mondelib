<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Service_Iframely_Host_Socialenginecustom
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Exception.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Service_Iframely_Host_Socialenginecustom
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Engine_Service_Iframely_Host_Socialenginecustom extends Engine_Service_Iframely_Host {

  /**
   * Constructor
   *
   * @param array $options
   */
  public function __construct($options = array()) {
  
		parent::__construct($options);
		$request = new Zend_Controller_Request_Http();
		if (!defined('_ENGINE_ADMIN_NEUTER'))
			$this->getHttpClient()->setHeaders('host', $request->getHttpHost());
  }
  
  public function get($URL, $TYPE = null) {
		return $this->getLinkData($URL, $TYPE);
  }
  
	public function geLinkContents($URL) {
		$ch = curl_init($URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.71 Safari/537.36');
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
		//curl_setopt($ch, CURLOPT_ENCODING, '');
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	
	public function getLinkData($uri, $TYPE = null) {
	
		$doc = new DOMDocument("1.0", 'utf-8');
		$html = $this->geLinkContents($uri);

		$encoding = 'utf-8';
		preg_match('/<html(.*?)>/i', $html, $regMatches);
		preg_match('/<meta[^<].*charset=["]?([\w-]*)["]?/i', $html, $charSetMatches);
		if (isset($charSetMatches[1])) {
			$encoding = $charSetMatches[1];
		} elseif(isset($regMatches[1])) {
			preg_match('/lang=["|\'](.*?)["|\']/is', $regMatches[1], $languages);
			if(isset($languages[1]) && in_array($languages[1], ['uk'])) {
				$encoding = 'Windows-1251';
			}
		}

		$contentType = '<meta http-equiv="Content-Type" content="text/html; charset=' . $encoding . '">';
		$html = str_replace('<head>', '<head>' . $contentType, $html);

		if (function_exists('mb_convert_encoding')) {
			@$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', $encoding));
		} else {
			@$doc->loadHTML($html);
		}
		
		$metaList = $doc->getElementsByTagName("meta");
		foreach ($metaList as $iKey => $meta) {
			$type = $meta->getAttribute('property');
			$content = $meta->getAttribute('content');
			if(empty($type)) {
				$type = $meta->getAttribute('name');
			}
			$iframely[$type] = $content;
		}

		$information = array('thumbnail' => '', 'title' => '', 'description' => '', 'duration' => '');
		
		//OG Title
		if(!empty($iframely['og:title'])) {
			$information['meta']['title'] = $iframely['og:title'];
		} else {
			$titleList = $doc->getElementsByTagName("title");
			if ($titleList->length > 0) {
				$information['meta']['title'] = $titleList->item(0)->nodeValue;
			} else {
				$information['meta']['title'] = '';
			}
		}
		
		//Site name
		if(!empty($iframely['og:site_name'])) {
			$information['meta']['site_name'] = $iframely['og:site_name'];
		} else {
			$information['meta']['site_name'] = 'Embed';
		}
		
		//OG Description
		if(!empty($iframely['og:description'])) {
			$information['meta']['description'] = $iframely['og:description'];
		} else {
			$titleList = $doc->getElementsByTagName("description");
			if ($titleList->length > 0) {
				$information['meta']['description'] = $titleList->item(0)->nodeValue;
			} else {
				$information['meta']['description'] = '';
			}
		}
		
		//Pass player variable
		$information['links']['player'] = true;
		
		//Get OG Image
		if (!empty($iframely['og:image'])) {
			$information['links']['thumbnail'][0]['href'] = $iframely['og:image'];
		}

		//Get video duration for Dailymotion and Vimeo for special case
		if(preg_match('/dailymotion/',$uri)) {
			$information['meta']['duration'] = isset($iframely['video:duration']) ? $iframely['video:duration'] : (isset($iframely['duration']) ? $iframely['duration'] : '');
		} elseif (preg_match('/vimeo/', $uri)) {
			$aScript = $doc->getElementsByTagName('script');
			$iVimeoDuration = 0;
			foreach($aScript as $script) {
				if(preg_match('/(.*?)duration":{"raw":(.*?),/',$script->textContent, $aHtmlMatch)) {
					$iVimeoDuration = (int)$aHtmlMatch[2];
					break;
				}
			}
			if(!empty($iVimeoDuration)) {
				$information['meta']['duration'] = $iVimeoDuration;
			}
		}
		if($TYPE == 'video' && empty($iframely['og:video:url'])) {
      $information['links']['player'] = false;
      return $information;
    } 
		//Get OG Embed URL
		$embedUrl = !empty($iframely['og:video:url']) ? $iframely['og:video:url'] : $uri;
		$information['html'] = '<iframe width="480" height="270" src="'.$embedUrl.'" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
		
		return $information;
	}
}
