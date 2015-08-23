<?php

namespace Phpantom\Document;

/**
 * Interface DocumentInterface
 * @package Phpantom\Document
 */
interface DocumentInterface
{
    /**
     * @param string $type
     * @param string $id
     * @param array $data
     * @return mixed
     */
    public function create($type, $id, array $data);

    /**
     * @param string $type
     * @param string $id
     * @param array $data
     * @return mixed
     */
    public function update($type, $id, array $data);

    /**
     * @param string $type
     * @param string $id
     * @return mixed
     */
    public function get($type, $id);

    /**
     * @param $type
     * @param $id
     * @return bool
     */
    public function exists($type, $id);

    /**
     * @param string $type
     * @param string $id
     * @return mixed
     */
    public function delete($type, $id);

    /**
     * @param string|null $type
     * @return mixed
     */
    public function getIterator($type = null);

    /**
     * @return mixed
     */
    public function clean();

    /**
     * @param string|null $type
     * @return mixed
     */
    public function count($type=null);

    /**
     * @return mixed
     */
    public function getTypes();
}
