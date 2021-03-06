<?php
namespace Phpantom;

use Assert\Assertion;
use Zend\Diactoros\Request;

class Resource implements \Serializable
{

    /**
     * @var array
     */
    private $meta = [];

    /**
     * @var string
     */
    private $type;

    /**
     * @var Request
     */
    private $httpRequest;

    public function __construct(Request $httpRequest, $type)
    {
        Assertion::string($type);
        $this->type = $type;
        $this->httpRequest = $httpRequest;
    }

    public function __sleep()
    {

    }

    public function getUrl()
    {
        return (string) $this->httpRequest->getUri();
    }

    /**
     * @return Request
     */
    public function getHttpRequest()
    {
        return $this->httpRequest;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @return string
     */
    public function getHash()
    {
        $data = ['type'=> $this->type, 'meta'=>$this->meta, 'url'=>(string) $this->getHttpRequest()->getUri()];
        return sha1(json_encode($data));
    }

    /**
     * @param array $meta
     * @return $this
     */
    public function setMeta(array $meta = [])
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * @param array $meta
     * @return $this
     */
    public function addMeta(array $meta = [])
    {
        $this->meta = array_merge($this->meta, $meta);
        return $this;
    }

    /**
     * @param null $var
     * @param null $default
     * @return array|null
     */
    public function getMeta($var = null, $default = null)
    {
        if (null === $var) {
            return $this->meta;
        }
        return isset($this->meta[$var])? $this->meta[$var] : $default;
    }

    /**
     * Proxy
     * @param $method
     * @param array $params
     * @return mixed
     */
    public function __call($method, $params = [])
    {
        $result = call_user_func_array([$this->httpRequest, $method], $params);
        if (0 === strpos($method, 'with')) {
            $this->httpRequest = $result;
            return $this;
        }
        return $result;
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
            'meta' => $this->meta,
            'type' => $this->type,
            'httpRequest' => Request\Serializer::toString($this->getHttpRequest())
        ];
        return serialize($data);
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
        $this->meta = $data['meta'];
        $this->type = $data['type'];
        $this->httpRequest = Request\Serializer::fromString($data['httpRequest']);
    }
}
