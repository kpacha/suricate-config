suricate-config
================

A simple config module with an integrated suricate service discovery system for php

[![Build Status](https://travis-ci.org/kpacha/suricate-config.png?branch=master)](https://travis-ci.org/kpacha/suricate-config) [![Dependency Status](https://www.versioneye.com/user/projects/54691a34950825a8f700004b/badge.svg?style=flat)](https://www.versioneye.com/user/projects/54691a34950825a8f700004b)

#Requirements

* git
* PHP >=5.3.3
* [kpacha/config](https://github.com/kpacha/config) (so check its dependencies!)
* [kpacha/suricate-php-sdk](https://github.com/kpacha/suricate-php-sdk) (so check its dependencies!)

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

Just set the value `\\Kpacha\\Suricate\\Config\\ServiceManager` to your `service-manager` config param and fix the url of your suricate server.

#Usage

##Config module

This package is a transparent extension for [kpacha/config](https://github.com/kpacha/config) module

##Console

The `kpacha/suricate-php-sdk` package comes with a simple client and several console commands bundled in a simple app. Check the [project web](https://github.com/kpacha/suricate-php-sdk) for more info.

The `kpacha/suricate-config` packages extends the `kpacha/suricate-php-sdk` and adds the `Update` command from `kpacha/config` in order to expose a clean CLI so you could add a cron to:

* Send a periodic heartbeat to notify the suricate server the node is up. Check the inline help for the command `$ bin/suricate-config s:h --help`
* Update the service info querying the suricate server for the services listed in the `service-name` area of your `suricate_services.yml` file with `$ bin/suricate-config c:u /path/to/config/dir`

Run the `suricate-config` script to trigger any console command. 

Visit the [suricate-php-sdk project](https://github.com/kpacha/suricate-php.sdk) for more info.
