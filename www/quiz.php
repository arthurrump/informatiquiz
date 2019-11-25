<?php
session_start();
include("helpers/db.php");
include("helpers/Parsedown.php");

if (!($quiz_run = get_active_quizrun_by_code($_GET["quiz"]))) {
    header("location: /?err=" . $_GET['quiz']);
    exit;
}

$quizrun_id = $quiz_run["id"];

if (!empty($_POST)) {
    $question_id = $_POST["question"];
    $answer = $_POST["answer"];
    $quizrun_id = $_POST["quiz"];
    $quizcode = $_POST["quizcode"];

    add_answer($quizrun_id, $question_id, session_id(), $answer);
    $_SESSION["answered-$quizrun_id"] = $question_id;

    header("location: /quiz.php?quiz=$quizcode");
    exit;
}

$answered = $_SESSION["answered-$quizrun_id"];
if ($answered == $quiz_run["current_question"]) {
    header("refresh: 2");
}

$parsedown = new Parsedown();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Informatiquiz</title>

    <!-- UIkit CSS -->
    <link rel="stylesheet" href="../uikit-3.2.3/css/uikit.min.css"/>

    <!-- UIkit JS -->
    <script src="../uikit-3.2.3/js/uikit.min.js"></script>
    <script src="../uikit-3.2.3/js/uikit-icons.min.js"></script>
</head>
<body>
<h1 class="uk-padding-small">Informatiquiz</h1>

<?php if ($quiz_run["current_question"] == 0) { ?>
    <p class="uk-padding-small">De quiz is nog niet gestart. Vernieuw <span uk-icon="icon: refresh"></span> de pagina
        als de quiz gestart is.</p>

<?php } else if ($answered == $quiz_run["current_question"]) { ?>
    <p class='uk-padding-small'>Je hebt de vraag beantwoord!</p>

<?php } else {
    $question = json_decode(get_question($quiz_run["current_question"]));
    ?>
    <div class="uk-card uk-card-default uk-card-body uk-box-shadow-large">
        <h3 class="uk-card-title">Vraag: </h3>
        <h3 class="uk-text-bold"><?php echo $parsedown->text($question->question) ?></h3>

        <form method="POST">
            <?php
            echo '<input type="hidden" name="question" value="' . $quiz_run["current_question"] . '">';
            echo '<input type="hidden" name="quiz" value="' . $quiz_run["id"] . "\" />";
            echo '<input type="hidden" name="quizcode" value="' . $quiz_run["access_code"] . '">';

            // Check for type of question (mc or html)
            if ($question->type === "mc") { ?>
                <div class="uk-form-controls uk-form-controls-text uk-padding-small">
                    <?php
                    for ($i = 0; $i < sizeof($question->answers); $i++) {
                        echo '<label class="uk-margin-small uk-text-large"><input class="uk-radio" type="radio" name="answer" value="$i" required>' . $parsedown->line($question->answers[$i]) . "</label><br>";
                    } ?>
                </div>
                <?php
            } else if ($question->type === "html") { ?>

                <div class="uk-margin">
                    <label class="uk-form-label" for="answer">Jouw HTML antwoord:</label>
                    <textarea class="uk-textarea" id="answer" rows="5" name="answer"></textarea>
                </div>
            <?php } ?>

            <input class="uk-width-1-1 uk-button uk-button-primary uk-button-large" type="submit" name="type"
                   value="Beantwoorden"/>
        </form>
    </div>

<?php } ?>
</body>
</html>