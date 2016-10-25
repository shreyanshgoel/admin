<?php
namespace Shared;
use \Curl\Curl as Curl;
use Framework\Registry as Registry;

class Cloudflare {
	protected static $conf = null;

	protected static $endPoint = 'https://api.cloudflare.com/client/v4/';

	protected static function _conf() {
		if (!(self::$conf)) {
			$conf = Registry::get("configuration");
			$cloudflare = $conf->parse("configuration/cf")->cloudflare;

			self::$conf = $cloudflare;
		}

		return self::$conf;
	}

	protected static function _requestClient() {
		$conf = self::_conf();
		
		$curl = new Curl();
		$curl->setHeader('X-Auth-Email', $conf->api->email);
		$curl->setHeader('X-Auth-Key', $conf->api->key);
		$curl->setHeader('Content-Type', 'application/json');

		return $curl;
	}

	public static function createDNS($opts) {
		$conf = self::_conf();
		$content = (isset($opts['content'])) ? $opts['content'] : $conf->server->ip;
		$proxied = true;

		$curl = self::_requestClient();

		$url = self::$endPoint . 'zones/' . $conf->api->zoneid . '/dns_records';
		$curl->post($url, [
			'type' => $opts['type'],
			'name' => $opts['name'],
			'content' => $content,
			'proxied' => true
		]);

		$response = $curl->response;
		if ($response->success) {
			return true;
		} else {
			return false;
		}
	}
}
