<?php

namespace Venta\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Venta\Console\Contract\SignatureParser as SignatureParserContract;

/**
 * Class SignatureParser
 *
 * @package Venta\Console
 */
class SignatureParser implements SignatureParserContract
{
    /**
     * Full signature string holder
     *
     * @var string
     */
    protected $_signature;

    /**
     * RegExp to match arguments
     * name[]?:description
     *
     * @var string
     */
    protected $_argumentsMatcher = '/^(?:\-\-)?([a-z]+)?(\[\])?(=)?(.*?)?$/';

    /**
     * Parameters matcher string
     *
     * @var string
     */
    protected $_parametersMatcher = '/{(.*?)}/';

    /**
     * @throws \Exception
     * {@inheritdoc}
     */
    public function parse(string $signature): array
    {
        $signature = explode(' ', $this->_signature = $signature);

        return array_merge($this->_parseParameters(), [
            'name' => array_shift($signature)
        ]);
    }

    /**
     * Parses arguments and options from signature string,
     * returns an array with definitions
     *
     * @return array
     */
    protected function _parseParameters()
    {
        $arguments = [];
        $options = [];
        $signatureArguments = $this->_getParameters();

        foreach ($signatureArguments as $value) {
            $item = [];
            $matches = [];
            $exploded = explode(':', $value);

            if (count($exploded) > 0 && preg_match($this->_argumentsMatcher, $exploded[0], $matches)) {
                $item['name'] = $matches[1];
                $item['type'] = $this->_defineType($matches[2] === '[]', $matches[3] === '=');
                $item['default'] = $matches[4] !== '' ? $matches[4] : null;
                $item['description'] = count($exploded) === 2 ? $exploded[1] : null;

                if ($matches[2] === '[]' && $item['default'] !== null) {
                    $item['default'] = explode(',', $item['default']);
                }

                if (substr($exploded[0], 0, 2) === '--') {
                    $options[] = $item;
                } else {
                    $arguments[] = $item;
                }
            }
        }

        return [
            'arguments' => $arguments,
            'options' => $options
        ];
    }

    /**
     * Returns array of parameters matches
     *
     * @return array
     */
    protected function _getParameters()
    {
        $matches = [];
        preg_match_all($this->_parametersMatcher, $this->_signature, $matches);

        return $matches[1];
    }

    /**
     * Defines type of an argument or option based on options
     *
     * @param  bool $array
     * @param  bool $optional
     * @return int
     */
    protected function _defineType($array = false, $optional = false)
    {
        $type = ($optional) ? InputArgument::OPTIONAL : InputArgument::REQUIRED;

        if ($array) {
            $type = InputArgument::IS_ARRAY | $type;
        }

        return $type;
    }
}