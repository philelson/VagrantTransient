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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Returns a list of the current environments
 *
 * @category Pegasus_Utilities
 * @package  Environments
 * @author   Philip Elson <phil@pegasus-commerce.com>
 * @license  MIT http://opensource.org/licenses/MIT
 * @link     http://pegasus-commerce.com
 */
class Environments extends VagrantTransient
{
    /**
     * This method returns the name if this application
     *
     * @return string
     */
    public function getName()
    {
        return 'environments';
    }

    /**
     * This method returns the description of this application
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Returns a list of the current environments';
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
        foreach ($this->getEnvironmentMessages() as $message => $style) {
            $this->printLn($message, $style);
        }
    }

    /**
     * This method returns an array of messages
     *
     * @return array
     */
    public function getEnvironmentMessages()
    {
        $messages = array();
        foreach ($this->getEnvironments() as $environment) {
            $style = 'general';
            if (false == $this->vagrentExists($environment)) {
                $style = 'warning';
            }
            $messages[$environment] = $style;
        }
        return $messages;
    }

    /**
     * This method sets the current environment
     * Overridden to stop the output of the current environment
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
}