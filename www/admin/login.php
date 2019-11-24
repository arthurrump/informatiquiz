<?php
session_start();
include("../helpers/db.php");
include("../helpers/session.php");

$errors = array();
if (!empty($_POST)) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    if (empty($username)) {
        $errors[] = "Vul een gebruikersnaam in.";
    }
    if (empty($password)) {
        $errors[] = "Vul een wachtwoord in.";
    }

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
    <title>Admin Login - Informatiquiz</title>

    <!-- UIkit CSS -->
    <link rel="stylesheet" href="../uikit-3.2.3/css/uikit.min.css"/>

    <!-- UIkit JS -->
    <script src="../uikit-3.2.3/js/uikit.min.js"></script>
    <script src="../uikit-3.2.3/js/uikit-icons.min.js"></script>
</head>
<body>
<div class="uk-position-center">

    <h1 class="uk-text-center uk-heading-medium">Informatiquiz</h1>

    <form class="uk-padding-large uk-background-muted" method="post">

        <h2>Log in</h2>
        <?php foreach ($errors as $err) { ?>
            <div class="uk-alert-danger uk-animation-shake" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <?php echo "<b>$err</b>"; ?>
            </div>
        <?php } ?>

        <div class="uk-margin">
            <label class="uk-form-label" for="username">Gebruikersnaam</label>
            <div class="uk-form-controls">
                <input class="uk-input uk-width-medium" type="text" id="username" name="username" autofocus required
                       value="<?php echo $username ?>"/>
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label" for="password">Wachtwoord</label>
            <div class="uk-form-controls">
                <input class="uk-input" id="password" type="password" name="password" required/>
            </div>
        </div>

        <input class="uk-button uk-button-primary" type="submit" value="Log in"/>
        <a class="uk-button uk-button-default uk-align-right" href="register.php">Registreer</a>
    </form>
</div>
</body>
</html>