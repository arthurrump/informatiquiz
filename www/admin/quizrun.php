<?php
session_start();
include("../helpers/session.php");
include("../helpers/db.php");
include("../helpers/Parsedown.php");

if (!loggedin()) {
    header("location: /admin/login.php");
    exit;
}

if (!empty($_POST)) {
    $quiz_id = $_POST["quiz_id"];
    $access_code = random_int(10000, 99999);
    $id = create_quizrun_for_quiz($quiz_id, $access_code, true);
    header("location: /admin/quizrun.php?id=$id");
    exit;
}

$quizrun_id = $_GET["id"];
if (!($quizrun = get_quizrun($quizrun_id))) {
    http_response_code(404);
    echo "This quizrun doesn't exist.";
    exit;
}

$questions = get_questions_for_quizrun($quizrun_id);

$parsedown = new Parsedown();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Admin - Informatiquiz</title>
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'">
    <link rel="stylesheet" type="text/css" href="/style.css"/>

    <!-- UIkit CSS -->
    <link rel="stylesheet" href="../uikit-3.2.3/css/uikit.min.css"/>

    <!-- UIkit JS -->
    <script src="../uikit-3.2.3/js/uikit.min.js"></script>
    <script src="../uikit-3.2.3/js/uikit-icons.min.js"></script>
</head>
<body class="admin-run">
<h1 class="uk-heading-large">Quiz Code: <?php echo $quizrun["access_code"]; ?></h1>
<h1 class="uk-text-large uk-heading-2xlarge">Large</h1>

<div class="uk-alert-danger" uk-alert>
    <a class="uk-alert-close" uk-close></a>
    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt.</p>
</div>


<?php if ($quizrun["active"]) {
    if (!!($current_question = get_question($quizrun["current_question"]))) {
        $question = json_decode($current_question); ?>
        <div class="question">
        <h3>
            <?php echo "Vraag " . $quizrun["current_question"] . ": " . $parsedown->text($question->question); ?>
        </h3>
        <ol>
            <?php
            if ($question->type === "mc") {
                foreach ($question->answers as $answer) {
                    echo "<li>" . $parsedown->line($answer) . "</li>";
                }
            }
            ?>
        </ol>
        </div><?php }
    if (!!($answers = get_answers_for_quizrun_question($quizrun["id"], $quizrun["current_question"]))) {
        echo "<h2>Resultaten</h2>";

        // Check if mc:
        if ($question->type === "mc") {
            echo "<ul>";
            foreach ($answers as $a) {
                echo "<li>" . (intval($a["answer"]) + 1) . ": " . $a["count"] . "</li>";
            }
            echo "</ul>";
        } elseif ($question->type === "html") {
            libxml_use_internal_errors(true);

            foreach ($answers as $a) { ?>
                <fieldset>
                    <legend>Antwoord</legend>
                    HTML Code: <br>
                    <code>
                        <?php echo htmlspecialchars($a["answer"]); ?>
                    </code>
                    <br><br> Browser: <br>
                    <?php
                        $doc = new DOMDocument();
                        $doc->loadHTML($a["answer"]);
                        if (libxml_get_errors()) {
                            echo "Geen valide HTML!!";
                            libxml_clear_errors();
                        } else {
                            echo $a["answer"];
                        }
                    ?>
                </fieldset>
            <?php }
        }
    }
    ?>

    <form method="POST" action="/admin/quizrun_next.php">
        <input type="hidden" name="quizrun" value="<?php echo $quizrun_id ?>"/>
        <input type="submit" value="<?php echo ($current_question) ? "Volgende vraag" : "Start Quiz"; ?>"/>
    </form>
<?php } else { ?>
    <h1>Einde</h1>
<?php } ?>
</body>
</html>