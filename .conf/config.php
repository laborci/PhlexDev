<?php return array (
  'app' => 'PhlexDev',
  'dev-mode' => true,
  'memcache' => false,
  'domain' => 'phlex6.dev',
  'domain-aliases' => 
  array (
    0 => '*.phlex6.dev',
  ),
  'root' => '/htdocs/PhlexDev/',
  'databases' => 
  array (
    'app' => 
    array (
      'user' => 'db',
      'password' => 'root:awd',
      'server' => 'localhost',
      'database' => 'PhlexDev',
    ),
  ),
  'kraft' => 
  array (
    'template' => '/htdocs/PhlexDev/.templates/',
    'ext' => 'phtml',
    'double-start-suffix' => '-begin',
    'double-end-suffix' => '-end',
    'template-source' => '/htdocs/PhlexDev/application/Template/',
  ),
);