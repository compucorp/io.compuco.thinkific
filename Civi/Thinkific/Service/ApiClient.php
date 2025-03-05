<?php

namespace Civi\Thinkific\Service;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use CRM_Thinkific_SettingsManager as SettingsManager;

class ApiClient {
  private const API_URL = 'https://api.thinkific.com/api/public/v1/';

  /**
   * @var array
   * @phpstan-ignore missingType.iterableValue
   */
  private array $requestHeaders = [];

  public function __construct() {
    $domainId = \CRM_Core_Config::domainID();

    /** @var array<string, array<int, array<string, mixed>>> $settings */
    $settings = civicrm_api3('setting', 'get', ['return' => [SettingsManager::API_KEY, SettingsManager::SUBDOMAIN]]);
    $this->requestHeaders = [
      'X-Auth-API-Key' => $settings['values'][$domainId][SettingsManager::API_KEY],
      'X-Auth-Subdomain' => $settings['values'][$domainId][SettingsManager::SUBDOMAIN],
      'Content-Type' => 'application/json',
    ];
  }

  /**
   * @param string $method
   * @param string $url
   * @param array<string,mixed> $headers
   *
   * @return \Psr\Http\Message\ResponseInterface
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function request(string $method, string $url, array $headers = []): ResponseInterface {
    $client = new Client([
      'headers' => array_merge($this->requestHeaders, $headers),
    ]);

    $completeUrl = self::API_URL . $url;

    return $client->request($method, $completeUrl);
  }

}
