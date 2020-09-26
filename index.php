<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "map";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8');

class MySQLQueryBuilder {
    protected $query;

    protected function reset(){
        $this->query = new Stdclass();
    }

    public function select(string $table){
        $this->reset();
        $this->query->base = "SELECT * FROM ".$table;
        $this->query->type = "select";
        return $this;
    }

    public function where(string $field, string $value, string $operator = '='){
        if(!in_array($this->query->type,['select', 'delete', 'update'])) return $this;
        $this->query->where[] = "$field $operator $value";
        return $this;
    }

    public function limit(int $start, int $offset){
        if(!in_array($this->query->type,['select'])) return $this;
        $this->query->limit = " LIMIT $start, $offset";
        return $this;
    }

    public function insert(string $table, array $fields){
        $this->reset();
        $keys = array_keys($fields);
        $values = array_values($fields);
        $this->query->base = "INSERT INTO $table (".implode(", ", $keys).") VALUES (".implode(", ", $values).")";
        $this->query->type = "insert";
    }

    public function update(string $table, array $fields){
        $this->reset();
        $statement = [];
        foreach ($fields as $key => $value){
            $statement[] = "$key = '$value'";
        }
        $this->query->base = "UPDATE $table SET ".implode(", ", $statement);
        $this->query->type = "update";
    }

    public function getQuery(){
        $query = $this->query;
        $sql = $query->base;
        if (!empty($query->where)) {
            $sql .= " WHERE " . implode(' AND ', $query->where);
        }
        if (isset($query->limit)) {
            $sql .= $query->limit;
        }
        $sql .= ";";
        return $sql;
    }
}

$input = json_decode(file_get_contents("php://input"));
$uri = str_replace(dirname($_SERVER['SCRIPT_NAME']),'',$_SERVER['REQUEST_URI']);
$output = [];
switch($_SERVER["REQUEST_METHOD"])
{
    case "GET":
        $params = explode("/",$uri);
        $table = $params[1];
        // $id = $params[2];
        $query = new MySQLQueryBuilder();
        $result = $conn->query($query->select($table)->getQuery());
        while ($row = $result->fetch_object()) $output[] = $row;
        http_response_code(200);
    break;

    case "POST":
    break;

    case "PUT":
    break;

    case "PATCH":
    break;

    case "DELETE":
    break;
}

header('Content-Type: application/json');
echo json_encode($output);