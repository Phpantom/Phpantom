<?php
namespace Phpantom;

use Phly\Http\Response as HttpResponse;

class Response
{
    /**
     * @var \Phly\Http\Response
     */
    private $httpResponse;

    public function __construct(HttpResponse $httpResponse)
    {
        $this->httpResponse = $httpResponse;
    }

    /**
     * @return mixed
     */
    public function getHttpResponse()
    {
        return $this->httpResponse;
    }

    public function getContent()
    {
        return (string) $this->getHttpResponse()->getBody();
    }
    /**
     * Proxy
     * @param $method
     * @param array $params
     * @return mixed
     */
    public function __call($method, $params = [])
    {
        return call_user_func_array([$this->httpResponse, $method], $params);
    }

//    /**
//     * Proxy
//     * @param $method
//     * @param array $params
//     * @return mixed
//     */
//    public function __call($method, $params = [])
//    {
//        $response =  call_user_func_array([$this->httpResponse, $method], $params);
//        if (! ($response instanceof $this)) {
//            return new self($response);
//        }
//        return $response;
//    }
}
