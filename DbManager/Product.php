<?php

require_once 'Connection.php';

class Product
{
    private $id;
    private $nome;
    private $prezzo;
    private $marca;


    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function getPrezzo()
    {
        return $this->prezzo;
    }

    public function setPrezzo($prezzo)
    {
        $this->prezzo = $prezzo;
    }

    public function getMarca()
    {
        return $this->marca;
    }

    public function setMarca($marca)
    {
        $this->marca = $marca;
    }

    public static function Find($id)
    {
        $pdo = self::Connect();
        $stmt = $pdo->prepare("select * from edoardo_rossi_ecommerce.products where id = :id");
        $stmt->bindParam(":id", $id);
        if ($stmt->execute()) {
            return $stmt->fetchObject("Product");
        } else {
            return false;
        }
    }

    public static function Create($params)
    {
        $pdo = self::Connect();
        $stmt = $pdo->prepare("insert into edoardo_rossi_ecommerce.products (nome,prezzo,marca) values (:nome,:prezzo,:marca)");
        $stmt->bindParam(":nome", $params["nome"]);
        $stmt->bindParam(":prezzo", $params["prezzo"]);
        $stmt->bindParam(":marca", $params["marca"]);
        if ($stmt->execute()) {
            $stmt = $pdo->prepare("select * from edoardo_rossi_ecommerce.products order by id desc limit 1");
            $stmt->execute();
            return $stmt->fetchObject("Product");
        } else {
            return false;
        }
    }

    public static function FetchAll()
    {
        $pdo = self::Connect();
        $stmt = $pdo->query("select * from edoardo_rossi_ecommerce.products");
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Product');

    }

    public function update($params)
    {
        $id = $this->getId();
        $pdo = self::Connect();

        if(!isset($params['nome']))
        {
            $params['nome'] = $this->getNome();
        }
        if(!isset($params['marca']))
        {
            $params['marca'] = $this->getMarca();
        }
        if(!isset($params['prezzo']))
        {
            $params['prezzo'] = $this->getPrezzo();
        }

        $stmt = $pdo->prepare("update edoardo_rossi_ecommerce.products set nome = :nome , marca = :marca , prezzo = :prezzo where id = :id ");
        $stmt->bindParam(":nome", $params['nome']);
        $stmt->bindParam(":marca", $params['marca']);
        $stmt->bindParam(":prezzo", $params['prezzo']);
        $stmt->bindParam(":id", $id);
        if(!$stmt->execute())
        {
            return false;
        }
        else
            return self::Find($this->getId());
    }

    public static function Connect()
    {
        return Connection::Connect("edoardo_rossi_ecommerce");
    }

    public function delete()
    {
        $pdo = self::Connect();
        $id = $this->getId();
        $stmt = $pdo->prepare("delete from edoardo_rossi_ecommerce.products where id = :id");
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}