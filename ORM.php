<?php
// ORM.php

require_once 'Database.php';
require_once 'ORMInterface.php';

class ORM implements ORMInterface
{
    protected $db;
    protected $table;
    protected $className;

    public function __construct($table, $className)
    {
        $this->db = (new Database())->getConnection();
        $this->table = $table;
        $this->className = $className; 
    }

    public function create($object)
    {
        $columns = array_keys(get_object_vars($object));
        $values = array_values(get_object_vars($object));

        $sql = "INSERT INTO {$this->table} (" . implode(", ", $columns) . ") VALUES (" . implode(", ", array_fill(0, count($values), '?')) . ")";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);

        return $this->db->lastInsertId();
    }

    public function read($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($object) 
    {
        $columns = array_keys(get_object_vars($object));
        $values = array_values(get_object_vars($object));
        $id = $object->id;

        $setClause = implode(" = ?, ", $columns) . " = ?";
        $sql = "UPDATE {$this->table} SET $setClause WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([...$values, $id]);
    }

    public function delete($id) 
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
    }

    public function find($criteria)
    {
        $columns = array_keys($criteria);
        $values = array_values($criteria);

        $sql = "SELECT * FROM {$this->table} WHERE " . implode(" = ? AND ", $columns) . " = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createTable()
    {
        $tableDefinition = $this->getTableDefinition();
        $columns = [];
        foreach ($tableDefinition as $column => $definition) {
            $columns[] = "$column $definition";
        }
        $columns = implode(", ", $columns);

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} ($columns)";
        $this->db->exec($sql);
    }

    public function updateTable()
    {
        $tableDefinition = $this->getTableDefinition();
        $columns = [];
        foreach ($tableDefinition as $column => $definition) {
            $columns[] = "ADD COLUMN $column $definition";
        }
        $columns = implode(", ", $columns);

        $sql = "ALTER TABLE {$this->table} $columns";
        $this->db->exec($sql);
    }

    public function dropTable()
    {
        $sql = "DROP TABLE IF EXISTS {$this->table}";
        $this->db->exec($sql);
    }

    private function getTableDefinition()
    {
        $reflectionClass = new ReflectionClass($this->className);
        $properties = $reflectionClass->getProperties();
        $definition = [];

        foreach ($properties as $property) {
            $name = $property->getName();

            // Default type and definition, customize as needed
            switch ($name) {
                case 'id':
                    $definition[$name] = 'INT AUTO_INCREMENT PRIMARY KEY';
                    break;
                case 'price':
                    $definition[$name] = 'DECIMAL(10, 2) NOT NULL';
                    break;
                default:
                    $definition[$name] = 'VARCHAR(255) NOT NULL';
            }
        }

        return $definition;
    }
}
