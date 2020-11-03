<?php

/*
 * This file is part of the Yellowfin REST API PHP Package
 *
 * (c) James Rickard <james.rickard@smartoysters.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SmartOysters\Yellowfin\Http;

use SmartOysters\Yellowfin\Builder;
use SmartOysters\Yellowfin\Exception\ResponseException;
use SmartOysters\Yellowfin\Exception\YellowfinException;

/**
 * @method Response get($type, $target, $options = [])
 * @method Response post($type, $target, $options = [])
 * @method Response put($type, $target, $options = [])
 * @method Response delete($type, $target, $options = [])
 */
class Request
{
    /**
     * The Http client instance
     * @var Client
     */
    protected $client;

    /**
     * Request constructor
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->builder = new Builder();
    }

    /**
     * Prepare and run the query
     *
     * @param $type
     * @param $target
     * @param array $options
     * @return Response
     * @throws ResponseException
     * @throws YellowfinException
     */
    protected function performRequest($type, $target, $options = [])
    {
        $this->builder->setTarget($target);

        $endpoint = $this->builder->buildEndpoint($options);

        $query = $this->builder->getQueryVars($options);

        return $this->executeRequest($type, $endpoint, $query);
    }

    /**
     * Execute the query against the HTTP client.
     *
     * @param string $type
     * @param string $endpoint
     * @param array  $query
     * @return Response
     * @throws ResponseException
     * @throws YellowfinException
     */
    protected function executeRequest($type, $endpoint, $query = [])
    {
        return $this->handleResponse(
            call_user_func_array([$this->client, $type], [$endpoint, $query])
        );
    }

    /**
     * Handling the server response.
     *
     * @param Response $response
     * @return Response
     * @throws ResponseException
     * @throws YellowfinException
     */
    protected function handleResponse(Response $response)
    {
        $content = $response->getContent();

        // Direct return of response when success
        if (in_array($response->getStatusCode(), [200,201])) {
            return $response;
        }

        if (!empty($content) || !($response->getStatusCode() == 302 || $response->isSuccess())) {
            if (in_array($response->getStatusCode(), [400, 401, 403, 404, 410])) {
                throw new ResponseException(
                    (isset($content->error) ? $content->error->message : "Error unknown."),
                    $response->getStatusCode()
                );
            }

            throw new YellowfinException(
                isset($content->error) ? $content->error->message : "Error unknown.",
                $response->getStatusCode()
            );
        }

        return $response;
    }

    /**
     * Set the endpoint name.
     *
     * @param $resource
     * @return $this
     */
    public function setResource($resource)
    {
        $this->builder->setResource($resource);

        return $this;
    }

    /**
     * Set the token.
     *
     * @param $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->builder->setToken($token);

        return $this;
    }

    /**
     * Pointing request operations to the request performer.
     *
     * @param       $name
     * @param array $args
     * @return Response
     */
    public function __call($name, $args = [])
    {
        if (in_array($name, ['get', 'post', 'put', 'delete'])) {
            $options = !empty($args[1]) ? $args[1] : [];

            return $this->performRequest($name, $args[0], $options);
        }
    }
}
