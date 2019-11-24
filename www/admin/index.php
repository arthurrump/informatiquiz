<?php
session_start();
include("../helpers/session.php");
include("../helpers/db.php");

if (!loggedin()) {
    header("location: /admin/login.php");
    exit;
}

$errors = array();
if (!empty($_POST)) {
    $title = $_POST["title"];

    if (empty($title)) {
        $errors = "Voer een titel in.";
    }

    if (empty($errors)) {
        $id = create_quiz_for_user(user_id(), $title);
        header("location: /admin/quiz.php?id=$id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Admin - Informatiquiz</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- UIkit CSS -->
    <link rel="stylesheet" href="../uikit-3.2.3/css/uikit.min.css"/>

    <!-- UIkit JS -->
    <script src="../uikit-3.2.3/js/uikit.min.js"></script>
    <script src="../uikit-3.2.3/js/uikit-icons.min.js"></script>
</head>
<body>
<div class="uk-position-center">

    <h1>Mijn Quizes</h1>
    <ul class="uk-list uk-list-striped uk-list-large">
        <?php foreach (get_quizes_for_user(user_id()) as $quiz) { ?>
            <li>
                <h3 class="uk-card-title">
                    <a href="quiz.php?id=<?php echo $quiz['id'] ?>"><?php echo $quiz['title'] ?></a>
                </h3>
            </li>
        <?php } ?>
    </ul>

    <form class="uk-background-muted uk-padding-small uk-form-horizontal" method="POST">

        <fieldset class="uk-fieldset">
            <legend class="uk-legend">Nieuwe quiz:</legend>

            <?php foreach ($errors as $err) { ?>
                <div class="uk-alert-danger uk-animation-shake" uk-alert>
                    <a class="uk-alert-close" uk-close></a>
                    <?php echo "<b>$err</b>"; ?>
                </div>
            <?php } ?>

            <div class="uk-margin">
                <label class="uk-form-label" for="title">Titel:</label>
                <div class="uk-form-controls">
                    <input class="uk-input" id="title" type="text" name="title" required>
                </div>
            </div>

            <input class="uk-button uk-button-primary" type="submit" value="Maak quiz"/>
        </fieldset>
    </form>

    <a class="uk-button uk-button-default uk-margin-small uk-align-right" href="logout.php">Log uit</a>

</div>
</body>
</html>