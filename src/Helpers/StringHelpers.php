<?php

/*
 * This file is part of the Yellowfin REST API PHP Package
 *
 * (c) James Rickard <james.rickard@smartoysters.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SmartOysters\Yellowfin\Helpers;

trait StringHelpers
{
    /**
     * String becomes CapitalCase
     *
     * @param $value
     * @return string|string[]
     */
    public function capsCase($value)
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return str_replace(' ', '', $value);
    }

    /**
     * String becomes camelCase
     *
     * @param $value
     * @return string|string[]
     */
    public function camelCase($value)
    {
        return lcfirst($this->capsCase($value));
    }

    public function snakeCase($value, $delimiter = '_')
    {
        $key = $value;

        if (! ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));

            $value = mb_strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value), 'UTF-8');
        }

        return $value;
    }
}
