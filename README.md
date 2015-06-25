# VagrantTransient

This application stops developers having to navigate to each project and check the Vagrant instance is down before starting their new Vagrant instance.

When configured within a VagrantFile of every project using Vagrant this application will shutdown all Vagrant instances before starting the current instance.

Please note that this isn't a plugin for Vagrant.

## Installation

Ensure you have downloaded and installed Vagrant 1.2+ from the
[Vagrant downloads page](http://downloads.vagrantup.com/).

__Tagrant Transient__ is designed to work with the [vagrant-triggers](https://github.com/emyl/Vagrant-triggers) plugin.

Installation is performed in the prescribed manner for Vagrant plugins:

        vagrant plugin install vagrant-triggers

Once this is installed Vagrant Transient can be done a couple of ways:

1. by downloading the vagrant_transient.phar file and adding it into /usr/bin
2. Add "pegasuscommerce/vagrant_transient" to composer using the current version

Once this has been done environments in Vagrant Transient are automatically created, destroyed and shutdown in Vagrant

## Using Vagrant Transient
*Note that Vagrant Transient will manage an instance once 'vagrant up' has been called or '{vagrant_transient} create' has been called from a directory with a Vagrantfile in it. Only then will an environment be managed by Vagrant Transient.*

Once installed you can use it to manage Vagrant instances through Vagrant or just via the terminal using the vagrant\_transient.phar, bin/vagrant\_transient to create, destroy etc.

###Adding the triggers to Vagrant
####Composer method
Open your Vagrantfile and add the following:

```bash
    
    config.trigger.before :up do
       run "bin/vagranttransient dc"
    end

    config.trigger.after :destroy do
       run "bin/vagranttransient destroy"
    end
```

####vagrant_transient.phar method

```bash

    config.trigger.before :up do
       run "vagrant_transient.phar dc"
    end

    config.trigger.after :destroy do
       run "vagrant_transient.phar destroy"
    end
```

This will call 'vagrant_transient.phar dc' when 'vagrant up' is called and
'vagrant_transient.phar destroy' when 'vagrant destroy' is called. Creating and
destroying vagrant-transient environments automatically.

###Commands which are available

* __clean__         Cleans all store environments
* __create__        Adds environment to storage
* __dc__            Shuts down all vagrants before creating the current environment
* __destory__       Removed environment from storage
* __down__          Shuts down all vagrant instances
* __migrate__       Migrates config from storage to storage
* __version__       Returns the version of this application
* __environments__  Returns a list of the current environments in Vagrant Transient


##Other

Feel free to report bugs, fork etc. If you want to contribute just create a pull request when you're ready.

This is a PHP implementation of the original https://bitbucket.org/pegasus-projects/vagrant-transient