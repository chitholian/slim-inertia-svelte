<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class M20211228112333_CreateDevicesTable extends Migration
{
    public function up()
    {
        $this->down();
        $this->db->execute("
CREATE TABLE devices (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    did         VARCHAR(96) NOT NULL,
    ip          VARCHAR(48) NOT NULL,
    port        SMALLINT UNSIGNED NOT NULL,
    user_agent  VARCHAR(190) NULL,
    last_seen   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    trusted     CHAR(1) NOT NULL DEFAULT 'n',
    user_id     BIGINT UNSIGNED NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    PRIMARY KEY (id),
    UNIQUE (did),
    FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL
                     
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");

    }

    public function down()
    {
        $this->db->execute("DROP TABLE IF EXISTS devices");
    }
}
