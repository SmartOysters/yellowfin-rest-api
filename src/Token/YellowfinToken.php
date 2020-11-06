<?php

namespace SmartOysters\Yellowfin\Token;

use GuzzleHttp\Client as GuzzleClient;
use SmartOysters\Yellowfin\Helpers\StringHelpers;
use SmartOysters\Yellowfin\Helpers\ArrayHelpers;
use SmartOysters\Yellowfin\Yellowfin;

class YellowfinToken
{
    use StringHelpers;
    use ArrayHelpers;

    /**
     * The access token.
     *
     * @var string
     */
    protected $accessToken;

    /**
     * The expiry date.
     *
     * @var string
     */
    protected $expiresAt;

    /**q
     * The token type.
     *
     * @var string
     */
    protected $tokenType;

    /**
     * The refresh token.
     *
     * @var string
     */
    protected $refreshToken;

    /**
     * The scope.
     *
     * @var string
     */
    protected $scope;

    /**
     * Serialised Logout Address
     *
     * @var string
     */
    protected $logout;

    /**
     * Yellowfin constructor.
     *
     * @param $config
     */
    public function __construct($config)
    {
        $config = $this->mapArrayKeys([$this, 'camelCase'], $config);

        foreach ($config as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Get the access token.
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Get the expiry date.
     *
     * @return string
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * Get the token type.
     *
     * @return string
     */
    public function getTokenType()
    {
        return $this->tokenType;
    }

    /**
     * Get the refresh token.
     *
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Get the scope.
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Get the logout.
     *
     * @return string
     */
    public function getLogout()
    {
        return $this->logout;
    }

    /**
     * Check if the access token exists.
     *
     * @return bool
     */
    public function valid()
    {
        return ! empty($this->accessToken);
    }

    /**
     * Refresh the token only if needed.
     *
     * @param Yellowfin $yellowfin
     */
    public function refreshIfNeeded($yellowfin)
    {
        if (! $this->needsRefresh()) {
            return;
        }

        $time_format = $this->milliseconds();
        $nonce = bin2hex(random_bytes(16));

        $client = new GuzzleClient([
            'headers' => [
                'Content-Type' => "application/json",
                'Accept' => "application/vnd.yellowfin.api-v1+json",
                'Authorization' => "YELLOWFIN ts={$time_format}, nonce={$nonce}, token={$this->getRefreshToken()}"
            ]
        ]);

        $response = $client->request('POST', $yellowfin->getBaseURI() . 'access-tokens', []);

        $resBody = json_decode($response->getBody()->getContents());
        $accessToken = $resBody->_embedded->accessToken;

        $this->accessToken = $accessToken->securityToken;
        $this->expiresAt = time() + $accessToken->expiry;
        $this->tokenType = 'access_token';
        $this->refreshToken = $resBody->securityToken;

        $storage = $yellowfin->getStorage();

        $storage->setToken($this);
    }

    /**
     * Check if the token needs to be refreshed.
     *
     * @return bool
     */
    public function needsRefresh()
    {
        return (int) $this->expiresAt - time() < 1;
    }

    private function milliseconds() {
        $mt = explode(' ', microtime());
        return ((int)$mt[1]) * 1000 + ((int)round($mt[0] * 1000));
    }
}
