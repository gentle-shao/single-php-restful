# Restful API in Single PHP File

This project used .htaccess rewrite module to make restful API in single PHP file.
Following the rules of Restful API includes endpoint, HTTP Request Methods, Filtering, HTTP Status Code and error handling.

# Install

Make sure your Apache rewrite module is installed and enabled and just put all files in the same directory.(Including `.htaccess` file).

Remember to configure first lines of `index.php` to fit your database configuration.
```
$servername = "YOUR-DATABASE-HOSTING";
$username = "USERNAME";
$password = "PASSWORD";
$dbname = "DATABASE";
```

# Usage

Visit `http://HOST:PORT/DIRECTORY` to starting call Restful-APIs.

## `GET` /table_name

Listing all resources in specific table.

Example response:
```
[
    {
        "person_id": "2",
        "person_name": "Person B"
    },
    {
        "person_id": "3",
        "person_name": "Person C"
    },
    {
        "person_id": "4",
        "person_name": "Person D"
    },
    {
        "person_id": "5",
        "person_name": "ABC1023"
    }
]
```