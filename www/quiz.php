<?php
    session_start();
    include("helpers/db.php");
    
    if (!($quiz_run = get_active_quizrun_by_code($_GET["quiz"]))) {
        http_response_code(404);
        echo "This quiz doesn't exist.";
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
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <title>PHPQuiz</title>
    <link rel="stylesheet" type="text/css" href="/style.css" />
</head>
<body>
    <h1>PHPQuiz</h1>

    <?php
        if ($quiz_run["current_question"] == 0) {
            echo "<p>De quiz is nog niet gestart. Vernieuw de pagina als de quiz gestart is.</p>";
        } else if ($_GET["answered"] == $quiz_run["current_question"]) {
            echo "<p>Je hebt de vraag beantwoord!</p>";
        } else {
            $current_question = get_question($quiz_run["current_question"]);
            $question = json_decode($current_question); ?>
            <div class="question">
                <p><?php echo $question->question ?></p>
                <form method="POST">
                    <?php 
                        echo "<input type=\"hidden\" name=\"question\" value=\"" . $quiz_run["current_question"] . "\" />";
                        echo "<input type=\"hidden\" name=\"quiz\" value=\"" . $quiz_run["id"] . "\" />";
                        echo "<input type=\"hidden\" name=\"quizcode\" value=\"" . $quiz_run["access_code"] . "\" />";
                        for ($i=0; $i < count($question->answers); $i++) { 
                            echo "<input type=\"radio\" name=\"answer\" value=\"$i\">" . $question->answers[$i] . "</input>";    
                        } 
                    ?>
                    <input type="submit" value="Beantwoorden" />
                </form>
            </div>
    <?php } ?>
</body>
</html>