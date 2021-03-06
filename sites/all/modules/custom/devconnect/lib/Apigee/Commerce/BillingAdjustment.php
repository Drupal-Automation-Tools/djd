<?php
namespace Apigee\Commerce;

use \Apigee\Util\Log as Log;

/* TODO: Flesh this whole class out once the docs are finished or we have some sample data */

class BillingAdjustment extends Base\BaseObject {

  private $id;

  public function __construct(\Apigee\Util\APIClient $client) {
    $this->init($client);

    $this->base_url = '/commerce/organizations/' . rawurlencode($client->getOrg()) . '/billing-adjustments';
    $this->wrapper_tag = 'billingAdjustment';
    // TODO: verify the following two items when docs are fleshed out
    $this->id_field = 'id';
    $this->id_is_autogenerated = TRUE;

    $this->initValues();
  }

  protected function initValues() {
    // TODO
  }

  public function instantiateNew() {
    return new BillingAdjustment($this->client);
  }

  public function loadFromRawData($data, $reset = FALSE) {
    if ($reset) {
      $this->initValues();
    }

    // @TODO Complete the properties to be skipped
    $excluded_properties = array();
    foreach (array_keys($data) as $property) {
      if (in_array($property, $excluded_properties)) {
        continue;
      }

      // form the setter method name to invoke setXxxx
      $setter_method = 'set' . ucfirst($property);

      if (method_exists($this, $setter_method)) {
        $this->$setter_method($data[$property]);
      }
      else {
        Log::write(__CLASS__, Log::LOGLEVEL_NOTICE, 'No setter method was found for property "' . $property . '"');
      }
    }
  }

  public function __toString() {
    $obj = array();
    $properties = array_keys(get_object_vars($this));
    $excluded_properties = array_keys(get_class_vars(get_parent_class($this)));
    foreach ($properties as $property) {
      if (in_array($property, $excluded_properties)) {
        continue;
      }
      if (isset($this->$property)) {
        $obj[$property] = $this->$property;
      }
    }
    return json_encode($obj);
  }

  public function getId() {
    return $this->id;
  }
  // Used in data load invoked by $this->loadFromRawData()
  private function setId($id) {
    $this->id = $id;
  }
}