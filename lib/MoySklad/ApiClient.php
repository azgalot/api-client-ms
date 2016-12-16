<?php

/**
 * PHP version 5.6
 *
 * API client class
 *
 * @category MoySklad
 * @package  MoySklad
 * @author   Artahanov A.A. <azgalot9@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://online.moysklad.ru/api/remap/1.1/doc/index.html
 */

namespace MoySklad;

use MoySklad\Http\Client;
use MoySklad\Response\ApiResponse;

/**
 * PHP version 5.6
 *
 * API client class
 *
 * @category MoySklad
 * @package  MoySklad
 * @author   Artahanov A.A. <azgalot9@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://online.moysklad.ru/api/remap/1.1/doc/index.html
 */
class ApiClient
{
    /**
     * Requests
     */
    const REQUEST_ATTRIBUTES_MAIN = array('metadata', 'all', 'bystore', 'byoperation');
    const REQUEST_ATTRIBUTES_SECOND = array(
        'accounts',
        'contactpersons',
        'packs',
        'cashiers',
        'positions'
    );
    
    /**
     * JsonAPI client МойСклад
     * @var client
     * @access protected
     */
    protected $client;

    /**
     * Entity mapping
     * @var entity
     * @access protected
     */
    protected $entity = array(
        "counterparty" => "entity",
        "consignment" => "entity",
        "currency" => "entity",
        "productFolder" => "entity",
        "service" => "entity",
        "product" => "entity",
        "contract" => "entity",
        "variant" => "entity",
        "project" => "entity",
        "state" => "entity",
        "employee" => "entity",
        "store" => "entity",
        "organization" => "entity",
        "retailshift" => "entity",
        "retailstore" => "entity",
        "cashier" => "entity",
        "customerOrder" => "entity",
        "demand" => "entity",
        "invoiceout" => "entity",
        "retaildemand" => "entity",
        "purchaseOrder" => "entity",
        "supply" => "entity",
        "invoicein" => "entity",
        "paymentin" => "entity",
        "paymentout" => "entity",
        "cashin" => "entity",
        "cashout" => "entity",
        "companysettings" => "entity",
        "expenseItem" => "entity",
        "country" => "entity",
        "uom" => "entity",
        "customentity" => "entity",
        "salesreturn" => "entity",
        "purchasereturn" => "entity",
        "stock" => "report",
        "assortment" => "pos",
        "openshift" => "pos",
        "closeshift" => "pos"
    );

    /**
     * Filters for get data requests
     * @var main_filters
     * @access protected
     */
    protected $main_filters = array(
        "updatedFrom",
        "updatedTo",
        "updatedBy",
        "state.name",
        "state.id",
        "organization.id",
        "search",
        "isDeleted",
        "limit",
        "offset",
        "filters"
    );

    /**
     * Client creating
     *
     * @param string $login    api login
     * @param string $password api password
     */
    public function __construct($login, $password)
    {
        $this->client = new Client($login, $password);
    }

    /**
     * Get data.
     *
     * @param array $params
     * @param array $filters
     *
     * @throws \InvalidArgumentException
     * @throws \MoySklad\Exception\CurlException
     * @throws \MoySklad\Exception\InvalidJsonException
     *
     * @return ApiResponse
     *
     * @access public
     */
    public function getData(
        $params,
        $filters = null
    )
    {
        if (empty($params)) {
            throw new \InvalidArgumentException('The `params` can not be empty');
        }

        $uri = $this->entity[reset($params)] . '/';

        foreach ($params as $param) {
            $uri .= $param . '/';
        }
        unset($param);
        $uri = trim($uri, '/');

        $filter = array();

        switch (count($params)) {
            case 1:
            case 2:
                if (!empty(array_diff(array_keys($filters), $this->main_filters))) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Wrong attributes: `%s`',
                            implode(', ', array_diff(array_keys($filters), $this->main_filters))
                        )
                    );
                }
                foreach ($filters as $index=>$value) {
                    $filter[$index] = $value;
                }
                unset($index, $value);
                break;
            case 3:
            case 4:
                $this->checkUuid($params[1]);
                if (!in_array($params[2], self::REQUEST_ATTRIBUTES_SECOND)) {
                    throw new \InvalidArgumentException(sprintf('Wrong attribute: `%s`', $params[2]));
                }
                break;
        }

        switch (count($params)) {
            case 2:
                if (!in_array($params[1], self::REQUEST_ATTRIBUTES_MAIN)) {
                    $this->checkUuid($params[1]);
                }
                break;
            case 4:
                $this->checkUuid($params[3]);
                break;
        }

        return $this->client->makeRequest(
            $uri,
            Client::METHOD_GET,
            $filter
        );
    }

    /**
     * Create data.
     *
     * @param mixed $param
     * @param array $data
     *
     * @throws \InvalidArgumentException
     * @throws \MoySklad\Exception\CurlException
     * @throws \MoySklad\Exception\InvalidJsonException
     *
     * @return ApiResponse
     *
     * @access public
     */
    public function createData($param, $data)
    {
        if (is_array($param)) {
            $type = $param[0];
            $uuid = $param[1];
            $this->checkUuid($uuid);
        } else {
            $type = $param;
            $uuid = null;
        }
        $parameters['data'] = $data;

        return $this->client->makeRequest(
            $this->entity[$type] . '/' . $type . (!is_null($uuid) ? ('/'.$uuid) : ''),
            Client::METHOD_POST,
            $parameters
        );
    }

    /**
     * Update data.
     *
     * @param string $type
     * @param string $uuid
     * @param json $data
     *
     * @throws \InvalidArgumentException
     * @throws \MoySklad\Exception\CurlException
     * @throws \MoySklad\Exception\InvalidJsonException
     *
     * @return ApiResponse
     *
     * @access public
     */
    public function updateData($type, $uuid, $data)
    {
        $this->checkUuid($uuid);

        $parameters['data'] = $data;

        return $this->client->makeRequest(
            sprintf($this->entity[$type] . '/' . $type . '/%s', $uuid),
            Client::METHOD_PUT,
            $parameters
        );
    }

    /**
     * Delete data.
     *
     * @param string $type
     * @param string $uuid
     *
     * @throws \InvalidArgumentException
     * @throws \MoySklad\Exception\CurlException
     * @throws \MoySklad\Exception\InvalidJsonException
     *
     * @return ApiResponse
     *
     * @access public
     */
    public function deleteData($type, $uuid)
    {
        $this->checkUuid($uuid);

        return $this->client->makeRequest(
            sprintf($this->entity[$type] . '/' . $type . '/%s', $uuid),
            Client::METHOD_DELETE
        );
    }

    /**
     * Check uuid.
     *
     * @param string $uuid
     * @throws \InvalidArgumentException
     * @access private
     */
    private function checkUuid($uuid)
    {
        if (is_null($uuid) || empty($uuid)) {
            throw new \InvalidArgumentException('The `uuid` can not be empty');
        }

        if (!preg_match("#^[\w\d]{8}-[\w\d]{4}-[\w\d]{4}-[\w\d]{4}-[\w\d]{12}$#", $uuid)) {
            if (preg_match("#^[a-z\d]+$#i", $uuid)) {
                throw new \InvalidArgumentException(sprintf('Wrong attribute: `%s`', $uuid));
            }
            throw new \InvalidArgumentException('The `uuid` has invalid format');
        }
    }
}
