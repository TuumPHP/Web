<?php
namespace Tuum\Web\View;

use Psr\Http\Message\UriInterface;
use Tuum\View\Helper\Data;
use Tuum\View\Helper\Errors;
use Tuum\View\Helper\Escape;
use Tuum\View\Helper\Inputs;
use Tuum\View\Helper\Message;

/**
 * Class Value
 *
 * @package Tuum\View\Values
 *
 * @property Errors           $errors
 * @property Inputs           $inputs
 * @property Message          $message
 * @property Data             $data
 * @property UriInterface     $uri
 */
class Value
{
    const MESSAGE = '-message-view';
    const INPUTS = '-input-view';
    const ERRORS = '-errors-view';
    const URI = '-uri-view';

    /**
     * @var Data
     */
    private $data;

    /**
     * @var Message
     */
    private $message;

    /**
     * @var Inputs
     */
    private $inputs;

    /**
     * @var Errors
     */
    private $errors;

    /**
     * @var UriInterface
     */
    private $uri;

    /**
     * @param null|callable $escape
     */
    public function __construct($escape = null)
    {
        if (is_callable($escape)) {
            $this->escape = $escape;
        }
    }

    /**
     * @param array $data
     * @return $this
     */
    public function withData($data)
    {
        $blank = clone($this);
        $blank->setup($data);
        return $blank;
    }

    /**
     * @param array $data
     */
    private function setup($data)
    {
        /**
         * @param string $key
         * @return array
         */
        $bite = function ($key = null) use ($data) {
            if (is_null($key)) {
                return $data;
            }
            if (array_key_exists($key, $data)) {
                $found = $data[$key];
                unset($data[$key]);
                return $found;
            }
            return [];
        };
        $escape        = $this->escape ?: new Escape();
        $this->inputs  = Inputs::forge($bite(self::INPUTS), $escape);
        $this->errors  = Errors::forge($bite(self::ERRORS));
        $this->message = Message::forge($bite(self::MESSAGE));
        $this->uri     = $bite(self::URI);
        $this->data    = Data::forge($bite(), $escape);
    }

    /**
     * accessing its internal properties.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->$key)) {
            return $this->$key;
        }
        return null;
    }

}