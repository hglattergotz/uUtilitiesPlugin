# uUtilitiesPlugin ![project status](http://stillmaintained.com/hglattergotz/uUtilitiesPlugin.png) #

Symfony plugin (1.2, 1.4) that provides a number of utility classes.

### Installation

Download the plugin as a zip file [here](https://github.com/hglattergotz/uUtilitiesPlugin/archive/master.zip)

Unpack the contents and move the folder to the plugins directory in your symfony
project. You might need to rename the folder from uUtilitiesPlugin-master to
simply uUtilitiesPlugin (github adds the -master).

To enable the plugin in your project add the following

```php
$this->enablePlugins('uUtilitiesPlugin');
```

to the setup() function in config/ProjectConfiguration.class.php.

The task will show up under the doctrine namespace when you issue ./symfony on
the command line.

### Use

To get help on the console command issue

```
./symonfy help doctrine:build-table-schema
```
