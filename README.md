# uUtilitiesPlugin ![project status](http://stillmaintained.com/hglattergotz/uUtilitiesPlugin.png) #

Symfony plugin (1.2, 1.4) that provides a number of utility classes.

### Installation

Download the plugin as a zip file [here](https://github.com/hglattergotz/uUtilitiesPlugin/archive/master.zip).

Unpack the contents and move the folder to the plugins directory in your symfony
project. You might need to rename the folder from *uUtilitiesPlugin-master* to
simply *uUtilitiesPlugin* (github adds the -master).

To enable the plugin in your project add the following

```php
$this->enablePlugins('uUtilitiesPlugin');
```

to the ```setup()``` function in ```config/ProjectConfiguration.class.php```.

The task will show up under the doctrine namespace when you issue ```./symfony``` on
the command line.

### Tasks

The plugin has 4 console commands:

 * doctrine:build-table-schema
 * uUtil:backup
 * uUtil:get-svn-revision
 * uUtil:showconfig

Use ```./symfony help``` on any of the console commands to get more information
about their use.

For example, to get the help on the doctrine:build-table-schema console command
run this on the command line in the project folder:

```
./symonfy help doctrine:build-table-schema
```

More information about the doctrine:build-table-schema can be found <a href="http://glatter-gotz.com/blog/2012/07/16/symfony-doctrine-build-schema-for-a-single-table">here</a>.
