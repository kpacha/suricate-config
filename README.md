suricate-config
================

A simple config module with an integrated suricate service discovery system for php

[![Build Status](https://travis-ci.org/kpacha/suricate-config.png?branch=master)](https://travis-ci.org/kpacha/suricate-config)

#Requirements

* git
* PHP >=5.3.3
* [suricate-php-sdk](https://github.com/kpacha/suricate-php.sdk) (so check it's dependencies!)

#Installation

##Standalone

##As a library (recomended)

Include the `kpacha/suricate-config` package in your compose.json with all the dependencies of your project

    "require":{
        "kpacha/suricate-config": "~0.1"
    }

###Git installation

Clone the repo

    $ git clone https://github.com/kpacha/suricate-config.git

Install the php dependencies

    $ cd suricate-config
    $ curl -sS https://getcomposer.org/installer | php
    $ php composer.phar install

###Composer installation

Create a project with composer

    $ curl -sS https://getcomposer.org/installer | php
    $ php composer.phar create-project kpacha/suricate-config [directory]

Remeber to set the [directory] parameter or composer will create the project in your current path.

#Config files

Note that suricate-config expects to find all your config files in a single dir. Currently, yaml is the only supported format for config files.

Also, you should keep in mind those rules:

* All parsed config files will be cached in a single native php config file.
* Every parsed config file will be stored in an array, indexed by its basename.
* The suricate-config module uses the `Symfony\Component\Config\ConfigCache` class to manage the cached configuration, so it will create a `.meta` file with some info about the cached files.
* suricate-config expects to find a config file called `suricate_services.yml` with some required info (check the [tests/fixtures](https://github.com/kpacha/suricate-config/tree/master/tests/fixtures) dir for an example)
* The service autodiscovering manager will create a config file called `suricate_services_solved.yml`file. Please, do not play with it.

##suricate_services.yml

The required fields are:

* *server*: the url of the suricate server
* *service-names*: the list of services to watch
* *default*: the default configuration to use when the suricate server is not reachable

#Usage

##Config module

Create a `Kpacha\Suricate\Config\Configuration` object.

    use Kpacha\Suricate\Config\Configuration;
    $configuration = new Configuration('/path/to/your/config/folder', true);

And you're ready to go! Just ask for your config data whenever you need it.

    $someModuleConfig = $configuration->get('some-module');

    try{
        $configuration->get('unknown-module'); // unknown-module.yml does not exist
    } catch(\Exception $e){
       // do something
    }

##Console

The *suricate-php-sdk* package comes with a simple client and several console commands bundled in a simple app. Check the [project web](https://github.com/kpacha/suricate-php-sdk) for more info.

The *suricate-config* packages extends the suricate-php-sdk in order to expose a clean CLI interface so you could add a cron to:

* Send a periodic heartbit to notify the suricate server the node is up. Check the inline help for the command `$ bin/suricate-config s:h --help`
* Update the service info querying the suricate server for the services listed in the `service-name` area of your `suricate_services.yml` file with `$ bin/suricate-config s:u /path/to/config/dir`

Run the `suricate-config` script to trigger any console command. 

Visit the [suricate-php-sdk project](https://github.com/kpacha/suricate-php.sdk) for more info.