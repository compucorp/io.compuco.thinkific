<?php

namespace Civi\Thinkific\Service;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Civi\Thinkific\SettingsManager;

class ApiClient {
  private const API_URL = 'https://api.thinkific.com/api/public/v1/';

  /**
   * @var array
   * @phpstan-ignore missingType.iterableValue
   */
  private array $requestHeaders = [];

  public function __construct() {
    /** @var array<int, array<string, mixed>> $settings */
    $settings = civicrm_api4('Setting', 'get', [
      'select' => [SettingsManager::API_KEY, SettingsManager::SUBDOMAIN],
      'checkPermissions' => FALSE,
    ])->getArrayCopy();

    $this->requestHeaders = [
      'X-Auth-API-Key' => $settings[0]['value'] ?? '',
      'X-Auth-Subdomain' => $settings[1]['value'] ?? '',
      'Content-Type' => 'application/json',
    ];
  }

  /**
   * @param string $method
   * @param string $url
   * @param array<string,mixed> $headers
   * @param array<string,mixed> $params
   *
   * @return \Psr\Http\Message\ResponseInterface
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function request(string $method, string $url, array $headers = [], array $params = []): ResponseInterface {
    $client = new Client([
      'headers' => array_merge($this->requestHeaders, $headers),
    ]);

    $completeUrl = self::API_URL . $url;

    return $client->request($method, $completeUrl, ['form_params' => $params]);
  }

}
