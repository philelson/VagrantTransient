<?php

namespace spec\Pegasus\Application\VagrantTransient;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class VagrantTransientSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Pegasus\Application\VagrantTransient\VagrantTransient');
    }

    function its_name_should_be_create()
    {
        $this->getName()->shouldEqual('vagrant-transient');
    }

    function it_should_remove_cached_environments_on_purge()
    {
        $dirs = $this->createEnvironments(true, 'it_should_remove_cached_environments_on_purge');
        $this->purge();
        foreach($dirs as $dir)
        {
            $this->createEnvironment($dir);
        }
        $this->save();
        $this->refresh();
        foreach($dirs as $dir)
        {
            $this->environmentExists($dir)->shouldEqual(true);
        }
        $this->purge();
        foreach($dirs as $dir)
        {
            $this->environmentExists($dir)->shouldEqual(false);
        }
        $this->deleteEnvironments($dirs);
    }

    function it_should_save_environments_to_storage_when_environment_has_vagrantfile()
    {
        $dirs = $this->createEnvironments(true, 'it_should_save_environments_to_storage_when_environment_has_vagrantfile');
        $this->purge();
        foreach($dirs as $dir)
        {
            $this->createEnvironment($dir);
        }
        $this->save();
        $this->refresh();
        foreach($dirs as $dir)
        {
            $this->environmentExists($dir)->shouldEqual(true);
        }
        $this->deleteEnvironments($dirs);
    }

    function it_should_not_save_environments_to_storage_when_environment_has_no_vagrantfile()
    {
        $dirs = $this->createEnvironments(false, 'it_should_not_save_environments_to_storage_when_environment_has_no_vagrantfile');
        $this->purge();
        foreach($dirs as $dir)
        {
            $this->createEnvironment($dir);
        }
        $this->save();
        $this->refresh();
        foreach($dirs as $dir)
        {
            $this->environmentExists($dir)->shouldEqual(false);
        }
        $this->deleteEnvironments($dirs);
    }

    function it_should_not_allow_duplicate_rows()
    {
        $dirs = $this->createEnvironments(true, 'it_should_not_allow_duplicate_rows');
        $this->purge();
        foreach($dirs as $dir)
        {
            $this->createEnvironment($dir);
            $this->environmentExists($dir)->shouldEqual(true);
            $size = $this->getEnvironmentCount();
            $this->createEnvironment($dir);
            $this->environmentExists($dir)->shouldEqual(true);
            $this->getEnvironmentCount()->shouldEqual($size);
        }
        $this->deleteEnvironments($dirs);
    }

    function it_should_return_the_number_of_environments()
    {
        $dirs = $this->createEnvironments(true, 'it_should_return_the_number_of_environments');
        $this->purge();
        foreach($dirs as $dir)
        {
            $this->createEnvironment($dir);
            $this->environmentExists($dir)->shouldEqual(true);
        }
        $this->getEnvironmentCount()->shouldEqual(sizeof($dirs));
        $this->deleteEnvironments($dirs);
    }

    function it_should_be_able_to_remove_a_row()
    {
        $dirs = $this->createEnvironments(true, 'it_should_be_able_to_remove_a_row');
        $this->purge();
        foreach($dirs as $dir)
        {
            $this->createEnvironment($dir);
            $this->environmentExists($dir)->shouldEqual(true);
            $size = $this->getEnvironmentCount();
            $this->createEnvironment($dir);
            $this->environmentExists($dir)->shouldEqual(true);
            $this->getEnvironmentCount()->shouldEqual($size);
            $this->removeEnvironment($dir);
            $this->environmentExists($dir)->shouldEqual(false);
        }
        $this->deleteEnvironments($dirs);
    }

    function it_should_allow_overriding_of_the_current_environment()
    {
        $this->getCurrentEnvironment()->shouldNotEqual('test');
        $this->setCurrentEnvironment('test');
        $this->getCurrentEnvironment()->shouldEqual('test');
    }

    /* Utility functions */

    private function createEnvironments($withVagrantFile=true, $prefix='test', $testDir='test', $dirs=10, $fileName='/Vagrantfile')
    {
        $directories = array();
        $path = realpath('.').'/'.$testDir.'/';
        for($ii = 0; $ii < $dirs; $ii++)
        {
            $newTestDir = $path.$prefix.'_'.$ii;
            mkdir($newTestDir, 0777, true);
            if(true == $withVagrantFile)
            {
                touch($newTestDir . $fileName);
            }
            $directories[] = $newTestDir;
        }
        return $directories;
    }

    private function deleteEnvironments($environments, $testDir='test', $fileName='/Vagrantfile')
    {
        foreach($environments as $dir)
        {
            if(true == file_exists($dir.$fileName))
            {
                unlink($dir.$fileName);
            }
            rmdir($dir);
        }
        rmdir(realpath('.').'/'.$testDir.'/');
    }
}
