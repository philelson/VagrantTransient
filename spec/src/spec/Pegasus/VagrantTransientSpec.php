<?php

namespace spec\Pegasus;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class VagrantTransientSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pegasus\VagrantTransient');
    }

    function its_name_should_be_create()
    {
        $this->getName()->shouldEqual('vagrant-transient');
    }

    function it_should_have_the_name_in_the_store()
    {
        $this->createEnvironment('example');
        $this->environmentExists('example')->shouldEqual(true);
        $this->createEnvironment('example2');
        $this->environmentExists('example2')->shouldEqual(true);
        $this->createEnvironment('example3');
        $this->environmentExists('example3')->shouldEqual(true);
        $this->createEnvironment('example4');
        $this->environmentExists('example4')->shouldEqual(true);
        $this->environmentExists('example5')->shouldEqual(false);
    }

    function it_should_remove_cached_environments_on_purge()
    {
        $this->createEnvironment('example4');
        $this->environmentExists('example4')->shouldEqual(true);
        $this->purge();
        $this->environmentExists('example4')->shouldEqual(false);
    }

    function it_should_save_environments_to_storage()
    {
        $names = array();
        for($ii = 0; $ii < 10; $ii++)
        {
            $name = 'environment_'.rand(0, 9999999);
            if(false == in_array($name, $names))
            {
                $names[] = $name;
            }
        }
        $this->purge();
        foreach($names as $name)
        {
            $this->createEnvironment($name);
        }
        $this->save();
        $this->refresh();
        foreach($names as $name)
        {
            $this->environmentExists($name)->shouldEqual(true);
        }
    }

    function it_should_not_allow_duplicate_rows()
    {
        $this->purge();
        $this->createEnvironment('test');
        $this->environmentExists('test')->shouldEqual(true);
        $this->createEnvironment('test');
        $this->environmentExists('test')->shouldEqual(true);
    }

    function it_should_be_able_to_remove_a_row()
    {
        $this->purge();
        $this->createEnvironment('test');
        $this->environmentExists('test')->shouldEqual(true);
        $this->createEnvironment('test');
        $this->environmentExists('test')->shouldEqual(true);
        $this->removeEnvironment('test');
        $this->environmentExists('test')->shouldEqual(false);
    }

    function it_should_allow_overriding_of_the_current_environment()
    {
        $this->setCurrentEnvironment('test');
        $this->getCurrentEnvironment()->shouldEqual('test');
    }

}
