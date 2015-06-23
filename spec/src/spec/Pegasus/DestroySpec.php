<?php

namespace spec\Pegasus;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DestroySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pegasus\Destroy');
    }
}
