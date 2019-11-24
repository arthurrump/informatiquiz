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

    if (empty($username)) {
        $errors[] = "Vul een gebruikersnaam in.";
    }
    if (empty($password)) {
        $errors[] = "Vul een wachtwoord in.";
    }

    if (empty($errors)) {
        //  Check if username was already in the database
        if (create_user($username, $password) === -1) {
            $errors[] = "Gebruikersnaam bestaat al.";
        } else {
            header("location: /admin/login.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Admin Register - Informatiquiz</title>

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

        <h2>Registreer</h2>
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

        <input class="uk-button uk-button-primary" type="submit" value="Registreer"/>
    </form>
</div>
</body>
</html>