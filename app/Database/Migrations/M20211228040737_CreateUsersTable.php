<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class M20211228040737_CreateUsersTable extends Migration
{
    public function up()
    {
        $this->down();
        $this->db->execute("
CREATE TABLE users (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    username    VARCHAR(64) NOT NULL,
    password    VARCHAR(190) NOT NULL,
    role        VARCHAR(16) NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    updated_at  DATETIME NULL ON UPDATE CURRENT_TIMESTAMP(),
    PRIMARY KEY (id),
    UNIQUE (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Insert default user.
        $this->db->execute("INSERT INTO users (username, role, password) VALUES (?, ?, ?)", ['admin', 'admin', password_hash('admin', PASSWORD_DEFAULT)]);
    }

    public function down()
    {
        $this->db->execute("DROP TABLE IF EXISTS users");
    }
}
