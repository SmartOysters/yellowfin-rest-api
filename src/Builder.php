<?php

/*
 * This file is part of the Yellowfin REST API PHP Package
 *
 * (c) James Rickard <james.rickard@smartoysters.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SmartOysters\Yellowfin;

use SmartOysters\Yellowfin\Helpers\ArrayHelpers;

class Builder
{
    use ArrayHelpers;

    /**
     * API base URL.
     *
     * @var string
     */
    protected $base = '/api/{endpoint}';

    /**
     * Resource name.
     *
     * @var string
     */
    protected $resource = '';

    /**
     * Full URI without resource.
     *
     * @var string
     */
    protected $target = '';

    /**
     * The API token.
     *
     * @var string
     */
    protected $token;

    /**
     * Get the name of the URI parameters.
     *
     * @param string $target
     * @return array
     */
    public function getParameters($target = '')
    {
        if (empty($target)) {
            $target = $this->getTarget();
        }

        preg_match_all('/:\w+/', $target, $result);

        return str_replace(':', '', $this->arrayFlatten($result));
    }

    /**
     * Replace URI tags by the values in options.
     *
     * buildUri(':id', ['id' => 55', 'name' => 'foo'])
     * will give:
     * 'organizations/55'
     *
     * @param array $options
     * @return mixed
     */
    public function buildEndpoint($options = [])
    {
        $endpoint = $this->getEndpoint();

        // Having the URI, we'll now replace every parameter preceed with a colon
        // character with the values matching the keys of the options array. If
        // any of these parameters is not set we'll notify with an exception.
        foreach ($options as $key => $value) {
            if (is_array($value)) {
                continue;
            }

            $endpoint = preg_replace("/:{$key}/", $value, $endpoint);
        }

        if (count($this->getParameters($endpoint))) {
            throw new \InvalidArgumentException('The URI contains unassigned params.');
        }

        return $endpoint;
    }

    /**
     * Get the full URI with the endpoint if any.
     *
     * @return string
     */
    protected function getEndpoint()
    {
        $result = $this->getTarget();

        if (!empty($this->getResource())) {
            $follow = (!empty($result)) ? '/'. $result : '';
            $result = $this->getResource() . $follow;
        }

        return $result;
    }

    /**
     * Get the options that are not replaced in the URI.
     *
     * @return array
     */
    public function getQueryVars(array $options = [])
    {
        $vars = $this->getParameters();

        return $this->arrayExclude($options, $vars);
    }

    /**
     * Get the resource name
     *
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set the resource name
     *
     * @return Builder
     */
    public function setResource($name)
    {
        $this->resource = $name;

        return $this;
    }

    /**
     * Get the target
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set the target.
     *
     * @return Builder
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Set the application token.
     *
     * @return Builder
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get the base URL.
     *
     * @return string
     */
    public function getBase()
    {
        return $this->base;
    }
}
