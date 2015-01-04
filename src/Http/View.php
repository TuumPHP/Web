<?php
namespace Tuum\Web\Http;

use Traversable;

class View extends Response
{
    /**
     * @var string
     */
    protected $file;

    /**
     * @param string $content
     * @param int    $status
     * @param array  $headers
     */
    public function __construct($content = '', $status = 200, $headers = array())
    {
        parent::__construct($content, $status, $headers);
    }

    /**
     * @param $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

}