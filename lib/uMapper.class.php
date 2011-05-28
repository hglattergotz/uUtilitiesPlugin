<?php
/**
 * Helper class that allows in-memory maps of small sets of database records to
 * be searched quickly without having to hit the database over and over.
 * This particular mapper will only work for records that contain an id and a
 * name field and only allows lookups either by the id or the name field.
 *
 * @package     uSettingsDoctrinePlugin
 * @subpackage  uMapper
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uMapper
{
  protected $name;
  protected $data;
  protected $nameToId;
  protected $idToName;

  public function __construct($name, $data)
  {
    $this->name = $name;
    $this->validata($data);
    $this->data = $data;
    $this->nameToId = array();
    $this->idToName = array();

    foreach ($data as $k => $rec)
    {
      $this->nameToId[$rec['name']] = $k;
      $this->idToName[$rec['id']] = $k;
    }
  }

  public function getData(){
    return $this->data;
  }
  
  public function getName($id)
  {
    if (!isset($this->idToName[$id]))
    {
      throw new Exception(__METHOD__.':'.__LINE__.'|'.
              $this->name.'|Invalid id '.$id);
    }

    return $this->data[$this->idToName[$id]]['name'];
  }

  public function hasName($name)
  {
    return isset($this->nameToId[$name]);
  }

  public function getId($name)
  {
    if (!isset($this->nameToId[$name]))
    {
      throw new Exception(__METHOD__.':'.__LINE__.'|'.
              $this->name.'|Invalid name '.$name);
    }

    return $this->data[$this->nameToId[$name]]['id'];
  }

  public function hasId($id)
  {
    return isset($this->idToName[$id]);
  }

  public function getRecById($id)
  {
    if (!isset($this->idToName[$id]))
    {
      throw new Exception(__METHOD__.':'.__LINE__.'|'.
              $this->name.'|Invalid id '.$id);
    }

    return $this->data[$this->idToName[$id]];
  }

  public function getRecByName($name)
  {
    if (!isset($this->nameToId[$name]))
    {
      throw new Exception(__METHOD__.':'.__LINE__.'|'.
              $this->name.'|Invalid name '.$name);
    }

    return $this->data[$this->nameToId[$name]];
  }

  /**
   * Ensure that the data conforms to the required structure for this mapper.
   *
   * @param <type> $data
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

    if (!isset($data[0]['id']) || !isset($data[0]['name']))
    {
      throw new Exception(__METHOD__.':'.__LINE__.
              '|'.$this->name.
              '|Data must be an associative array and must have fields id AND name');
    }
  }
}