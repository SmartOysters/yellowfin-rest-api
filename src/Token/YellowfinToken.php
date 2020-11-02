<?php

namespace SmartOysters\Yellowfin\Token;

use GuzzleHttp\Client as GuzzleClient;

class YellowfinToken
{
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
     * FarmDecisionTech constructor.
     *
     * @param $config
     */
    public function __construct($config)
    {
        $config = array_map('camel_case', $config);

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
    public function expiresAt()
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
     * @param $yellowfin
     */
    public function refreshIfNeeded($yellowfin)
    {
        if (! $this->needsRefresh()) {
            return;
        }

        $client = new GuzzleClient([
            'auth' => [
                $yellowfin->getClientId(),
                $yellowfin->getClientSecret()
            ]
        ]);

        $response = $client->request('POST', 'https://api.farmdecisiontech.net.au/token.php', [
            'form_params' => [
                'grant_type'   => 'refresh_token',
                'refresh_token' => $this->refreshToken
            ]
        ]);

        $tokenInstance = json_decode($response->getBody());

        $this->accessToken = $tokenInstance->access_token;
        $this->expiresAt = time() + $tokenInstance->expires_in;
        $this->tokenType = $tokenInstance->token_type;

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
}
