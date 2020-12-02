<?php

/*
 * This file is part of the Yellowfin REST API PHP Package
 *
 * (c) James Rickard <james.rickard@smartoysters.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SmartOysters\Yellowfin\Resources;

use SmartOysters\Yellowfin\Resources\Base\Resource;
use SmartOysters\Yellowfin\Http\Response;

class LoginTokens extends Resource
{
    /**
     * Create a new single sign on token
     * @link https://developers.yellowfinbi.com/dev/api-docs/current/#operation/createLoginToken
     *
     * @param string  $userName
     * @param string  $password
     * @param integer $clientOrgRef
     * @param boolean $noPassword
     * @param array   $loginParameters
     * @param array   $customParameters
     * @return Response
     */
    public function createLoginToken($userName, $password, $clientOrgRef, $noPassword = false, $loginParameters = [], $customParameters = [])
    {
        $signOnUser = [
            'userName' => $userName,
            'password' => $password,
            'clientOrgRef' => $clientOrgRef
        ];

        return $this->request->post('', compact('signOnUser', 'loginParameters', 'noPassword', 'customParameters'));
    }


    /**
     * Deletes an SSO login token (log off)
     * @link https://developers.yellowfinbi.com/dev/api-docs/current/#operation/deleteLoginToken
     *
     * @param string $loginTokenId
     * @return Response
     */
    public function deleteLoginToken($loginTokenId)
    {
        return $this->request->delete(':loginTokenId', compact('loginTokenId'));
    }
}

