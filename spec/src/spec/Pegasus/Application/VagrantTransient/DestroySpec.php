<?php

namespace spec\Pegasus\Application\VagrantTransient;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DestroySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pegasus\Application\VagrantTransient\Destroy');
    }
}
