<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class M20211228112343_CreateLoginReportsTable extends Migration
{
    public function up()
    {
        $this->down();
        $this->db->execute("
CREATE TABLE login_reports (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    did         VARCHAR(96) NOT NULL,
    ip          VARCHAR(48) NOT NULL,
    port        SMALLINT UNSIGNED NOT NULL,
    user_id     BIGINT UNSIGNED NULL,
    user_agent  VARCHAR(190) NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    logout_time DATETIME NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE CASCADE 
                     
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci");

    }

    public function down()
    {
        $this->db->execute("DROP TABLE IF EXISTS login_reports");
    }
}
