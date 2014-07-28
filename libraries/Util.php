<?php
/**
 * Webwork
 * Copyright (C) 2011 IceFlame.net
 *
 * Permission to use, copy, modify, and/or distribute this software for
 * any purpose with or without fee is hereby granted, provided that the
 * above copyright notice and this permission notice appear in all copies.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE
 * FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY
 * DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER
 * IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING
 * OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 *
 * @package     Webwork
 * @version     0.1-dev
 * @link        http://www.iceflame.net
 * @license     ISC License (http://www.opensource.org/licenses/ISC)
 */

/**
 * Common utilities
 *
 * @author   Christian Neff <christian.neff@gmail.com>
 */
class Util {

    /**
     * Generates a URL to a relative path based on the application URL
     * @param    string   $path    The relative path of the location
     * @param    array    $query   Optional data that is added to the URL as query string.
     *                               For more information see {@link http://www.php.net/http_build_query}
     * @return   string
     * @access   public
     * @static
     */
    public static function makeURL($path = '', $query = null) {
        $root_url = System::setting('Web:Url');

        $result = $root_url.'/'.$path;

        if (isset($query) && is_array($query))
            $result .= '?'.http_build_query($query);

        return $result;
    }

    /**
     * Generates a URL to a module page by path
     * @param    string   $page_path   The path of the module page
     * @param    array    $query       Optional data that is added to the URL as query string.
     *                                   For more information see {@link http://www.php.net/http_build_query}
     * @return   string
     * @access   public
     * @static
     */
    public static function makePageURL($page_path, $query = null) {
        $root_url = System::setting('Web:Url');

        if (System::setting('Web:UrlRewrite')) {
            $result = $root_url.'/'.$page_path;

            if (isset($query) && is_array($query))
                $result .= '?'.http_build_query($query);
        } else {
            $result = $root_url.'/?p='.$page_path;

            if (isset($query) && is_array($query))
                $result .= '&'.http_build_query($query);
        }

        return $result;
    }

    /**
     * Generates a URL to a theme file
     * @param    string   $filename   The name of the file (appended to path)
     * @param    string   $module     Use module theme path instead of global theme path
     * @param    string   $theme      Use this specified theme
     * @return   string
     * @access   public
     * @static
     */
    public static function makeThemeFileURL($filename, $module = null, $theme = null) {
        $rooturl = System::setting('Web:Url');

        if (!isset($theme))
            $theme = System::setting('View:Theme');

        if (isset($module)) {
            $path = $rooturl.'/websites/'.WW_SITE_NAME.'/modules/'.$module;
        } else {
            $path = $rooturl;
        }

        return "{$path}/themes/{$theme}/{$filename}";
    }

    /**
     * Returns the value of an HTTP cookie. Returns FALSE if the cookie is not set.
     * @param    string   $name   The name of the cookie. The prefix is prepended automatically.
     * @return   mixed
     * @access   public
     * @static
     */
    public static function getCookie($name) {
        $name_prefix = System::setting('Cookie:NamePrefix');
        $name = $name_prefix.$name;
        
        if (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        } else {
            return false;
        }
    }
    
    /**
     * Defines a cookie to be sent along with the rest of the HTTP headers
     * @param    string   $name       The name of the cookie
     * @param    mixed    $value      The value of the cookie. Can be a string or an array. If it is an array, all of its
     *                                  elements will be set all cookies with the name '<name>[<element key>]' and with the
     *                                  value of the array element.
     * @param    mixed    $expire     The time the cookie expires. There are several input possibilities:
     *                                  * 0 (zero) = Cookie expires at the end of the session (default)
     *                                  * UNIX timestamp, DateTime object or time/date string
     * @return   bool
     * @access   public
     * @static
     */
    public static function setCookie($name, $value, $expire = 0) {
        if (headers_sent())
            return false;
        
        $expire = Util::toTimestamp($expire);

        if (is_array($value)) {
            foreach ($value as $element_key => $element_value)
                self::setCookie($name.'['.$element_key.']', $element_value, $expire);
            
            return true;
        } else {
            $name_prefix = System::setting('Cookie:NamePrefix');
            $name = $name_prefix.$name;
            
            $path   = System::setting('Cookie:Path');
            $domain = System::setting('Cookie:Domain');

            return setcookie($name, $value, $expire, $path, $domain);
        }
    }
    
    /**
     * Forces the deletion of a cookie by setting it to expire
     * @param    string   $name   The name of the cookie
     * @return   bool
     * @access   public
     * @static
     */
    public static function deleteCookie($name) {
        return self::setCookie($name, '', time()-3600);
    }

    /**
     * Generates a header redirection
     * @param    string   $url        The URL where the redirection goes to
     * @param    int      $response   Forces the HTTP response code to the specified value (Default: 302)
     * @return   bool
     * @access   public
     * @static
     */
    public static function redirect($url, $response = 302) {
        return header('Location: '.$url, true, $response);
    }
    
    /**
     * Lists the languages that the browser accepts by parsing the 'Accept-Language' header. Returns an array on success
     *   or FALSE if no or an invalid header was sent.
     * @return   array
     * @access   public
     * @static
     */
    public static function getBrowserLanguages() {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $languages = array();

            // Break up the string into pieces (languages and q-factors)
            $pattern = '/(?P<locale>[a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(?P<q>1|0\.[0-9]+))?/i';
            $matched = preg_match_all($pattern, $_SERVER['HTTP_ACCEPT_LANGUAGE'], $items, PREG_SET_ORDER);

            if ($matched != false) {
                // Create list of accepted languages with their q-factor (omitted q-factor = 1)
                foreach ($items as $item)
                    $languages[$item['locale']] = !empty($item['q']) ? (float) $item['q'] : 1.0;

                // Sort the list based on q-factor
                arsort($languages, SORT_NUMERIC);
                
                return array_keys($languages);
            }
        }
        
        return false;
    }
    
    /**
     * Checks if the given value matches the list of patterns
     * @param    string   $value   The value to match
     * @param    string   $list    List of fnmatch() patterns separated by commas
     * @return   bool
     * @access   public
     * @static
     */
    public static function matchesPatternList($value, $list) {
        $patterns = explode(',', $list);
        
        foreach ($patterns as $pattern) {
            if (fnmatch($pattern, $value))
                return true;
        }
        
        return false;
    }

    /**
     * Transforms the given input into a timestamp
     * @param    mixed    $input   Time/Date input can be UNIX timestamp, DateTime object or time/date string
     * @return   int
     * @access   public
     * @static
     */
    public static function toTimestamp($input) {
        if (is_numeric($input)) {
            // Numeric input, we handle it as timestamp
            return (int) $input;
        } elseif ($input instanceof DateTime) {
            // DateTime object, get timestamp
            return $input->getTimestamp();
        } else {
            // strtotime() should handle it
            $strtotime = strtotime($input);
            if ($strtotime != -1 && $strtotime !== false) {
                return $strtotime;
            } else {
                // strtotime() was not able to parse, use current time
                return time();
            }
        }
    }

    /**
     * Parses a Webwork settings file. Returns a multidimensional array, with the section names and settings included.
     * @param    string   $filename   The filename of the YAML file being parsed
     * @return   array
     * @access   public
     * @static
     */
    public static function parseSettings($filename) {
        if (!is_readable($filename))
            trigger_error('File "'.$filename.'" does not exist or is not readable', E_USER_ERROR);

        return \Symfony\Component\Yaml\Yaml::parse($filename);
    }

}
