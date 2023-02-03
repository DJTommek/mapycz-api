<?php declare(strict_types=1);

namespace DJTommek\MapyCzApi;

class Utils
{
	/**
	 * CURL request with very simple settings
	 *
	 * @param string $url URL to be loaded
	 * @param array<int, mixed> $curlOpts indexed array of options to curl_setopt()
	 * @return string content of requested page
	 * @throws \Exception if error occured or page returns no content
	 * @author https://gist.github.com/DJTommek/97048e875a91b67123b0c544bc46c116
	 */
	public static function fileGetContents(string $url, array $curlOpts = []): string
	{
		$curl = curl_init($url);
		if ($curl === false) {
			throw new \Exception('CURL can\'t be initialited.');
		}
		$curlOpts[CURLOPT_RETURNTRANSFER] = true;
		$curlOpts[CURLOPT_HEADER] = true;
		curl_setopt_array($curl, $curlOpts);
		/** @var string|false $curlResponse */
		$curlResponse = curl_exec($curl);
		if ($curlResponse === false) {
			$curlErrno = curl_errno($curl);
			throw new \Exception(sprintf('CURL request error %s: "%s"', $curlErrno, curl_error($curl)));
		}
		$curlInfo = curl_getinfo($curl);
		list($header, $body) = explode("\r\n\r\n", $curlResponse, 2);
		if ($curlInfo['http_code'] >= 500) {
			throw new \Exception(sprintf('Page responded with HTTP code %d: Text response: "%s"', $curlInfo['http_code'], $body), $curlInfo['http_code']);
		}
		if (!$body) {
			$responseCode = trim(explode(PHP_EOL, $header)[0]);
			throw new \Exception(sprintf('Bad response from CURL request from URL "%s": "%s".', $url, $responseCode));
		}
		return $body;
	}

	/**
	 * Polyfil for PHP 8 str_starts_with
	 *
	 * @link https://www.php.net/manual/en/function.str-starts-with.php
	 * @link https://github.com/symfony/polyfill/blob/e2fbb83eac51707a8ccab28559f8dc6b12a4ec5a/src/Php80/Php80.php#L96
	 */
	public static function str_starts_with(string $haystack, string $needle): bool
	{
		return strncmp($haystack, $needle, \strlen($needle)) === 0;
	}
}
