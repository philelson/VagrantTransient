<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2015  Philip Elson <phil@pegasus-commerce.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * Date: 23/06/15
 * Time: 11:25
 *
 * PHP version 5.3+
 *
 * @category Pegasus_Utilities
 * @package  VagrantTransient
 * @author   Philip Elson <phil@pegasus-commerce.com>
 * @license  MIT http://opensource.org/licenses/MIT
 * @link     http://pegasus-commerce.com
 */
namespace Pegasus;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * This class provides the core methods for VagrantTransient
 *
 * @category Pegasus_Utilities
 * @package  VagrantTransient
 * @author   Philip Elson <phil@pegasus-commerce.com>
 * @license  MIT http://opensource.org/licenses/MIT
 * @link     http://pegasus-commerce.com
 */
class VagrantTransient extends Command
{
    /**
     * This is the default file where the _environments are stored.
     */
    const DEFAULT_STORE_NAME    = "/.VagrantTransient";

    /**
     * This is the name of the VagrantFile used by Vagrant
     */
    const VAGRANT_FILE_NAME     = 'VagrantFile';

    /**
     * Storage name is held here
     *
     * @var null
     */
    private $_storeName          = null;

    /**
     * Array which holds the _environments
     *
     * @var array
     */
    private $_environments       = array();

    /**
     * Terminal output
     *
     * @var null
     */
    protected $output           = null;

    /**
     * Current working dir
     * @var null
     */
    protected $pwd              = null;

    /**
     * String which represents the current environment
     */
    protected $currentEnvironment = null;

    /**
     * Configures the application
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName($this->getName())
            ->setDescription($this->getDescription())
            ->addOption(
                'storage',
                null,
                InputOption::VALUE_OPTIONAL,
                'Environment Location Storage',
                $this->getDefaultFileName()
            );
    }

    /**
     * This method returns the name of the instance app.
     * Must be overridden in all children
     *
     * @return string
     */
    public function getName()
    {
        return 'vagrant-transient';
    }

    /**
     * This method returns the description of the instance app.
     * Must be overridden in all children
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Vagrant Transient';
    }

    /**
     * This method is called before runTransient()
     *
     * @return $this
     */
    public function before()
    {
        $this->setCurrentEnvironment($this->getDefaultCurrentEnvironment());
        $this->loadEnvironments();
        return $this;
    }

    /**
     * Method runs the instance specific code.
     *
     * @return void
     * @throws \Exception when not implemented
     */
    public function runTransient()
    {
        throw new \Exception('Not Yet Implemented');
    }

    /**
     * This is the method which is called after the runTransient.
     *
     * @return void
     */
    public function after()
    {
        $this->save();
    }

    /**
     * This is the command which executes the application
     *
     * @param InputInterface  $input  input interface for  command
     * @param OutputInterface $output output interface for command
     * 
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->pwd = getcwd();
        $this->output = $output;
        $this->_storeName = $input->getOption('storage');
        $this->loadOutputStyles();
        $this->before();
        $this->runTransient();
        $this->after();
    }

    /**
     * This method loads the command styles (wanring|general|notice|fatal_error)
     *
     * @return void
     */
    public function loadOutputStyles()
    {
        $styleOne = array('bold');
        $styleTwo = array('bold', 'underscore');
        $style = new OutputFormatterStyle('white', 'red', $styleOne);
        $this->output->getFormatter()->setStyle('warning', $style);
        $style = new OutputFormatterStyle('white', 'blue', $styleOne);
        $this->output->getFormatter()->setStyle('general', $style);
        $style = new OutputFormatterStyle('white', 'green', $styleOne);
        $this->output->getFormatter()->setStyle('notice', $style);
        $style = new OutputFormatterStyle('white', 'red', $styleTwo);
        $this->output->getFormatter()->setStyle('fatal_error', $style);
    }

    /**
     * This method loads the _environments from disk into an array
     *
     * @return array
     */
    protected function loadEnvironments()
    {
        $this->_environments = array();
        if (false == file_exists($this->getFileName())) {
            touch($this->getFileName());
        }
        $tempEnvironments = file($this->getFileName());
        foreach ($tempEnvironments as $key => $value) {
            $value = trim($value);
            if (null != $value && '' != $value && false == is_array($value)) {
                $this->_environments[] = $value;
            }
        }
        return $this->_environments;
    }

    /**
     * This method iterates over the _environments writing them to disk.
     *
     * @return string
     */
    public function save()
    {
        if (true == file_exists($this->getFileName())) {
            unlink($this->getFileName());
        }
        touch($this->getFileName());
        $content = "";
        foreach ($this->getEnvironments() as $environment) {
            $content .= "{$environment}\n";
        }
        file_put_contents($this->getFileName(), $content);
        return $content;
    }

    /**
     * This method reloads the _environments
     *
     * @return void
     */
    public function refresh()
    {
        $this->loadEnvironments();
    }

    /**
     * Returns a user specific path to ~/.VagrantTransient
     *
     * @return string
     */
    public function getDefaultFileName()
    {
        return getenv("HOME").self::DEFAULT_STORE_NAME;
    }

    /**
     * This method returns the storage file name.
     * Defaults to ~/.VagrantTransient
     *
     * @return null|string
     */
    public function getFileName()
    {
        if (null == $this->_storeName) {
            $this->_storeName = $this->getDefaultFileName();
        }
        return $this->_storeName;
    }

    /**
     * This method purges the _environments array
     *
     * @return environments
     */
    public function purge()
    {
        $this->_environments = array();
        return $this->environments;
    }

    /**
     * This method returns the environment array
     *
     * @return array
     */
    public function getEnvironments()
    {
        return $this->_environments;
    }

    /**
     * This method overrides the _environments array.
     *
     * @param array $_environments Is the new array of environments
     *
     * @return $this
     */
    public function setEnvironments(array $_environments)
    {
        $this->_environments = $_environments;
        return $this;
    }

    /**
     * This method adds the environment to storage if it is not already in storage.
     *
     * @param string $name is the name of the environment to create.
     *
     * @return $this
     */
    public function createEnvironment($name)
    {
        $environments = $this->getEnvironments();
        $changed = false;
        if (false == $this->environmentExists($name)) {
            $environments[] = $name;
            $changed = true;
        }
        if (true == $changed) {
            $this->setEnvironments($environments);
            $environments = array_reverse($environments);
            $this->setEnvironments($environments);
        }
        return $this;
    }

    /**
     * This method returns true if the environment exists.
     *
     * @param string $name Is the name of the environment to be checked
     *
     * @return bool
     */
    public function environmentExists($name)
    {
        return in_array($name, $this->getEnvironments());
    }

    /**
     * This method removed an environment from the system.
     * The environment must match exactly to the path in storage.
     *
     * @param string $name Is the name of the environment to be removed
     *
     * @return $this
     */
    public function removeEnvironment($name)
    {
        $environments = $this->getEnvironments();
        if (true == in_array($name, $environments)) {
            if (($key = array_search($name, $environments)) !== false) {
                unset($environments[$key]);
            }
        }
        $this->setEnvironments($environments);
        return $this;
    }

    /**
     * This method halts every Vagrant environment.
     * This is the only method which executes a command.
     *
     * @return $this;
     */
    protected function environmentsDown()
    {
        foreach ($this->getEnvironments() as $environment) {
            if (true == file_exists($environment)) {
                //echo $environment;
                chdir($environment);
                $this->printLn("Shutting down vagrant in {$environment}");
                $output = '';
                $return = '';
                exec('vagrant halt', $output, $return);
                if (false == is_array($output)) {
                    $output = array($output);
                }
                $style = (0 == $return) ? 'notice' : 'warning';
                $this->printLn(implode(', ', $output), $style);
            } else {
                $this->printLn("Dir not found {$environment}");

            }
        }
        chdir($this->pwd);
        return $this;
    }

    /**
     * This method prints a line to the terminal.
     *
     * @param string $message Is the message
     * @param string $type    Is the type of message (warning|notice|general)
     *
     * @return void
     */
    protected function printLn($message, $type='general')
    {
        if (null != $this->output) {
            $message = (null == $type) ? $message : "<{$type}>{$message}</{$type}>";
            $this->output->writeLn($message);
        }
    }

    /**
     * Returns the directory of this environment.
     *
     * @return string which represents the current environment
     */
    public function getCurrentEnvironment()
    {
        return $this->currentEnvironment;
    }

    /**
     * This method sets the current environment
     *
     * @param string $currentEnvironment is the environment name to use
     * as the default.
     *
     * @return $this
     */
    public function setCurrentEnvironment($currentEnvironment)
    {
        $this->currentEnvironment = $currentEnvironment;
        return $this;
    }

    /**
     * This method returns the default current environment.
     *
     * @return string which represents the default environment
     */
    public function getDefaultCurrentEnvironment()
    {
        return __DIR__;
    }

    /**
     * This method iterates over the _environments. If there is no VagrantFile
     * within that environment then it is removed from the list of _environments.
     *
     * @return $this
     */
    protected function cleanEnvironments()
    {
        $environmentsWhichExist = array();
        foreach ($this->getEnvironments() as $key => $environment) {
            $fileName = $environment.DIRECTORY_SEPARATOR.self::VAGRANT_FILE_NAME;
            if (true == file_exists($fileName)) {
                $environmentsWhichExist[] = $environment;
            }
        }
        $this->setEnvironments($environmentsWhichExist);
        return $this;
    }
}