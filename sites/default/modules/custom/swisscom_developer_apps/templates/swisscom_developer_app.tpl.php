<?php
/**
 * @file
 * Displays detail for a single app.
 *
 * Available vars:
 *   $account - stdClass: user owning this app.
 *   $access_type - string: read, write, read/write, none
 *   $callback_url - string
 *   $name - string
 *   $status - string (App status)
 *   $app_attributes - associative array: display-name => value.
 *   $credentials - array of associative arrays with the following keys:
 *     apiproducts - array of strings
 *     consumer_key
 *     consumer_secret
 *     status (Credential status)
 *   $analytics_chart - boolean|string
 *
 * Each $credentials['apiproducts'] is an associative array with the following
 * keys:
 *   display_name
 *   description
 *   status (API Product status)
 */

// IMPORTANT: latest credential is last in array!
$credential = (isset($credentials) ? end($credentials) : NULL);
$show_analytics = ($analytics_chart !== FALSE);
$app_id = arg(3);

$download_base = 'user/' . arg(1) . '/app-performance/' . arg(3);
$query = drupal_static('devconnect_developer_apps_analytics_performance');
$uid_arg = (module_exists('me') && $GLOBALS['user']->uid == $account->uid ? 'me' : $account->uid);
?>

<ul class="nav nav-tabs">
  <li class="active"><a data-toggle="tab" href="#keys">Keys</a></li>
  <li><a data-toggle="tab" href="#products">APIs</a></li>
  <li><a data-toggle="tab" href="#details">API Details</a></li>
  <li><a href="<?php print base_path(); ?>user/<?php print $uid_arg; ?>/apps/<?php print $app_id; ?>/edit-app">Edit</a></li>
  <?php if ($show_analytics): ?>
    <li><a data-toggle="tab" href="#performance">API Performance</a></li>
  <?php endif; ?>
</ul>

<div class="tab-content app-details">
  <div class="tab-pane active" id="keys">
    <h3>Keys</h3>

    <p>The Client ID is used to allow you access Swisscom APIs, and is required in the header for most calls.  The
    Client ID Secret is only needed for some APIs, see the <a href="/docs/api">API Documentation</a> and
      <a href="/docs/api/getting-started">Getting Started Guide</a> for more information.</p>
    <?php if (isset($credential)): ?>
      <div class="key">
        <span class="key-label">Client ID:</span>
        <?php print check_plain($credential['consumer_key']); ?>
      </div>
      <div class="key">
        <span class="key-label">Client ID Secret:</span>

        <?php print check_plain($credential['consumer_secret']); ?>
      </div>
    <?php endif; ?>
    <?php if (!empty($callback_url)): // certain client implementations may make callback_url optional ?>
      <div class="key">
        <span class="key-label">Callback URL:</span>
        <?php print check_plain($callback_url); ?>
      </div>
    <?php endif; ?>
    <?php foreach ($app_attributes as $label => $value): ?>
      <div class="key">
        <span class="key-label"><?php print check_plain($label); ?>:</span>
        <?php print check_plain($value); ?>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="tab-pane" id="products">
    <?php
    if (isset($credential)) {
      foreach ($credential['apiproducts'] as $apiproduct) {
        print '<div class="app-content"><div class="app-info-wrapper">';
        print '<h4 class="app-product-title">' . check_plain($apiproduct['display_name']) . '</h4>';
        if (strlen($apiproduct['description']) > 0) {
          print '<div class="app-desc">' . check_plain($apiproduct['description']) . '</div>';
        }
        print '</div>';
        if (!empty($apiproduct['status'])) {
          print '<div class="apiproduct-status">Approval Status: ' . check_plain(ucfirst($apiproduct['status'])) . '</div>';
        }
        print '</div><br>';
      }
    }
    ?>
  </div><?php // end tab-pane products ?>
  <div class="tab-pane" id="details">
    <h3>API Key Details</h3>

    <div class="well">
      <?php print '<div class="control-group app-name"><strong>API key name:</strong><div>' . check_plain($name) . '</div></div>'; ?>
      <?php print '<div class="control-group callback-url"><strong>Callback URL:</strong><div>' . check_plain($callback_url) . '</div></div>'; ?>
      <?php
      if (isset($credential['apiproducts'][0])) {
        print '<div class="control-group api-products"><strong>APIs:</strong>';
        if (isset($credential)) {
          foreach ($credential['apiproducts'] as $apiproduct) {
            print '<div class="api-product">' . check_plain($apiproduct['display_name']);
            if (strlen($apiproduct['description']) > 0) {
              print '&nbsp;-&nbsp;' . check_plain($apiproduct['description']);
            }
            print '</div>';
          }
        }
        print '</div>'; // end control-group api-products
      }
      print '<div class="control-group app-status"><strong>Status:</strong><div>' . check_plain($credential['status']) . '</div></div>';
      ?>
    </div><?php // end well ?>
  </div><?php // end tab-pane details ?>
  <?php if ($show_analytics): ?>
    <div class="tab-pane" id="performance">
      <div class="span24 well">
        <?php print drupal_render(drupal_get_form('devconnect_developer_apps_analytics_form')); ?>
      </div>
      <?php if (empty($analytics_chart)): ?>
        <p>
          No performance data is available for the criteria you supplied.
        </p>
      <?php else: ?>
        <div class="btn-group pull-right">
          <a class="export btn dropdown-toggle" data-toggle="dropdown" href="#">Export<span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="<?php print url($download_base . '/xml', array('query' => $query)); ?>">XML</a></li>
            <li><a href="<?php print url($download_base . '/csv', array('query' => $query)); ?>">CSV</a></li>
          </ul>
        </div>
        <div style="clear: both; margin-top:20px">
          <?php print $analytics_chart; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
