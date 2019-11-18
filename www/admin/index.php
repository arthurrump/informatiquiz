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

        if (empty($title)) { $errors = "Voer een titel in."; }

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
    <title>Admin - PHPQuiz</title>
    <link rel="stylesheet" type="text/css" href="/style.css" />
</head>
<body>
    <h1>Quizes</h1>
    <ul>
        <?php foreach (get_quizes_for_user(user_id()) as $quiz) {
            $id = $quiz["id"];
            $title = $quiz["title"];
            echo "<li><a href=\"/admin/quiz.php?id=$id\">$title</a></li>"; 
        } ?>
    </ul>
    <form method="POST">
        <h2>Nieuwe quiz</h2>
        <ul class="errors">
            <?php foreach ($errors as $err) {
                echo "<li>$err</li>";
            } ?>
        </ul>
        <label for="title">Titel</label>
        <input type="text" name="title" />
        <input type="submit" value="Maak quiz" />
    </form>
</body>
</html>