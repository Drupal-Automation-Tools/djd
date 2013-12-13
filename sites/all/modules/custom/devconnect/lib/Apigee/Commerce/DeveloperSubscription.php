<?php
namespace Apigee\Commerce;

use \Apigee\Util\Log as Log;

// TODO: finish this class when the docs are finished

class DeveloperSubscription extends Base\BaseObject {

  private $developer_id;
  private $product_id;
  private $term;

  /**
   * Organization
   * @var \Apigee\Commerce\Organization
   */
  private $organization;

  /**
   * @var \Apigee\Commerce\Product
   */
  private $products = array();

  /**
   * com.apigee.commerce.model.Developer
   * @var \Apigee\Commerce\Developer
   */
  private $developer;

  private $id;

  public function __construct($developer_id, $product_id, $term, \Apigee\Util\APIClient $client) {
    $this->init($client);

    $this->developer_id = $developer_id;
    $this->product_id = $product_id;
    $this->term = $term;

    $this->base_url = '/commerce/organizations/' . rawurlencode($client->getOrg()) . '/developers/' . rawurlencode($developer_id) . '/products/' . rawurlencode($product_id) . '/TERM/' . rawurlencode($term) . '/subscriptions';
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
    return new DeveloperSubscription($this->developer_id, $this->product_id, $this->term, $this->client);
  }

  public function loadFromRawData($data, $reset = FALSE) {
    if ($reset) {
      $this->initValues();
    }
    $excluded_properties = array('product', 'developer', 'organization');
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

    if (isset($data['organization'])) {
      $organization = new Organization($this->client);
      $organization->loadFromRawData($data['organization']);
      $this->organization = $organization;
    }

    if (isset($data['product'])) {
      $product = new Product($this->client);
      $product->loadFromRawData($data['product']);
      $this->products[] = $product;
    }

    if (isset($data['developer'])) {
      $developer = new Developer($this->client);
      $developer->loadFromRawData($data['developer']);
      $this->developer= $developer;
    }
  }

  public function __toString() {
    $obj = array();
    $properties = array_keys(get_object_vars($this));
    $excluded_properties = array_keys(get_class_vars(get_parent_class($this)));
    $excluded_properties += array(
      'developer_id',
      'product_id',
      'term'
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