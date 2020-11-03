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

class Response
{
    /**
     * The response code.
     *
     * @var integer
     */
    protected $statusCode;

    /**
     * The response data.
     *
     * @var mixed
     */
    protected $content;

    /**
     * The response headers.
     *
     * @var array
     */
    private $headers;

    /**
     * Response constructor.
     *
     * @param integer $statusCode
     * @param mixed   $content
     * @param array   $headers
     */
    public function __construct($statusCode, $content, $headers = [])
    {
        $this->statusCode = $statusCode;
        $this->content = $content;
        $this->headers = $headers;
    }

    /**
     * Check if the request was successful.
     */
    public function isSuccess()
    {
        if (! $this->getContent()) {
            return false;
        }

        return $this->getContent();
    }

    /**
     * Get the request data.
     */
    public function getData()
    {
        if ($this->isSuccess() && !empty($this->getContent())) {
            return $this->getContent();
        }

        return null;
    }

    /**
     * Get the status code.
     *
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Get the content.
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get the headers array.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
