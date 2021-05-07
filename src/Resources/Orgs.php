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

class Orgs extends Resource
{
    protected $disabled = ['create'];

    /**
     * Create an Organisation
     * @link https://developers.yellowfinbi.com/dev/api-docs/current/#operation/createOrg
     *
     * @param       $clientRefId
     * @param       $name
     * @param       $defaultTimezone
     * @param       $customStylePath
     * @param array $options
     * @return Response
     */
    public function createOrg($clientRefId, $name, $defaultTimezone, $customStylePath, $options = [])
    {
        $options = array_merge(
            compact('clientRefId', 'name', 'defaultTimezone', 'customStylePath'),
            $options
        );

        return $this->request->post('', $options);
    }

    /**
     * Update an organization
     * @link https://developers.yellowfinbi.com/dev/api-docs/current/#operation/updateOrg
     *
     * @param       $ipOrg
     * @param       $clientRefId
     * @param       $name
     * @param       $defaultTimezone
     * @param       $customStylePath
     * @param array $options
     * @return Response
     */
    public function updateOrg($ipOrg, $clientRefId, $name, $defaultTimezone, $customStylePath, $options = [])
    {
        $options = array_merge(
            compact('ipOrg', 'clientRefId', 'name', 'defaultTimezone', 'customStylePath'),
            $options
        );

        return $this->request->patch(':ipOrg', $options);
    }

    /**
     * Add login access for a user to this org
     * @link https://developers.yellowfinbi.com/dev/api-docs/current/#operation/addOrgUserAccess
     *
     * @param $ipOrg
     * @param $userId
     * @return Response
     */
    public function addOrgUserAccess($ipOrg, $userId)
    {
        $options = array_merge(
            compact('ipOrg', 'userId')
        );

        return $this->request->post(':ipOrg/user-access', $options);
    }

    /**
     * Add login access for a user to this org
     * @link * https://developers.yellowfinbi.com/dev/api-docs/v1.2/#operation/getOrgUserAccessList
     *
     * @param $ipOrg
     * @return Response
     */
    public function getOrgUserAccessList($ipOrg)
    {
        return $this->request->post(':ipOrg/user-access', compact('ipOrg'));
    }

    /**
     * Remove a user's login access to the given org
     * @link https://developers.yellowfinbi.com/dev/api-docs/current/#operation/removeOrgUserAccess
     *
     * @param $ipOrg
     * @param $userId
     * @return Response
     */
    public function removeOrgUserAccess($ipOrg, $userId)
    {
        $options = array_merge(
            compact('ipOrg', 'userId')
        );

        return $this->request->delete(':ipOrg/user-access/:userId', $options);
    }
}
