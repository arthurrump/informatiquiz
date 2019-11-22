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
            // Multiple choice, with 2+ answer options
            $q = array(
                "type" => "mc",
                "question" => $question,
                "answers" => $_POST['answer'],
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
    <script type="text/javascript" src="js/quiz.js"></script>

    <!--    <link rel="stylesheet" type="text/css" href="/style.css"/>-->

    <!-- UIkit CSS -->
    <link rel="stylesheet" href="../uikit-3.2.3/css/uikit.min.css"/>

    <!-- UIkit JS -->
    <script src="../uikit-3.2.3/js/uikit.min.js"></script>
    <script src="../uikit-3.2.3/js/uikit-icons.min.js"></script>

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
    </ul>

    <ul class="uk-switcher uk-margin uk-background-muted">
        <li>
            <form method="POST" class="uk-padding">

                <div class="uk-margin">
                    <label class="uk-form-label" for="question">Vraag</label>
                    <textarea class="uk-textarea" id="question" rows="3" placeholder="Type hier je vraag"
                              name="question"></textarea>
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
                <input class="uk-width-1-1 uk-button uk-button-primary uk-button-large" type="submit" name="type"
                       value="Voeg meerkeuze vraag toe"/>
            </form>
        </li>
        <li>
            <!-- Question where student has to answer with HTML code -->
            <form method="POST" class="uk-padding">

                <div class="">
                    <label class="uk-form-label" for="question">Vraag</label>
                    <textarea class="uk-textarea" id="question" rows="3" placeholder="Type hier je vraag"
                              name="question"></textarea>
                </div>

                <div class="uk-margin">
                    <label class="uk-form-label" for="question">Goede HTML antwoord:</label>
                    <textarea class="uk-textarea" id="question" rows="5" placeholder="Type hier het HTML antwoord"
                              name="correct"></textarea>
                </div>

                <input class="uk-width-1-1 uk-button uk-button-primary uk-button-large" type="submit" name="type"
                       value="Voeg open HTML vraag toe"/>
            </form>
        </li>
    </ul>
</body>
</html>