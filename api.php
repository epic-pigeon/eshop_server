<?php

define("MYSQLI_ERROR_CODE", 0);
define("BAD_ARGUMENTS_ERROR_CODE", 1);
define("UNKNOWN_ERROR_CODE", 2);

define("DB_HOST", "localhost");
define("DB_USER", "eshop");
define("DB_PASSWORD", 'aNjeZeQd.7}cQz$Q');
define("DB_DATABASE", "eshop");

class MySQLException extends Exception {
    public function __construct($message, Throwable $previous = null)
    {
        parent::__construct($message, MYSQLI_ERROR_CODE, $previous);
    }
}

class BadArgumentsException extends Exception {
    public function __construct($arr, Throwable $previous = null)
    {

        $message = "The following parameters are either not specified or malformed: " . join(", ", $arr);
        parent::__construct($message, BAD_ARGUMENTS_ERROR_CODE, $previous);
    }
}

function exceptionToJSON(Exception $exception) {
    $code = ($exception instanceof MySQLException || $exception instanceof BadArgumentsException) ? $exception->getCode() : UNKNOWN_ERROR_CODE;
    $message = $exception->getMessage();
    $result = [
        "success" => false,
        "message" => $message,
        "code" => $code
    ];
    return json_encode($result);
}

$operations = [
    "get_count" => function ($dbc, $query) {
        if ($query['table']) {
            $result = mysqli_query($dbc, "SELECT COUNT(*) FROM `" . $query['table'] . "`");
            if ($result !== false) return (int) $result; else throw new MySQLException(mysqli_error($dbc));
        } else throw new BadArgumentsException(["table"]);
    }
];

if (isset($_REQUEST["operation"])) {
    if ($operation = $operations[$_REQUEST["operation"]]) {
        $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
        try {
            if (!$link) throw new MySQLException("Could not connect to the database");
            $result = $operation(
                $link,
                $_REQUEST
            );
            echo json_encode([
                "success" => true,
                "result" => $result
            ]);
        } catch (Exception $e) {
            echo exceptionToJSON($e);
        }
    } else echo '{"success":false,"code":1,"message":"Operation ' . $_REQUEST["operation"] . ' does not exist"}';
} else echo '{"success":false,"code":1,"message":"Operation not specified"}';
