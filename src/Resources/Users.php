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

class Users extends Resource
{
    protected $disabled = ['fetch', 'update'];

    /**
     * Get available metadata for the Users list
     * @link https://developers.yellowfinbi.com/dev/api-docs/current/#operation/getUsersListsMetadata
     *
     * @param array $options
     * @return Response
     */
    public function getUsersListsMetadata($options = [])
    {
        return $this->request->get('metadata', $options);
    }

    /**
     * Get a user
     * @link https://developers.yellowfinbi.com/dev/api-docs/current/#operation/getUser
     *
     * @param int $id
     * @return Response
     */
    public function getUser($userId)
    {
        return $this->request->get(':userId', compact('userId'));
    }

    /**
     * Update a user
     * @link https://developers.yellowfinbi.com/dev/api-docs/current/#operation/updateUser
     *
     * @param $userId
     * @param $userTitle
     * @param $description
     * @return Response
     */
    public function updateUser($userId, $userTitle, $description)
    {
        $options = array_merge(
            compact('userId', 'userTitle', 'description')
        );

        return $this->request->put(':userId', $options);
    }

}
