<?php

/**
 * PHP version 5.6
 *
 * Response from MoySklad API
 *
 * @category MoySklad
 * @package  MoySklad
 * @author   Artahanov A.A. <azgalot9@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://online.moysklad.ru/api/remap/1.1/doc/index.html
 */

namespace MoySklad\Response;

use MoySklad\Exception\InvalidJsonException;

/**
 * PHP version 5.6
 *
 * Response from retailCRM API
 *
 * @category MoySklad
 * @package  MoySklad
 * @author   Artahanov A.A. <azgalot9@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://online.moysklad.ru/api/remap/1.1/doc/index.html
 */
class ApiResponse implements \ArrayAccess
{
    // HTTP response status code
    protected $statusCode;

    // response assoc array
    protected $response;

    /**
     * ApiResponse constructor.
     *
     * @param int   $statusCode   HTTP status code
     * @param mixed $responseBody HTTP body
     *
     * @throws InvalidJsonException
     */
    public function __construct($statusCode, $responseBody = null)
    {
        $this->statusCode = (int) $statusCode;

        if (!empty($responseBody)) {
            $response = json_decode($responseBody, true);

            if (!$response && JSON_ERROR_NONE !== ($error = json_last_error())) {
                throw new InvalidJsonException(
                    "Invalid JSON in the API response body. Error code #$error",
                    $error
                );
            }

            $this->response = $response;
        }
    }

    /**
     * Return HTTP response status code
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * HTTP request was successful
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->statusCode < 400;
    }

    /**
     * Allow to access for the property throw class method
     *
     * @param string $name      method name
     * @param mixed  $arguments method parameters
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        // convert getSomeProperty to someProperty
        $propertyName = strtolower(substr($name, 3, 1)) . substr($name, 4);

        if (!isset($this->response[$propertyName])) {
            throw new \InvalidArgumentException("Method \"$name\" not found");
        }

        return $this->response[$propertyName];
    }

    /**
     * Allow to access for the property throw object property
     *
     * @param string $name property name
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (!isset($this->response[$name])) {
            throw new \InvalidArgumentException("Property \"$name\" not found");
        }

        return $this->response[$name];
    }

    /**
     * Offset set
     *
     * @param mixed $offset offset
     * @param mixed $value  value
     *
     * @throws \BadMethodCallException
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('This activity not allowed');
    }

    /**
     * Offset unset
     *
     * @param mixed $offset offset
     *
     * @throws \BadMethodCallException
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('This call not allowed');
    }

    /**
     * Check offset
     *
     * @param mixed $offset offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->response[$offset]);
    }

    /**
     * Get offset
     *
     * @param mixed $offset offset
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (!isset($this->response[$offset])) {
            throw new \InvalidArgumentException("Property \"$offset\" not found");
        }

        return $this->response[$offset];
    }
}
