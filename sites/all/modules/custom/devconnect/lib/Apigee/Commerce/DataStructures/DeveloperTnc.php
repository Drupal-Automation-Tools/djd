<?php

namespace Apigee\Commerce\DataStructures;

use Apigee\Commerce\Types\DeveloperTncsActionType;

class DeveloperTnc extends DataStructure {

  private $action;

  private $audit_date;

  private $id;

  private $tnc;

  public function __construct($data = NULL) {
    if (is_array($data)) {
      $excluded_properties = array('tnc');
      $this->loadFromRawData($data, $excluded_properties);
    }
  }

  public function getAction() {
    return $this->action;
  }
  public function setAction($action) {
    $this->action = DeveloperTncsActionType::get($action);
  }

  public function getAuditDate() {
    return $this->audit_date;
  }
  public function setAuditDate($audit_date) {
    $this->audit_date = $audit_date;
  }

  public function getId() {
    return $this->id;
  }
  public function setId($id) {
    $this->id = $id;
  }

  public function getTnc() {
    return $this->tnc;
  }
  public function setTnc($tnc) {
    $this->tnc = $tnc;
  }

  public function __toString() {
    $object = array(
      'action' => $this->action,
      'auditDate' => $this->audit_date,
      'id' => $this->id,
    );
    return json_encode($object, JSON_FORCE_OBJECT);
  }
}