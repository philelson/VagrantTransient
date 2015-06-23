<?php

namespace spec\Pegasus;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DownSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pegasus\Down');
    }
}
