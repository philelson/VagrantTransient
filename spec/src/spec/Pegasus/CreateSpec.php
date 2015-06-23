<?php

namespace spec\Pegasus;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CreateSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pegasus\Create');
    }

    function its_name_should_be_create()
    {
        $this->getName()->shouldEqual('create');
    }
}
