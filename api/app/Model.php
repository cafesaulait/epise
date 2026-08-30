<?php
namespace app;

abstract class Model
{
    protected \mysqli $_connexion;
    public string $table = '';
    public string $primaryKey = 'id';

    public function __construct()
    {
        $this->getConnection();
    }

    public function getConnection(): void
    {
        $this->_connexion = ConnexionBDD::getInstance()->getConnexion();
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM `{$this->table}`";
        $stmt = $this->_connexion->prepare($sql);
        if (!$stmt || !$stmt->execute()) {
            throw new \RuntimeException($stmt ? $stmt->error : $this->_connexion->error);
        }
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getOne(int $id): array|false
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1";
        $stmt = $this->_connexion->prepare($sql);
        if (!$stmt) throw new \RuntimeException($this->_connexion->error);
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) throw new \RuntimeException($stmt->error);
        return $stmt->get_result()->fetch_assoc();
    }

    protected function jsonInput(): array
    {
        $raw = file_get_contents('php://input');
        if (!$raw) return [];
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
?>