<?php
namespace Tuum\Web\Http;

use Traversable;

class View extends Response
{
    use ResponseWithTrait;

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

    /**
     * @param array|Traversable $data
     * @return $this
     */
    public function fill($data)
    {
        if (is_array($data) || $data instanceof Traversable) {
            foreach ($data as $name => $value) {
                $this->data[$name] = $value;
            }
        }
        return $this;
    }

}