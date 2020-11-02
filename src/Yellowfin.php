<?php

namespace SmartOysters\Yellowfin;

use Devio\Pipedrive\Http\PipedriveClient4;
use Devio\Pipedrive\Http\Request;
use Devio\Pipedrive\Http\PipedriveClient;
use GuzzleHttp\Client as GuzzleClient;

/**
 * @package Yellowfin
 */
class Yellowfin
{
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

    protected $isOauth;

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

    public function isOauth()
    {
        return $this->isOauth;
    }

    /**
     * Pipedrive constructor.
     *
     * @param $token
     */
    public function __construct($token = '', $uri = '')
    {
        $this->token = $token;
        $this->baseURI = $uri;

        $this->isOauth = false;
    }

    /**
     * Prepare for OAuth.
     *
     * @param $config
     * @return FarmDecisionTech
     */
    public static function OAuth($config)
    {
        $new = new self('oauth', 'https://api.farmdecisiontech.net.au/token.php');

        $new->isOauth = true;

        $new->clientId = $config['clientId'];
        $new->clientSecret = $config['clientSecret'];
        $new->redirectUrl = $config['redirectUrl'];

        $new->storage = $config['storage'];

        return $new;
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
     * Redirect to OAuth.
     */
    public function OAuthRedirect()
    {
        $params = [
            'client_id'    => $this->clientId,
            'state'        => '',
            'redirect_uri' => $this->redirectUrl,
        ];
        $query = http_build_query($params);
        $url = 'https://oauth.pipedrive.com/oauth/authorize?' . $query;
        header('Location: ' . $url);
        exit;
    }

    /**
     * Get current OAuth access token object (which includes refreshToken and expiresAt)
     */
    public function getAccessToken()
    {
        return $this->storage->getToken();
    }

    /**
     * OAuth authorization.
     *
     * @param $code
     */
    public function authorize($code)
    {
        $client = new GuzzleClient([
            'auth' => [
                $this->getClientId(),
                $this->getClientSecret()
            ]
        ]);
        $response = $client->request('POST', 'https://oauth.pipedrive.com/oauth/token', [
            'form_params' => [
                'grant_type'   => 'authorization_code',
                'code'         => $code,
                'redirect_uri' => $this->redirectUrl,
            ]
        ]);
        $resBody = json_decode($response->getBody());

        $token = new PipedriveToken([
            'accessToken'  => $resBody->access_token,
            'expiresAt'    => time() + $resBody->expires_in,
            'refreshToken' => $resBody->refresh_token,
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
        return 'Devio\\Pipedrive\\Resources\\' . Str::studly($resource);
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
     * @return Client
     */
    protected function getClient()
    {
        if ($this->guzzleVersion >= 6) {
            return $this->isOauth()
                ? PipedriveClient::OAuth($this->getBaseURI(), $this->storage, $this)
                : new PipedriveClient($this->getBaseURI(), $this->token);
        } else {
            return new PipedriveClient4($this->getBaseURI(), $this->token);
        }
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
}
