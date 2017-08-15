<?php

return array(
  'driver' => 'smtp',
  'host' => 'smtp.sendgrid.net',
  'port' => 587,
  'from' => array('address' => 'hello@dentacoin.com', 'name' => 'DentaCoin'),
  'encryption' => 'tls',
  'username' => env('SENDGRID_USERNAME'),
  'password' => env('SENDGRID_PASSWORD'),
);