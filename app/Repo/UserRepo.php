<?php

namespace App\Repo;

class UserRepo extends BaseRepo
{
    public function findById($id)
    {
        $q = $this->db->execute("SELECT * FROM users WHERE id = ?", [$id]);
        if ($r = $q->fetch()) {
            return $r;
        }
        return null;
    }

    public function insertNewLogin($device, $user)
    {
        $this->db->execute("INSERT INTO login_reports (did, ip, port, user_agent, user_id) VALUES (:did, :ip, :port, :user_agent, :user_id)", [
            'did' => $device['did'],
            'ip' => $device['ip'],
            'port' => $device['port'],
            'user_agent' => $device['user_agent'],
            'user_id' => $user['id'],
        ]);
        return $this->db->lastInsertId();
    }

    public function updateLoginReport($loginID)
    {
        $this->db->execute("UPDATE login_reports SET logout_time = NOW() WHERE id = ? AND logout_time IS NULL", [$loginID]);
    }
}
