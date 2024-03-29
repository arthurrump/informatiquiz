<?php
session_start();
include("helpers/db.php");

$errors = array();
if (!empty($_POST)) {
    if (!($quiz_run = get_active_quizrun_by_code($_POST["quiz"]))) {
        $errors[] = "Quiz code " . htmlspecialchars($_POST["quiz"]) . " bestaat niet (meer).";
    } else {
        $_SESSION["name"] = $_POST["name"];
        header("location: /quiz.php?quiz=" . $_POST["quiz"]);
        exit;
    }
}

if (!empty($_GET["err"])) {
    $errors[] = "Quiz code " . htmlspecialchars($_GET["err"]) . " bestaat niet (meer).";
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Informatiquiz</title>

    <?php include 'helpers/head.php' ?>
</head>
<body>
<div class="uk-position-center">

    <h1 class="uk-text-center uk-heading-medium">Informatiquiz</h1>

    <form class="uk-padding-large uk-background-muted" method="POST">

        <?php include 'helpers/output_errors.php'?>

        <h2>Doe mee met een quiz!</h2>

        <div class="uk-margin">
            <label class="uk-form-label" for="quiz_code">Quiz code</label>
            <div class="uk-form-controls">
                <input class="uk-input" id="quiz_code" type="number" name="quiz" min="10000" max="99999" required
                       autofocus/>
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label" for="name">Voornaam</label>
            <div class="uk-form-controls">
                <input class="uk-input" id="name" type="text" name="name" required/>
            </div>
        </div>

        <input class="uk-button uk-button-primary" type="submit" value="Doe mee"/>
    </form>
</div>
</body>
</html>