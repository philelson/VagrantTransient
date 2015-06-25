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
namespace Pegasus\Application\VagrantTransient;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
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
     * This is the application version
     */
    const VERSION               = "0.2.0";

    /**
     * This is the default file where the _environments are stored.
     */
    const DEFAULT_STORE_NAME    = "/.VagrantTransient";

    /**
     * This is the default log file
     */
    const DEFAULT_LOG           = "/.VagrantTransient.log";

    /**
     * This is the name of the VagrantFile used by Vagrant
     */
    const VAGRANT_FILE_NAME     = 'Vagrantfile';

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
     * Log instance
     * @var null
     */
    protected $log              = null;

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
     * This method returns the version of the application
     *
     * @return float
     */
    public function getVersion()
    {
        return self::VERSION;
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
     * @param InputInterface  $input  is the input interface
     * @param OutputInterface $output is the output interface
     *
     * @return $this
     */
    public function before(InputInterface $input=null,
        OutputInterface $output=null
    ) {
        $this->pwd          = getcwd();
        $this->output       = $output;
        $this->_storeName   = $input->getOption('storage');
        $this->loadOutputStyles();
        $this->setCurrentEnvironment($this->getDefaultCurrentEnvironment());
        $this->loadEnvironments();
        return $this;
    }

    /**
     * Method runs the instance specific code.
     *
     * @param InputInterface  $input  is the input interface
     * @param OutputInterface $output is the output interface
     *
     * @return void
     * @throws \Exception when not implemented
     */
    public function runTransient(InputInterface $input=null,
        OutputInterface $output=null
    ) {
        throw new \Exception('Not Yet Implemented');
    }

    /**
     * This is the method which is called after the runTransient.
     *
     * @param InputInterface  $input  is the input interface
     * @param OutputInterface $output is the output interface
     *
     * @return void
     */
    public function after(InputInterface $input=null, OutputInterface $output=null)
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
        $this->before($input, $output);
        $this->runTransient($input, $output);
        $this->after($input, $output);
    }

    /**
     * This method loads the command styles (warning|general|notice|fatal_error)
     *
     * @return void
     */
    public function loadOutputStyles()
    {
        $styleOne   = array('bold');
        $styleTwo   = array('bold', 'underscore');
        $style      = new OutputFormatterStyle();
        $this->output->getFormatter()->setStyle('normal', $style);
        $style      = new OutputFormatterStyle('white', 'red', $styleOne);
        $this->output->getFormatter()->setStyle('warning', $style);
        $style      = new OutputFormatterStyle('white', 'green', $styleOne);
        $this->output->getFormatter()->setStyle('general', $style);
        $style      = new OutputFormatterStyle('white', 'blue', $styleOne);
        $this->output->getFormatter()->setStyle('notice', $style);
        $style      = new OutputFormatterStyle('white', 'red', $styleTwo);
        $this->output->getFormatter()->setStyle('fatal_error', $style);
    }

    /**
     * This method loads the _environments from disk into an array
     *
     * @return array
     */
    protected function loadEnvironments()
    {
        $this->purge();
        if (false == file_exists($this->getFileName())) {
            touch($this->getFileName());
        }
        $tempEnvironments = file($this->getFileName());
        foreach ($tempEnvironments as $key => $value) {
            $value = trim($value);
            if (null != $value && '' != $value && false == is_array($value)) {
                if (true == $this->_vagrentExists($value)) {
                    $this->_environments[] = $value;
                }
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
     * This method returns the path to the users home directory.
     *
     * @return string
     */
    public function getUserHome()
    {
        return getenv("HOME");
    }

    /**
     * Returns a user specific path to ~/.VagrantTransient
     *
     * @return string
     */
    public function getDefaultFileName()
    {
        return $this->getUserHome().self::DEFAULT_STORE_NAME;
    }

    /**
     * This method allows the user to override the store file name.
     *
     * This should be an absolute path.
     *
     * @param string $fileName Is the new store file name
     *
     * @return string
     */
    protected function setFileName($fileName)
    {
        $this->_storeName = $fileName;
        return $this->_storeName;
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
     * This method returns the path to the log file
     *
     * @return string
     */
    private function _getLogFileName()
    {
        static $logFileName = null;
        if (null == $logFileName) {
            $logFileName = $this->getUserHome().self::DEFAULT_LOG;
        }
        return $logFileName;
    }

    /**
     * This method purges the _environments array
     *
     * @return environments
     */
    public function purge()
    {
        $this->_environments = array();
        return $this->_environments;
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
     * Note that this does not to any environment validation
     *
     * @param array $environments Is the new array of environments
     *
     * @return $this
     */
    public function setEnvironments(array $environments)
    {
        $this->_environments = $environments;
        return $this;
    }

    /**
     * This method returns the number of environments
     *
     * @return int
     */
    public function getEnvironmentCount()
    {
        if (null == $this->_environments) {
            return 0;
        }
        return sizeof($this->_environments);
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
        if (false == $this->_vagrentExists($name)) {
            $msg = self::VAGRANT_FILE_NAME." not found in {$name}, skipping";
            $this->printLn($msg, 'error');
            return;
        }
        $environments       = $this->getEnvironments();
        $changed            = false;
        if (false == $this->environmentExists($name)) {
            $environments[] = $name;
            $changed        = true;
            $this->printLn("Environment {$name} created");
        }
        if (true == $changed) {
            $this->setEnvironments($environments);
            $environments   = array_reverse($environments);
            $this->setEnvironments($environments);
        } else {
            $message = "Environment {$name} already exists, skipping";
            $this->printLn($message, 'notice', false);
        }
        return $this;
    }

    /**
     * This method returns true if the environment exists.
     * This does not check the file system. Simply returns true if the environment
     * is within the storage array.
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
        $removed = false;
        $environments = $this->getEnvironments();
        if (true == in_array($name, $environments)) {
            if (($key = array_search($name, $environments)) !== false) {
                $this->printLn("Removing environment {$name}");
                unset($environments[$key]);
                $removed = true;
            }
        }
        if (false == $removed) {
            $this->printLn("environment {$name} not found or removed");
        }
        $this->setEnvironments($environments);
        return $this;
    }

    /**
     * This method halts every Vagrant environment.
     * This is the only method which executes a command.
     *
     * @param bool $ignoreCurrentEnvironment tells the method to ignore the
     * current environment and not call halt on it.
     *
     * @return $this;
     */
    protected function environmentsDown($ignoreCurrentEnvironment=false)
    {
        foreach ($this->getEnvironments() as $environment) {
            if (true == $ignoreCurrentEnvironment 
                && $environment == $this->getCurrentEnvironment()
            ) {
                $this->printLn("Skipping current environment {$environment}");
            } else if (true == file_exists($environment)) {
                chdir($environment);
                $this->printLn("Attempting shutdown down vagrant in {$environment}");
                $output = '';
                $return = '';
                exec('vagrant halt', $output, $return);
                if (false == is_array($output)) {
                    $output = array($output);
                }
                if (0 != $return) {
                    $this->printLn("Error found, logged", 'warning');
                    $this->printLn(implode(', ', $output), 'warning', false);
                } else {
                    $this->printLn(implode(', ', $output), 'notice');
                }
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
     * @param bool   $out     Tells the method to print to the terminal or just log
     *
     * @return void
     */
    protected function printLn($message, $type='general', $out=true)
    {
        if (null == $message) {
            return;
        }
        if (null != $this->output) {
            $message = (null == $type) ? $message : "<{$type}>{$message}</{$type}>";
            if (true == $out) {
                $this->output->writeLn($message);
            }
        }
        $this->getLog()->addInfo($message, array('type' => $type));
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
        $this->printLn("Current Environment set to {$currentEnvironment}");
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
        return realpath('.');
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

            $msg = "Cleaning environment  from storage {$environment}";
            if (true == $this->_vagrentExists($environment)) {
                $environmentsWhichExist[] = $environment;
                $msg = "Not cleaning from storage {$environment}";
            }
            $this->printLn($msg);
        }
        $this->setEnvironments($environmentsWhichExist);
        return $this;
    }

    /**
     * This method migrates an old store to a new store.
     * It does however validate that the environment is valid
     * before adding it to the store.
     *
     * @param string $oldStore Is the path to the old store
     *
     * @return array of new environments
     */
    protected function migrateEnvironments($oldStore)
    {
        $currentStore = $this->getFileName();   //As a local cache
        if (false == file_exists($oldStore)) {
            $this->printLn("Old store not found at {$oldStore}", 'error');
        } else {
            $this->printLn("Old store found {$oldStore}");
        }
        $this->setFileName($oldStore);
        $this->loadEnvironments();
        $this->printLn("Old environments loaded");
        $oldEnvironments = $this->getEnvironments();
        $this->setFileName($currentStore);
        $this->loadEnvironments();
        foreach ($oldEnvironments as $oldEnvironment) {
            /*
             * Doing it this way validates the environment exists
             * before adding them to the store
             */
            $this->createEnvironment($oldEnvironment);
        }
        $this->save();
        $this->printLn("Old environments appended onto current store");
        $this->getEnvironments();
    }

    /**
     * This method returns true if the environment returned has a VagrantFile in it
     *
     * @param string $environment to check
     * 
     * @return bool
     */
    private function _vagrentExists($environment)
    {
        $fileName = $environment.DIRECTORY_SEPARATOR.self::VAGRANT_FILE_NAME;
        return file_exists($fileName);
    }

    /**
     * This method returns a singleton instance of the logger class.
     *
     * @return Logger
     */
    public function getLog()
    {
        if (null == $this->log) {
            $handler    = new StreamHandler($this->_getLogFileName(), Logger::INFO);
            $this->log  = new Logger('VagrantTransient');
            $this->log->pushHandler($handler);
        }
        return $this->log;
    }
}