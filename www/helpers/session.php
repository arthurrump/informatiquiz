<?php
function loggedin() {
    return $_SESSION["logged_in"] === true;
}

function login($id) {
    $_SESSION["logged_in"] = true;
    $_SESSION["user_id"] = $id;
}

function user_id() {
    return $_SESSION["user_id"];
}

function logout() {
    session_destroy();
}
?>