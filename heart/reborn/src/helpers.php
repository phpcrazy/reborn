<?php

/**
 * Helper Functions for Reborn
 *
 * @package Reborn
 * @author Myanmar Links Professional Web Development Team
 **/

if(! function_exists('dump'))
{
	/**
	 * Dump the given value. Use for var_dump().
	 * If you want to die the script after dump, use as
	 * <code>
	 * 		dump($value, true);
	 * </code>
	 *
	 * @param mixed $value
	 * @param boolean $die
	 * @param boolean $export Use var_export instead of var_dump
	 * @param string $title Title for Dump
	 * @return string
	 */
	function dump($value, $die = false, $export = false, $title = null)
	{
		if (is_string($die)) {
			$title = $die;
			$export = false;
			$die = false;
		}

		if (is_string($export)) {
			$title = $export;
			$export = false;
		}

		$backtrace = debug_backtrace();
		$file = $backtrace[0]['file'];
		$line = $backtrace[0]['line'];

		echo '<pre style="border: 1px dashed #71CF4D; padding: 5px 10px; background: #F2F2F2; margin: 10px 15px;">';
		if ($title) {
			echo '<h2 style="color: #565656; margin: 0px; padding-bottom: 5px; font-size: 13px; font-weight: normal;">Dump Title : '.$title.'</h2>';
		}
		echo '<h3 style="color: #990000; margin-top: 0px; padding-bottom: 5px; border-bottom: 1px dashed #999; font-size: 13px; font-weight: normal;">';
		echo 'Dump at &raquo; <span style="color: #000099;">'.$file.'</span> ';
		echo 'line number <span style="color: #009900;">'.$line.'</span>.</h3>';
		if ($export) {
			var_export($value);
		} else {
			var_dump($value);
		}
		echo '</pre>';
		if ($die) {
			die;
		}
	}
}

if(! function_exists('h'))
{
	/**
	 * Filter function for htmlspecialchars.
	 * See detail at php's htmlspecialchars function.
	 *
	 * @param string $str
	 * @param int $flags
	 * @param string $encode
	 * @param boolean $doubleEncode
	 * @return string
	 */
	function h($str, $flags = ENT_QUOTES, $encode = 'UTF-8', $doubleEncode = true)
	{
		return htmlspecialchars($str, $flags, $encode, $doubleEncode);
	}
}

if(! function_exists('t'))
{
	/**
	 * Helper function for the translate.
	 * See detail at Translate::get()
	 *
	 * @param string $key
	 * @param array $replace Replace value for langauge string
	 * @param string $default
	 * @return string
	 **/
	function t($key, $replace = null, $default = null)
	{
		return \Translate::get($key, $replace, $default);
	}
}

if(! function_exists('flash'))
{
	/**
	 * Get the flush session view (with html).
	 * This function is same with Flash::flahsBox();
	 *
	 *
	 * @return string
	 */
	function flash()
	{
		return \Flash::flashBox();
	}
}

if(! function_exists('value'))
{
	/**
	 * Return the value of given param,
	 * If the value is closure, return closure result.
	 *
	 * @param mixed $val
	 * @return mixed
	 */
	function value($val)
	{
		return ($val instanceof \Closure) ? $val() : $val;
	}
}

if(! function_exists('slug'))
{
	/**
	 * Convert the given string to slug(URL) type string
	 *
	 * @param string $str
	 * @param string $separator Separator for space. Defaut is '-'
	 * @return string
	 **/
	function slug($str, $separator = '-')
	{
		return \Reborn\Util\Str::slug($str, $separator);
	}
}

if(! function_exists('sanitize'))
{
	/**
	 * Sanitize the given string by given pattern
	 * example :
	 * <code>
	 * 		// Output is "Who are you"
	 * 		sanitize('Who are you?', 'A-Za-z-0-9-\s');
	 * </code>
	 *
	 * @param string $str String
	 * @param string $pattern Regular Expression Pattern
	 * @return string
	 **/
	function sanitize($str, $pattern)
	{
		return \Reborn\Util\Str::sanitize($str, $pattern);
	}
}

if(! function_exists('random_str'))
{
	/**
	 * Generate the random string.
	 *
	 * @param integer $length Length of random string
	 * @return string
	 **/
	function random_str($length = 10)
	{
		return \Reborn\Util\Str::random($length);
	}
}

if(! function_exists('array_pluck'))
{
	/**
	 * Pluck the value from given array
	 *
	 * @param array $arr
	 * @param string $key
	 * @return array
	 **/
	function array_pluck($arr, $key)
	{
		return array_map(function($a) use($key) { return $a[$key]; }, $arr);
	}
}

if(! function_exists('array_get'))
{
	/**
	 * Get an item from an array using "dot" notation.
	 * This function is original from Illuminate\Supprot\src\helpers.php file.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	function array_get($array, $key, $default = null)
	{
		if (is_null($key)) return $array;

		foreach (explode('.', $key) as $segment)
		{
			if ( ! is_array($array) or ! array_key_exists($segment, $array))
			{
				return value($default);
			}

			$array = $array[$segment];
		}

		return $array;
	}
}

if(! function_exists('arr_is_multi'))
{
	/**
	 * Check the given array is multidimensional array or not.
	 *
	 * @param  array   $array
	 * @return boolean
	 */
	function arr_is_multi($array)
	{
	    return count(array_filter($array, 'is_array')) > 0;
	}
}

if(! function_exists('arr_to_object'))
{
	/**
	 * Convert given array to object.
	 *
	 * @param array $array
	 * @return object
	 */
	function arr_to_object($array)
	{
	    return json_decode(json_encode((array) $array));
	}
}

if(! function_exists('is_json'))
{
	/**
	 * Check given string is Json or not.
	 * This function is original from
	 * http://stackoverflow.com/questions/6041741/fastest-way-to-check-if-a-string-is-json-in-php
	 *
	 * @param string $string
	 * @return boolean
	 **/
	function is_json($string)
	{
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}
}

if(! function_exists('remove_base_url'))
{
	/**
	 * Helper function fto remove baseUrl from given url
	 *
	 * @param string $url
	 * @return string
	 **/
	function remove_base_url($url)
	{
		return str_replace(rbUrl(), '', $url);
	}
}

if(! function_exists('rbUrl'))
{
	/**
	 * Helper function for the Uri::create().
	 *
	 * @param string $path Uri path to create.
	 * @return string
	 **/
	function rbUrl($path = '')
	{
		return \Uri::create($path);
	}
}

if(! function_exists('adminUrl'))
{
	/**
	 * Helper function for the Uri::create() with ADMIN Panel Link.
	 *
	 * @param string $path Uri path to create.
	 * @return string
	 **/
	function adminUrl($path = '')
	{
		$admin = \Setting::get('adminpanel_url');
		$path = ltrim($path, '/');
		return \Uri::create($admin.'/'.$path);
	}
}

if(! function_exists('css'))
{
	/**
	 * Helper function for the Asset::css().
	 *
	 * @param string $file CSS filename with extension.
	 * @param string $media Medaia type for CSS tag. Default is "all"
	 * @param string $module If you want to use CSS from module, set module name
	 * @return string
	 **/
	function css($file, $media = "all", $module = null)
	{
		$theme = Registry::get('app')->theme;
		$asset = new Reborn\Asset\Asset($theme->getThemePath());
		return $asset->css($file, $media, $module);
	}
}

if(! function_exists('less'))
{
	/**
	 * Helper function for the Asset::less().
	 *
	 * @param string $file LESS filename with extension.
	 * @param string $media Medaia type for CSS tag. Default is "all"
	 * @param string $module If you want to use LESS from module, set module name
	 * @return string
	 **/
	function less($file, $media = "all", $module = null)
	{
		$theme = Registry::get('app')->theme;
		$asset = new Reborn\Asset\Asset($theme->getThemePath());
		return $asset->less($file, $media, $module);
	}
}

if(! function_exists('js'))
{
	/**
	 * Helper function for the Asset::js().
	 *
	 * @param string $file Js Filename with extension
	 * @param string $module If you want to use JS from module, set module name
	 * @return string
	 **/
	function js($file, $module = null)
	{
		$theme = Registry::get('app')->theme;
		$asset = new Reborn\Asset\Asset($theme->getThemePath());
		return $asset->js($file, $module);
	}
}

if(! function_exists('img'))
{
	/**
	 * Helper function for the Asset::img().
	 *
	 * @param string $file Image file name with extension
	 * @param string|null $alt Text for the ALT.
	 * @param array $attr Other attribute for img tag (eg: title, id, etc.)
	 * @param string|null $module If you want file from module, set the module name.
	 * @return string
	 **/
	function img($file, $alt = null, $attr = array(), $module = null)
	{
		$theme = Registry::get('app')->theme;
		$asset = new Reborn\Asset\Asset($theme->getThemePath());
		return $asset->img($file, $alt, $attr, $module);
	}
}

if(! function_exists('assetPath'))
{
	/**
	 * Helper function for the Asset File Path (css, js, img).
	 *
	 * @param string $type Asset file type
	 * @param string $module Module name if file is exists in module
	 * @return string
	 **/
	function assetPath($type = null, $module = null)
	{
		$theme = Registry::get('app')->theme;
		$asset = new Reborn\Asset\Asset($theme->getThemePath());
		switch($type) {
			case 'css' :
				return $asset->getCssPath($module);
				break;
			case 'js' :
				return $asset->getJsPath($module);
				break;
			case 'img' :
			case 'image' :
				return $asset->getImgPath($module);
				break;
			default :
				return $asset->getAssetPath($module);
				break;
		}
	}
}

if (! function_exists('global_asset'))
{
	/**
	 * Hlper Function for Global Assets Tags
	 *
	 * @param string $type Asset Type
	 * @param string $filename asset file name
	 * @return string
	 */
	function global_asset($type, $filename) {
		$asset = new Reborn\Asset\Asset(BASE.'global'.DS);

		switch($type) {
			case 'css' :
				return $asset->css($filename);
				break;
			case 'js' :
				return $asset->js($filename);
				break;
			case 'img' :
			case 'image' :
				return $asset->img($filename);
				break;
			default :
				return null;
				break;
		}
	}
}

if (! function_exists('arr_for_select'))
{
	/**
	 * Converts a multi-dimensional associative array into an array of key => values base on * your set field name.
	 *
	 * @param   array   the array to convert(Submit StdClass)
	 * @param   string	the field name of the key field
	 * @param   string	the field name of the value field
	 * @return  array
	 */
	function arr_for_select()
	{
		$args = func_get_args();

		$return = array();

		switch(count($args))
		{
			case 3:
				foreach ($args[0] as $itteration):
					if(is_object($itteration)) $itteration = (array) $itteration;
					$return[$itteration[$args[1]]] = $itteration[$args[2]];
				endforeach;
			break;

			case 2:
				foreach ($args[0] as $key => $itteration):
					if(is_object($itteration)) $itteration = (array) $itteration;
					$return[$key] = $itteration[$args[1]];
				endforeach;
			break;

			case 1:
				foreach ($args[0] as $itteration):
					$return[$itteration] = $itteration;
				endforeach;
			break;

			default:
				return false;
		}

		return $return;
	}
}

if (! function_exists('e2s'))
{
	/**
	 * Eloquent Model Object to the Select Array
	 *
	 * @param Object $data Eloquent Model Object
	 * @param string $key Array Key
	 * @param string $value Array Value
	 * @param boolean $blank If you want to add --Select-- in return array, set true
	 * @return array
	 **/
	function e2s($data, $key, $value, $blank = false)
	{
		$select = array();

			foreach ($data as $k => $v) {
				$select[$v->$key] = $v->$value;
			}

			if ($blank) {
				$select = array('' => '-- Select --') + $select;
			}

			return $select;
	}
}

if (! function_exists('country'))
{
	/**
	 * Get the country name by country key
	 *
	 * @param string $key
	 * @return string
	 **/
	function country($key)
	{
		$lists = \Config::get('country');

		if (array_key_exists($key, $lists)) {
			return $lists[$key];
		}

		return null;
	}
}

if ( ! function_exists('gravatar'))
{
	/**
	 * Gravatar Function
	 *
	 * @param string $email Email address for gravatar
	 * @param int $size Size for gravatar. Default is 50
	 * @param string $rating Rating for gravatar. Default is 'g'
	 * @param string $default Default key for gravatar
	 * @param boolean $url_only Set true if you want gravater url only. Default is false
	 * @return	string URL
	 */
	function gravatar($email = '', $size = 50, $name = null, $rating = 'g', $default = null, $url_only = false)
	{
		 $base_url 	= '//www.gravatar.com/avatar/';
		 $email = empty($email) ? '00000000000000000000000000000000' : md5(strtolower(trim($email)));
		 $size = '?s=' . $size;
		 $rating = '&amp;r=' . $rating;
		 $default = is_null($default) ? '' : '&amp;d='.$default;

		 $gravatar = $base_url . $email . $size . $rating . $default;

		 if ($url_only != true)
		 {
			$gravatar = "<img src='$gravatar' alt='$name' class='gravatar' />";
		 }

		 return $gravatar;
	}
}

if (! function_exists('checkOnline'))
{
	/**
	 * Check the Internet Connection is avaliable or not
	 *
	 * @param string $url Optional
	 * @param int $port Optional
	 * @return boolean
	 **/
	function checkOnline($url = 'www.google.com', $port = 80)
	{
	    $connected = @fsockopen($url, $port);
	    if ($connected){
	        $is_conn = true;
	        fclose($connected);
	    }else{
	        $is_conn = false;
	    }

	    return $is_conn;
	}
}

if (! function_exists('setting'))
{
	/**
	 * Helper Function of Setting::get().
	 *
	 * @param string $key
	 * @return mixed
	 **/
	function setting($key)
	{
        return \Setting::get($key);
	}
}

if (! function_exists('cycle'))
{
	/**
	 * Cycle is like a
	 * https://docs.djangoproject.com/en/dev/ref/templates/builtins/#std:templatetag-cycle.
	 * Cycle among the given strings or variables each time this tag is encountered.
	 * Within a loop, cycles among the given strings each time through the loop.
	 *
	 * @param string
	 * @return string
	 **/
	function cycle()
	{
		static $i;

		if (func_num_args() === 0) {
			$i = 0;
			return null;
		}

		$args = func_get_args();
		$key = $i++ % count($args);
		return $args[$key];
	}
}

if (! function_exists('is_home'))
{
	/**
	 * Helper Function is_home() for Theme Developer.
	 *
	 * @return boolean
	 **/
	function is_home()
	{
		$home_page = \Setting::get('home_page');
		$uri = \Uri::segments();

		if ( empty($uri) ) {
			return true;
		} elseif ( $home_page == $uri[0] ) {
			return true;
		}

		return false;
	}
}

if (! function_exists('markdown'))
{
	/**
	 * Transform Markdown to HTML with dflydev\markdown.
	 *
	 * @param string $text Markdown Text String
	 * @return string
	 **/
	function markdown($text)
	{
    	$markdownParser = new dflydev\markdown\MarkdownParser();

    	return $markdownParser->transformMarkdown($text);
	}
}

if (! function_exists('markdown_extra'))
{
	/**
	 * Transform MarkdownExtra to HTML with dflydev\markdown.
	 *
	 * @param string $text Markdown Text String
	 * @return string
	 **/
	function markdown_extra($text)
	{
    	$markdownParser = new dflydev\markdown\MarkdownExtraParser();

    	return $markdownParser->transformMarkdown($text);
	}
}

if (! function_exists('num'))
{
	/**
	 * Change Number with localization
	 * This is only for Myanmar Number
	 *
	 * @param string $str Number string
	 * @return string
	 **/
	function num($str)
	{
		\Translate::load('numbers');
		$nums = \Translate::get('numbers.formats');
		$search = array_keys($nums);
		$replace = array_values($nums);

		return str_replace($search, $replace, $str);
	}
}

if(! function_exists('theme_config'))
{
	/**
	 * Helper function for theme_config value.
	 *
	 * @param string $key Config Key
	 * @param mixed $default Default value to return if config key doesn't found
	 * @return mixed
	 **/
	function theme_config($key, $default = null)
	{
		$theme = Registry::get('app')->theme;

		try {
			$theme_config = $theme->config();
		} catch (Exception $e) {
			return $default;
		}

		return array_get($theme_config, $key, $default);
	}
}

if (!function_exists('first'))
{
	/**
	 * This function will be help in looping.
	 * If you need something at looping's first time only.
	 * example :
	 * <code>
	 * $i = 0;
	 * foreach($images as $img) {
	 * 		if($i == 0) {
	 * 			echo "<div class="active">
	 * 		} else {
	 * 			echo "<div>";
	 * 		}
	 * 		<img src="$img">
	 * 		</div>
	 * 		$i++;
	 * }
	 * //Use first()
	 * foreach ($images as $img) {
	 * 		<div class="first('active')">
	 * 			<img src="$img">
	 * 		</div>
	 * }
	 * </code>
	 *
	 * @return string
	 **/
	function first($var)
	{
		static $j = 0;

		if (0 === $j) {
			$j++;
			return $var;
		}
	}
}

if (! function_exists('template_parse'))
{
	/**
	 * Helper function for Reborn Parser render for template string
	 *
	 * @param string $template Template string
	 * @param array $data Data array
	 * @return string
	 **/
	function template_parse($template, $data = array())
	{
		global $app;
		return $app['view']->renderAsStr($template, $data);
	}
}

