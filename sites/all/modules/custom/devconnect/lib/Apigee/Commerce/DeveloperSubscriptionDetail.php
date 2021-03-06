<?php
namespace Apigee\Commerce;

// TODO: finish this class when the docs are finished

class DeveloperSubscriptionDetail extends Base\BaseObject {

  private $developer_id;
  private $product_id;

  private $id;

  public function __construct($developer_id, $product_id, \Apigee\Util\APIClient $client) {
    $this->init($client);

    $this->developer_id = $developer_id;
    $this->product_id = $product_id;

    $this->base_url = '/commerce/organizations/' . rawurlencode($client->getOrg()) . '/developers/' . rawurlencode($developer_id) . '/products/' . rawurlencode($product_id) . '/subscriptions_details';
    $this->wrapper_tag = 'developerSubscriptionDetail';
    // TODO: verify the following two items when docs are fleshed out
    $this->id_field = 'id';
    $this->id_is_autogenerated = TRUE;

    $this->initValues();
  }

  protected function initValues() {
    // TODO
    $this->id = NULL;
  }

  public function instantiateNew() {
    return new DeveloperSubscriptionDetail($this->developer_id, $this->product_id, $this->client);
  }

  public function loadFromRawData($data, $reset = FALSE) {
    if ($reset) {
      $this->initValues();
    }
    $properties = array_keys(get_object_vars($this));
    $excluded_properties = array_keys(get_class_vars(get_parent_class($this)));
    $excluded_properties += array(
      'developer_id',
      'product_id',
    );
    foreach ($properties as $property) {
      if (in_array($property, $excluded_properties)) {
        continue;
      }
      if (isset($data[$property])) {
        $this->$property = $data[$property];
      }
    }
  }

  public function __toString() {
    $obj = array();
    $properties = array_keys(get_object_vars($this));
    $excluded_properties = array_keys(get_class_vars(get_parent_class($this)));
    $excluded_properties += array(
      'developer_id',
      'product_id',
    );
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