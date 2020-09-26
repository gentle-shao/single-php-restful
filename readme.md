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

Available filter paramaters:
| Paramater      | Description |
| ----------- | ----------- |
| ?limit=`10`      | Specifc the number of results       |
| ?offset=`10`   | Specifc the offset number of results        |
| ?page=`1`&per_page=`10`   | Specific the number of page index(starts from 0) and number per page |
| ?sortby=`column`&order=`desc`   | Order by `column` and sort by `desc` |
| ?`column`=`value`   | Filtering value of specific column |

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

## `GET` /table_name/id

Listing specifiy resources in specific table.

Available filter paramaters:
| Paramater      | Description |
| ----------- | ----------- |
| ?limit=`10`      | Specifc the number of results       |
| ?offset=`10`   | Specifc the offset number of results        |
| ?page=`1`&per_page=`10`   | Specific the number of page index(starts from 0) and number per page |
| ?sortby=`column`&order=`desc`   | Order by `column` and sort by `desc` |
| ?`column`=`value`   | Filtering value of specific column |

Example response:
```
[
    {
        "person_id": "2",
        "person_name": "Person B"
    }
]
```

## `POST` /table_name

Create resource to table.

Example request:

```
{
    "person_name": "Steve Jobs"
}
```

Example Response:

```
{
    "person_name": "Steve Jobs"
}
```

## `PUT` /table_name/id

Update resource instance to table.

Example request:

```
{
    "person_name": "Steve Jobs"
}
```

Example Response:

```
{
    "person_name": "Steve Jobs"
}
```

## `PATCH` /table_name/id

Update resource instance to table.

Example request:

```
{
    "person_name": "Steve Jobs"
}
```

Example Response:

```
{
    "person_name": "Steve Jobs"
}
```

## `DELETE` /table_name/id

Delete resource instance to table.

In the case of success, the response code is 204 and nothing will be returned.