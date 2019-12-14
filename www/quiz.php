<?php
session_start();
include("helpers/db.php");
include("helpers/Parsedown.php");

if (!($quiz_run = get_active_quizrun_by_code($_GET["quiz"]))) {
    header("location: /?err=" . $_GET['quiz']);
    exit;
}

if (empty($_SESSION["name"])) {
    header("location: /");
    exit;
}

$quizrun_id = $quiz_run["id"];
$question = json_decode(get_question($quiz_run["current_question"]));

if (!empty($_POST)) {
    $question_id = $_POST["question"];
    $answer = $_POST["answer"];
    $quizrun_id = $_POST["quiz"];
    $quizcode = $_POST["quizcode"];

    if ($question->type === "open_php") {
        function replace_hackerearth_paths($message) {
            return preg_replace("/\\/hackerearth\\/.*\\.php/", "/index.php", $message);
        }
        $hackerearth_api_secret = file_get_contents("/run/secrets/hackerearth_api_secret");

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.hackerearth.com/v3/code/run/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => array(
                "client_secret" => $hackerearth_api_secret,
                "lang" => "PHP",
                "source" => $answer)
        ));

        $response = curl_exec($curl);
        if (curl_error($curl)) {
            $answer = json_encode(array("answer" => $answer));
        } else {
            $result = json_decode($response);

            $php_result = array();
            if ($result->compile_status !== "OK") {
                $php_result["compile_error"] = replace_hackerearth_paths($result->compile_status);
            } else {
                if (!empty($result->run_status->stderr))
                    $php_result["runtime_error"] = replace_hackerearth_paths($result->run_status->stderr);
                $php_result["output"] = $result->run_status->output;
            }
                
            $answer = json_encode(array(
                "answer" => $answer,
                "result" => $php_result
            ));
        }

        curl_close($curl);
    }

    $user = str_replace("::", ":", $_SESSION["name"]) . "::" . session_id();
    add_answer($quizrun_id, $question_id, $user, $answer);
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

    <?php include 'helpers/head.php' ?>
</head>
<body>
<h1 class="uk-padding-small">Informatiquiz</h1>

<?php if ($quiz_run["current_question"] == 0) { ?>
    <p class="uk-padding-small">De quiz is nog niet gestart. Vernieuw <span uk-icon="icon: refresh"></span> de pagina
        als de quiz gestart is.</p>

<?php } else if ($answered == $quiz_run["current_question"]) { ?>
    <p class='uk-padding-small'>Je hebt de vraag beantwoord!</p>

<?php } else { ?>
    <div class="uk-card uk-card-default uk-card-body uk-box-shadow-large">
        <h3 class="uk-card-title">Vraag: </h3>
        <h3 class="uk-text-bold"><?php echo $parsedown->text($question->question) ?></h3>

        <form method="POST">
            <?php
            echo '<input type="hidden" name="question" value="' . $quiz_run["current_question"] . '">';
            echo '<input type="hidden" name="quiz" value="' . $quiz_run["id"] . "\" />";
            echo '<input type="hidden" name="quizcode" value="' . $quiz_run["access_code"] . '">';

            // Check for type of question
            switch ($type = $question->type) {
                case "closed": ?>
                    <div class="uk-form-controls uk-form-controls-text uk-padding-small">
                        <?php
                        for ($i = 0; $i < sizeof($question->answers); $i++) {
                            echo '<label class="uk-margin-small uk-text-large"><input class="uk-radio" type="radio" name="answer" value="' . $i . '" required>' . $parsedown->line($question->answers[$i]) . "</label><br>";
                        } ?>
                    </div>
                    <?php
                    break;

                case "open_html": ?>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="answer">Jouw HTML antwoord:</label>
                        <textarea class="uk-textarea" id="answer" rows="5" name="answer"></textarea>
                    </div>
                    <?php break;

                case "open_css": ?>
                    <div class="uk-margin">
                        <label class="uk-form-label" for="answer">Jouw CSS antwoord:</label>
                        <textarea class="uk-textarea" id="answer" rows="5" name="answer"></textarea>
                    </div>
                    <?php break;

                case "open_php": ?>
                    <div class="uk-margin">
                        <lbael class="uk-form-label" for="answer">Pas de PHP code aan:</label>
                        <textarea class="uk-textarea" id="answer" rows="5" name="answer"><?php echo $question->given_code; ?></textarea>
                    </div>
                    <?php break;
            } ?>


            <input class="uk-width-1-1 uk-button uk-button-primary uk-button-large" type="submit" name="type"
                   value="Beantwoorden"/>
        </form>
    </div>

<?php } ?>
</body>
</html>