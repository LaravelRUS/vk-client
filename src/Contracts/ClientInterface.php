<?php
/**
 * This file is part of VkClient package.
 *
 * @author ATehnix <atehnix@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ATehnix\VkClient\Contracts;

use ATehnix\VkClient\Contracts\RequestInterface;
use ATehnix\VkClient\Exceptions\VkException;

/**
 * Interface ClientInterface
 *
 * @package ATehnix\VkClient\Contracts
 */
interface ClientInterface
{
    /**
     * @param RequestInterface $request
     * @return array
     * @throws VkException
     */
    public function send(RequestInterface $request);

    /**
     * @param string $method
     * @param array $parameters
     * @param string|null $token
     * @return array
     * @throws VkException
     */
    public function request($method, $parameters = [], $token = null);
}
