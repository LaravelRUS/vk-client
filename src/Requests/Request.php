<?php

/**
 * This file is part of VkClient package.
 *
 * @author ATehnix <atehnix@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ATehnix\VkClient\Requests;

use ATehnix\VkClient\Contracts\RequestInterface;
use ATehnix\VkClient\Contracts\ScriptableInterface;

/**
 * Class Request
 *
 * @package ATehnix\VkClient
 */
class Request implements RequestInterface, ScriptableInterface
{
    /**
     * @var string
     */
    protected $method;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var null|string
     */
    protected $token;

    /**
     * Request constructor.
     *
     * @param string $method
     * @param array $parameters
     * @param null|string $token
     */
    public function __construct($method, array $parameters, $token = null)
    {
        $this->method = $method;
        $this->parameters = $parameters;
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return null|string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function toScript()
    {
        $parameters = [];
        
        foreach ($this->parameters as $key => $value) {
            $parameters[] = sprintf('"%s": "%s"', $key, $value);
        }

        return sprintf('API.%s({%s})', $this->method, implode(', ', $parameters));
    }
}
