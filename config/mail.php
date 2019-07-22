<?php

return array(
  'driver' => 'smtp',
  'host' => 'smtp.sendgrid.net',
  'port' => 587,
  'from' => array(
  	'address' => 'reviews@dentacoin.com', 
  	'name' => 'Dentacoin Trusted Reviews',
    'address-vox' => 'dentavox@dentacoin.com', 
    'name-vox' => 'DentaVox Market Research Platform',
    'address-dentacoin' => 'admin@dentacoin.com', 
    'name-dentacoin' => 'Dentacoin Team',
  ),
  'encryption' => 'tls',
  'username' => env('SENDGRID_USERNAME'),
  'password' => env('SENDGRID_PASSWORD'),
);