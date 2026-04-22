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
    /** @var null|string $accessToken */
    $accessToken = \Civi::settings()->get(SettingsManager::API_ACCESS_TOKEN);

    $this->requestHeaders = [
      'Authorization' => $accessToken ? 'Bearer ' . $accessToken : '',
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
