<?php

class Sitetranslator_Api_Core extends Core_Api_Abstract
{
    /**
     * Translate the given string into given target language
     * @param Array,String
     * return array
     */
    function getTranslatedBaseMessage($getDefaultLanguageArray, $locale,$variables = array()) {
        $replaceBefore = array_keys($variables);
        $replaceAfter = array_values($variables);
        $seprater = "<Z_X_Y>";
        $joiner = "<Z_X_Y>";
        $bigData = array();
        $data = array();
        foreach ($getDefaultLanguageArray as $key => $value) {
            if (empty($value)) {
                continue;
            }
            if (is_array($value)) {
                $data[] = str_replace($replaceBefore, $replaceAfter, $value[0]);
                $data[] = str_replace($replaceBefore, $replaceAfter, $value[1]);
            } else {
                $data[] = str_replace($replaceBefore, $replaceAfter, $value);
            }
            if (strlen(join($seprater, $data)) > 20000) {
                $bigData[] = join($seprater, $data);
                $data = array();
            }
        }

        $bigData[] = join($seprater, $data);
        $translatedArray = array();
        foreach ($bigData as $key => $value) {
            $responseData = $this->getTranslatedResponse($value, 'en', $locale);
            if ($responseData['responseCode'] != 200) {
                return $responseData;
            } else {
                $translatedArray = !empty($translatedArray) ? array_merge($translatedArray, explode($joiner, $responseData["responseData"])) : explode($joiner, $responseData["responseData"]);
            }
            if (engine_count($bigData) > 2) {
                sleep(40);
            }
        }
        $key = 0;
        $data = array();
        foreach ($getDefaultLanguageArray as $pKey => $value) {
            if (empty($value)) {
                continue;
            }
            if (is_array($value)) {
                $content = array(
                    str_replace('"', '""', trim(str_ireplace($replaceAfter, $replaceBefore, $translatedArray[$key++]))),
                    str_replace('"', '""', trim(str_ireplace($replaceAfter, $replaceBefore, $translatedArray[$key++])))
                );
            } else {
                $content = str_replace('"', '""', trim(str_ireplace($replaceAfter, $replaceBefore, $translatedArray[$key++])));
            }
            $data[str_replace('"', '""', $pKey)] = $content;
        }
        return array("responseCode" => 200, "responseData" => $data);
    }
    /**
     * Translate the given string into given target language
     * @param String,String,String
     * return array
     */
    function getTranslatedResponse($bigData, $sourceCode, $targetCode) {
        $apiKey = Engine_Api::_()->getApi('settings', 'core')->getSetting('sitetranslator.google.api.key');
        $url = 'https://www.googleapis.com/language/translate/v2?key=' . $apiKey;
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_POSTFIELDS, array('q' => $bigData,
            "source" => $sourceCode, 'target' => $targetCode));
        $response = curl_exec($handle);
        $responseDecoded = json_decode($response, true);
        $responseCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);      //Here we fetch the HTTP response code
        curl_close($handle);
        if ($responseCode != 200) {
            return array('responseCode' => $responseCode, 'responseData' => $responseDecoded['error']['errors'][0]['message']);
        } else {
            return array('responseCode' => $responseCode, 'responseData' => $responseDecoded['data']['translations'][0]['translatedText']);
        }
    }
    
    public function isSiteMobileModeEnabled() {
        return $this->checkSitemobileMode('tablet-mode') || $this->checkSitemobileMode('mobile-mode');
    }

    public function checkSitemobileMode($mode = 'fullsite-mode') {
        if (Engine_Api::_()->hasModuleBootstrap('sitemobile')) {
            return (bool) (Engine_API::_()->sitemobile()->getViewMode() === $mode);
        } else {
            return (bool) ('fullsite-mode' === $mode);
        }
    }   
}
