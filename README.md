# VagrantTransient

This application stops developers having to navigate to each project and check the Vagrant instance is down before starting their new Vagrant instance.

When configured within a VagrantFile of every project using Vagrant this application will shutdown all Vagrant instances before starting the current instance.

This is a PHP implementation of https://bitbucket.org/pegasus-projects/vagrant-transient
