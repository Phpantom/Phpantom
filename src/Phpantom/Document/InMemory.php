<?php

namespace Phpantom\Document;

use Assert\Assertion;

/**
 * Class InMemory
 * @package Phantom\Document
 */
class InMemory implements DocumentInterface
{
    /**
     * @var array
     */
    private $storage = [];

    /**
     * @param string $type
     * @param string $id
     * @param array $data
     * @return mixed
     */
    public function create($type, $id, array $data)
    {
        if ($this->exists($type, $id)) {
            throw new \DomainException('Document already exists');
        }

        $this->storage[$type][$id] = $data;
    }

    /**
     * @param string $type
     * @param string $id
     * @param array $data
     * @return mixed
     */
    public function update($type, $id, array $data)
    {
        Assertion::string($type);
        Assertion::string($id);
        if (!$this->exists($type, $id)) {
            throw new \DomainException('Document does not exist');
        }
        $this->storage[$type][$id] = $data;
    }

    /**
     * @param string $type
     * @param string $id
     * @return mixed
     */
    public function get($type, $id)
    {
        Assertion::string($type);
        Assertion::string($id);
        if (!$this->exists($type, $id)) {
            return null;
        }
        return $this->storage[$type][$id];
    }

    /**
     * @param string $type
     * @param string $id
     * @return mixed
     */
    public function delete($type, $id)
    {
        Assertion::string($type);
        Assertion::string($id);

        if (!$this->exists($type, $id)) {
            throw new \DomainException('Document does not exist');
        }
        unset($this->storage[$type][$id]);
    }

    /**
     * @param string|null $type
     * @return mixed
     */
    public function getIterator($type = null)
    {
        Assertion::nullOrString($type);
        if (null !== $type) {
            if (!empty($this->storage[$type])) {
                return new \ArrayIterator($this->storage[$type]);
            } else {
                return new \ArrayIterator([]);
            }
        } else {
            $storage = [];
            foreach ($this->storage as $type => $data) {
                foreach ($data as $id => $doc) {
                    $storage[$id] = $doc;
                }
            }
            return new \ArrayIterator($storage);
        }
    }

    /**
     * @return mixed
     */
    public function clean()
    {
        $this->storage = [];
    }

    /**
     * @param string|null $type
     * @return mixed
     */
    public function count($type = null)
    {
        Assertion::nullOrString($type);
        if (null !== $type) {
            return count($this->storage[$type]);
        }
        $count = 0;
        foreach ($this->storage as $type => $data) {
            $count += count($data);
        }
        return $count;
    }

    /**
     * @return mixed
     */
    public function getTypes()
    {
        return array_keys($this->storage);
    }

    /**
     * @param $type
     * @param $id
     * @return bool
     */
    public function exists($type, $id)
    {
        Assertion::string($type);
        Assertion::string($id);

        return isset($this->storage[$type][$id]);
    }
}
