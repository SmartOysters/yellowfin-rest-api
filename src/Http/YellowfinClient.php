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

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Exception\BadResponseException;
use SmartOysters\Yellowfin\Helpers\ArrayHelpers;

class YellowfinClient implements Client
{
    use ArrayHelpers;

    /**
     * The Guzzle client instance.
     * @var GuzzleClient
     */
    protected $client;

    protected $queryDefaults = [];

    /**
     * SaferMeClient constructor.
     *
     * @param string $url
     * @param string $token
     * @param array $options
     */
    public function __construct($url, $token, $options = [])
    {
        list($headers, $query) = [[], []];
        $time_format = $this->milliseconds();
        $nonce = bin2hex(random_bytes(16));

        $headers = [
            'Content-Type' => "application/json",
            'Accept' => "application/vnd.yellowfin.api-v1+json",
            'Authorization' => "YELLOWFIN ts={$time_format}, nonce={$nonce}, token={$token->getAccessToken()}"
        ];

        $this->client = new GuzzleClient(array_merge([
            'base_uri' => $url,
            'query' => $query,
            'headers' => $headers
        ], $options));
    }

    /**
     * Create an OAuth client.
     *
     * @param $url
     * @param $storage
     * @param $yellowfin
     * @return YellowfinClient
     */
    public static function OAuth($url, $storage, $yellowfin)
    {
        $token = $storage->getToken();

        if (! $token || ! $token->valid()) {
            $token = $yellowfin->authorize();
        }

        $token->refreshIfNeeded($yellowfin);

        return new self($url, $token);
    }

    /**
     * Perform a GET request.
     *
     * @param       $url
     * @param array $parameters
     * @return Response
     */
    public function get($url, $parameters = [])
    {
        $options = $this->getClient()
            ->getConfig();
        $this->arraySet($options, 'query', array_merge($parameters, $options['query']));

        // For this particular case we have to include the parameters into the
        // URL query. Merging the request default query configuration to the
        // request parameters will make the query key contain everything.
        return $this->execute(new GuzzleRequest('GET', $url), $options);
    }

    /**
     * Perform a POST request.
     *
     * @param $url
     * @param array $parameters
     * @return Response
     */
    public function post($url, $parameters = [])
    {
        $request = new GuzzleRequest('POST', $url);
        $form = 'form_params';
        $data = $parameters;

        // If any file key is found, we will assume we have to convert the data
        // into the multipart array structure. Otherwise, we will perform the
        // request as usual using the form_params with the given parameters.
        if (isset($parameters['file'])) {
            $form = 'multipart';
            $data = $this->multipart($parameters);
        }

        return $this->execute($request, [$form => $data]);
    }

    /**
     * Convert the parameters into a multipart structure.
     *
     * @param array $parameters
     * @return array
     */
    protected function multipart(array $parameters)
    {
        if (!($file = $parameters['file']) instanceof \SplFileInfo) {
            throw new \InvalidArgumentException('File must be an instance of \SplFileInfo.');
        }

        $result = [];
        $content = file_get_contents($file->getPathname());

        foreach ($this->arrayExclude($parameters, 'file') as $key => $value) {
            $result[] = ['name' => $key, 'contents' => (string)$value];
        }
        // Will convert every element of the array into a format accepted by the
        // multipart encoding standards. It will also add a special item which
        // includes the file key name, the content of the file and its name.
        $result[] = ['name' => 'file', 'contents' => $content, 'filename' => $file->getFilename()];

        return $result;
    }

    /**
     * Perform a PUT request.
     *
     * @param       $url
     * @param array $parameters
     * @return Response
     */
    public function put($url, $parameters = [])
    {
        $request = new GuzzleRequest('PUT', $url);

        return $this->execute($request, ['form_params' => $parameters]);
    }

    /**
     * Perform a DELETE request.
     *
     * @param       $url
     * @param array $parameters
     * @return Response
     */
    public function delete($url, $parameters = [])
    {
        $request = new GuzzleRequest('DELETE', $url);

        return $this->execute($request, ['form_params' => $parameters]);
    }

    /**
     * Execute the request and returns the Response object.
     *
     * @param GuzzleRequest $request
     * @param GuzzleClient|null $client
     * @return Response
     */
    protected function execute(GuzzleRequest $request, array $options = [], $client = null)
    {
        $client = $client ?: $this->getClient();

        try {
            $response = $client->send($request, $options);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        }

        $body = $response->getHeader('location') ?: json_decode($response->getBody());

        return new Response(
            $response->getStatusCode(), $body, $response->getHeaders()
        );
    }

    /**
     * Return the client.
     *
     * @return GuzzleClient
     */
    public function getClient()
    {
        return $this->client;
    }

    private function milliseconds() {
        $mt = explode(' ', microtime());
        return ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
    }
}
