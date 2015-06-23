<?php
 /**
 *  
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

class VagrantTransient extends Command
{
    /**
     * This is the default file where the environments are stored.
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
    private $storeName          = null;

    /**
     * Array which holds the environments
     *
     * @var array
     */
    private $environments       = array();

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
     * Configures the application
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
     */
    public function before()
    {
        $this->loadEnvironments();
    }

    /**
     * Method runs the instance specific code.
     */
    public function runTransient()
    {
        throw new Exception('Not Yet Implemented');
    }

    /**
     * This is the method which is called after the runTransient.
     */
    public function after()
    {
        $this->save();
    }

    /**
     * This is the command which executes the application
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->pwd = getcwd();
        $this->output = $output;
        $this->storeName = $input->getOption('storage');
        $this->loadOutputStyles();
        $this->before();
        $this->runTransient();
        $this->after();
    }

    /**
     * This method loads the command styles (wanring|general|notice|fatal_error)
     */
    public function loadOutputStyles()
    {
        $style = new OutputFormatterStyle('white', 'red', array('bold'));
        $this->output->getFormatter()->setStyle('warning', $style);
        $style = new OutputFormatterStyle('white', 'blue', array('bold'));
        $this->output->getFormatter()->setStyle('general', $style);
        $style = new OutputFormatterStyle('white', 'green', array('bold'));
        $this->output->getFormatter()->setStyle('notice', $style);
        $style = new OutputFormatterStyle('white', 'red', array('bold', 'underscore'));
        $this->output->getFormatter()->setStyle('fatal_error', $style);
    }

    /**
     * This method loads the environments from disk into an array
     *
     * @return array
     */
    protected function loadEnvironments()
    {
        $this->environments = array();
        if(false == file_exists($this->getFileName()))
        {
            touch($this->getFileName());
        }
        $tempEnvironments = file($this->getFileName());
        foreach($tempEnvironments as $key => $value)
        {
            $value = trim($value);
            if(null != $value && '' != $value && false == is_array($value))
            {
                $this->environments[] = $value;
            }
        }
        return $this->environments;
    }

    /**
     * This method iterates over the environments writing them to disk.
     *
     * @return string
     */
    public function save()
    {
        unlink($this->getFileName());
        touch($this->getFileName());
        $content = "";
        foreach($this->getEnvironments() as $environment)
        {
            $content .= "{$environment}\n";
        }
        file_put_contents($this->getFileName(), $content);
        return $content;
    }

    /**
     * This method reloads the environments
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
        if(null == $this->storeName) {
            $this->storeName = $this->getDefaultFileName();
        }
        return $this->storeName;
    }

    /**
     * This method purges the environments array
     */
    public function purge()
    {
        $this->environments = array();
    }

    /**
     * This method returns the environment array
     *
     * @return array
     */
    public function getEnvironments()
    {
        return $this->environments;
    }

    /**
     * This method overrides the environments array.
     *
     * @param array $environments
     * @return $this
     */
    public function setEnvironments(array $environments)
    {
        $this->environments = $environments;
        return $this;
    }

    /**
     * This method adds the environment to storage if it is not already in storage.
     *
     * @param $name
     * @return $this
     */
    public function createEnvironment($name)
    {
        $environments = $this->getEnvironments();
        $changed = false;
        if(false == $this->environmentExists($name))
        {
            $environments[] = $name;
            $changed = true;
        }
        if(true == $changed)
        {
            $this->setEnvironments($environments);
            $environments = array_reverse($environments);
            $this->setEnvironments($environments);
        }
        return $this;
    }

    /**
     * This method returns true if the environment exists.
     *
     * @param $name
     * @return bool
     */
    public function environmentExists($name)
    {
        return in_array($name, $this->getEnvironments());
    }

    /**
     * This method removed an environment from the system.
     * The environment must match exectly to the path in storage.
     *
     * @param $name
     * @return $this
     */
    public function removeEnvironment($name)
    {
        $environments = $this->getEnvironments();
        if(true == in_array($name, $environments))
        {
            if(($key = array_search($name, $environments)) !== false)
            {
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
        foreach($this->getEnvironments() as $environment)
        {
            if(true == file_exists($environment))
            {
                //echo $environment;
                chdir($environment);
                $this->printLn("Shutting down vagrant in {$environment}");
                $output = '';
                $return = '';
                exec('vagrant halt', $output, $return);
                if(false == is_array($output))
                {
                    $output = array($output);
                }
                $this->printLn(implode(', ', $output), 0 == $return ? 'notice' : 'warning');
            }
            else
            {
                $this->printLn("Dir not found {$environment}");

            }
        }
        chdir($this->pwd);
        return $this;
    }

    /**
     * This method prints a line to the terminal.
     *
     * @param $message      Is the message
     * @param string $type  Is the type of message (warning|notice|general)
     */
    protected function printLn($message, $type='general')
    {
        if(null != $this->output)
        {
            $message = (null == $type) ? $message : "<{$type}>{$message}</{$type}>";
            $this->output->writeLn($message);
        }
    }

    /**
     * Returns the directory of this environment.
     *
     * @return string
     */
    protected function getCurrentEnvironment()
    {
        return __DIR__;
    }

    /**
     * This method iterates over the environments. If there is no VagrantFile within that environment
     * then it is removed from the list of environments.
     *
     * @return $this
     */
    protected function cleanEnvironments()
    {
        $environmentsWhichExist = array();
        foreach($this->getEnvironments() as $key => $environment)
        {
            if(true == file_exists($environment.DIRECTORY_SEPARATOR.self::VAGRANT_FILE_NAME))
            {
                $environmentsWhichExist[] = $environment;
            }
        }
        $this->setEnvironments($environmentsWhichExist);
        return $this;
    }
}