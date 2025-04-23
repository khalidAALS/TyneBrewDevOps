<?php
class Database {
    // databasse connection details
    private string $host = "213.171.200.35"; // database host
    private string $dbname = "kalsayed"; //database name
    private string $user = "kalsayed"; // databse username
    private string $pass = "Password20*"; // databse password
    private string $dsn; // source name for PDO connection (DSN)

    // consructor to start the (DSN) string
    public function __construct() {
        $this->dsn = "mysql:host={$this->host};dbname={$this->dbname}";
    }
    // establishes the connection to the databse using a PDO
    public function connect(): PDO {
        try {
            // creates a new PDO with an instancwe of the DSN, username and password
            $conn = new PDO($this->dsn, $this->user, $this->pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // set erros to exception for better debugging
            return $conn; // returns to established connection
        } catch (PDOException $e) {
            // logs erros for debugging
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection error. Please try again later."); // displays error message
        }
    }
}
