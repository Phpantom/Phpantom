<?php

namespace Phpantom\BlobsStorage\Adapter;

use Assert\Assertion;
use Gaufrette\Filesystem;
use Phpantom\BlobsStorage\AdapterInterface;
use Phpantom\Resource;

class Gaufrette implements AdapterInterface
{
    /**
     * Max length of filename
     */
    const MAX_NAME = 255;

    /**
     * @var string
     */
    private $indexFileName = 'index.dat';

    private $filesystem;

    private $root;

    private $useHashFilename = false;

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @return boolean
     */
    public function getUseHashFilename()
    {
        return $this->useHashFilename;
    }

    /**
     * @return string
     */
    public function getIndexFileName()
    {
        return $this->indexFileName;
    }


    public function __construct(Filesystem $filesystem, $root = '/tmp', $useHashFilename = false)
    {
        Assertion::string($root);
        Assertion::boolean($useHashFilename);
        $this->filesystem = $filesystem;
        $this->useHashFilename = $useHashFilename;
    }

    protected function getPath(Resource $resource)
    {
        $url = $resource->getHttpRequest()->getUri();
        $url = preg_replace('/^[a-z0-9]+:\/\//', '', $url);
        $sections = explode('/', $url);
        $sections = array_map(
            function ($section) use ($resource) {
                $section = rawurldecode($section);
                $section = $this->truncate($section, $resource);
                return rawurldecode($section);
            },
            $sections
        );
        array_unshift($sections, $this->getRoot());

        $last = end($sections);
        if ($last === '') {
            array_splice($sections, -1, 1, $this->getIndexFileName());
        }
        if ($this->getUseHashFilename()) {
            array_splice($sections, -1, 1, sha1($last));
        }

        return implode(DIRECTORY_SEPARATOR, $sections);
    }

    /**
     * @param string $fileName
     * @param \Phpantom\Resource|Resource $resource
     * @return string
     */
    protected function truncate($fileName, Resource $resource)
    {
        if ($this->strlen($fileName) > self::MAX_NAME) {
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $extensionLength = $this->strlen($extension);
            if ($extensionLength > 0 && $extensionLength <= 4) {
                $suffix = '.' . $extension;
            } else {
                $suffix = '';
            }
            $fileName = $this->substr($fileName, 0, self::MAX_NAME - 8 - $this->strlen($suffix))
                . '-' . $this->substr($resource->getHash(), 0, 7) . $suffix;
        }

        return $fileName;
    }

    private function strlen($str)
    {
        return mb_strlen($str, 'utf-8');
    }

    /**
     * @param $str
     * @param $start
     * @param null $length
     * @return string
     */
    private function substr($str, $start, $length = null)
    {
        return mb_substr($str, $start, $length, 'utf-8');
    }


    /**
     * Returns an array of all keys (files and directories)
     *
     * @return array
     */
    public function keys()
    {
        return $this->getFilesystem()->keys();
    }

    /**
     * @param \Phpantom\Resource|Resource $resource
     * @param string $contents
     * @param bool $overwrite
     * @return mixed
     */
    public function write(Resource $resource, $contents = '', $overwrite = true)
    {
        Assertion::string($contents);
        Assertion::boolean($overwrite);
        $path = $this->getPath($resource);
        $this->getFilesystem()->write($path, $contents, $overwrite);
        return $path;
    }

    /**
     * @param \Phpantom\Resource|Resource $resource
     * @return mixed
     */
    public function read(Resource $resource)
    {
        return $this->getFilesystem()->read($this->getPath($resource));
    }

    /**
     * @param \Phpantom\Resource|Resource $resource
     * @return mixed
     */
    public function exists(Resource $resource)
    {
        return $this->getFilesystem()->has($this->getPath($resource));
    }

    /**
     * @param \Phpantom\Resource|Resource $resource
     * @return mixed
     */
    public function delete(Resource $resource)
    {
        $this->getFilesystem()->delete($this->getPath($resource));
    }

    /**
     *
     */
    public function clear()
    {
        foreach($this->keys() as $key) {
            $this->getFilesystem()->delete($key);
        }
    }
}
