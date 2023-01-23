<?php

namespace Pokemon\Resources\Interfaces;

use Pokemon\Models\Model;

/**
 * Interface QueriableResourceInterface
 *
 * @package Pokemon\Resources\Interfaces
 */
interface QueriableResourceInterface extends ResourceInterface
{

    /**
     * @param array $query
     *
     * @return QueriableResourceInterface
     */
    public function where(array $query);

    /**
     * @param string $identifier
     *
     * @return Model|null
     */
    public function find($identifier);

    /**
     * @param int $page
     * @return QueriableResourceInterface
     */
    public function page(int $page);

    /**
     * @param int $pageSize
     * @return QueriableResourceInterface
     */
    public function pageSize(int $size);

    /**
     * @param array $attributes
     *
     * @return QueriableResourceInterface
     */
    public function orderBy(array $attributes);

    /**
     * @param array $attributes
     *
     * @return QueriableResourceInterface
     */
    public function select(array $attributes);

}