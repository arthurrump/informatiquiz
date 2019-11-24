<?php
session_start();
include("helpers/db.php");
include("helpers/Parsedown.php");

if (!($quiz_run = get_active_quizrun_by_code($_GET["quiz"]))) {
    header("location: /?err=" . $_GET['quiz']);
    exit;
}

if (!empty($_POST)) {
    $question_id = $_POST["question"];
    $answer = $_POST["answer"];
    $quizrun_id = $_POST["quiz"];
    $quizcode = $_POST["quizcode"];

    add_answer($quizrun_id, $question_id, session_id(), $answer);

    header("location: /quiz.php?quiz=$quizcode&answered=$question_id");
    exit;
}

$parsedown = new Parsedown();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Informatiquiz</title>
    <link rel="stylesheet" type="text/css" href="/style.css"/>
</head>
<body>
<h1>Informatiquiz</h1>

<?php
if ($quiz_run["current_question"] == 0) {
    echo "<p>De quiz is nog niet gestart. Vernieuw de pagina als de quiz gestart is.</p>";
} else if ($_GET["answered"] == $quiz_run["current_question"]) {
    echo "<p>Je hebt de vraag beantwoord!</p>";
} else {
    $current_question = get_question($quiz_run["current_question"]);
    $question = json_decode($current_question); ?>
    <div class="question">
        <?php echo $parsedown->text($question->question) ?>
        <form method="POST">
            <?php
            echo "<input type=\"hidden\" name=\"question\" value=\"" . $quiz_run["current_question"] . "\" />";
            echo "<input type=\"hidden\" name=\"quiz\" value=\"" . $quiz_run["id"] . "\" />";
            echo "<input type=\"hidden\" name=\"quizcode\" value=\"" . $quiz_run["access_code"] . "\" />";

            // Check for type of question (mc or html)
            if ($question->type === "mc") {
                for ($i = 0; $i < sizeof($question->answers); $i++) {
                    echo "<p><input type=\"radio\" name=\"answer\" value=\"$i\" />" . $parsedown->line($question->answers[$i]) . "</p>";
                }
            } else if ($question->type === "html") {
                echo '<textarea name="answer" lines="10"></textarea>';
            }
            ?>
            <input type="submit" value="Beantwoorden"/>
        </form>
    </div>
<?php } ?>
</body>
</html>