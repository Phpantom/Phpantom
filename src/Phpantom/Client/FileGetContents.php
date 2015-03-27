<?php

namespace Phantom\Client;

use Assert\Assertion;
use Phpantom\Client\ClientInterface;
use Phpantom\Resource;
use Phly\Http\Response;

class FileGetContents implements ClientInterface
{
    private $timeout = 10;

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function setSiteout($timeout)
    {
        Assertion::numeric($timeout);
        $this->timeout = $timeout;
        return $this;
    }
    /**
     * @param \Phpantom\Resource|Resource $resource
     * @return mixed
     */
    public function load(Resource $resource)
    {
        $headersList = [];
        //'header'=>'Connection: close' @see http://php.net/manual/en/function.file-get-contents.php comments
        foreach ($resource->getHeaders() as $key => $val) {
            $headersList[] = "$key: $val";
        }
        $opts = array('http' =>
            array(
                'method'  => $resource->getMethod(),
                'header'  => implode("\r\n", $headersList),
                'content' => $resource->getBody(), //$resource->getRequestData(),
                'timeout' => $this->getTimeout(),
                'ignore_errors' => true, //don't throw errors on 404 and so on
                'request_fulluri' => true,
//                'protocol_version' => 1.1
                'proxy' => $resource->getMeta('proxy')? $resource->getMeta('proxy') : null
            )
        );

        $context  = stream_context_create($opts);
        $data = @file_get_contents($resource->getUri(), false, $context);
        $response = new Response();
        $response->getBody()->write($data);
        if (!empty($http_response_header)) {
            foreach($http_response_header as $header) {
                if (strpos($header, ':')) {
                    list($k, $v) = explode(':', $header);
                    $response = $response->withAddedHeader($k, $v);
                } else {//status
                    list ($protocol, $status) = explode(" ", $header, 2);
                    $response = $response->withAddedHeader('status', $status)
                                         ->withStatus(intval($status));
                }
            }
        }
        return $response;
    }
}
