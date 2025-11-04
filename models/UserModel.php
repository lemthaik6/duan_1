<?php

class UserModel extends BaseModel
{
    protected $table = 'users';

    public function findByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function findByUsername($username)
    {
        $sql = "SELECT * FROM {$this->table} WHERE username = :username LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['username' => $username]);
        return $stmt->fetch();
    }

    public function createUser($fullName, $email, $password)
    {
        // create a username from email prefix and ensure uniqueness
        $base = preg_replace('/[^a-z0-9_]/i', '', strstr($email, '@', true));
        $username = $base ?: 'user';
        $suffix = '';
        $i = 0;
        while ($this->findByUsername($username . $suffix)) {
            $i++;
            $suffix = $i;
            if ($i > 1000) break; // safety
        }
        $username = $username . $suffix;

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $data = [
            'username' => $username,
            'password' => $hashed,
            'email' => $email,
            'full_name' => $fullName,
            'role' => 'user'
        ];

        return $this->create($data);
    }

    public function authenticate($identity, $password)
    {
        // identity can be email or username
        $user = $this->findByEmail($identity);
        if (!$user) {
            $user = $this->findByUsername($identity);
        }

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }
}
