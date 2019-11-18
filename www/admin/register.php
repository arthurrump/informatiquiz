<?php 
    session_start();
    include("../helpers/db.php");
    include("../helpers/session.php");

    if (loggedin()) {
        header("location: /admin");
        exit;
    }

    $errors = array();
    if (!empty($_POST)) {
        $username = $_POST["username"];
        $password = $_POST["password"];
        
        if (empty($username)) { $errors[] = "Vul een gebruikersnaam in."; }
        if (empty($password)) { $errors[] = "Vul een wachtwoord in."; }

        if (empty($errors)) {
            create_user($username, $password);
            header("location: /admin/login.php");
            exit;
        }
    }
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Admin Register - PHPQuiz</title>
    <link rel="stylesheet" type="text/css" href="/style.css" />
</head>
<body>
    <?php
        if (empty($_POST) || !empty($errors)) { ?>
            <div class="login-box">
                <h1>Registreer</h1>
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
                    <input type="submit" value="Registreer" />
                </form>
            </div>
    <?php } ?>
</body>
</html>