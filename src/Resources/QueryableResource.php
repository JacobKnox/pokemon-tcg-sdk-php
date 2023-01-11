<?php

namespace Pokemon\Resources;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;
use Pokemon\Models\Model;
use Pokemon\Models\Pagination;
use Pokemon\Pokemon;
use Pokemon\Resources\Interfaces\QueriableResourceInterface;
use stdClass;

/**
 * Class QueryableResource
 *
 * @package Pokemon\Resources
 */
class QueryableResource extends JsonResource implements QueriableResourceInterface
{

    const DEFAULT_PAGE = 1;
    const DEFAULT_PAGE_SIZE = 250;
    const ALLOWED_ASCENDING = [0, 'ascending', '']; // array values allowed to respresent ascending order
    const ALLOWED_DESCENDING = [1, 'descending', '-']; // array values allowed to represent descending order

    /**
     * @var array
     */
    protected $query = []; // corresponds to q, the search query.

    /**
     * @var int
     */
    protected $page = self::DEFAULT_PAGE; // corresponds to page, the page of data to access.

    /**
     * @var int
     */
    protected $pageSize = self::DEFAULT_PAGE_SIZE; // corresponds to pageSize, the maximum amount of cards to return.

    /**
     * @var array
     */
    protected $orderBy = []; // corresponds to orderBy, the field(s) to order the results by.

    /**
     * @var array
     */
    protected $select = []; // corresponds to select, a comma delimited list of fields to return in the response. By default, all fields are returned if this query parameter is not used.

    /**
     * @var string|null
     */
    protected $identifier;

    /**
     * @return Request
     */
    protected function prepare(): Request
    {
        $uri = $this->resource;

        if (!empty($this->identifier)) {
            $uri = $uri . '/' . $this->identifier;
            $this->identifier = null;

            return new Request($this->method, $uri);
        }

        $queryParams = [];
        if (!empty($this->query)) {
            $query = array_map(function ($attribute, $value) {
                if(is_array($value)){
                    return $attribute . ':' . implode(' ' . $value[0] . ' ' . $attribute . ':', array_slice($value, 1)) . '';
                }
                else if(is_int($attribute)){
                    return $value;
                }
                return $attribute . ':' . $value . '';
            }, array_keys($this->query), $this->query);

            $queryParams['q'] = implode(' ', $query);
            $this->query = [];
        }

        $queryParams['page'] = $this->page;
        if ($this->page !== self::DEFAULT_PAGE) {
            $this->page = self::DEFAULT_PAGE;
        }

        $queryParams['pageSize'] = $this->pageSize;
        if ($this->pageSize !== self::DEFAULT_PAGE_SIZE) {
            $this->pageSize = self::DEFAULT_PAGE_SIZE;
        }

        if (!empty($this->orderBy)) {
            $queryParams['orderBy'] = implode(',', $this->orderBy);
            $this->orderBy = [];
        }

        if(!empty($this->select)){
            $queryParams['select'] = implode(',', $this->select);
            $this->select = [];
        }
        
        $uri = $this->resource . '?' . http_build_query($queryParams);

        return new Request($this->method, $uri);
    }

    /**
     * @param array $query
     * @return QueriableResourceInterface
     */
    public function where(array $query): QueriableResourceInterface
    {
        $this->query = array_merge($this->query, $query);

        return $this;
    }

    /**
     * @param int $page
     * @return QueriableResourceInterface
     */
    public function page(int $page): QueriableResourceInterface
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @param int $pageSize
     * @return QueriableResourceInterface
     */
    public function pageSize(int $pageSize): QueriableResourceInterface
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    /**
     * @param array $attributes
     * @return QueriableResourceInterface
     * @throws InvalidArgumentException
     *
     **/
    public function orderBy(array $attributes): QueriableResourceInterface
    {
        // if the attributes array passed in is already a list (meaning they either simply listed it "name,-number" or ["name","-number"])
        if(array_is_list($attributes)){
            // simply join them with commas
            $this->orderBy = array_merge($this->orderBy, implode(',', $attributes));
        }
        // otherwise, they chose a more complicated option
        else{
            $this->orderBy = array_merge($this->orderBy, implode(',', array_map(function ($attribute, $value) {
                // if the value is one of the accepted values for ascending, then just return the attribute
                if(in_array($value, self::ALLOWED_ASCENDING)){
                    return $attribute;
                }
                // if the value is one of the accepted values for descending, then return the attributee with a negative in front
                else if(in_array($value, self::ALLOWED_DESCENDING)){
                    return '-' . $attribute;
                }
                // otherwise, throw an InvalidArgumentException, because the value is not permitted
                else{
                    throw new InvalidArgumentException($value . " is not a valid ordering.");
                }
            }, array_keys($attributes), $attributes)));
        }

        return $this;
    }

    /**
     * @param array $attributes
     * @return QueriableResourceInterface
     *
     **/
    public function select(array $attributes): QueriableResourceInterface
    {
        $this->select = array_merge($this->select, $attributes);

        return $this;
    }

    /**
     * @param stdClass $data
     * @return Model|null
     */
    protected function transform(stdClass $data): ?Model
    {
        $model = null;
        $class = '\\Pokemon\\Models\\' . ucfirst($this->inflector->singularize($this->resource));

        if (class_exists($class)) {
            /** @var Model $model */
            $model = new $class;
            $model->fill($data);
        }

        return $model;
    }

    /**
     * @param stdClass $response
     * @return array
     */
    protected function transformAll(stdClass $response): array
    {
        return array_map(function ($data) {
            return $this->transform($data);
        }, $response->data);
    }

    /**
     * @param string $identifier
     * @return Model|null
     * @throws InvalidArgumentException
     * @throws GuzzleException
     */
    public function find($identifier): ?Model
    {
        $this->identifier = $identifier;
        try {
            $response = $this->getResponseData($this->client->send($this->prepare()));
        } catch (ClientException $e) {
            throw new InvalidArgumentException('Card not found with identifier: ' . $identifier);
        }

        return $this->transform($response->data);
    }

    /**
     * @return Pagination
     * @throws GuzzleException
     */
    public function pagination(): Pagination
    {
        $response = $this->getResponseData($this->client->send($this->prepare()));

        $pagination = new Pagination();
        $pagination->setPage($response->page);
        $pagination->setPageSize($response->pageSize);
        $pagination->setCount($response->count);
        $pagination->setTotalCount($response->totalCount);

        return $pagination;
    }

}