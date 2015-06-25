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

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Migrates config from storage to storage
 *
 * @category Pegasus_Utilities
 * @package  Version
 * @author   Philip Elson <phil@pegasus-commerce.com>
 * @license  MIT http://opensource.org/licenses/MIT
 * @link     http://pegasus-commerce.com
 */
class Version extends VagrantTransient
{
    /**
     * This method returns the name if this application
     *
     * @return string
     */
    public function getName()
    {
        return 'version';
    }

    /**
     * This method returns the description of this application
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Returns the version of this application';
    }

    /**
     * This method performs the logic of this application.
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
        $this->printLn("Vagrant Transient Version: ".$this->getVersion(), 'normal');
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
        $this->output       = $output;
        $this->loadOutputStyles();
        return $this;
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
        //do nothing
    }
}