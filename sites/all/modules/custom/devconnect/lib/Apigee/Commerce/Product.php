<?php

namespace Apigee\Commerce;

use \Apigee\Exceptions\ParameterException as ParameterException;
use \Apigee\Util\Log as Log;

class Product extends Base\BaseObject {

  /**
   * Product id
   * @var string
   */
  private $id;

  /**
   * Product Name
   * @var string
   */
  private $name;

  /**
   * Display Name
   * @var string
   */
  private $display_name;

  /**
   * Description
   * @var string
   */
  private $description;

  /**
   * Environment
   * @var string
   */
  private $environment;

  /**
   * Supports Refund
   * @var boolean
   */
  private $supports_refund;

  /**
   * Credit terms - in how many days payment is due (for postpaid)
   * @var int
   */
  private $payment_due_days;

  /**
   * Status
   * @var string
   */
  private $status;

  /**
   * Tax Category
   * @var string
   */
  private $tax_category;

  /**
   * Custom Payment Term
   * @var boolean
   */
  private $custom_payment_term;

  /**
   * Product price points
   * @var array Array of instances of \Apigee\Commerce\PricePoint
   */
  private $price_points = array();

  /**
   * The following directive break the apix create application.. commenting out
   * @var array Array of instances of \Apigee\Commerce\DataStructures\SuborgProduct
   */
  private $suborg_products = array();

  /**
   * Organization
   * @var \Apigee\Commerce\Organization
   */
  private $organization;

  /**
   * Transaction Success Criteria
   * @var string
   */
  private $transaction_success_criteria;

  /**
   * Refound Success Criteria
   * @var string
   */
  private $refund_success_criteria;

  /**
   * Transaction TTL
   * @var int
   */
  private $transaction_ttl;

  /**
   * Custom Attribute 1 Name
   * @var string
   */
  private $custom_att1_name;

  /**
   * Custom Attribute 2 Name
   * @var string
   */
  private $custom_att2_name;

  /**
   * Custom Attribute 3 Name
   * @var string
   */
  private $custom_att3_name;

  /**
   * Custom Attribute 4 Name
   * @var string
   */
  private $custom_att4_name;

  /**
   * Custom Attribute 5 Name
   * @var string
   */
  private $custom_att5_name;

  /**
   * Developer categories
   * @var array Array of \Apigee\Commerce\DeveloperCategory
   */
  private $developer_categories = array();

  /**
   * Developers defined as Brokers
   * @var array Array of \Apigee\Commerce\Developer
   */
  private $brokers = array();

  public function __construct(\Apigee\Util\APIClient $client) {

    $this->init($client);

    $this->base_url = '/commerce/organizations/' . rawurlencode($this->client->getOrg()) . '/products';
    $this->wrapper_tag = 'product';
    $this->id_field = 'id';
    $this->id_is_autogenerated = FALSE;

    $this->initValues();
  }

  public function instantiateNew() {
    return new Product($this->client);
  }

  public function loadFromRawData($data, $reset = FALSE) {
    if ($reset) {
      $this->initValues();
    }
    $excluded_properties = array('pricePoints', 'suborgProducts', 'organization', 'developerCategory', 'broker');
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

    if (isset($data['pricePoints']) && is_array($data['pricePoints']) && count($data['pricePoints']) > 0) {
      foreach ($data['pricePoints'] as $price_point_item) {
        $price_point = new PricePoint($this->id, $this->client);
        $price_point->loadFromRawData($price_point_item);
        $this->price_points[] = $price_point;
      }
    }

    if (isset($data['suborgProducts']) && is_array($data['suborgProducts']) && count($data['suborgProducts']) > 0) {
      foreach ($data['suborgProducts'] as $suborg_product_item) {
        $suborg_product = new SuborgProduct($this->id, $this->client);
        $suborg_product->loadFromRawData($suborg_product_item);
        $this->suborg_products[] = $suborg_product;
      }
    }

    if (isset($data['organization'])) {
      $organization = new Organization($this->client);
      $organization->loadFromRawData($data['organization']);
      $this->organization = $organization;
    }

    if (isset($data['developerCategory']) && is_array($data['developerCategory']) && count($data['developerCategory']) > 0) {
      foreach ($data['developerCategory'] as $cat_item) {
        $category = new DeveloperCategory($this->client);
        $category->loadFromRawData($cat_item);
        $this->developer_categories[] = $category;
      }
    }

    // TODO verify that brokers are Developers
    if (isset($data['broker']) && is_array($data['broker']) && count($data['broker']) > 0) {
      foreach ($data['broker'] as $broker_item) {
        $broker = new Developer($this->client);
        $broker->loadFromRawData($broker_item);
        $this->brokers[] = $broker;
      }
    }
  }

  protected function initValues() {
    $this->name = '';
    $this->display_name = '';
    $this->description = '';
    $this->environment = '';
    $this->supports_refund = FALSE;
    $this->payment_due_days = 0;
    $this->status = 'ACTIVE';
    $this->tax_category = NULL;
    $this->custom_payment_term = FALSE;
    $this->price_points = array();
    $this->suborg_products = array();
    $this->organization = NULL;
    $this->transaction_success_criteria = '';
    $this->refund_success_criteria = '';
    $this->transaction_ttl = 0;
    $this->custom_att1_name = '';
    $this->custom_att2_name = '';
    $this->custom_att3_name = '';
    $this->custom_att4_name = '';
    $this->custom_att5_name = '';
    $this->developer_categories = array();
    $this->brokers = array();

  }

  public function __toString() {
    // @TODO Verify
    $obj = array();
    $properties = array_keys(get_object_vars($this));
    $excluded_properties = array('org', 'developerCategory', 'broker');
    $excluded_properties = array_merge(array_keys(get_class_vars(get_parent_class($this))), $excluded_properties);
    foreach ($properties as $property) {
      if (in_array($property, $excluded_properties)) {
        continue;
      }
      if (isset($this->$property)) {
        if (is_object($this->$property)) {
          $obj[$property] = json_decode((string) $this->$property, TRUE);
        }
        else {
          $obj[$property] = $this->$property;
        }
      }
    }
    return json_encode($obj);
  }

  // getters/setters

  /**
   * Get Product id
   * @return string
   */
  public function getId() {
    return $this->id;
  }

  /**
   * Get Product Name
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Get Display Name
   * @return string
   */
  public function getDisplayName() {
    return $this->display_name;
  }

  /**
   * Get Description
   * @return string
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * Get Environment
   * @return string
   */
  public function getEnvironment() {
    return $this->environment;
  }

  /**
   * Get Supports Refund
   * @return boolean
   */
  public function getSupportsRefund() {
    return $this->supports_refund;
  }

  /**
   * Get Credit terms - in how many days payment is due (for postpaid)
   * @return int
   */
  public function getPaymentDueDays() {
    return $this->payment_due_days;
  }

  /**
   * Get Status
   * @return string
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * Get Tax Category
   * @return string
   */
  public function getTaxCategory() {
    return $this->tax_category;
  }

  /**
   * Get Custom Payment Term
   * @return boolean
   */
  public function getCustomPaymentTerm() {
    return $this->custom_payment_term;
  }

  /**
   * Get Product Price Points
   * @return array Array of instances of \Apigee\Commerce\PricePoint
   */
  public function getPricePoints() {
    return $this->price_points;
  }

  /**
   *
   * @return array Array of instances of \Apigee\Commerce\SuborgProduct
   */
  public function getSuborgProducts() {
    return $this->suborg_products;
  }

  /**
   * Get Organization
   * @return \Apigee\Commerce\Organization
   */
  public function getOrganization() {
    return $this->organization;
  }

  /**
   * Get Transaction Success Criteria
   * @return string
   */
  public function getTransactionSuccessCriteria() {
    return $this->transaction_success_criteria;
  }

  /**
   * Get Refound Success Criteria
   * @return string
   */
  public function getRefundSuccessCriteria() {
    return $this->refund_success_criteria;
  }

  /**
   * Get Transaction TTL
   * @return int
   */
  public function getTransactionTTL() {
    return $this->transaction_ttl;
  }

  /**
   * Get Custom Attribute 1 Name
   * @return string
   */
  public function getCustomAtt1Name() {
    return $this->custom_att1_name;
  }

  /**
   * Get Custom Attribute 2 Name
   * @return string
   */
  public function getCustomAtt2Name() {
    return $this->custom_att2_name;
  }

  /**
   * Get Custom Attribute 3 Name
   * @return string
   */
  public function getCustomAtt3Name() {
    return $this->custom_att3_name;
  }

  /**
   * Get Custom Attribute 4 Name
   * @return string
   */
  public function getCustomAtt4Name() {
    return $this->custom_att4_name;
  }

  /**
   * Get Custom Attribute 5 Name
   * @return string
   */
  public function getCustomAtt5Name() {
    return $this->custom_att5_name;
  }

  /**
   * Get Developer categories
   * @return array Array of \Apigee\Commerce\DeveloperCategory
   */
  public function getDeveloperCategories() {
    return $this->developer_categories;
  }

  /**
   * Get Developers defined as Brokers
   * @return array Array of \Apigee\Commerce\Developer
   */
  public function getBrokers() {
    return $this->brokers;
  }

  /**
   * Set Product id
   * @param string $id
   * @return void
   */
  public function setId($id) {
    $this->id = $id;
  }

  /**
   * Set Product Name
   * @param string $name
   * @return void
   */
  public function setName($name) {
    $this->name = $name;
  }

  /**
   * Set Display Name
   * @param string $display_name
   */
  public function setDisplayName($display_name) {
    $this->display_name = $display_name;
  }

  /**
   * Set Description
   * @param string $description
   * @return void
   */
  public function setDescription($description) {
    $this->description = $description;
  }

  /**
   * Set Environment
   * @param string $environment
   * @return void
   */
  public function setEnvironment($environment) {
    $this->environment = $environment;
  }

  /**
   * Set Supports Refund
   * @param boolean $supports_refund
   * @return void
   */
  public function setSupportsRefund($supports_refund) {
    $this->supports_refund = $supports_refund;
  }

  /**
   * Set Credit terms - in how many days payment is due (for postpaid)
   * @param int $payment_due_days
   * @return void
   */
  public function setPaymentDueDays($payment_due_days) {
    $this->payment_due_days = $payment_due_days;
  }

  /**
   * Set Status
   * @param string $status Possible values CREATED|INACTIVE|ACTIVE
   * @return void
   * @throws \Apigee\Exceptions\ParameterException
   */
  public function setStatus($status) {
    $status = strtoupper($status);
    if (!in_array($status, array('CREATED', 'INACTIVE', 'ACTIVE'))) {
      throw new ParameterException('Invalid product status value: ' . $status);
    }
    $this->status = $status;
  }

  /**
   * Set Tax Category
   * @param string $tax_category Possible values INFORMATION_SERVICES|ECOMMERCE
   * @return void
   * @throws \Apigee\Exceptions\ParameterException
   */
  public function setTaxCategory($tax_category) {
    $tax_category = strtoupper($tax_category);
    if (!in_array($tax_category, array('INFORMATION_SERVICES', 'ECOMMERCE'))) {
      throw new ParameterException('Invalid product tax category value: ' . $tax_category);
    }
    $this->tax_category = $tax_category;
  }

  /**
   * Set Custom Payment Term
   * @param boolean $custom_payment_term
   * @return void
   */
  public function setCustomPaymentTerm($custom_payment_term) {
    $this->custom_payment_term = $custom_payment_term;
  }

  /**
   * Add Product Price Point
   * @param \Apigee\Commerce\PricePoint $price_point
   * @return void
   */
  public function addPricePoints(PricePoint $price_point) {
    $this->price_points[] = $price_point;
  }

  /**
   * Remove all PricePoints from this Product
   * @return void
   */
  public function clearPricePoints() {
    $this->price_points = array();
  }

  /**
   * Add SuborgProduct
   * @param \Apigee\Commerce\SuborgProduct $suborg_product
   * @return void
   */
  public function addSuborgProduct(SuborgProduct $suborg_product) {
    $this->suborg_products[] = $suborg_product;
  }

  /**
   * Remove all SuborgProducts from this product
   * @return void
   */
  public function clearSuborgProducts() {
    $this->suborg_products = array();
  }

  /**
   * Set Organization
   * @param \Apigee\Commerce\Organization $organization
   * @return void
   */
  public function setOrganization(Organization $organization) {
    $this->organization = $organization;
  }

  /**
   * Set Transaction Success Criteria
   * @param string $transaction_success_criteria
   * @return void
   */
  public function setTransactionSuccessCriteria($transaction_success_criteria) {
    $this->transaction_success_criteria = $transaction_success_criteria;
  }

  /**
   * Set Refound Success Criteria
   * @param string $refund_success_criteria
   * @return void
   */
  public function setRefundSuccessCriteria($refund_success_criteria) {
    $this->refund_success_criteria = $refund_success_criteria;
  }

  /**
   * Set Transaction TTL
   * @param int $transaction_ttl
   * @return void
   */
  public function setTransactionTTL($transaction_ttl) {
    $this->transaction_ttl = $transaction_ttl;
  }

  /**
   * Set Custom Attribute 1 Name
   * @param string $custom_att1_name
   * @return void
   */
  public function setCustomAtt1Name($custom_att1_name) {
    $this->custom_att1_name = $custom_att1_name;
  }

  /**
   * Set Custom Attribute 2 Name
   * @param string $custom_att2_name
   * @return void
   */
  public function setCustomAtt2Name($custom_att2_name) {
    $this->custom_att2_name = $custom_att2_name;
  }

  /**
   * Set Custom Attribute 3 Name
   * @param string $custom_att3_name
   * @return void
   */
  public function setCustomAtt3Name($custom_att3_name) {
    $this->custom_att3_name = $custom_att3_name;
  }

  /**
   * Set Custom Attribute 4 Name
   * @param string $custom_att4_name
   * @return void
   */
  public function setCustomAtt4Name($custom_att4_name) {
    $this->custom_att4_name = $custom_att4_name;
  }

  /**
   * Set Custom Attribute 5 Name
   * @param string $custom_att5_name
   * @return void
   */
  public function setCustomAtt5Name($custom_att5_name) {
    $this->custom_att5_name = $custom_att5_name;
  }

  /**
   * Add DeveloperCategory
   * @param \Apigee\Commerce\DeveloperCategory
   * @return void
   */
  public function addDeveloperCategory($developer_category) {
    $this->developer_categories[] = $developer_category;
  }

  /**
   * Remove all DeveloperCategory objects from this product
   * @return void
   */
  public function clearDeveloperCategories() {
    $this->developer_categories = array();
  }

  /**
   * Add Developers defined as Brokers
   * @param \Apigee\Commerce\Developer $broker
   * @return void
   */
  public function addBroker($broker) {
    $this->brokers[] = $broker;
  }

  /**
   * Remove all Brokers from this product
   * @return void
   */
  public function clearBrokers() {
    $this->brokers = array();
  }
}