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

/**
 * Interface RequestInterface
 *
 * @package ATehnix\VkClient
 */
interface RequestInterface
{
    /**
     * @return string
     */
    public function getMethod();

    /**
     * @return array
     */
    public function getParameters();

    /**
     * @return null|string
     */
    public function getToken();
}
