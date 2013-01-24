<?php

include __DIR__.'/../../../../../test/bootstrap/unit.php';

$t = new lime_test(3, new lime_output_color());

$optionsConfig = array(
  new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'etsync'),
  new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
  new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'exacttarget'),
  new sfCommandOption('mode', null, sfCommandOption::PARAMETER_REQUIRED, 'The mode in which to operate'),
  new sfCommandOption('install', null, sfCommandOption::PARAMETER_NONE, 'Install the cron job'),
  new sfCommandOption('cronpath', null, sfCommandOption::PARAMETER_REQUIRED, 'The cron path', '/etc/cron.d'),
  new sfCommandOption('crontime', null, sfCommandOption::PARAMETER_REQUIRED, 'The cron time', '*/5 * * * *'),
);

$options = array(
  'application' => 'app',
  'env' => 'dev',
  'connection' => 'doctrine',
  'mode' => 'incremental',
  'install' => true,
  'cronpath' => '/etc/cron.d',
  'crontime' => '*/5 * * * *'
);

$expected = ' --application=app --env=dev --connection=doctrine --mode=incremental --install --cronpath=/etc/cron.d --crontime=*/5 * * * *';
$t->is(optionsHelper::makeOptionsString($options, $optionsConfig), $expected);

$options = array(
  'application' => 'app',
  'env' => 'dev',
  'connection' => 'doctrine',
  'mode' => 'incremental',
  'install' => false,
  'cronpath' => '/etc/cron.d',
  'crontime' => '*/5 * * * *'
);

$expected = ' --application=app --env=dev --connection=doctrine --mode=incremental --cronpath=/etc/cron.d --crontime=*/5 * * * *';
$t->is(optionsHelper::makeOptionsString($options, $optionsConfig), $expected);

$options = array(
  'application' => 'app',
  'env' => 'dev',
  'connection' => 'doctrine',
  'mode' => null,
  'install' => false,
  'cronpath' => '/etc/cron.d',
  'crontime' => '*/5 * * * *'
);

$expected = ' --application=app --env=dev --connection=doctrine --cronpath=/etc/cron.d --crontime=*/5 * * * *';
$t->is(optionsHelper::makeOptionsString($options, $optionsConfig), $expected);
