<?php
// MySQL Connection Initialize

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "map";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8');

// Query Builder Class

class MySQLQueryBuilder {
    protected $query;

    protected function reset(){
        $this->query = new Stdclass();
    }

    public function primary(string $table)
    {
        $this->reset();
        $this->query->base = "SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'";
        $this->query->type = "show";
        return $this;
    }

    public function exists(string $table)
    {
        $this->reset();
        $this->query->base = "SHOW TABLES LIKE '$table'";
        $this->query->type = "show";
        return $this;
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
        $statement = [];
        foreach ($fields as $key => $value) $statement[] = "'$value'";
        $this->query->base = "INSERT INTO $table (".implode(", ", $keys).") VALUES (".implode(", ", $statement).")";
        $this->query->type = "insert";
        return $this;
    }

    public function update(string $table, array $fields){
        $this->reset();
        $statement = [];
        foreach ($fields as $key => $value) $statement[] = "$key = '$value'";
        $this->query->base = "UPDATE $table SET ".implode(", ", $statement);
        $this->query->type = "update";
        return $this;
    }

    public function delete(string $table){
        $this->reset();
        $this->query->base = "DELETE FROM $table";
        $this->query->type = "delete";
        return $this;
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

// Processing input, outputs and request uri

$input = json_decode(file_get_contents("php://input"));
$uri = str_replace(dirname($_SERVER['SCRIPT_NAME']),'',$_SERVER['REQUEST_URI']);
$output = [];

// Processing paramaters

$params = explode("/",$uri);
if(count($params) >= 2 && strlen($params[1]) > 0)
{
    $table = $params[1];
    $query = new MySQLQueryBuilder();
    $result = $conn->query($query->exists($table)->getQuery());
    if(!$result || mysqli_num_rows($result) <= 0)
    {
        $output['error'] = "The table doesn't exist.";
        http_response_code(400);
    } else {
        $usable = true;
    }
} else {
    $output['error'] = "Missing argument: Table name.";
}
$table = htmlspecialchars($params[1]);
if(count($params) >= 3 && is_numeric($params[2])) $id = $params[2];

// CRUD logics

if(isset($usable)){
    switch($_SERVER["REQUEST_METHOD"])
    {
        case "GET":
            $query = new MySQLQueryBuilder();
            $statement = $query->select($table);
            if(isset($id))
            {
                $primary_key_query = new MySQLQueryBuilder();
                $pk_query = $conn->query($primary_key_query->primary($table)->getQuery());
                if(mysqli_num_rows($pk_query) == 1)
                {
                    $primary_key = $pk_query->fetch_object()->Column_name;
                    $statement->where($primary_key,$id);
                }
            }
            
            $result = $conn->query($statement->getQuery());
            if(isset($id) && mysqli_num_rows($result) == 0)
            {
                $output['error'] = "The record doesn't exist.";
                http_response_code(404);
                break;
            }
            while ($row = $result->fetch_object()) $output[] = $row;
            http_response_code(200);
        break;

        case "POST":
            $query = new MySQLQueryBuilder();
            $result = $conn->query($query->insert($table,get_object_vars($input))->getQuery());
            if(!$result)
            {
                $output['error'] = "Unable to create resource. Error message: ".mysqli_error($conn);
                http_response_code(422);
                break;
            } else {
                $output = $input;
                http_response_code(201);
            }
        break;

        case "PUT":
            if(!isset($id))
            {
                $output['error'] = "Must provide an identifier for the resource.";
                http_response_code(400);
                break;
            }

            $query = new MySQLQueryBuilder();
            $primary_key_query = new MySQLQueryBuilder();
            $pk_query = $conn->query($primary_key_query->primary($table)->getQuery());
            if(mysqli_num_rows($pk_query) == 1)
            {
                $primary_key = $pk_query->fetch_object()->Column_name;
                if(mysqli_num_rows($conn->query($query->select($table)->where($primary_key,$id)->getQuery())) <= 0)
                {
                    $output['error'] = "Resource not found.";
                    http_response_code(404);
                    break;
                } else {
                    if($conn->query($query->update($table,get_object_vars($input))->where($primary_key,$id)->getQuery()))
                    {
                        $output = $input;
                        http_response_code(201);
                        break;
                    } else {
                        $output['error'] = "Unable to update resource. Error message: ".mysqli_error($conn);
                        http_response_code(422);
                        break;
                    }
                }
            } else {
                $output['error'] = "Unable to update resource due to incorrect primary key number.";
                http_response_code(400);
                break;
            }
        break;

        case "PATCH":
            if(!isset($id))
            {
                $output['error'] = "Must provide an identifier for the resource.";
                http_response_code(400);
                break;
            }

            $query = new MySQLQueryBuilder();
            $primary_key_query = new MySQLQueryBuilder();
            $pk_query = $conn->query($primary_key_query->primary($table)->getQuery());
            if(mysqli_num_rows($pk_query) == 1)
            {
                $primary_key = $pk_query->fetch_object()->Column_name;
                if(mysqli_num_rows($conn->query($query->select($table)->where($primary_key,$id)->getQuery())) <= 0)
                {
                    $output['error'] = "Resource not found.";
                    http_response_code(404);
                    break;
                } else {
                    if($conn->query($query->update($table,get_object_vars($input))->where($primary_key,$id)->getQuery()))
                    {
                        $output = $input;
                        http_response_code(201);
                        break;
                    } else {
                        $output['error'] = "Unable to update resource. Error message: ".mysqli_error($conn);
                        http_response_code(422);
                        break;
                    }
                }
            } else {
                $output['error'] = "Unable to update resource due to incorrect primary key number.";
                http_response_code(400);
                break;
            }
        break;

        case "DELETE":
            if(!isset($id))
            {
                $output['error'] = "Must provide an identifier for the resource.";
                http_response_code(400);
                break;
            }

            $query = new MySQLQueryBuilder();
            $primary_key_query = new MySQLQueryBuilder();
            $pk_query = $conn->query($primary_key_query->primary($table)->getQuery());
            if(mysqli_num_rows($pk_query) == 1)
            {
                $primary_key = $pk_query->fetch_object()->Column_name;
                if(mysqli_num_rows($conn->query($query->select($table)->where($primary_key,$id)->getQuery())) <= 0)
                {
                    $output['error'] = "Resource not found.";
                    http_response_code(404);
                    break;
                } else {
                    $conn->query($query->delete($table)->where($primary_key,$id)->getQuery());
                    http_response_code(204);
                    break;
                }
            } else {
                $output['error'] = "Unable to update resource due to incorrect primary key number.";
                http_response_code(400);
                break;
            }
        break;
    }
}

// Output and exit

header('Content-Type: application/json');
echo json_encode($output);
return;