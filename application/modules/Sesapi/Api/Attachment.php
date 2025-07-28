<?php

/**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesapi
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: Attachement.php 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
class Sesapi_Api_Attachment extends Core_Api_Abstract {
    public function onAttachLink($data)
    {
        try {
            $viewer = Engine_Api::_()->user()->getViewer();
            if( Engine_Api::_()->sesapi()->hasSubject() ) {
                $subject = Engine_Api::_()->sesapi()->getSubject();
                if( $subject->getType() != 'user' ) {
                    $data['parent_type'] = $subject->getType();
                    $data['parent_id'] = $subject->getIdentity();
                }
            }

            // Filter HTML
            $filter = new Zend_Filter();
            $filter->addFilter(new Engine_Filter_Censor());
            $filter->addFilter(new Engine_Filter_HtmlSpecialChars());
            if( !empty($data['title']) ) {
                $data['title'] = $filter->filter($data['title']);
            }
            if( !empty($data['description']) ) {
                $data['description'] = $filter->filter($data['description']);
            }

            $link = $this->createLink($viewer, $data);
        } catch( Exception $e ) {
            Engine_Api::_()->getApi('response','sesapi')->sendResponse(array('error'=>'1','error_message'=>$e->getMessage(), 'result' => array()));
        }
        return $link;
    }
    public function createLink(Core_Model_Item_Abstract $owner, $data)
    {
        $table = Engine_Api::_()->getDbTable('links', 'core');

        if( empty($data['parent_type']) || empty($data['parent_id']) )
        {
            $data['parent_type'] = $owner->getType();
            $data['parent_id'] = $owner->getIdentity();
        }
        $uri = $data["uri"];
        $client = new Zend_Http_Client($uri, array(
            'maxredirects' => 2,
            'timeout'      => 10,
        ));
        // Try to mimic the requesting user's UA
        $client->setHeaders(array(
            'User-Agent' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'X-Powered-By' => 'Zend Framework'
        ));
        $response = $client->request();
        $result =  Engine_Api::_()->getApi('attachment','sesapi')->previewHtml($uri, $response);
        $data = array_merge($result['link'],$data);
        if(empty($data["description"]))
            $data["description"] = $data["title"];
        $link = $table->createRow();
        $link->setFromArray($data);
        $link->owner_type = $owner->getType();
        $link->owner_id = $owner->getIdentity();
        $link->save();

        // Now try to create thumbnail
        $thumbnail = (string) @$data['images'];
        $thumbnail_parsed = @parse_url($thumbnail);
        //$ext = @ltrim(strrchr($thumbnail_parsed['path'], '.'), '.');
        //$link_parsed = @parse_url($link->uri);

        // Make sure to not allow thumbnails from domains other than the link (problems with subdomains, disabled for now)
        //if( $thumbnail && $thumbnail_parsed && $thumbnail_parsed['host'] === $link_parsed['host'] )
        //if( $thumbnail && $ext && $thumbnail_parsed && engine_in_array($ext, array('jpg', 'jpeg', 'gif', 'png')) )
        if( $thumbnail && $thumbnail_parsed )
        {
            $tmp_path = APPLICATION_PATH . '/temporary/link';
            $tmp_file = $tmp_path . '/' . md5($thumbnail);

            if( !is_dir($tmp_path) && !mkdir($tmp_path, 0777, true) ) {
                throw new Core_Model_Exception('Unable to create tmp link folder : ' . $tmp_path);
            }

            $src_fh = fopen($thumbnail, 'r');
            $tmp_fh = fopen($tmp_file, 'w');
            stream_copy_to_stream($src_fh, $tmp_fh, 1024 * 1024 * 2);
            fclose($src_fh);
            fclose($tmp_fh);

            if( ($info = getimagesize($tmp_file)) && !empty($info[2]) ) {
                $ext = Engine_Image::image_type_to_extension($info[2]);
                $thumb_file = $tmp_path . '/thumb_'.md5($thumbnail) . '.'.$ext;

                $image = Engine_Image::factory();
                $image->open($tmp_file)
                    ->resize(120, 240)
                    ->write($thumb_file)
                    ->destroy();

                $thumbFileRow = Engine_Api::_()->storage()->create($thumb_file, array(
                    'parent_type' => $link->getType(),
                    'parent_id' => $link->getIdentity()
                ));

                $link->photo_id = $thumbFileRow->file_id;
                $link->save();

                @unlink($thumb_file);
            }

            @unlink($tmp_file);
        }

        return $link;
    }

    public function previewHtml($uri)
    {
        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        // clean URL for html code
        $uri = trim(strip_tags($uri));
        $displayUri = $uri;
        $info = parse_url($displayUri);
        if( !empty($info['path']) ) {
            $displayUri = str_replace($info['path'], urldecode($info['path']), $displayUri);
        }

        $view->url = !empty($displayUri) ? Engine_String::convertUtf8($displayUri) : '';
        $view->title = '';
        $view->description = '';
        $view->thumb = null;
        $view->imageCount = 0;
        $view->images = array();
        try {
          if(strpos($uri,'youtubevideo') !== false || strpos($uri,'https://youtu.be/') !== false || strpos($uri,'youtube') !== false) {
            $information = Engine_Api::_()->core()->handleIframelyInformation($uri);
            $result['link']['title'] = Engine_String::convertUtf8($information['title']);
            $result['link']['description'] = Engine_String::convertUtf8($information['description']);
            $result['link']['thumb'] = $information['thumbnail'] ? $information['thumbnail'] : "";
            $result['link']['medium'] = "";
            $result['link']['images'] = $information['thumbnail'] ? $information['thumbnail'] : "";
            return $result;
            //$this->_getFromClientRequest($uri);
          } else{
            $this->_getFromIframely($config, $uri);
          }
        } catch( Exception $e ) {
            throw $e;
        }
        $result['link']['title'] = Engine_String::convertUtf8($view->title);
        $result['link']['description'] = Engine_String::convertUtf8($view->description);
        $result['link']['thumb'] = ($view->thumb ? $view->thumb : "");
        $result['link']['medium'] = "";
        $result['link']['images'] = engine_count($view->images) > 0 ? $view->images[0] : "";
        return $result;
    }

    protected function _getFromIframely($config, $uri)
    {   
        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        $uriHost = Zend_Uri::factory($uri)->getHost();

        $config = Engine_Api::_()->getApi('settings', 'core')->core_iframely;
        $iframely = Engine_Iframely::factory($config)->get($uri);
        if(engine_in_array($uriHost, array('youtube.com','www.youtube.com','youtube','youtu.be')) && empty($iframely['meta'])){
            $iframely = $this->YoutubeVideoInfo($uri);
            $images = array();
            if( !empty($iframely['thumbnail']) ) {
                $images[] = $iframely['thumbnail'];
            }
            if( !empty($iframely['title']) ) {
                $view->title = $iframely['title'];
            }
            if( !empty($iframely['description']) ) {
                $view->description = $iframely['description'];
            }
        } else {
            $images = array();
            if( !empty($iframely['links']['thumbnail']) ) {
                $images[] = $iframely['links']['thumbnail'][0]['href'];
            }
            if( !empty($iframely['meta']['title']) ) {
                $view->title = $iframely['meta']['title'];
            }
            if( !empty($iframely['meta']['description']) ) {
                $view->description = $iframely['meta']['description'];
            }
        }

        $view->imageCount = engine_count($images);
        $view->images = $images;
        $allowRichHtmlTyes = array(
            'player',
            'image',
            'reader',
            'survey',
            'file'
        );
        if(!empty($iframely['links']))
        $typeOfContent = array_intersect(array_keys($iframely['links']), $allowRichHtmlTyes);
        if( $typeOfContent ) {
            $view->richHtml = $iframely['html'];
        }
    }

    protected function _getFromClientRequest($uri)
    {
        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        $info = parse_url($uri);
        if( !empty($info['path']) ) {
            $path = urldecode($info['path']);
            foreach( explode('/', $info['path']) as $path ) {
                $paths[] = urlencode($path);
            }
            $uri = str_replace($info['path'], join('/', $paths), $uri);
        }
        $client = new Zend_Http_Client($uri, array(
            'maxredirects' => 2,
            'timeout' => 10,
        ));
        // Try to mimic the requesting user's UA
        $client->setHeaders(array(
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.2 Safari/605.1.15',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'X-Powered-By' => 'Zend Framework'
        ));
        $response = $client->request();
        // Get content-type
        list($contentType) = explode(';', $response->getHeader('content-type'));
        $view->contentType = $contentType;
        // Handling based on content-type
        switch( strtolower($contentType) ) {
            // Images
            case 'image/gif':
            case 'image/jpeg':
            case 'image/jpg':
            case 'image/tif': // Might not work
            case 'image/xbm':
            case 'image/xpm':
            case 'image/png':
            case 'image/bmp': // Might not work
                $this->_previewImage($uri, $response);
                break;
            // HTML
            case '':
            case 'text/html':
                $this->_previewHtml($uri, $response);
                break; 
            // Plain text
            case 'text/plain':
                $this->_previewText($uri, $response);
                break;
            // Unknown
            default:
                break;
        }
    }

    protected function _previewImage($uri, Zend_Http_Response $response)
    {
        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        $view->imageCount = 1;
        $view->images = array($uri);
    }

    protected function _previewText($uri, Zend_Http_Response $response)
    {
        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        $body = $response->getBody();
        if( preg_match('/charset=([a-zA-Z0-9-_]+)/i', $response->getHeader('content-type'), $matches) ||
            preg_match('/charset=([a-zA-Z0-9-_]+)/i', $response->getBody(), $matches) ) {
            $charset = trim($matches[1]);
        } else {
            $charset = 'UTF-8';
        }
        // Reduce whitespace
        $body = preg_replace('/[\n\r\t\v ]+/', ' ', $body);
        $view->title = substr($body, 0, 63);
        $view->description = substr($body, 0, 255);
    }

    protected function _previewHtml($uri, Zend_Http_Response $response)
    {
        $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
        $body = $response->getBody();
        $body = trim($body);
        if( preg_match('/charset=([a-zA-Z0-9-_]+)/i', $response->getHeader('content-type'), $matches) ||
            preg_match('/charset=([a-zA-Z0-9-_]+)/i', $response->getBody(), $matches) ) {
            $view->charset = $charset = trim($matches[1]);
        } else {
            $view->charset = $charset = 'UTF-8';
        }
        if( function_exists('mb_convert_encoding') ) {
            $body = mb_convert_encoding($body, 'HTML-ENTITIES', $charset);
        }
        // Get DOM
        if( class_exists('DOMDocument') ) {
            $dom = new Zend_Dom_Query($body);
        } else {
            $dom = null; // Maybe add b/c later
        }
        $title = null;
        if( $dom ) {
            $titleList = $dom->query('title');
            if(is_countable($titleList) && engine_count($titleList) > 0 ) {
                $title = trim($titleList->current()->textContent);
            }
        }
        $view->title = $title;
        $description = null;
        if( $dom ) {
            $descriptionList = $dom->queryXpath("//meta[@name='description']");
            // Why are they using caps? -_-
            if( engine_count($descriptionList) == 0 ) {
                $descriptionList = $dom->queryXpath("//meta[@name='Description']");
            }
            // Try to get description which is set under og tag
            if( engine_count($descriptionList) == 0 ) {
                $descriptionList = $dom->queryXpath("//meta[@property='og:description']");
            }
            if( engine_count($descriptionList) > 0 ) {
                $description = trim($descriptionList->current()->getAttribute('content'));
            }
        }
        $view->description = $description;
        $thumb = null;
        if( $dom ) {
            $thumbList = $dom->queryXpath("//link[@rel='image_src']");
            $attributeType = 'href';
            if(engine_count($thumbList) == 0 ) {
                $thumbList = $dom->queryXpath("//meta[@property='og:image']");
                $attributeType = 'content';
            }
            if( engine_count($thumbList) > 0 ) {
                $thumb = $thumbList->current()->getAttribute($attributeType);
            }
        }
        $view->thumb = $thumb;
        $medium = null;
        if( $dom ) {
            $mediumList = $dom->queryXpath("//meta[@name='medium']");
            if( engine_count($mediumList) > 0 ) {
                $medium = $mediumList->current()->getAttribute('content');
            }
        }
        $view->medium = $medium;
        // Get baseUrl and baseHref to parse . paths
        $baseUrlInfo = parse_url($uri);
        $baseUrl = null;
        $baseHostUrl = null;
        $baseUrlScheme = $baseUrlInfo['scheme'];
        $baseUrlHost = $baseUrlInfo['host'];
        if( $dom ) {
            $baseUrlList = $dom->query('base');
            if( $baseUrlList && engine_count($baseUrlList) > 0 && $baseUrlList->current()->getAttribute('href') ) {
                $baseUrl = $baseUrlList->current()->getAttribute('href');
                $baseUrlInfo = parse_url($baseUrl);
                if( !isset($baseUrlInfo['scheme']) || empty($baseUrlInfo['scheme']) ) {
                    $baseUrlInfo['scheme'] = $baseUrlScheme;
                }
                if( !isset($baseUrlInfo['host']) || empty($baseUrlInfo['host']) ) {
                    $baseUrlInfo['host'] = $baseUrlHost;
                }
                $baseHostUrl = $baseUrlInfo['scheme'] . '://' . $baseUrlInfo['host'] . '/';
            }
        }
        if( !$baseUrl ) {
            $baseHostUrl = $baseUrlInfo['scheme'] . '://' . $baseUrlInfo['host'] . '/';
            if( empty($baseUrlInfo['path']) ) {
                $baseUrl = $baseHostUrl;
            } else {
                $baseUrl = explode('/', $baseUrlInfo['path']);
                array_pop($baseUrl);
                $baseUrl = join('/', $baseUrl);
                $baseUrl = trim($baseUrl, '/');
                $baseUrl = $baseUrlInfo['scheme'] . '://' . $baseUrlInfo['host'] . '/' . $baseUrl . '/';
            }
        }
        $images = array();
        if( $thumb ) {
            $images[] = $thumb;
        }
        if( $dom ) {
            $imageQuery = $dom->query('img');
            foreach( $imageQuery as $image ) {
                $src = $image->getAttribute('src');
                // Ignore images that don't have a src
                if( !$src || false === ($srcInfo = @parse_url($src)) ) {
                    continue;
                }
                $ext = ltrim(strrchr($src, '.'), '.');
                // Detect absolute url
                if( strpos($src, '/') === 0 ) {
                    // If relative to root, add host
                    $src = $baseHostUrl . ltrim($src, '/');
                } elseif( strpos($src, './') === 0 ) {
                    // If relative to current path, add baseUrl
                    $src = $baseUrl . substr($src, 2);
                } elseif( !empty($srcInfo['scheme']) && !empty($srcInfo['host']) ) {
                    // Contians host and scheme, do nothing
                } elseif( empty($srcInfo['scheme']) && empty($srcInfo['host']) ) {
                    // if not contains scheme or host, add base
                    $src = $baseUrl . ltrim($src, '/');
                } elseif( empty($srcInfo['scheme']) && !empty($srcInfo['host']) ) {
                    // if contains host, but not scheme, add scheme?
                    $src = $baseUrlInfo['scheme'] . ltrim($src, '/');
                } else {
                    // Just add base
                    $src = $baseUrl . ltrim($src, '/');
                }

                if( !engine_in_array($src, $images) ) {
                    $images[] = $src;
                }
            }
        }
        // Unique
        $images = array_values(array_unique($images));
        // Truncate if greater than 20
        if( engine_count($images) > 30 ) {
            array_splice($images, 30, engine_count($images));
        }
        $view->imageCount = engine_count($images);
        $view->images = $images;
    }

//  public function previewHtml($uri, Zend_Http_Response $response)
//  {
    // $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
//    $result = array();
//    $body = $response->getBody();
//    $body = trim($body);
//    if( preg_match('/charset=([a-zA-Z0-9-_]+)/i', $response->getHeader('content-type'), $matches) ||
//        preg_match('/charset=([a-zA-Z0-9-_]+)/i', $response->getBody(), $matches) ) {
//      $view->charset = $charset = trim($matches[1]);
//    } else {
//      $view->charset = $charset = 'UTF-8';
//    }
//    if( function_exists('mb_convert_encoding') ) {
//      $body = mb_convert_encoding($body, 'HTML-ENTITIES', $charset);
//    }
//    // Get DOM
//    if( class_exists('DOMDocument') ) {
//      $dom = new Zend_Dom_Query($body);
//    } else {
//      $dom = ""; // Maybe add b/c later
//    }
//    $title = "";
//    if( $dom ) {
//      $titleList = $dom->query('title');
//      if(is_countable($titleList) && engine_count($titleList) > 0 ) {
//        $title = trim($titleList->current()->textContent);
//        $title = substr($title, 0, 255);
//      }
//    }
//    $result['link']['title'] = $title;
//    $description = "";
//    if( $dom ) {
//      $descriptionList = $dom->queryXpath("//meta[@name='description']");
//      // Why are they using caps? -_-
//      if( engine_count($descriptionList) == 0 ) {
//        $descriptionList = $dom->queryXpath("//meta[@name='Description']");
//      }
//      // Try to get description which is set under og tag
//      if( engine_count($descriptionList) == 0 ) {
//        $descriptionList = $dom->queryXpath("//meta[@property='og:description']");
//      }
//      if( engine_count($descriptionList) > 0 ) {
//        $description = trim($descriptionList->current()->getAttribute('content'));
//        $description = substr($description, 0, 255);
//      }
//    }
//     $result['link']['description'] = $description;
//    $thumb = "";
//    if( $dom ) {
//      $thumbList = $dom->queryXpath("//link[@rel='image_src']");
//      if( engine_count($thumbList) > 0 ) {
//        $thumb = $thumbList->current()->getAttribute('href');
//      }
//    }
//     $result['link']['thumb'] = $thumb;
//    $medium = "";
//    if( $dom ) {
//      $mediumList = $dom->queryXpath("//meta[@name='medium']");
//      if( engine_count($mediumList) > 0 ) {
//        $medium = $mediumList->current()->getAttribute('content');
//      }
//    }
//     $result['link']['medium'] = $medium;
//    // Get baseUrl and baseHref to parse . paths
//    $baseUrlInfo = parse_url($uri);
//    $baseUrl = "";
//    $baseHostUrl = "";
//    $baseUrlScheme = $baseUrlInfo['scheme'];
//    $baseUrlHost = $baseUrlInfo['host'];
//    if( $dom ) {
//      $baseUrlList = $dom->query('base');
//      if( $baseUrlList && engine_count($baseUrlList) > 0 && $baseUrlList->current()->getAttribute('href') ) {
//        $baseUrl = $baseUrlList->current()->getAttribute('href');
//        $baseUrlInfo = parse_url($baseUrl);
//        if (!isset($baseUrlInfo['scheme']) || empty($baseUrlInfo['scheme'])) {
//          $baseUrlInfo['scheme'] = $baseUrlScheme;
//        }
//        if (!isset($baseUrlInfo['host']) || empty($baseUrlInfo['host'])) {
//          $baseUrlInfo['host'] = $baseUrlHost;
//        }
//        $baseHostUrl = $baseUrlInfo['scheme'].'://'.$baseUrlInfo['host'].'/';
//      }
//    }
//    if( !$baseUrl ) {
//      $baseHostUrl = $baseUrlInfo['scheme'].'://'.$baseUrlInfo['host'].'/';
//      if( empty($baseUrlInfo['path']) ) {
//        $baseUrl = $baseHostUrl;
//      } else {
//        $baseUrl = explode('/', $baseUrlInfo['path']);
//        array_pop($baseUrl);
//        $baseUrl = join('/', $baseUrl);
//        $baseUrl = trim($baseUrl, '/');
//        $baseUrl = $baseUrlInfo['scheme'].'://'.$baseUrlInfo['host'].'/'.$baseUrl.'/';
//      }
//    }
//    $images = array();
//    if( $thumb ) {
//      $images[] = $thumb;
//    }
//    if( $dom ) {
//      $imageQuery = $dom->query('img');
//      foreach( $imageQuery as $image )
//      {
//        $src = $image->getAttribute('src');
//        // Ignore images that don't have a src
//        if( !$src || false === ($srcInfo = @parse_url($src)) ) {
//          continue;
//        }
//        $ext = ltrim(strrchr($src, '.'), '.');
//        // Detect absolute url
//        if( strpos($src, '/') === 0 ) {
//          // If relative to root, add host
//          $src = $baseHostUrl . ltrim($src, '/');
//        } else if( strpos($src, './') === 0 ) {
//          // If relative to current path, add baseUrl
//          $src = $baseUrl . substr($src, 2);
//        } else if( !empty($srcInfo['scheme']) && !empty($srcInfo['host']) ) {
//          // Contians host and scheme, do nothing
//        } else if( empty($srcInfo['scheme']) && empty($srcInfo['host']) ) {
//          // if not contains scheme or host, add base
//          $src = $baseUrl . ltrim($src, '/');
//        } else if( empty($srcInfo['scheme']) && !empty($srcInfo['host']) ) {
//          // if contains host, but not scheme, add scheme?
//          $src = $baseUrlInfo['scheme'] . ltrim($src, '/');
//        } else {
//          // Just add base
//          $src = $baseUrl . ltrim($src, '/');
//        }
//        // Ignore images that don't come from the same domain
//        //if( strpos($src, $srcInfo['host']) === false ) {
//          // @todo should we do this? disabled for now
//          //continue;
//        //}
//        // Ignore images that don't end in an image extension
//        if( !engine_in_array($ext, array('jpg', 'jpeg', 'gif', 'png')) ) {
//          // @todo should we do this? disabled for now
//          //continue;
//        }
//        if( !engine_in_array($src, $images) ) {
//          $images[] = $src;
//        }
//      }
//    }
//    // Unique
//    $images = array_values(array_unique($images));
//    $imagePreview = '';
//    // Truncate if greater than 20
//    if( engine_count($images) > 0 ) {
//      $imagePreview = $images[0];
//    }
//     $result['link']['images'] = $imagePreview;
//     return $result;
//  }
    public function onAttachVideo($data) {
        if (!is_array($data) || empty($data['video_id'])) {
            return;
        }
        $video = Engine_Api::_()->getItem('video', $data['video_id']);
        // update $video with new title and description
        $video->title = $data['title'];
        $video->description = !empty($data['description']) ? $data['description'] : '';
        // Set parents of the video
        if (Engine_Api::_()->sesapi()->hasSubject()) {
            $subject = Engine_Api::_()->sesapi()->getSubject();
            $subject_type = $subject->getType();
            $subject_id = $subject->getIdentity();

            $video->parent_type = $subject_type;
            $video->parent_id = $subject_id;
        }
        $video->search = 1;
        $video->save();
        if (!($video instanceof Core_Model_Item_Abstract) || !$video->getIdentity()) {
            return;
        }
        return $video;
    }
    public function YoutubeVideoInfo($uri) {
        $video_id = $this->GetYouTubeId($uri);
        $key = Engine_Api::_()->getApi('settings', 'core')->getSetting('video.youtube.apikey');
        if(empty($key)){
            return;
        }
        $url = 'https://www.googleapis.com/youtube/v3/videos?id='.$video_id.'&key='.$key.'&part=snippet,player,contentDetails';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $response_a = json_decode($response,TRUE);    
        $iframely =  $response_a['items'][0];
        if (!engine_in_array('player', array_keys($iframely))) {
            return;
        }
        $information = array('thumbnail' => '', 'title' => '', 'description' => '', 'duration' => '');
        if (!empty($iframely['snippet']['thumbnails'])) {
            $information['thumbnail'] = $iframely['snippet']['thumbnails']['high']['url'];
            if (parse_url($information['thumbnail'], PHP_URL_SCHEME) === null) {
                $information['thumbnail'] = str_replace(array('://', '//'), '', $information['thumbnail']);
                $information['thumbnail'] = "http://" . $information['thumbnail'];
            }
        }
        if (!empty($iframely['snippet']['title'])) {
            $information['title'] = $iframely['snippet']['title'];
        }
        if (!empty($iframely['snippet']['description'])) {
            $information['description'] = $iframely['snippet']['description'];
        }
        if (!empty($iframely['contentDetails']['duration'])) {
            $information['duration'] =  Engine_Date::convertISO8601IntoSeconds($iframely['contentDetails']['duration']);
        }
        $information['code'] = $iframely['player']['embedHtml'];
        return $information; 
    }
    function getYouTubeId($url)
    {
        if (stristr($url,'youtu.be/'))
            {preg_match('/(https:|http:|)(\/\/www\.|\/\/|)(.*?)\/(.{11})/i', $url, $final_ID); return $final_ID[4]; }
        else 
            {@preg_match('/(https:|http:|):(\/\/www\.|\/\/|)(.*?)\/(embed\/|watch.*?v=|)([a-z_A-Z0-9\-]{11})/i', $url, $IDD); return $IDD[5]; }
    }
}
