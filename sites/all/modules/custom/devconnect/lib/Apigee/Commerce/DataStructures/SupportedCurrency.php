<?php

namespace Apigee\Commerce\DataStructures;

class SupportedCurrency {

  /**
   * Organization
   * @var \Apigee\Commerce\Organization
   */
  public $organization;

  /**
   * Currency id
   * @var string
   */
  public $id;

  /**
   * Supported Currency Name
   * @var string
   */
  public $name;

  /**
   * Display Name
   * @var string
   */
  public $displayName;

  /**
   * Description
   * @var string
   */
  public $description;

  /**
   * Virtual currency
   * @var boolean
   */
  public $virtualCurrency;

  /**
   * Status.
   * @var string Allowed values [CREATED|INACTIVE|ACTIVE]
   */
  public $status;

  /**
   * Credit Limit for Postpaid developers. This can be overridden for each developer in developer balance.
   * @var double
   */
  public $creditLimit;

  /**
   * Constructor
   * @param array $data
   */
  public function __construct($data = NULL) {
    if (is_array($data)) {
      foreach (array_keys(get_object_vars($this)) as $var) {
        if (isset($data[$var])) {
          $this->$var = $data[$var];
        }
      }
    }
  }

  public function __toString() {
    return json_encode($this);
  }

}