<?php

namespace spec\Pegasus\Application\VagrantTransient;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DownSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pegasus\Application\VagrantTransient\Down');
    }
}
