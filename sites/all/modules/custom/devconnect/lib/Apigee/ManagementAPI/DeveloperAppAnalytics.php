<?php
/**
 * @file
 * Exposes Developer App Analytics data from the Management API.
 *
 * @author djohnson
 */

namespace Apigee\ManagementAPI;

use Apigee\Exceptions\ParameterException;
use Apigee\Exceptions\EnvironmentException;
use Apigee\Util\Cache;
use Apigee\Util\APIClient;

class DeveloperAppAnalytics extends Base implements DeveloperAppAnalyticsInterface {
  /**
   * @var string
   */
  protected $environment;

  protected $baseUrl;

  /**
   * Initializes the environment and sets up the APIClient.
   * @param \Apigee\Util\APIClient $client
   * @param string $env
   */
  public function __construct(APIClient $client, $env = '*') {
    $this->init($client);
    $this->setEnvironment($env);
  }

  /**
   * Validates and sets the environment. The asterisk wildcard is allowed.
   *
   * @param string $env
   * @throws \Apigee\Exceptions\EnvironmentException
   */
  public function setEnvironment($env) {
    if ($env != '*') {
      $environments = $this->getAllEnvironments();
      if (!in_array($env, $environments)) {
        throw new EnvironmentException('Invalid environment ' . $env . '.');
      }
    }
    $this->environment = $env;
    $this->baseUrl = '/organizations/' . $this->urlEncode($this->getClient()->getOrg()) . '/environments/' . $this->urlEncode($env) . '/stats/';
  }

  /**
   * Gets the current environment.
   *
   * @return string
   */
  public function getEnvironment() {
    return $this->environment;
  }

  /**
   * Returns a list of all environments for this org.
   *
   * @return array
   */
  public function getAllEnvironments() {
    $env = Cache::get('devconnect_org_environments', array());
    if (empty($env)) {
      $env = $this->queryEnvironments();
      Cache::set('devconnect_org_environments', $env);
    }
    return $env;
  }

  /**
   * After ensuring params are valid, fetches analytics data.
   *
   * @param string $developer_id
   * @param string $app_name
   * @param string $metric
   * @param string $time_start
   * @param string $time_end
   * @param string $time_unit
   * @param string $sort_by
   * @param string $sort_order
   * @return array
   */
  public function getByAppName($developer_id, $app_name, $metric, $time_start, $time_end, $time_unit, $sort_by, $sort_order = 'ASC') {
    $params = self::validateParameters($metric, $time_start, $time_end, $time_unit, $sort_by, $sort_order);

    $filter = "(developer_app eq '$app_name'";
    if (!empty($developer_id)) {
      $org = $this->client->getOrg();
      $filter .= " and developer eq '$org@@@$developer_id'";
    }
    $filter .= ')';

    $params['filter'] = $filter;

    $url = $this->baseUrl . 'apps?';
    $first = TRUE;
    foreach ($params as $name => $val) {
      if ($first) {
        $first = FALSE;
      }
      else {
        $url .= '&';
      }
      $url .= $name . '=' . urlencode($val);
    }
    $this->client->get($url);
    $response = $this->getResponse();
    $response = $response['Response'];

    $datapoints = array();
    $timestamps = array();
    foreach ($response['TimeUnit'] as $timestamp) {
      $timestamps[] = floor($timestamp / 1000);
    }
    if (array_key_exists('stats', $response) && array_key_exists('data', $response['stats'])) {
      foreach ($response['stats']['data'] as $response_item) {
        $item_caption = '';
        foreach ($response_item['identifier']['names'] as $key => $value) {
          if ($value == 'developer_app') {
            $item_caption = $response_item['identifier']['values'][$key];
            break;
          }
        }
        foreach ($response_item['metric'] as $array) {
          $env = $array['env'];
          $i = 0;
          foreach ($array['values'] as $metric_value) {
            $datapoints[$item_caption][$env][$timestamps[$i++]] = $metric_value;
          }
        }
      }
    }
    return $datapoints;
  }

  /**
   * Queries Management API to get a list of all environments configured for the org.
   *
   * @return array
   */
  public function queryEnvironments() {
    $org = $this->getClient()->getOrg();
    $env_url = '/organizations/' . $this->urlEncode($org) . '/environments';
    $this->client->get($env_url);
    return $this->getResponse();
  }

  /**
   * Lists all metrics valid for Developer Apps.
   *
   * @static
   * @return array
   */
  public static function getMetrics() {
    return array(
      'message_count' => t('Message Count'),
      'message_count-first24hrs' => t('Message Count - First 24 Hours'),
      'message_count-second24hrs' => t('Message Count - Second 24 Hours'),
      'error_count' => t('Error Count'),
      'error_count-first24hrs' => t('Error Count - First 24 Hours'),
      'user_count' => t('User Count'),
      'user_count-first24hrs' => t('User Count - First 24 Hours'),
      'total_response_time' => t('Total Response Time'),
      'max_response_time' => t('Maximum Response Time'),
      'min_response_time' => t('Minimum Response Time'),
      'end_point_response_time' => t('Endpoint Response Time')
    );
  }

  /**
   * Returns a keyed array of allowable time units. Array keys are machine names
   * and values are human-readable names.
   *
   * @return array
   */
  public static function getTimeUnits() {
    return array(
      'second' => t('Second'),
      'minute' => t('Minute'),
      'hour' => t('Hour'),
      'day' => t('Day'),
      'week' => t('Week'),
      'month' => t('Month'),
      'quarter' => t('Quarter'),
      'year' => t('Year'),
      // The rest of these are just silly.
      /*
      'decade' => t('Decade'),
      'century' => t('Century'),
      'millennium' => t('Millenium')
      */
    );
  }

  /**
   * Validates common parameters for analytics API calls, and bundles them
   * into an appropriately-structured array to be passed into an HTTP call.
   *
   * @static
   * @param string $metric
   * @param string $time_start
   * @param string $time_end
   * @param string $time_unit
   * @param string $sort_by
   * @param string $sort_order
   * @return array
   * @throws ParameterException
   */
  protected static function validateParameters($metric, $time_start, $time_end, $time_unit, $sort_by, $sort_order) {
    $metric_items = preg_split('!\s*,\s*!', $metric);
    if (count($metric_items) == 0) {
      throw new ParameterException('Missing metric.');
    }
    $valid_metrics = array_keys(self::getMetrics());
    foreach ($metric_items as $metric_item) {
      if (!in_array($metric_item, $valid_metrics)) {
        throw new ParameterException('Invalid metric.');
      }
    }
    $sort_by_items = preg_split('!\s*,\s*!', $sort_by);
    if (count($sort_by_items) == 0) {
      throw new ParameterException('Missing sort-by metric');
    }
    foreach ($sort_by_items as $sort_by_item) {
      if (!in_array($sort_by_item, $valid_metrics)) {
        throw new ParameterException('Invalid sort-by metric.');
      }
    }
    if (!in_array($time_unit, array_keys(self::getTimeUnits()))) {
      throw new ParameterException('Invalid time unit.');
    }
    $sort_order = strtoupper($sort_order);
    if ($sort_order != 'ASC' && $sort_order != 'DESC') {
      throw new ParameterException('Invalid sort order.');
    }
    $time_range = self::parseTime($time_start) . '~' . self::parseTime($time_end);
    $payload = array(
      'timeRange' => $time_range,
      'timeUnit' => $time_unit,
      'sortby' => join(',', $sort_by_items),
      'sort' => $sort_order,
      '_optimized' => 'js',
      'select' => join(',', $metric_items),
    );

    return $payload;
  }

  /**
   * Parses an incoming time string or Unix timestamp into an internally-acceptable time format.
   *
   * If the time input cannot be parsed, an exception is thrown.
   *
   * @static
   * @param string|int $time
   * @return string
   * @throws ParameterException
   */
  protected static function parseTime($time) {
    $int_time = FALSE;
    if (is_int($time)) {
      $int_time = $time;
    }
    if (preg_match('!^([0-9]{1,2})/([0-9]{1,2})/([0-9]{4}) ([0-9]{2}):([0-9]{2})$!', $time, $matches)) {
      list (, $m, $d, $y, $h, $i) = $matches;
      if ($m >= 0 && $m <= 12 && $d >= 1 && $d <= 31 && $h >= 0 && $h <= 12 && $i >= 0 && $i <= 59) {
        $int_time = mktime($h, $i, 0, $m, $d, $y);
      }
    }
    if ($int_time === FALSE) {
      $int_time = @strtotime($time);
    }
    if ($int_time === FALSE) {
      throw new ParameterException('Invalid time format.');
    }
    return date('m/d/Y H:i', $int_time);
  }
}
