<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Config\Bridge\Yaml;

use Webiny\Component\StdLib\Exception\ExceptionAbstract;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\FileObject\FileObject;
use Webiny\Component\StdLib\StdObject\StdObjectWrapper;
use Webiny\Component\StdLib\StdObject\StringObject\StringObject;

/**
 * Bridge for Yaml parser
 *
 * @package   Webiny\Component\Config\Bridge\Yaml
 */
class Yaml implements YamlInterface
{
    use StdLibTrait;

    /**
     * @var YamlInterface
     */
    private $_driverInstance = null;

    /**
     * Default Yaml driver class name
     * @var string
     */
    private static $_driverClass = 'Webiny\Component\Config\Bridge\Yaml\SymfonyYaml\SymfonyYaml';

    /**
     * Instance of Yaml driver to use
     * @var null|YamlInterface
     */
    private static $_customDriver = null;

    /**
     * Driver interface to enforce
     * @var string
     */
    private static $_driverInterface = 'Webiny\Component\Config\Bridge\Yaml\YamlInterface';

    /**
     * Set Yaml driver to use by Yaml bridge
     *
     * @param $driver string|YamlInterface
     *
     * @throws YamlException
     */
    public static function setDriver($driver)
    {

        if (!self::isInstanceOf($driver, self::$_driverInterface)) {
            if (self::isString($driver) || self::isStringObject($driver)) {
                $driver = StdObjectWrapper::toString($driver);
                $driver = new $driver;
                if (self::isInstanceOf($driver, self::$_driverInterface)) {
                    self::$_customDriver = $driver;

                    return;
                }
            }
            throw new YamlException(ExceptionAbstract::MSG_INVALID_ARG, [
                    '$driver',
                    self::$_driverInterface
                ]
            );
        }
        self::$_customDriver = $driver;

        return;
    }

    /**
     * Get bridge instance
     *
     * @param mixed $resource
     *
     * @return YamlInterface
     */
    public static function getInstance($resource = null)
    {
        return new static($resource);
    }

    /**
     * Get current Yaml value as string
     *
     * @param int  $indent
     * @param bool $wordWrap
     *
     * @throws YamlException
     * @return string
     */
    function getString($indent = 2, $wordWrap = false)
    {
        $res = $this->_driverInstance->getString($indent, $wordWrap);
        if (!$this->isString($res) && !$this->isStringObject($res)) {
            throw new YamlException('YamlInterface method _getString() must return a string or StringObject.');
        }

        return StdObjectWrapper::toString($res);
    }

    /**
     * Get Yaml value as array
     *
     * @throws YamlException
     * @return array
     */
    function getArray()
    {
        $res = $this->_driverInstance->getArray();
        if (!$this->isArray($res) && !$this->isArrayObject($res)) {
            throw new YamlException('YamlInterface method getArray() must return an array or ArrayObject.');
        }

        return StdObjectWrapper::toArray($res);
    }

    /**
     * Set driver resource to work on
     *
     * @param mixed $resource
     *
     * @throws YamlException
     * @return $this
     */
    function setResource($resource)
    {
        $res = $this->_driverInstance->setResource($resource);
        if (!$this->isInstanceOf($res, self::$_driverInterface)) {
            throw new YamlException('YamlInterface method setSource() must return YamlInterface object.');
        }

        return $res;
    }

    /**
     * Create Yaml bridge
     *
     * @param mixed $resource
     */
    private function __construct($resource = null)
    {
        if ($this->isInstanceOf(self::$_customDriver, self::$_driverInterface)) {
            // If custom driver instance was set, we need to use a copy of it and set it's resource
            $this->_driverInstance = clone $this->_driverInstance->setResource($resource);
        } else {
            $this->_driverInstance = new self::$_driverClass();
            $this->_driverInstance->setResource($resource);
        }
    }
}