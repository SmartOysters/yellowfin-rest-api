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

class Admin extends Resource
{
    protected $disabled = ['fetch', 'update'];

    /**
     * Create one or more users in the current organization
     * @link https://developers.yellowfinbi.com/dev/api-docs/current/#operation/createUsersAdmin
     *
     * @param string $userId       Required
     * @param string $emailAddress Required
     * @param string $roleCode     Required
     * @param string $password     Required
     * @param string $firstName
     * @param string $lastName
     * @param string $languageCode
     * @param string $timeZoneCode
     * @param array  $options
     * @return Response
     */
    public function createUsersAdmin($userId, $emailAddress, $roleCode, $password, $firstName = '', $lastName = '', $languageCode = '', $timeZoneCode = '', $options = [])
    {
        $options = array_merge(
            [compact('userId', 'emailAddress', 'roleCode', 'password', 'firstName', 'lastName', 'languageCode', 'timeZoneCode')],
            $options
        );

        return $this->request->post('users', $options);
    }

    /**
     * * Update a user for the purpose of user administration
     * @link https://developers.yellowfinbi.com/dev/api-docs/current/#operation/updateUserAdmin
     *
     * @param string $emailAddress
     * @param string $roleCode
     * @param string $firstName
     * @param string $lastName
     * @param string $languageCode
     * @param string $timeZoneCode
     * @return Response
     */
    public function updateUserAdmin($emailAddress = '', $roleCode = '', $firstName = '', $lastName = '', $languageCode = '', $timeZoneCode = '')
    {
        $options = array_merge(
            compact('emailAddress', 'roleCode', 'firstName', 'lastName', 'languageCode', 'timeZoneCode')
        );

        return $this->request->put('users/:userId', $options);
    }

    /**
     * Get a user for the purpose of user administration
     * @link https://developers.yellowfinbi.com/dev/api-docs/current/#operation/getUserAdmin
     *
     * @param $userId
     * @return Response
     */
    public function getUserAdmin($userId)
    {
        return $this->request->get('users/:userId', compact('userId'));
    }

    /**
     * Update the OrgAccess for a User
     * @link https://developers.yellowfinbi.com/dev/api-docs/current/#operation/addUserOrgAccess
     *
     * @param integer $userId
     * @param integer $orgId
     * @param array   $options
     * @return Response
     */
    public function addUserOrgAccess($userId, $orgId, $options = [])
    {
        $options = array_merge(
            compact('userId', 'orgId'),
            $options
        );

        return $this->request->post('users/:userId/org-access', $options);
    }
}
