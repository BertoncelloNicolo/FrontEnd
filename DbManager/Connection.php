<?php

class Connection
{
    public static function Connect($dbname)
    {
        $dsn = "mysql:dbname={$dbname};host=127.0.0.1";
        try
        {
            $pdo = new PDO($dsn, 'root', 'edoardo2005');

            $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            return $pdo;

        }catch (PDOException $e)
        {
            die("Errore durante la connessione al DB ");
        }
    }
}