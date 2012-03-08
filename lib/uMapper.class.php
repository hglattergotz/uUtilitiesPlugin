<?php
/**
 * uMapper takes an array of arrays (database records for example) and maps the
 * specified field to the field called 'id' and vice verca. This way it is
 * possible to access any record by either it's 'id' field or the field that is
 * specified.
 * 
 * This class throws exceptions by default. This can be suppressed by passing
 * the $throwException parameter with a value of false to the various functions
 * that do throw exceptions.
 * The constructor will throw an exception that cannot be suppressed.
 * 
 * Use Case:
 *   Lookup id's for a smallish set (in the thousands) of records many times
 *   (hundreds of thousands of times or even millions of times) during a long
 *   running operation.
 * 
 * Requirements:
 *   The records that are loaded into the mapper must be associative arrays and
 *   must contain a key named 'id'.
 *   The 'id' and mapped field must have unique values.
 * 
 * Example:
 *   $data = array(
 *     array('id' => 1, 'lookup' => 'fld1', 'key1' => 'somedata1', ...),
 *     array('id' => 2, 'lookup' => 'fld2', 'key1' => 'somedata2', ...),
 *     array('id' => 3, 'lookup' => 'fld3', 'key1' => 'somedata3', ...),
 *     array('id' => 4, 'lookup' => 'fld4', 'key1' => 'somedata4', ...),
 *   );
 * 
 *   $map = new uMapper('Field Mapper', $data, 'lookup');
 * 
 *   // To get the id of the record that has a lookup field value of 'fld3'.
 *   $record = $map->getRecByName('fld3');
 * 
 *   // To get the id of the record that has lookup == 'fld3'
 *   $id = $map->getId('fld3');
 *
 * @package     uUtilitiesPlugin
 * @subpackage  uMapper
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uMapper
{
  /**
   * @var string, The name for the mapper instance. This is used when throwing
   *              exceptions and can be helpful to identify the mapper instance
   *              in larger systems that have multiple mapper instances. 
   */
  protected $name;
  
  /**
   * @var array, The original data array that gets mapped.
   */
  protected $data;
  
  /**
   * @var string, The name of the field/key to map to the id field/key.
   */
  protected $fieldToMap;
  
  /**
   * @var array, The internal lookup array that maps the name to the id.
   */
  protected $nameToId;
  
  /**
   * @var array, The internal lookup array that maps the id to the name.
   */
  protected $idToName;

  /**
   * Constructor
   * 
   * @param string $name       See description above
   * @param array  $data       See description above
   * @param string $fieldToMap See description above
   * @throws Exception
   */
  public function __construct($name, $data, $fieldToMap = 'name')
  {
    $this->name = $name;
    $this->fieldToMap = $fieldToMap;
    $this->validata($data);
    $this->data = $data;
    $this->nameToId = array();
    $this->idToName = array();

    foreach ($data as $k => $rec)
    {
      $this->nameToId[$rec[$fieldToMap]] = $k;
      $this->idToName[$rec['id']] = $k;
    }
  }

  /**
   * Return the original array that was passed to the constructor.
   * 
   * @return array 
   */
  public function getData()
  {
    return $this->data;
  }
  
  /**
   * Return the name (value) of the mapped field for the record with id = $id.
   * The 'name' is not really accurate here, but the method name has not been
   * changed to something more appropriate for legacy reasons.
   * 
   * @param integer $id             The record id
   * @param boolean $throwException To throw or not to throw
   * @param mixed $default          If $throwException is false then $default is
   *                                returned in case of error
   * @return string                 The value of the mapped field
   * @throws Exception 
   */
  public function getName($id, $throwException = true, $default = null)
  {
    $name = $default;
    
    if (!isset($this->idToName[$id]))
    {
      if ($throwException)
      {
        throw new Exception(__METHOD__.':'.__LINE__.'|'.
                $this->name.'|Invalid id '.$id);
      }
    }
    else
    {
      $name = $this->data[$this->idToName[$id]][$this->fieldToMap];
    }
    
    return $name;
  }

  /**
   * Check for the presents of a particular value (name) of the mapped field
   * among the records.
   * 
   * @param string $name The value of the mapped field to find
   * @return boolean     True if the value is found, fals otherwise
   */
  public function hasName($name)
  {
    return isset($this->nameToId[$name]);
  }

  /**
   * Return the id of the record that has a mapped field with the value $name.
   * 
   * @param string  $name           The value of the mapped field
   * @param boolean $throwException To throw or not to throw
   * @param mixed   $default        The default value to return in case of error
   *                                and if $throwException is false
   * @return integer                The id of the record that matches $name
   * @throws Exception
   */
  public function getId($name, $throwException = true, $default = null)
  {
    $id = $default;

    if (!isset($this->nameToId[$name]))
    {
      if ($throwException)
      {
        throw new Exception(__METHOD__.':'.__LINE__.'|'.
                $this->name.'|Invalid name '.$name);
      }
    }
    else
    {
      $id = $this->data[$this->nameToId[$name]]['id'];
    }

    return $id;
  }

  /**
   * Check for presents of a record with $id.
   * 
   * @param integer  $id The id to find
   * @return boolean     True if there is a record with id == $id, false
   *                     otherwise
   */
  public function hasId($id)
  {
    return isset($this->idToName[$id]);
  }

  /**
   * Return the entire record that matches $id.
   * 
   * @param integer $id             The id to look for
   * @param boolean $throwException To throw or not to throw
   * @param mixed   $default        Default value to return in case of error and
   *                                $throwException is false
   * @return mixed                  The record that matches the criteria or
   *                                $default in case of error and
   *                                $throwException is false
   * @throws Exception
   */
  public function getRecById($id, $throwException = true, $default = array())
  {
    $rec = $default;
    
    if (!isset($this->idToName[$id]))
    {
      if ($throwException)
      {
        throw new Exception(__METHOD__.':'.__LINE__.'|'.
                $this->name.'|Invalid id '.$id);
      }
    }
    else
    {
      $rec = $this->data[$this->idToName[$id]];
    }
    
    return $rec;
  }

  /**
   * Return the entire record that matches $name (value).
   * 
   * @param string  $name           The name/value to match
   * @param boolean $throwException To throw or not to throw
   * @param mixed   $default        Default value to return in case of error and
   *                                $throwException is false
   * @return mixed                  The record that matches the criteria or
   *                                $default in case of error and
   *                                $throwException is false
   * @throws Exception
   */
  public function getRecByName($name, $throwException = true, $default = array())
  {
    $rec = $default;
    
    if (!isset($this->nameToId[$name]))
    {
      if ($throwException)
      {
        throw new Exception(__METHOD__.':'.__LINE__.'|'.
                $this->name.'|Invalid name '.$name);
      }
    }
    else
    {
      $rec = $this->data[$this->nameToId[$name]];
    }
    
    return $rec;
  }

  /**
   * Ensure that the data conforms to the required structure for this mapper.
   *
   * @param array $data
   */
  protected function validata($data)
  {
    if (!is_array($data))
    {
      throw new Exception(__METHOD__.':'.__LINE__.
              '|'.$this->name.'|Cannot construct mapper, data not passed as array.');
    }

    if (count($data) == 0)
    {
      throw new Exception(__METHOD__.':'.__LINE__.
              '|'.$this->name.'|Cannot construct mapper, no data passed.');
    }

    if (!isset($data[0]['id']) || !isset($data[0][$this->fieldToMap]))
    {
      throw new Exception(__METHOD__.':'.__LINE__.
              '|'.$this->name.
              '|Data must be an associative array and must have fields id AND '.$this->fieldToMap);
    }
  }
}