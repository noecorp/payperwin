<?php namespace App\Contracts\Service;

interface Shortener {

	/**
	 * Shorten the specified url into the short slug.
	 *
	 * @param string $url
	 * @param string $slug
	 *
	 * @return string The final short url.
	 */
	public function url($url, $slug);

}
