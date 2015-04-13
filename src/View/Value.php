<?php
namespace Tuum\Web\View;

use Psr\Http\Message\UriInterface;
use Tuum\Form\DataView;
use Tuum\Form\Data\Data;
use Tuum\Form\Data\Errors;
use Tuum\Form\Data\Inputs;
use Tuum\Form\Data\Message;

/**
 * Class Value
 *
 * @package Tuum\Web\View\Values
 *
 */
class Value extends DataView
{
    const MESSAGE = '-message-view';
    const INPUTS = '-input-view';
    const ERRORS = '-errors-view';
    const URI = '-uri-view';

    /**
     * @var UriInterface
     */
    public $uri;

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
        $this->inputs  = Inputs::forge($bite(self::INPUTS), $this->escape);
        $this->errors  = Errors::forge($bite(self::ERRORS));
        $this->message = Message::forge($bite(self::MESSAGE));
        $this->uri     = $bite(self::URI);
        $this->data    = Data::forge($bite(), $this->escape);
    }

}