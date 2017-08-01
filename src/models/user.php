<?php

// User model
// Creates users table & columns in the database

// get a DBAL Connection through the Doctrine\DBAL\DriverManager class (src: Doctrine documentation)
$config = new \Doctrine\DBAL\Configuration();
$connectionParams = array(
    'driver'    => 'pdo_mysql',
    'host'      => 'localhost',
    'dbname'    => 'php_buzz_db',
    'user'      => 'root',
    'password'  => 'root',
    'charset'   => 'utf8',
);
$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

$sm = $conn->getSchemaManager();
$dbs = $sm->listDatabases();

// check if a 'user' table already exists; if not, create one
if (!$sm->tablesExist('user')) {

  $schema = new \Doctrine\DBAL\Schema\Schema();

  // create a table
  $user = $schema->createTable('user');

  // add columns to table using the following method from Doctrine documentation:
  //   addColumn( string $columnName, string $typeName, array $options = array() )
  $user->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
  $user->setPrimaryKey(array('id'));  // set the 'id' field as the primary key
  $user->addColumn('name', 'string', array('length' => 32));
  $user->addUniqueIndex(array('name'));
  $user->addColumn('created_at', 'datetimetz', array('default' => 0));
  $user->addColumn('updated_at', 'datetimetz', array('default' => 0));

  $platform = $conn->getDatabasePlatform();
  $queries = $schema->toSql($platform);
  foreach ($queries as $sql) {
    $conn->exec($sql);
  }

}
