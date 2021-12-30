<?php

namespace App\Database;

use Exception;
use PDO;

class Migrator
{
    protected string $migrationPath;
    protected ?DB $db;

    public function __construct(DB $db = null)
    {
        $this->migrationPath = __DIR__ . '/../Database/Migrations';
        $this->db = $db;
    }

    public function setDB(DB $db)
    {
        $this->db = $db;
    }

    public function generateMigrationClass($filename)
    {
        $chars = " \t\r\n\f\v_-";
        $name = ucwords($filename, $chars);
        $name = str_replace(str_split($chars), '', $name);
        $name = 'M' . date('YmdHis') . "_$name";
        $template = <<<EOF
<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class $name extends Migration
{
    public function up()
    {
    }

    public function down()
    {
    }
}

EOF;
        file_put_contents($this->migrationPath . '/' . $name . '.php', $template);
        echo "Created migration file '$name'\n";
    }

    /**
     * @throws Exception
     */
    public function migrate()
    {
        $this->checkDB();
        // Determine next batch number.
        $res = $this->db->query("SELECT MAX(batch_no) AS batch FROM f1_migrations")->fetch();
        $batch = (int)$res['batch'] + 1;
        // Get applied migrations.
        $res = $this->db->query("SELECT name FROM f1_migrations")->fetchAll(PDO::FETCH_COLUMN, 0);
        // Collect migration files.
        $files = glob($this->migrationPath . '/*.php');
        $count = 0;
        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (in_array($name, $res)) continue;
            echo "Migrating '$name'...\n";
            $cls = "App\\Database\\Migrations\\$name";
            $migration = new $cls($this->db);
            try {
                $migration->up();
                $this->db->execute("INSERT INTO f1_migrations (name, batch_no, applied_on) VALUES (?, ?, now())", [$name, $batch]);
                $count++;
            } catch (\PDOException $e) {
                echo "Migration Failed for $name\n";
                throw $e;
            }
        }
        echo "$count migrations done\n";
    }

    /**
     * @throws Exception
     */
    public function rollback()
    {
        $this->checkDB();
        // Get latest migrations.
        $res = $this->db->query("SELECT name FROM f1_migrations WHERE batch_no = (SELECT MAX(batch_no) FROM f1_migrations) ORDER BY name DESC")->fetchAll(PDO::FETCH_COLUMN, 0);
        $this->undo($res);
    }

    /**
     * @throws Exception
     */
    public function truncateDB()
    {
        $this->checkDB();
        $res = $this->db->query("SELECT name FROM f1_migrations ORDER BY name DESC")->fetchAll(PDO::FETCH_COLUMN, 0);
        $this->undo($res);
    }

    private function undo(array $names)
    {
        $count = 0;
        foreach ($names as $name) {
            if (!file_exists($this->migrationPath . "/$name.php")) {
                echo "Warning: Migration file '$name' does not exist.\n";
            } else {
                echo "Rolling back '$name'...\n";
                $cls = "App\\Database\\Migrations\\$name";
                $migration = new $cls($this->db);
            }
            if (isset($migration)) {
                try {
                    $migration->down();
                } catch (\PDOException $e) {
                    echo "Rollback failed for $name\n";
                    throw $e;
                }
            }
            $this->db->execute("DELETE FROM f1_migrations WHERE name = ?", [$name]);
            $count++;
        }
        echo "$count migrations rolled back.\n";
    }

    /**
     * @throws Exception
     */
    private function checkDB()
    {
        if (!($this->db instanceof DB)) {
            throw new Exception("Database is not provided.");
        }
        // Create migration table.
        $this->db->execute("CREATE TABLE IF NOT EXISTS f1_migrations (name VARCHAR(128) NOT NULL, batch_no INT UNSIGNED NOT NULL, applied_on DATETIME NOT NULL, PRIMARY KEY (name))");
    }
}
