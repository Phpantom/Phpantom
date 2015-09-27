<?php

namespace Phpantom\Document;

use Phpantom\Resource;

class Manager
{
    private $docStorage;

    public function __construct(DocumentInterface $docStorage)
    {
        $this->docStorage = $docStorage;
    }

    /**
     * @return DocumentInterface
     */
    public function getDocStorage()
    {
        return $this->docStorage;
    }


    public function getBoundDocument(Resource $resource)
    {
        $meta = $resource->getMeta();
        return $this->getDocument($meta['item_type'], $meta['item_id']);
    }

    /**
     * @param $type
     * @param $id
     * @return mixed
     */
    public function getDocument($type, $id)
    {
        return $this->getDocStorage()->get($type, $id);
    }

    public function updateBoundDocument(Resource $resource, array $data)
    {
        $meta = $resource->getMeta();
        $this->updateDocument($meta['item_type'], $meta['item_id'], $data);
    }

    /**
     * @param $type
     * @param $id
     * @param array $data
     */
    public function updateDocument($type, $id, array $data)
    {
        $this->getDocStorage()->update($type, $id, $data);
    }


    /**
     * @param \Phpantom\Resource|Resource $resource
     * @param $docType
     * @param $docId
     */
    public function bindResourceToDoc(Resource $resource, $docType, $docId)
    {
        $resource->setMeta(['item_id' => $docId, 'item_type' => $docType]);
    }


    /**
     * @param $type
     * @param $id
     * @param array $data
     */
    public function createDocument($type, $id, array $data)
    {
        $this->getDocStorage()->create($type, $id, $data);
    }

    /**
     * @param $type
     * @param $id
     */
    public function deleteDocument($type, $id)
    {
        $this->getDocStorage()->delete($type, $id);
    }

    /**
     * @param $type
     * @param $id
     * @return bool
     */
    public function documentExists($type, $id)
    {
        return $this->getDocStorage()->exists($type, $id);
    }

//    /**
//     * @param $type
//     * @return mixed
//     */
//    public function getDocumentsList($type)
//    {
//        return $this->getDocStorage()->getList($type);
//    }

}
