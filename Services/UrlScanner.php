<?php

namespace Tom32i\UrlScannerBundle\Services;

use Tom32i\UrlScannerBundle\Helpers\simple_html_dom;

class UrlScanner
{
    public static function getDataFromUrl($url)
    {
        $url = trim($url);

        $return = array();
        $html = self::cUrlGet($url);

        $return['html'] = $html;

        if($html)
        {
            $dom = self::str_get_html($html);

            $head_title = $dom->find('head title',0);
            $og_title = $dom->find('head meta[property=og:title]',0);
            $h1_title = $dom->find('h1',0);
            $description = $dom->find('head meta[name=description]',0);
            $og_description = $dom->find('head meta[property=og:description]',0);
            $image = $dom->find('head meta[property=og:image]',0);

            if($head_title || $og_title)
            {
                $return['title'] = html_entity_decode($og_title ? $og_title->content : ($head_title->plaintext ? $head_title->plaintext : $h1_title->plaintext));
            }

            if($description || $og_description)
            {
                $return['description'] = html_entity_decode($og_description ? $og_description->content : $description->content);
            }
            if($image)
            {
                $return['image'] =  $image->content;
            }
        }

        return $return;
    }

    public static function cUrlGet($url)
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,         // return web page 
            CURLOPT_HEADER         => false,        // don't return headers 
            CURLOPT_FOLLOWLOCATION => true,         // follow redirects 
            CURLOPT_ENCODING       => "",           // handle all encodings 
            CURLOPT_USERAGENT      => "spider",     // who am i 
            CURLOPT_AUTOREFERER    => true,         // set referer on redirect 
            CURLOPT_CONNECTTIMEOUT => 1200,          // timeout on connect 
            CURLOPT_TIMEOUT        => 1200,          // timeout on response 
            CURLOPT_MAXREDIRS      => 10,           // stop after 10 redirects 
            CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl 
            CURLOPT_SSL_VERIFYPEER => false,        // 
            CURLOPT_VERBOSE        => 1   
        );

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        $err     = curl_errno($ch); 
        $errmsg  = curl_error($ch) ; 
        $header  = curl_getinfo($ch); 
        curl_close($ch);

        //$result = preg_replace("[\n\r]", "", $result);
        /*$match_redirect = preg_match('#^HTTP/1\.1 30[1-2].+Location: ((https?://)?(\.)?[^ \r\n]+).+#mis', $result, $matches);
        
        if($match_redirect>0)
        {
            return self::cUrlGet($matches[1]);
        }*/

        $match_html = preg_match('#.*(<html.+</html>).*#mis', $result, $matches_html);

        if($match_html>0)
        {
            if(array_key_exists(1, $matches_html))
            {
                return $matches_html[1];
            }

            return false;
        }

        return array($header, $err ,$errmsg);
    }

    public static function str_get_html($str, $lowercase = true, $forceTagsClosed = true, $target_charset = 'UTF-8', $stripRN = true, $defaultBRText = "\r\n")
    {
        $dom = new simple_html_dom(null, $lowercase, $forceTagsClosed, $target_charset, $defaultBRText);

        if (empty($str))
        {
            $dom->clear();
            return false;
        }

        $dom->load($str, $lowercase, $stripRN);

        return $dom;
    }
}

?>