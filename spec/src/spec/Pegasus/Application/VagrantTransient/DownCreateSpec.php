<?php

namespace spec\Pegasus\Application\VagrantTransient;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DownCreateSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pegasus\Application\VagrantTransient\DownCreate');
    }
}
