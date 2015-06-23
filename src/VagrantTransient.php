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
    const DEFAULT_STORE_NAME    = "/.VagrantTransient";

    private $storeName          = null;

    private $environments       = array();

    protected $output           = null;

    protected $pwd              = null;

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

    public function getName()
    {
        return 'vagrant-transient';
    }

    public function getDescription()
    {
        return 'Vagrant Transient';
    }

    public function before()
    {
        $this->loadEnvironments();
    }

    public function runTransient()
    {
        throw new Exception('Not Yet Implemented');
    }

    public function after()
    {
        $this->save();
    }

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

    public function refresh()
    {
        $this->loadEnvironments();
    }

    public function getDefaultFileName()
    {
        return getenv("HOME").self::DEFAULT_STORE_NAME;
    }

    public function getFileName()
    {
        if(null == $this->storeName) {
            $this->storeName = $this->getDefaultFileName();
        }
        return $this->storeName;
    }

    public function purge()
    {
        $this->environments = array();
    }

    public function getEnvironments()
    {
        return $this->environments;
    }

    public function setEnvironments(array $environments)
    {
        $this->environments = $environments;
        return $this;
    }

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

    public function environmentExists($name)
    {
        return in_array($name, $this->getEnvironments());
    }

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
    }

    protected function printLn($message, $type='general')
    {
        if(null != $this->output)
        {
            $message = (null == $type) ? $message : "<{$type}>{$message}</{$type}>";
            $this->output->writeLn($message);
        }
    }

    protected function getCurrentEnvironment()
    {
        return __DIR__;
    }
}