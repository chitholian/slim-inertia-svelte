<?php

namespace App\Repo;

use App\Database\DB;

class BaseRepo
{
    protected DB $db;

    protected array $insertFields = [];
    protected array $updateFields = [];
    protected string $created_at = 'created_at';
    protected string $updated_at = 'updated_at';
    protected string $table;
    protected string $pKey = 'id';

    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    public function create(array $data, $get_id = true): string
    {
        $binds = [];
        foreach ($this->insertFields as $field) {
            $binds[$field] = $data[$field] ?? null;
        }
        $fields = $this->insertFields;
        $values = array_map(function ($f) {
            return ":$f";
        }, $this->insertFields);
        if ($this->created_at) {
            $fields[] = $this->created_at;
            $values[] = "NOW()";
        }
        $q = "INSERT INTO $this->table (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
        $this->db->execute($q, $binds);
        return $get_id ? $this->db->lastInsertId() : '';
    }


    public function update(array $data, $where = [], $count = true): int
    {
        $binds = [];
        $setParts = [];
        foreach ($this->updateFields as $field) {
            if (isset($data[$field])) {
                $setParts[] = "$field = :S_$field";
                $binds["S_$field"] = $data[$field];
            }
        }
        if ($this->updated_at) {
            $setParts[] = $this->updated_at . " = NOW()";
        }
        $whereParts = [1];
        foreach ($where as $k => $v) {
            $whereParts[] = "$k = :W_$k";
            $binds["W_$k"] = $v;
        }

        $q = "UPDATE $this->table SET " . implode(', ', $setParts) . " WHERE " . implode(' AND ', $whereParts);
        $stmt = $this->db->execute($q, $binds);
        return $count ? $stmt->rowCount() : -1;
    }

    public function delete($where, $ids = [], $count = true): int
    {
        $binds = [];
        $whereParts = [];
        if (!empty($where)) {
            foreach ($where as $k => $v) {
                $whereParts[] = "$k = :W_$k";
                $binds["W_$k"] = $v;
            }
        }
        if (!empty($ids)) {
            $whereParts[] = "$this->pKey = :WK_$this->pKey";
        }
        $WHERE = empty($whereParts) ? '' : "WHERE " . implode(' AND ', $whereParts);

        $q = $this->db->prepare("DELETE FROM $this->table $WHERE");
        if (empty($ids)) {
            $q->execute($binds);
        } else {
            foreach ($ids as $id) {
                $q->execute([...$binds, ":WK_$this->pKey" => $id]);
            }
        }
        return $count ? $q->rowCount() : -1;
    }
}
