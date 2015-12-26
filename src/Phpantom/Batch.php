<?php
namespace Phpantom;
use Zend\Diactoros\Request;

class Batch implements \Serializable
{

    /**
     * @var array
     */
    private $resources = [];


    public function addResource(Resource $resource)
    {
        $this->resources[$resource->getHash()] = $resource;
    }

    public function removeResource(Resource $resource)
    {
        unset($this->resources[$resource->getHash()]);
    }

    public function addResources(array $resources = [])
    {
        foreach ($resources as $resource) {
            $this->addResource($resource);
        }
    }

    public function removeResources(array $resources)
    {
        foreach ($resources as $resource) {
            $this->removeResource($resource);
        }
    }

    public function getResources()
    {
        return $this->resources;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        $data = [
            'resources' => $this->resources,
        ];
        return serialize($data);
    }


    public function count()
    {
        return count($this->resources);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->resources = $data['resources'];
    }
}
