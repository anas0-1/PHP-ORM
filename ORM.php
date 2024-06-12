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
        $objectVars = get_object_vars($object);
        $columns = array_keys($objectVars);
        $values = array_values($objectVars);
        $id = $object->id;

        // Remove 'id' from the columns and values
        $index = array_search('id', $columns);
        if ($index !== false) {
            unset($columns[$index]);
            unset($values[$index]);
        }

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

    public function createTable()
    {
        $tableDefinition = $this->getTableDefinition($this->className);
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
        $currentDefinition = $this->getTableDefinition($this->className);
        $newDefinition = $this->getTableDefinition($this->className);

        $columnsToAdd = [];
        foreach ($newDefinition as $column => $definition) {
            if (!isset($currentDefinition[$column])) {
                $columnsToAdd[] = "ADD COLUMN $column $definition";
            }
        }

        if (!empty($columnsToAdd)) {
            $sql = "ALTER TABLE {$this->table} " . implode(", ", $columnsToAdd);
            $this->db->exec($sql);
        }
    }

    private function getTableDefinition($className)
    {
        $reflectionClass = new ReflectionClass($className);
        $properties = $reflectionClass->getProperties();
        $definition = [];

        foreach ($properties as $property) {
            $name = $property->getName();

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
?>
