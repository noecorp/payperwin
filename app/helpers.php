<?php

if ( ! function_exists('app_url'))
{
	/**
	 * Generate a url for the application.
	 *
	 * On top of the url() helper, this uses the actual APP_URL environment variable.
	 *
	 * @param  string  $path
	 * @param  mixed   $parameters
	 * @param  bool    $secure
	 * @return string
	 */
	function app_url($path = null, $parameters = array())
	{
		$url = url($path, $parameters);

		$siteUrl = getenv('APP_URL');

		if (!$siteUrl || substr($url, 0, strlen($siteUrl)) == $siteUrl)
		{
			return $url;
		}

		return rtrim($siteUrl, '/') . '/' . substr($url, strpos($url, $path));
	}
}
