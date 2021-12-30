<?php

namespace App\Repo;

class ExtraRepo extends BaseRepo
{
    public function trackDevice($device, $user)
    {
        $q = $this->db->execute("SELECT id FROM devices WHERE did = ? LIMIT 1", [$device['did']]);
        if ($q->fetch()) {
            $this->db->execute("UPDATE devices SET last_seen = NOW(), ip = :ip, port = :port, user_agent = :user_agent, user_id = :user_id WHERE did = :did", [
                'did' => $device['did'],
                'ip' => $device['ip'],
                'port' => $device['port'],
                'user_agent' => $device['user_agent'],
                'user_id' => $user['id'],
            ]);
        } else {
            $this->db->execute("INSERT INTO devices (did, ip, port, user_agent, user_id) VALUES (:did, :ip, :port, :user_agent, :user_id)", [
                'did' => $device['did'],
                'ip' => $device['ip'],
                'port' => $device['port'],
                'user_agent' => $device['user_agent'],
                'user_id' => $user['id'],
            ]);
        }
    }
}
