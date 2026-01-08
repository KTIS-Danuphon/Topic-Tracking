<?php
define('HOST', 'localhost');
define('USER', 'root');
define('PASSWORD', '');
define('DATABASE', 'db_topic_tracking');
define('CHARSET', 'utf8');
function DB()
{
    static $instance;
    if ($instance === null) {
        $opt = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        );
        $dsn = 'mysql:host=' . HOST . ';dbname=' . DATABASE . ';charset=' . CHARSET;
        $instance = new PDO($dsn, USER, PASSWORD, $opt);
    }
    return $instance;
}
date_default_timezone_set('Asia/Bangkok');
class CRUD
{
    protected $db;
    public function __construct()
    {
        try {
            $this->db = DB();
        } catch (PDOException $e) {
            die("Failed to connect to DB: " . $e->getMessage());
        }
    }
    public function __destruct()
    {
        $this->db = null;
    }
    public function Insert_Data($table, $fields)
    {
        try {
            $columnString = implode(',', array_keys($fields));
            $valueString = ":" . implode(',:', array_keys($fields));
            $sql = "INSERT INTO " . $table . " (" . $columnString . ") VALUES (" . $valueString . ")";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($fields);
            $lastId = $this->db->lastInsertId();
            return $lastId;
        } catch (PDOException $e) {
            die("Error: " . $sql . $e->getMessage());
        }
    }
    public function ReadData($table, $fields, $where)
    {
        $data = [];
        try {
            $sql = "SELECT " . $fields . " FROM " . $table . " " . $where;
            $query = $this->db->query($sql) or die("failed!");
            while ($result = $query->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $result;
            }
            return $data;
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage() . "<br>SQL: " . $sql);
        }
    }
    public function ReadOne_Data($table, $id, $fields_id)
    {
        try {
            $sql = "SELECT * FROM " . $table . " WHERE " . $fields_id . " = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch();
            return $result;
        } catch (PDOException $e) {
            die("Error: " . $sql . $e->getMessage());
        }
    }
    public function Update_Data($table, $fields, $conditions)
    {
        $colvalSet = '';
        $whereSql = '';
        $i = 0;
        foreach ($fields as $key => $val) {
            $pre = ($i > 0) ? ', ' : '';
            $colvalSet .= $pre . $key . "='" . $val . "'";
            $i++;
        }
        if (!empty($conditions) && is_array($conditions)) {
            $whereSql .= ' WHERE ';
            $i = 0;
            foreach ($conditions as $key => $value) {
                $pre = ($i > 0) ? ' AND ' : '';
                $whereSql .= $pre . $key . " = '" . $value . "'";
                $i++;
            }
        }
        try {
            $sql = "UPDATE " . $table . " SET " . $colvalSet . $whereSql;
            $query = $this->db->prepare($sql);
            $query->execute();
            return  $query->rowCount();
        } catch (PDOException $e) {
            die("Error: " . $sql . $e->getMessage());
        }
    }
    public function delete($table, $conditions)
    {
        $whereSql = '';
        if (!empty($conditions) && is_array($conditions)) {
            $whereSql .= ' WHERE ';
            $i = 0;
            foreach ($conditions as $key => $value) {
                $pre = ($i > 0) ? ' AND ' : '';
                $whereSql .= $pre . $key . " = '" . $value . "'";
                $i++;
            }
        }
        try {
            $sql = "DELETE FROM " . $table . $whereSql;
            $delete = $this->db->exec($sql);
            return $delete ? $delete : false;
        } catch (PDOException $e) {
            die("Error: " . $sql . $e->getMessage());
        }
    }
    public function Insert_Into_Select($targetTable, $targetColumns, $sourceTable, $sourceColumns, $conditions = "")
    {
        try {
            // แปลงอาร์เรย์คอลัมน์เป็นสตริงที่คั่นด้วยเครื่องหมาย จุลภาค(,)
            $targetColumnsString = implode(',', $targetColumns);
            $sourceColumnsString = implode(',', $sourceColumns);

            $sql = "INSERT INTO $targetTable ($targetColumnsString) SELECT $sourceColumnsString FROM $sourceTable";

            //เพิ่มเงื่อนไข WHERE ถ้า $conditions ไม่ว่าง
            if (!empty($conditions)) {
                $sql .= " WHERE $conditions";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            $sql2 = "ALTER TABLE " . $targetTable . " AUTO_INCREMENT = 1";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute();

            return $stmt->rowCount(); // Return จำนวนแถวที่มีการเพิ่ม
        } catch (PDOException $e) {
            // Log error และ return false
            error_log("Error executing query: " . $e->getMessage());
            return false;
        }
    }
}
