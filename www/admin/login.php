<?php 
    session_start();
    include("../helpers/db.php");
    include("../helpers/session.php");

    $errors = array();
    if (!empty($_POST)) {
        $username = $_POST["username"];
        $password = $_POST["password"];
        
        if (empty($username)) { $errors[] = "Vul een gebruikersnaam in."; }
        if (empty($password)) { $errors[] = "Vul een wachtwoord in."; }

        if (empty($errors)) {
            if (!($user = find_user($username))) {
                $errors[] = "Deze gebruiker bestaat niet.";
            } else {
                if (password_verify($password, $user["pw_hash"])) {
                    login($user["id"]);
                } else {
                    $errors[] = "Onjuist wachtwoord.";
                }
            }
        }
    }

    if (loggedin()) {
        header("location: /admin");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Admin Login - PHPQuiz</title>
    <link rel="stylesheet" type="text/css" href="/style.css" />
</head>
<body>
    <div class="login-box">
        <h1>Log in</h1>
        <ul class="errors">
            <?php foreach ($errors as $err) {
                echo "<li>$err</li>";
            } ?>
        </ul>
        <form method="post">
            <label for="username">Gebruikersnaam</label>
            <?php
                echo "<input type=\"username\" name=\"username\" value=\"$username\" />";
            ?>
            <label for="password">Wachtwoord</label>
            <input type="password" name="password" />
            <input type="submit" value="Log in" />
        </form>
    </div>
</body>
</html>