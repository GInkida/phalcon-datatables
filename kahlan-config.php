<?php
use Kahlan\Filter\Filter;
use Kahlan\Reporter\Coverage\Exporter\Coveralls;

$args = $this->commandLine();
$args->option('coverage', 'default', 3);

Filter::register('phalcon.namespace', function($chain) {
  $this->_autoloader->addPsr4('Spec\\Models\\', __DIR__ . '/spec/models/');
});

Filter::register('phalcon.coverage-exporter', function($chain) {
    $reporter = $this->reporters()->get('coverage');
    if (!$reporter) return;

    Coveralls::write([
        'collector'      => $reporter,
        'file'           => 'coveralls.json',
        'service_name'   => 'travis-ci',
        'service_job_id' => getenv('TRAVIS_JOB_ID') ?: null
    ]);
    return $chain->next();
});

Filter::apply($this, 'namespaces', 'phalcon.namespace');
Filter::apply($this, 'reporting', 'phalcon.coverage-exporter');

$di = new \Phalcon\DI\FactoryDefault();
$di->setShared('db', function() {
  return new \Phalcon\Db\Adapter\Pdo\Sqlite([
    'dbname' => __DIR__ . '/spec/db.sqlite',
  ]);
});
\Phalcon\DI::setDefault($di);
