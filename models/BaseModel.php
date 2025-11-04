<?php

class BaseModel
{
    protected $table;
    protected $pdo;

    // Kết nối CSDL
    public function __construct()
    {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8', DB_HOST, DB_PORT, DB_NAME);

        try {
            $this->pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, DB_OPTIONS);
        } catch (PDOException $e) {
            // Xử lý lỗi kết nối
            die("Kết nối cơ sở dữ liệu thất bại: {$e->getMessage()}. Vui lòng thử lại sau.");
        }
    }

    // Lấy tất cả bản ghi
    public function all($select = ['*'], $orderBy = '', $limit = null)
    {
        $columns = implode(',', $select);
        $sql = "SELECT {$columns} FROM {$this->table}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Tìm bản ghi theo ID
    public function find($id, $select = ['*'])
    {
        $columns = implode(',', $select);
        $sql = "SELECT {$columns} FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    // Tạo bản ghi mới
    public function create($data)
    {
        $columns = implode(',', array_keys($data));
        $values = implode(',', array_map(function($item) {
            return ":$item";
        }, array_keys($data)));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$values})";
        $stmt = $this->pdo->prepare($sql);
        
        if ($stmt->execute($data)) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    // Cập nhật bản ghi
    public function update($id, $data)
    {
        $setClause = implode(',', array_map(function($item) {
            return "$item = :$item";
        }, array_keys($data)));

        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        
        $data['id'] = $id;
        return $stmt->execute($data);
    }

    // Xóa bản ghi
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    // Tìm kiếm theo điều kiện
    public function findBy($conditions, $select = ['*'], $orderBy = '', $limit = null)
    {
        $columns = implode(',', $select);
        $whereClause = implode(' AND ', array_map(function($item) {
            return "$item = :$item";
        }, array_keys($conditions)));

        $sql = "SELECT {$columns} FROM {$this->table} WHERE {$whereClause}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($conditions);
        return $stmt->fetchAll();
    }

    // Đếm số bản ghi
    public function count($conditions = [])
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        
        if (!empty($conditions)) {
            $whereClause = implode(' AND ', array_map(function($item) {
                return "$item = :$item";
            }, array_keys($conditions)));
            $sql .= " WHERE {$whereClause}";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($conditions);
        return $stmt->fetch()['total'];
    }

    // Kiểm tra xem bảng có cột cụ thể hay không
    public function hasColumn($columnName)
    {
        try {
            $sql = "SHOW COLUMNS FROM {$this->table} LIKE :col";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['col' => $columnName]);
            $row = $stmt->fetch();
            return $row !== false;
        } catch (Throwable $e) {
            return false;
        }
    }

    // Bắt đầu transaction
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    // Commit transaction
    public function commit()
    {
        return $this->pdo->commit();
    }

    // Rollback transaction
    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    // Hủy kết nối CSDL
    public function __destruct()
    {
        $this->pdo = null;
    }
}
