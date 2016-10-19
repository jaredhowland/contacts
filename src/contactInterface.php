<?php
/**
  * Create a vCard
  *
  * @author Jared Howland <contacts@jaredhowland.com>
  * @version 2016-10-19
  * @since 2016-10-05
  *
  */

require_once 'config.php';

interface contactInterface {
  public function add_full_name($name);
  public function add_name($last_name, $first_name, $additional_name, $prefix, $suffix);
  public function add_nickname($name);
  public function add_photo($photo);
  public function add_birthday($year, $month, $day);
  public function add_address($po_box, $extended, $street, $city, $state, $zip, $country, $type);
  public function add_label($label, $type);
  public function add_telephone($phone, $type);
  public function add_email($email, $type);
  public function add_mailer($mailer);
  public function add_time_zone($time_zone);
  public function add_lat_long($lat, $long);
  public function add_title($title);
  public function add_role($role);
  public function add_logo($logo);
  public function add_agent($agent);
  public function add_organization($organization);
  public function add_categories($categories);
  public function add_note($note);
  public function add_product_id($product_id);
  public function add_revision();
  public function add_sort_string($sort_string);
  public function add_sound($sound);
  public function add_unique_identifier($unique_identifier);
  public function add_url($url);
  public function add_classification($classification);
  public function add_key($key);
  public function add_extended_type($label, $value);
}

?>
