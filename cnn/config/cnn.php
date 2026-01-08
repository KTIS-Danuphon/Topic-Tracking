<?php 
define('HOST', 'localhost');
// define('USER', 'topic_admin');
// define('PASSWORD', 'JOJmawbl$02b?y7t');
// define('DATABASE', 'db_topic_tracking');
define('USER', 'root');
define('PASSWORD', '');
define('DATABASE', 'db_topic_tracking');
define('CHARSET', 'utf8');
function DB(){
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

?>