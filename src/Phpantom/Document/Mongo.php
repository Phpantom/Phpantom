<?php

namespace Phpantom\Document;

use Assert\Assertion;

/**
 * @todo add prefix for 'document' like for frontier and result storage
 * Class Mongo
 * @package Phpantom\Document
 */
class Mongo implements DocumentInterface
{
    /**
     * @var \MongoDB
     */
    private $storage;

    /**
     * @param \MongoDB $storage
     */
    public function __construct(\MongoDB $storage)
    {
        $this->storage = $storage;

    }

    /**
     * @param string $type
     * @param string $id
     * @param array $data
     * @return mixed|void
     */
    public function create($type, $id, array $data)
    {
        Assertion::string($type);
        Assertion::string($id);
        $data['_id'] = $id;
        $data['_type'] = $type;
        unset($data['id']);
        $this->storage->documents->save(
            $data
        );
    }

    /**
     * @param string $type
     * @param string $id
     * @param array $data
     * @return mixed|void
     */
    public function update($type, $id, array $data)
    {
        Assertion::string($type);
        Assertion::string($id);
        unset($data['id']);
        unset($data['_id']);
        $this->storage->documents->update(
            ['_id'=>$id],
            ['$set' => $data]
        );

    }

    /**
     * @param string $type
     * @param string $id
     * @return array|null
     */
    public function get($type, $id)
    {
        Assertion::string($type);
        Assertion::string($id);

        $document = $this->storage->documents->findOne(array(
                '_id' => $id
            ));

        if ($document) {
            return array_merge($document, array(
                        'id' => $id,
                    ));
        }

        return null;

    }

    /**
     * @param string $type
     * @param string $id
     * @return mixed|void
     */
    public function delete($type, $id)
    {
        Assertion::string($type);
        Assertion::string($id);

        $data['_type'] = $type;
        $data['_id'] = $id;
        unset($data['id']);
        $this->storage->documents->remove(
            ['_id'=>$id],
            ['justOne' => true]
        );
    }

    /**
     * @param $type
     * @return array
     */
    public function getIds($type)
    {
        Assertion::string($type);

        $cursor = $this->storage->documents->find(['_type'=>$type], ['_id']);
        $ids = [];
        foreach ($cursor as $doc) {
            $ids[] =  $doc['_id'];
        }
        return $ids;
    }

    /**
     * @param string $type
     * @return \MongoCursor
     */
    public function getList($type)
    {
        Assertion::string($type);

        return $this->storage->documents->find(['_type'=>$type]);
    }


    /**
     * @param null|string $type
     * @return \MongoCursor
     */
    public function getIterator($type = null)
    {
        Assertion::nullOrString($type);
        return is_null($type)?
            $this->storage->documents->find() :
            $this->storage->documents->find(['_type'=>$type]);
    }

    /**
     * @return array
     */
    public function clean()
    {
        return $this->storage->documents->drop();
    }

    /**
     * @param null $type
     * @return int
     */
    public function count($type = null)
    {
        Assertion::nullOrString($type);
        return is_null($type)?
            $this->storage->documents->count(['_type'=>$type]) :
            $this->storage->documents->count();
    }

    /**
     *
     */
    public function getTypes()
    {
        return $this->storage->documents->distinct('_type');
    }
}
