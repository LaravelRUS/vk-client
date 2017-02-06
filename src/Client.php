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

use ATehnix\VkClient\Contracts\ClientInterface as ClientContract;
use ATehnix\VkClient\Contracts\RequestInterface;
use ATehnix\VkClient\Exceptions\VkException;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Client
 *
 * @package ATehnix\VkClient
 */
class Client implements ClientContract
{
    const API_URI = 'https://api.vk.com/method/';
    const API_VERSION = '5.62';
    const API_TIMEOUT = 30.0;

    /**
     * @var ClientInterface
     */
    protected $http;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var bool
     */
    protected $passError = false;

    /**
     * Client constructor.
     *
     * @param string $version
     * @param ClientInterface $http
     */
    public function __construct($version = null, ClientInterface $http = null)
    {
        $this->version = $version ?: static::API_VERSION;
        $this->http = $this->resolveHttpClient($http);
    }

    /**
     * @param ClientInterface|null $http
     * @return ClientInterface
     */
    private function resolveHttpClient(ClientInterface $http = null)
    {
        return $http ?: new HttpClient([
            'base_uri'    => static::API_URI,
            'timeout'     => static::API_TIMEOUT,
            'http_errors' => false,
            'headers'     => [
                'User-Agent' => 'github.com/atehnix/vk-client',
                'Accept'     => 'application/json',
            ],
        ]);
    }

    /**
     * @param string $token
     * @return static
     */
    public function setDefaultToken($token)
    {
        $this->token = $token;

        return $this;
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
     * @param RequestInterface $request
     * @return array
     * @throws \ATehnix\VkClient\Exceptions\VkException
     */
    public function send(RequestInterface $request)
    {
        return $this->request(
            $request->getMethod(),
            $request->getParameters(),
            $request->getToken()
        );
    }

    /**
     * @param string $method
     * @param array $parameters
     * @param string|null $token
     * @return array
     * @throws \ATehnix\VkClient\Exceptions\VkException
     */
    public function request($method, $parameters = [], $token = null)
    {
        $options = $this->buildOptions($parameters, $token);
        $response = $this->http->request('POST', $method, $options);

        return $this->getResponseData($response);
    }

    /**
     * @param array $parameters
     * @param string|null $requestToken
     * @return array
     */
    protected function buildOptions($parameters, $requestToken)
    {
        $parameters['v'] = $this->version;
        $token = $requestToken ?: $this->token;

        if ($token) {
            $parameters['access_token'] = $token;
        }

        return [RequestOptions::FORM_PARAMS => $parameters];
    }

    /**
     * @param ResponseInterface $response
     * @return array
     * @throws VkException
     */
    protected function getResponseData(ResponseInterface $response)
    {
        $data = json_decode((string)$response->getBody(), true);

        if (!$this->passError) {
            $this->checkErrors($data);
        }

        return $data;
    }

    /**
     * @param array|false $data
     * @throws VkException
     */
    protected function checkErrors($data)
    {
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new VkException('Invalid VK response format: ' . json_last_error_msg());
        }

        if (!is_array($data)) {
            throw new VkException('Invalid response format');
        }

        if (isset($data['error'])) {
            throw self::toException($data['error']);
        }

        if (isset($data['execute_errors'][0])) {
            throw self::toException($data['execute_errors'][0]);
        }
    }

    /**
     * @param array $error
     * @return VkException
     */
    public static function toException($error)
    {
        $message = isset($error['error_msg']) ? $error['error_msg'] : '';
        $code = isset($error['error_code']) ? $error['error_code'] : 0;

        $map = [
            0  => Exceptions\VkException::class,
            1  => Exceptions\UnknownErrorVkException::class,
            5  => Exceptions\AuthorizationFailedVkException::class,
            6  => Exceptions\TooManyRequestsVkException::class,
            7  => Exceptions\PermissionDeniedVkException::class,
            9  => Exceptions\TooMuchSimilarVkException::class,
            10 => Exceptions\InternalErrorVkException::class,
            14 => Exceptions\CaptchaRequiredVkException::class,
            15 => Exceptions\AccessDeniedVkException::class,
        ];

        /** @var \Exception|\Throwable $exception */
        $exception = isset($map[$code]) ? $map[$code] : $map[0];

        return new $exception($message, $code);
    }
}
