<?php

$username = 'SYSDBA';
$pass = 'root';

// . $username . ';' . $pass
echo $dsn = 'firebird:dbname=localhost:gds_db:/D:\SYNDATA.FDB';

try {

  $PDO = new \PDO($dsn, $username, $pass);
  $PDO->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

  print_r($PDO);

  $query = "SELECT * FROM PRODUTOS_estoque WHERE cod_produto = 1";

  $sth = $PDO->prepare($query);
  $result = $sth->fetchAll(PDO::FETCH_COLUMN);
  
  
  print_r($result);
  

}catch(PDOException $e){

  echo $e->getMessage();

}

