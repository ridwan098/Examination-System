<?php

    $servername = "35.233.45.51";
    $username = "root";
    $password = "ViBOAFp8ua";
    $dbname = "higherexam";

    class Class_DB{
        public function __construct($servername, $username, $password){
            $this->serverName = $servername;
            $this->username = $username;
            $this->password = $password;
            $this->conn = null;
        }

        public function __destruct(){
            $this->conn = null;
        }

        public function connectToDb($dbname){
            // Connect to sql db
            try {
                $this->conn = new PDO("mysql:host=$this->serverName;dbname=$dbname", $this->username, $this->password);
                // set the PDO error mode to exception
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            catch(PDOException $e)
            {
                die("Connection failed: " . $e->getMessage());
            }
        }

        public function executeQuery($query, $inputParams=null){
            if ($this->conn){
                $result = $this->conn->prepare($query);
                $result->execute($inputParams);
                return $result;
            }
            else{
                throw new Exception("No active connection");
            }
        }

        public function getLastInsertId(){
            return $this->conn->lastInsertId();
        }

        public function closeConn(){
            $this->conn = null;
        }
    }

?>