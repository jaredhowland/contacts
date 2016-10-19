<?php
require_once 'config.php';

$directory = new vcard;
$directory->add_full_name('Jane Doe');
$directory->add_name('Doe', 'Jane');
$directory->add_nickname('Janie');
$directory->add_photo('http://images.designntrend.com/data/images/full/77769/jane-doe.jpg?w=780');
$directory->add_birthday(null, 2, 10);
$directory->add_address(null, null, '123 Main St', 'Provo', 'UT', '84602', 'United States', 'dom,postal,parcel,work');
$directory->add_address(null, null, '123 Main St', 'Provo', 'UT', '84602', 'United States', 'dom,postal,parcel,home');
$directory->add_label('Jane Doe\n123 Main St\nProvo, UT 84602', 'dom,parcel');
$directory->add_telephone('555-555-5555', 'cell,iphone');
$directory->add_email('jane_doe@domain.com');
$directory->add_time_zone('-7');
$directory->add_lat_long(40.3333331, -111.7777775);
$directory->add_title('System Administrator');
$directory->add_role('Programmer');
// $directory->add_agent($agent);
$directory->add_organization('Awesome Company');
$directory->add_categories('School,Work');
$directory->add_note('Not much is known about Jane Doe.');
$directory->add_sort_string('Doe');
// $directory->add_sound($sound);
$directory->add_url('http://www.example.com');
// $directory->add_key($key);
$directory->add_anniversary('2010-10-10');
$directory->add_supervisor('Jane Smith');
$directory->add_spouse('John Doe');
$directory->add_child('Jeff Doe');
$directory->add_child('Lisa Doe');

$directory->build_vcard(true);

?>
