<?php

/**
 * This file is part of VkClient package.
 *
 * @author ATehnix <atehnix@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ATehnix\VkClient;

use ATehnix\VkClient\Exceptions\InvalidGrantVkException;
use ATehnix\VkClient\Exceptions\VkException;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface;

/**
 * Class Auth
 *
 * @package ATehnix\VkClient
 */
class Auth
{
    const BASE_URI = 'https://oauth.vk.com/';
    const AUTHORIZE_URI = 'authorize?client_id=%s&scope=%s&redirect_uri=%s&response_type=code&display=page';
    const TOKEN_URI = 'access_token?client_id=%s&client_secret=%s&redirect_uri=%s&code=%s';
    const TIMEOUT = 30;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $redirectUri;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var ClientInterface
     */
    protected $http;

    /**
     * @var bool
     */
    protected $passError = false;

    /**
     * Auth constructor.
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUri
     * @param string $scope
     * @param ClientInterface $http
     */
    public function __construct($clientId, $clientSecret, $redirectUri, $scope = '', ClientInterface $http = null)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->scope = $scope;
        $this->http = $http ?: new HttpClient([
            'base_uri'    => static::BASE_URI,
            'timeout'     => static::TIMEOUT,
            'http_errors' => false,
            'headers'     => [
                'User-Agent' => 'github.com/atehnix/vk-client',
                'Accept'     => 'application/json',
            ],
        ]);
    }

    /**
     * @param bool $bool
     * @return static
     */
    public function setPassError($bool = true)
    {
        $this->passError = $bool;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return static::BASE_URI.sprintf(
            static::AUTHORIZE_URI,
            $this->clientId,
            $this->scope,
            $this->redirectUri
        );
    }

    /**
     * @param string $code
     * @return string
     * @throws VkException
     */
    public function getToken($code)
    {
        if (!$code) {
            return null;
        }

        $response = $this->http->request('GET', sprintf(
            static::TOKEN_URI,
            $this->clientId,
            $this->clientSecret,
            $this->redirectUri,
            $code
        ));

        $data = json_decode((string)$response->getBody(), true);

        if (isset($data['error']) && !$this->passError) {
            throw $this->getException($data);
        }

        return isset($data['access_token']) ? $data['access_token'] : $data;
    }

    /**
     * @param array $data
     * @return VkException
     */
    protected function getException($data)
    {
        $exception = ($data['error'] === 'invalid_grant')
            ? InvalidGrantVkException::class
            : VkException::class;

        return new $exception($data['error']);
    }
}
