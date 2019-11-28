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
        // Multiple choice, with 2+ answer options
        switch ($_POST["type"]) {
            case "closed":
                $q = array(
                    "type" => "mc",
                    "question" => $question,
                    "answers" => $_POST['answer'],
                    "correct" => $correct);
                break;

            case "open_html":
                $q = array(
                    "type" => "html",
                    "question" => $question,
                    "correct" => $correct);
                break;

            case "open_css":
                $q = array(
                    "type" => "css",
                    "question" => $question,
                    "correct" => $correct);
                break;
        }

        create_question_for_quiz($quiz_id, json_encode($q));
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Admin - Informatiquiz</title>
    <script type="text/javascript" src="js/quiz.js"></script>

    <?php include '../helpers/head.php' ?>
</head>
<body>
<h1><?php echo $title; ?></h1>
<form method="POST" action="quizrun.php">
    <input type="hidden" name="quiz_id" value="<?php echo $quiz_id ?>"/>
    <input type="submit" class="uk-button uk-button-primary uk-border-rounded" value="Start quiz"/>
</form>
<?php foreach (get_questions_for_quiz($quiz_id) as $quiz) {
    $id = $quiz["id"];
    $question = $quiz["question"];
    echo htmlspecialchars($question) . "<br/>";
} ?>

<h2>Nieuwe vraag</h2>

<ul class="errors">
    <?php foreach ($errors as $err) { ?>
        <div class="uk-alert-danger" uk-alert>
            <a class="uk-alert-close" uk-close></a>
            <p><?php echo $err ?></p>
        </div>
    <?php } ?>
</ul>

<ul uk-tab>
    <li><a href="#">Meerkeuze</a></li>
    <li><a href="#">Open HTML</a></li>
    <li><a href="#">Open CSS</a></li>
</ul>

<ul class="uk-switcher uk-margin uk-background-muted">
    <li>
        <form method="POST" class="uk-padding">

            <div class="uk-margin">
                <label class="uk-form-label" for="question">Vraag</label>
                <textarea class="uk-textarea" id="question" rows="3" placeholder="Type hier je vraag"
                          name="question" autofocus></textarea>
            </div>

            <div class="uk-padding uk-card-default uk-margin">
                <legend class="uk-legend">Opties</legend>

                <div class="answer uk-margin uk-grid-small" uk-grid>
                    <input type="radio" name="correct" value="1" class="uk-radio uk-margin-left">
                    <div class="uk-width-1-2@s">
                        <textarea class="uk-textarea" rows="1" spellcheck="true" name="answer[]"></textarea>
                    </div>
                </div>

                <div class="answer uk-margin uk-grid-small" uk-grid>
                    <input type="radio" name="correct" value="2" class="uk-radio uk-margin-left">
                    <div class="uk-width-1-2@s">
                        <textarea class="uk-textarea" rows="1" spellcheck="true" name="answer[]"></textarea>
                    </div>
                </div>

                <button class="uk-button uk-button-primary" type="button" onclick="addOption(this)">
                    <span uk-icon="plus"></span>
                    Optie toevoegen
                </button>
            </div>

            <button class="uk-width-1-1 uk-button uk-button-primary uk-button-large"
                    type="submit" name="type" value="closed"> Voeg meerkeuze vraag toe
            </button>
        </form>
    </li>
    <li>
        <!-- Question where student has to answer with HTML code -->
        <form method="POST" class="uk-padding">

            <label class="uk-form-label" for="question_html">Vraag</label>
            <textarea class="uk-textarea" id="question_html" rows="3" placeholder="Type hier je vraag" name="question">
            </textarea>

            <div class="uk-margin">
                <label class="uk-form-label" for="answer_html">Correcte HTML antwoord
                    (<a href="https://www.w3schools.com/XML/schema_intro.asp">XML validatie schema</a>)</label>
                <textarea class="uk-textarea" id="answer_html" rows="10" name="correct">
<&quest;xml version="1.0"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified">

    <!-- Typ hier je validatie van HTML-elementen -->

</xs:schema></textarea>
            </div>

            <button class="uk-width-1-1 uk-button uk-button-primary uk-button-large"
                    type="submit" name="type" value="open_html"> Voeg open HTML vraag toe
            </button>
        </form>
    </li>
    <!-- Question where student has to answer with CSS code -->
    <li>
        <form method="POST" class="uk-padding">

            <label class="uk-form-label" for="question_css">Vraag</label>
            <textarea class="uk-textarea" id="question_css" rows="3" placeholder="Type hier je vraag"
                      name="question"></textarea>

            <div class="uk-margin">
                <label class="uk-form-label" for="answer_css">Correcte CSS antwoord (antwoorden worden alleen
                    gecontroleerd
                    op validiteit)</label>
                <textarea class="uk-textarea" id="answer_css" rows="10" name="correct"
                          placeholder="Typ hier het juiste CSS antwoord"></textarea>
            </div>

            <button class="uk-width-1-1 uk-button uk-button-primary uk-button-large"
                    type="submit" name="type" value="open_css"> Voeg open CSS vraag toe
            </button>
        </form>
    </li>
</ul>
</body>
</html>