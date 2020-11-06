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

use SmartOysters\Yellowfin\Helpers\StringHelpers;
use SmartOysters\Yellowfin\Http\Request;
use SmartOysters\Yellowfin\Http\YellowfinClient;
use GuzzleHttp\Client as GuzzleClient;
use SmartOysters\Yellowfin\Token\YellowfinToken;
use SmartOysters\Yellowfin\Resources\Users;

/**
 * @method Users users()
 */
class Yellowfin
{
    use StringHelpers;

    /**
     * The base URI.
     *
     * @var string
     */
    protected $baseURI;

    /**
     * The API token.
     *
     * @var string
     */
    protected $token;

    /**
     * @var array|mixed
     */
    protected $options;

    /**
     * The OAuth client id.
     *
     * @var string
     */
    protected $clientId;

    /**
     * The client secret string.
     *
     * @var string
     */
    protected $clientSecret;

    /**
     * The client organisation reference.
     *
     * @var string
     */
    protected $clientOrg;

    /**
     * The redirect URL.
     *
     * @var string
     */
    protected $redirectUrl;

    /**
     * The OAuth storage.
     *
     * @var mixed
     */
    protected $storage;

    /**
     * Pipedrive constructor.
     *
     * @param $token
     */
    public function __construct($token = '', $uri = '', $options = [])
    {
        $this->token = $token;
        $this->baseURI = $uri;
        $this->options = $options;
    }

    /**
     * Get the client ID.
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Get the client secret.
     *
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * Get the client organisation reference
     *
     * @return string
     */
    public function getClientOrg()
    {
        return $this->clientOrg;
    }

    /**
     * Get the redirect URL.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /**
     * Get the storage instance.
     *
     * @return mixed
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Get current OAuth access token object (which includes refreshToken and expiresAt)
     */
    public function getAccessToken()
    {
        return $this->storage->getToken();
    }

    /**
     * Prepare for OAuth.
     *
     * @param $config
     * @return Yellowfin
     */
    public static function OAuth($config)
    {
        $new = new self('oauth', $config['uri']);

        $new->clientId = $config['clientId'];
        $new->clientSecret = $config['clientSecret'];
        $new->clientOrg = $config['clientOrg'];
        $new->redirectUrl = $config['redirectUrl'];
        $new->options = (array_key_exists('options', $config)) ? $config['options'] : [];

        $new->storage = $config['storage'];

        return $new;
    }

    /**
     * OAuth authorization.
     */
    public function authorize()
    {
        $time_format = $this->milliseconds();
        $nonce = bin2hex(random_bytes(16));

        $client = new GuzzleClient([
            'headers' => [
                'Content-Type' => "application/json",
                'Accept' => "application/vnd.yellowfin.api-v1+json",
                'Authorization' => "YELLOWFIN ts={$time_format}, nonce={$nonce}"
            ]
        ]);

        $response = $client->request('POST', $this->baseURI . 'refresh-tokens', [
            'json' => array_merge([
                'userName'   => $this->getClientId(),
                'password'   => $this->getClientSecret(),
                'clientOrgRef' => $this->getClientOrg()
            ], $this->options)
        ]);

        $resBody = json_decode($response->getBody()->getContents());
        $accessToken = $resBody->_embedded->accessToken;

        $token = new YellowfinToken([
            'access_token'  => $accessToken->securityToken,
            'expires_at'    => time() + $accessToken->expiry,
            'refresh_token' => $resBody->securityToken,
            'token_type' => 'refresh_token'
        ]);

        $this->storage->setToken($token);
    }

    /**
     * Get the resource instance.
     *
     * @param $resource
     * @return mixed
     */
    public function make($resource)
    {
        $class = $this->resolveClassPath($resource);

        return new $class($this->getRequest());
    }

    /**
     * Get the resource path.
     *
     * @param $resource
     * @return string
     */
    protected function resolveClassPath($resource)
    {
        return 'SmartOysters\\Yellowfin\\Resources\\' . $this->capsCase($resource);
    }

    /**
     * Get the request instance.
     *
     * @return Request
     */
    public function getRequest()
    {
        return new Request($this->getClient());
    }

    /**
     * Get the HTTP client instance.
     *
     * @return YellowfinClient
     */
    protected function getClient()
    {
        return YellowfinClient::OAuth($this->getBaseURI(), $this->storage, $this);
    }

    /**
     * Get the base URI.
     *
     * @return string
     */
    public function getBaseURI()
    {
        return $this->baseURI;
    }

    /**
     * Set the base URI.
     *
     * @param string $baseURI
     */
    public function setBaseURI($baseURI)
    {
        $this->baseURI = $baseURI;
    }

    /**
     * Set the token.
     *
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Any reading will return a resource.
     *
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->make($name);
    }

    /**
     * Methods will also return a resource.
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (! in_array($name, get_class_methods(get_class()))) {
            return $this->{$name};
        }
    }

    private function milliseconds() {
        $mt = explode(' ', microtime());
        return ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
    }
}
