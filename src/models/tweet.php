<?php

// Tweet model
// Creates tweets table & columns in the database

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

// check if a 'tweet' table already exists; if not, create one
if (!$sm->tablesExist('tweet')) {

  $schema = new \Doctrine\DBAL\Schema\Schema();

  // create a table
  $tweet = $schema->createTable('tweet');

  // add columns to table using the following method from Doctrine documentation:
  //   addColumn( string $columnName, string $typeName, array $options = array() )
  $tweet->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
  $tweet->setPrimaryKey(array('id'));  // set the 'id' field as the primary key
  $tweet->addColumn('content', 'string', array('length' => 140));
  $tweet->addColumn('author_id', 'integer', array('unsigned' => true));
  $tweet->addColumn('created_at', 'datetimetz', array('default' => 0));
  $tweet->addColumn('updated_at', 'datetimetz', array('default' => 0));

  $platform = $conn->getDatabasePlatform();
  $queries = $schema->toSql($platform);
  foreach ($queries as $sql) {
    $conn->exec($sql);
  }

  // add the user's id as the author_id to the tweet table as a foreign key; cascade delete such that deleting a user deletes all of their tweets
  $conn->exec('ALTER TABLE tweet ADD CONSTRAINT FOREIGN KEY (author_id) REFERENCES user (id) ON DELETE CASCADE');

}
