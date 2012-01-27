<?php
/**
 * @package     uUtilitiesPlugin
 * @subpackage  SchemaBuilder
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class SchemaBuilder
{
  public function __construct()
  {}
  
  public static function getInstance()
  {
    return new self();
  }

  /**
   * Update the existing schema.yml file with any new tables that were passed
   * to the method.
   * 
   * @param string $outputFile The full path of the current schema file
   * @param array $connections An associative array of connections and their
   *                           tables. See buildPHPModels
   * @param array $options     Any options to be passed to
   *                           Doctrine_Import_Builder
   * @return type 
   */
  public function update($outputFile, array $connections = array(), array $options = array())
  {
    try
    {
      $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tmp_doctrine_models';

      $options['generateBaseClasses'] = isset($options['generateBaseClasses']) ? $options['generateBaseClasses'] : false;
      $models = $this->buildPHPModels($directory, $connections, $options);

      if (empty($models) && !is_dir($directory))
      {
        throw new Exception('No models generated from your databases');
      }

      $this->generateYaml($outputFile, $directory, array(), Doctrine_Core::MODEL_LOADING_AGGRESSIVE);

      Doctrine_Lib::removeDirectories($directory);
    }
    catch (Exception $e)
    {
      throw new Exception(__METHOD__ . ':' . __LINE__ . '|' . $e->getMessage());
    }
    
    return 0;
  }

  /**
   * Build the schema for multiple connections and specific tables for those
   * connections.
   * 
   * Example:
   *   $connections = array(
   *       'connection1' => array('table1', 'table2'),
   *       'connection2' => array('table1', 'table2')
   *   );
   * 
   * @param type $directory
   * @param array $connections Array of connection names with their associated
   *                           tables
   * @param array $options
   * @return array 
   */
  protected function buildPHPModels($directory, array $connections = array(), array $options = array())
  {
    $classes = array();

    $manager = Doctrine_Manager::getInstance();

    foreach ($manager as $name => $connection)
    {
      // Limit the databases to the ones specified by $connections.
      // Check only happens if array is not empty
      $connectionNames = array_keys($connections);
      
      if (!empty($connections) && !in_array($name, $connectionNames))
      {
        continue;
      }

      $builder = new Doctrine_Import_Builder();
      $builder->setTargetPath($directory);
      $builder->setOptions($options);

      $definitions = array();
      $currentConnName = $connection->getName();

      foreach ($connection->import->listTables() as $table)
      {
        if (!in_array($table, $connections[$currentConnName]))
        {
          continue;
        }
        
        $definition = array();
        $definition['tableName'] = $table;
        $definition['className'] = Doctrine_Inflector::classify(Doctrine_Inflector::tableize($table));
        $definition['columns'] = $connection->import->listTableColumns($table);
        $definition['connection'] = $connection->getName();
        $definition['connectionClassName'] = $definition['className'];

        try
        {
          $definition['relations'] = array();
          $relations = $connection->import->listTableRelations($table);
          $relClasses = array();
          foreach ($relations as $relation)
          {
            $table = $relation['table'];
            $class = Doctrine_Inflector::classify(Doctrine_Inflector::tableize($table));
            if (in_array($class, $relClasses))
            {
              $alias = $class . '_' . (count($relClasses) + 1);
            }
            else
            {
              $alias = $class;
            }
            $relClasses[] = $class;
            $definition['relations'][$alias] = array(
                'alias' => $alias,
                'class' => $class,
                'local' => $relation['local'],
                'foreign' => $relation['foreign']
            );
          }
        }
        catch (Exception $e)
        {
          
        }

        $definitions[strtolower($definition['className'])] = $definition;
        $classes[] = $definition['className'];
      }

      // Build opposite end of relationships
      foreach ($definitions as $definition)
      {
        $className = $definition['className'];
        $relClasses = array();
        foreach ($definition['relations'] as $alias => $relation)
        {
          if (in_array($relation['class'], $relClasses) || isset($definitions[$relation['class']]['relations'][$className]))
          {
            $alias = $className . '_' . (count($relClasses) + 1);
          }
          else
          {
            $alias = $className;
          }
          $relClasses[] = $relation['class'];
          $definitions[strtolower($relation['class'])]['relations'][$alias] = array(
              'type' => Doctrine_Relation::MANY,
              'alias' => $alias,
              'class' => $className,
              'local' => $relation['foreign'],
              'foreign' => $relation['local']
          );
        }
      }

      // Build records
      foreach ($definitions as $definition)
      {
        $builder->buildRecord($definition);
      }
    }

    return $classes;
  }

  protected function generateYaml($schemaPath, $directory = null, $models = array(), $modelLoading = null)
  {
    $currentProjectModels = (array) sfYaml::load($schemaPath);
    
    $export = new Doctrine_Export_Schema();
    $newProjectModels = $export->buildSchema($directory, $models, $modelLoading);

    $projectModels = array_merge($currentProjectModels, $newProjectModels);
    
    if (is_dir($schemaPath))
    {
      $schemaPath = $schemaPath . DIRECTORY_SEPARATOR . 'schema.yml';
    }
        
    return Doctrine_Parser::dump($projectModels, 'yml', $schemaPath);
  }

}