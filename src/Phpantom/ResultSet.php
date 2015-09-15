<?php

namespace Phpantom;

use Phpantom\Frontier\FrontierInterface;

/**
 * Class ResultSet
 * @package Phpantom
 */
class ResultSet
{
    /**
     * @var Resource|Resource
     */
    private $resource;

    /**
     * @param Resource $resource
     */
    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @var array
     */
    private $newResources = [];

    /**
     * @var array
     */
    private $relatedResources = [];

    /**
     * @var array
     */
    private $items = [];

    /**
     * @var bool
     */
    private $isBlob = false;

    /**
     * @param Resource $resource
     * @return $this
     */
    public function addNewResource(Resource $resource, $priority = FrontierInterface::PRIORITY_NORMAL, $force = false)
    {
        $this->newResources[$priority][] = ['resource' => $resource, 'force' => $force];
        return $this;
    }

    /**
     * @param Resource $resource
     * @return $this
     */
    public function addRelatedResources(
        Resource $resource,
        Item $item,
        $priority = FrontierInterface::PRIORITY_HIGH,
        $force = false
    ) {
        $resource = $resource->withHeader('Referer', $this->resource->getUrl());
        $resource->addMeta(
            [
                'item_id' => $item->id,
                'item_type' => $item->type,
                'rel_resource_url' => $this->resource->getUrl(),
                'rel_resource_type' => $this->resource->getType(),
                'rel_resource_meta' => $this->resource->getMeta()
            ]
        );
        $this->relatedResources[$priority][] = ['resource' => $resource, 'force' => $force];
        return $this;
    }

    /**
     * @param Item $item
     * @throws \Respect\Validation\Exceptions\NestedValidationExceptionInterface
     * @return $this
     */
    public function addItem(Item $item)
    {
        if ($item->validate()) {
            $this->items[] = $item;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getNewResources()
    {
        return $this->newResources;
    }

    /**
     * @return array
     */
    public function getRelatedResources()
    {
        return $this->relatedResources;
    }

    /**
     * @return array
     */
    public function getResources()
    {
        return ($this->getNewResources() + $this->getRelatedResources());
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return mixed
     */
    public function isBlob()
    {
        return $this->isBlob;
    }

    public function markAsBlob()
    {
        $this->isBlob = true;
        return $this;
    }

}
