<?php
session_start();
include("../helpers/session.php");
include("../helpers/db.php");

if (!loggedin()) {
    header("location: /admin/login.php");
    exit;
}

$quiz_id = $_GET["id"];
if (!($title = get_quiz_title($quiz_id))) {
    http_response_code(404);
    echo "This quiz doesn't exist.";
    exit;
}

$errors = array();
if (!empty($_POST)) {
    $question = $_POST["question"];
    $correct = $_POST["correct"];

    if (empty($question)) {
        $errors[] = "Vul een vraag in.";
    }
    if (empty($correct)) {
        $errors[] = "Kies het juiste antwoord.";
    }


    if (empty($errors)) {

        if ($_POST["type"] === "Voeg meerkeuze vraag toe") {
            // Mulitple choice
            $answers = array($_POST["answer1"], $_POST["answer2"], $_POST["answer3"], $_POST["answer4"]);

            $q = array(
                "type" => "mc",
                "question" => $question,
                "answers" => $answers,
                "correct" => $correct);
            create_question_for_quiz($quiz_id, json_encode($q));
        } else {
            // HTML question
            $q = array(
                "type" => "html",
                "question" => $question,
                "correct" => $correct);
            create_question_for_quiz($quiz_id, json_encode($q));
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Admin - Informatiquiz</title>
    <link rel="stylesheet" type="text/css" href="/style.css"/>
</head>
<body>
<h1><?php echo $title; ?></h1>
<form method="POST" action="quizrun.php">
    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id ?>"/>
    <input type="submit" value="Start quiz"/>
</form>
<?php foreach (get_questions_for_quiz($quiz_id) as $quiz) {
    $id = $quiz["id"];
    $question = $quiz["question"];
    echo htmlspecialchars($question) . "<br/>";
} ?>

<h2>Nieuwe vraag</h2>


<fieldset>
    <legend>Meerkeuze vraag</legend>
    <form method="POST">

        <ul class="errors">
            <?php foreach ($errors as $err) {
                echo "<li>$err</li>";
            } ?>
        </ul>


        <fieldset>
            <legend>Vraag</legend>
            <textarea name="question"></textarea>
        </fieldset>
        <fieldset>
            <legend>Antwoordmogelijkheden</legend>
            <textarea name="answer1"></textarea>
            <textarea name="answer2"></textarea>
            <textarea name="answer3"></textarea>
            <textarea name="answer4"></textarea>
        </fieldset>
        <fieldset>
            <legend>Goede antwoord</legend>
            <input type="radio" name="correct" value="1">1</input>
            <input type="radio" name="correct" value="2">2</input>
            <input type="radio" name="correct" value="3">3</input>
            <input type="radio" name="correct" value="4">4</input>
        </fieldset>
        <input type="submit" name="type" value="Voeg meerkeuze vraag toe"/>
    </form>

</fieldset>
<br>

<!-- Question where student has to answer with HTML code -->
<fieldset>
    <legend>Open vraag (HTML)</legend>
    <form method="POST">

        <fieldset>
            <legend>Vraag</legend>
            <textarea name="question"></textarea>
        </fieldset>
        <fieldset>
            <legend>Goede HTML antwoord</legend>
            <textarea name="correct"></textarea>
        </fieldset>
        <input type="submit" name="type" value="Voeg open vraag (HTML) toe"/>
    </form>
</fieldset>

</body>
</html>