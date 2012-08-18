<?php

/*
 * This file is part of Pharmacy.
 *
 * (c) Rob Loach <robloach@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pharmacy;

use Symfony\Component\Finder\Finder;

/**
 * The Pharmacy Compiler designed to create a new phar file from some Finders.
 */
class Compiler
{
    protected $name = 'pharmacy.phar';
    protected $signaturealgorithm = \Phar::SHA1;
    protected $version = 'git';
    protected $stub = 'stub.php';
    protected $finders = array();
    protected $base = '';

    /**
     * Create a new Compiler from a given pharmacy.json file.
     */
    public static function fromFile($file = 'pharmacy.json')
    {
        // Make sure we have a valid file name.
        $compiler = new Compiler();

        // Load the Pharmacy data from the json file.
        $contents = file_get_contents($file);
        $json = json_decode($contents);
        $compiler->base = dirname(realpath($file));

        // Process some of the initial variables.
        if (isset($json->name)) {
            $compiler->name = $json->name;
        }
        if (isset($json->version)) {
            $compiler->version = $json->version;
        }
        if (isset($json->stub)) {
            $compiler->stub = $compiler->base.DIRECTORY_SEPARATOR.$json->stub;
        }
        if (isset($json->signaturealgorithm)) {
            $sigtype = $json->signaturealgorithm;
            switch ($json->signaturealgorithm) {
                case 'MD5':
                    $compiler->signaturealgorithm = \Phar::MD5;
                    break;
                case 'SHA1':
                    $compiler->signaturealgorithm = \Phar::SHA1;
                    break;
                case 'SHA256':
                    $compiler->signaturealgorithm = \Phar::SHA256;
                    break;
                case 'SHA512':
                    $compiler->signaturealgorithm = \Phar::SHA512;
                    break;
                case 'OPENSSL':
                    $compiler->signaturealgorithm = \Phar::OPENSSL;
                    break;
            }
        }
        if (isset($json->files)) {
            foreach ($json->files as $definition) {
                $compiler->addFinderDefinition($definition);
            }
        }

        return $compiler;
    }

    /**
     * Retrieve the desired name of the archive.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name of the archive.
     *
     * @param string $name
     *   The final name of the archive which will be created.
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Retrieve the signature algorithm for the archive.
     */
    public function getSignatureAlgorithm()
    {
        return $this->signaturealgorithm;
    }

    /**
     * Set the signature algorithm for the archive.
     *
     * @param int $signaturealgorithm
     *   The desired signature algorithm for the archive.
     *
     * @see http://www.php.net/manual/pt_BR/phar.setsignaturealgorithm.php
     */
    public function setSignatureAlgorithm($signaturealgorithm)
    {
        $this->signaturealgorithm = $signaturealgorithm;

        return $this;
    }

    /**
     * Get the current version of the project.
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the current version of the project.
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    public function getStub()
    {
        return $this->stub;
    }

    public function setStub($file)
    {
        $this->stub = $file;

        return $this;
    }

    public function getFinders()
    {
        return $this->finders;
    }

    public function setFinders($finders)
    {
        $this->finders = $finders;

        return $this;
    }

    public function addFinder($finder)
    {
        $this->finders[] = $finder;

        return $this;
    }

    /**
     * Adds an object definition to the array of Finders.
     *
     * @param array $definition
     *   The definition of the Finder, as an object.
     */
    public function addFinderDefinition($definition)
    {
        $finder = new Finder();

        // Process each parameter available for the Finder.
        $params = array(
            'depth', 'name', 'notName', 'contains', 'notContains', 'size',
            'exclude', 'ignoreDotFiles', 'ignoreVCS', 'addVCSPattern', 'in'
        );
        foreach ($params as $param) {
            if (isset($definition->$param)) {
                $finder->$param($definition->$param);
            }
            // Allow passing in multiple conditions to single parameters.
            $plural = $param.'s';
            if (isset($definition->$plural)) {
                foreach ($definition->$plural as $value) {
                    $finder->$param($value);
                }
            }
        }

        $this->finders[] = $finder;

        return $this;
    }

    /**
     * Compiles the given definitions into a phar archive.
     */
    public function compile()
    {
        // Clear out the old phar file if one exists.
        if (file_exists($this->name)) {
            unlink($this->name);
        }

        // Create the new Phar archive.
        $phar = new \Phar($this->name, 0, $this->name);
        $phar->setSignatureAlgorithm($this->signaturealgorithm);
        $phar->startBuffering();

        // Add each of the files.
        foreach ($this->finders as $finder) {
            foreach ($finder as $file) {
                // @todo Handle stripWhitespace parameter.
                // @todo Handle string replacements with the preg_replace param.
                $this->addFile($phar, $this->base, $file);
            }
        }

        // Add the stub.
        if (isset($this->stub)) {
            if (is_file($this->stub)) {
                // @todo Figure out a better way to send parameters to the stub.
                global $version;
                $version = $this->version;
                // @todo Is there a better way to do stubs?
                $stub = include($this->stub);
                $phar->setStub($stub);
            }
        }
        $phar->stopBuffering();

        // @todo Allow compressing the phar file?
        //$phar->compressFiles(\Phar::GZ);

        // @todo Provide better output if there was an error, or success.
        return true;
    }

    /**
     * Add a file to a phar archive.
     */
    private function addFile($phar, $base, $file, $strip = true)
    {
        $path = str_replace($base.DIRECTORY_SEPARATOR, '', $file->getRealPath());

        if ($strip) {
            $content = php_strip_whitespace($file);
        } elseif ('LICENSE' === basename($file)) {
            $content = "\n".file_get_contents($file)."\n";
        } else {
            $content = file_get_contents($file);
        }

        $content = str_replace('@package_version@', $this->version, $content);

        $phar->addFromString($path, $content);
    }
}
