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

use ATehnix\VkClient\Contracts\ScriptableInterface;

/**
 * Class Request
 *
 * @package ATehnix\VkClient
 */
class ExecuteRequest extends Request
{
    /**
     * ExecuteRequest constructor.
     *
     * @param array $parameters
     * @param null|string $token
     */
    public function __construct(array $parameters, $token = null)
    {
        parent::__construct('execute', $parameters, $token);
    }

    /**
     * @param ScriptableInterface[] $requests
     * @param null|string $token
     * @return ExecuteRequest
     */
    public static function make(array $requests, $token = null)
    {
        $scripts = array_map(function ($request) {
            if (!$request instanceof ScriptableInterface) {
                throw new \InvalidArgumentException(
                    'Argument must be an array instances of '.ScriptableInterface::class
                );
            }

            return $request->toScript();
        }, $requests);

        $parameters['code'] = sprintf('return [%s];', implode(', ', $scripts));

        return new static($parameters, $token);
    }
}
