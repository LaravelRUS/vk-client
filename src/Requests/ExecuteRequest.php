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
     * @param Request[] $requests
     * @return ExecuteRequest
     */
    public static function make(array $requests)
    {
        $scripts = array_map(function ($request) {
            if (!$request instanceof Request) {
                throw new \InvalidArgumentException('Argument must be an array instances of '.Request::class);
            }

            return $request->toScript();
        }, $requests);

        $parameters['code'] = sprintf('return [%s];', implode(', ', $scripts));

        return new static($parameters);
    }
}
