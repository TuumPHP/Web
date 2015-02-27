<?php
namespace Tuum\Web\Stack;

use Tuum\Locator\LocatorInterface;
use Tuum\Web\Psr7\Request;
use Tuum\Web\Psr7\Response;
use Tuum\Web\Web;
use Tuum\Web\Middleware\MiddlewareTrait;
use Tuum\Web\MiddlewareInterface;

class UrlMapper implements MiddlewareInterface
{
    use MiddlewareTrait;
    
    /**
     * @var LocatorInterface
     */
    protected $locator;

    public $setting = [];

    /**
     * @param LocatorInterface $locator
     */
    public function __construct($locator)
    {
        $this->locator = $locator;
        $this->setting = array(
            '.html'     => ['asHtml', 'getContents'],
            '.html.raw' => ['asText', 'getContents', '.html'],
            '.txt'      => ['asText', 'getContents'],
            '.text'     => ['asText', 'getContents'],
            '.php'      => ['asHtml', 'execute' ]
        );
    }

    /**
     * @param Request $request
     * @return Response|null
     */
    public function __invoke($request)
    {
        $path    = $request->getUri()->getPath();
        $setting = $this->findSetting($path);
        if (!$setting) {
            return $this->execNext($request);
        }
        $type   = $setting[0];
        $method = $setting[1];
        if (isset($setting[2])) {
            $oldExt = $setting['extension'];
            $newExt = $setting[2];
            $path = substr($path, 0, - strlen($oldExt)) . $newExt;
        }
        $file    = $this->locator->locate($path);
        if (!$file) {
            return $this->execNext($request);
        }
        return $request->respond()->$type($this->$method($file));
    }

    /**
     * @param string $file
     * @return array
     */
    public function findSetting($file)
    {
        foreach ($this->setting as $extension => $setting) {
            if (substr($file, -strlen($extension)) === $extension) {
                $setting['extension'] = $extension;
                return $setting;
            }
        }
        return [];
    }

    /**
     * @param $file
     * @return string
     */
    public function getContents($file)
    {
        return file_get_contents($file);
    }

    /**
     * @param $file
     */
    public function execute($file)
    {
        include($file);
    }
}
