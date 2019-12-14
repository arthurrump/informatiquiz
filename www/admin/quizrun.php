<?php
session_start();
include("../helpers/session.php");
include("../helpers/db.php");
include("../helpers/Parsedown.php");
include("../helpers/check_code.php");


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
    <!--  TODO   <meta http-equiv="Content-Security-Policy" content="default-src 'self'">-->

    <?php include '../helpers/head.php' ?>
</head>
<body class="admin-run">
<h1 class="uk-heading-medium">Quiz Code: <?php echo $quizrun["access_code"]; ?></h1>

<?php
// Check if the quiz is active
if ($quizrun["active"]) {

    // Check if there is a question to display
    if (!!($current_question = get_question($quizrun["current_question"]))) {
        $question = json_decode($current_question); ?>
        <div class="uk-margin uk-card uk-card-default uk-padding">
            <h3 class="uk-card-title">
                <?php echo "Vraag " . $quizrun["current_question"] . ": " . $parsedown->text($question->question); ?>
            </h3>

            <?php if ($question->type === "closed") { ?>
                <ol type="a">
                    <?php foreach ($question->answers as $answer) { ?>
                        <li class="uk-text-large">
                            <?php echo $parsedown->line($answer); ?>
                        </li>
                    <?php } ?>
                </ol>
            <?php } ?>

            <form method="post">
                <input type="submit" class="uk-button uk-button-primary" value="Toon resultaten">
            </form>
        </div>
    <?php }


    // Check if there are results to show
    if (!!($answers = get_answers_for_quizrun_question($quizrun["id"], $quizrun["current_question"]))) {
        echo "<h2>Resultaten (" . array_sum(array_map(function ($a) {
                return $a["count"];
            }, $answers)) . ")</h2>";

        // Check what kind of question is asked
        switch ($question->type) {
            // Multiple choice question
            case "closed": ?>
                <ul class='uk-list'>
                    <?php foreach ($answers as $a) { ?>
                        <li>
                            <?php echo chr(intval($a["answer"]) + 97) . "(" . $a["count"] . ")" ?> :
                            <progress class="uk-progress uk-animation-slide-left" value="<?php echo $a["count"] ?>"
                                      max="<?php echo sizeof($answers) ?>"><?php echo sizeof($answers) ?>">
                            </progress>
                        </li>
                    <?php } ?>
                </ul>

                <?php break;

            // Open HTML question
            case "open_html": ?>

                <div uk-filter="target: .js-filter">
                    <div class="uk-grid-small uk-flex-middle" uk-grid>
                        <div class="uk-width-expand">

                            <div class="uk-grid-small uk-grid-divider uk-child-width-auto" uk-grid>
                                <div>
                                    <ul class="uk-subnav uk-subnav-pill" uk-margin>
                                        <li class="uk-active" uk-filter-control><a href="#">Alle</a></li>
                                    </ul>
                                </div>
                                <div>
                                    <ul class="uk-subnav uk-subnav-pill" uk-margin>
                                        <li uk-filter-control="[data-correct='yes']"><a href="#">Goed</a></li>
                                        <li uk-filter-control="[data-correct='valid']"><a href="#">Valide HTML, maar
                                                verkeerd</a></li>
                                        <li uk-filter-control="[data-correct='no']"><a href="#">Fout</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- The list of all the answers -->
                    <ul class="js-filter uk-child-width-auto" uk-grid>
                        <?php foreach ($answers as $a) {
                            // Check if answer is correct, this might generate libxml errors ?>
                            <li data-correct="<?php echo is_correct_html_answer($question->xsd, $a["answer"]); ?>">
                                <div class="uk-card uk-card-default uk-card-body">
                                    <h3 class="uk-card-title">HTML antwoord van ...</h3>

                                    <div class="uk-card-default uk-padding-small">
                                        <pre><?php echo htmlspecialchars($a["answer"]); ?></pre>
                                    </div>

                                    <div class="uk-card-default uk-padding-small">
                                        <?php if ($errors = libxml_get_errors()) { ?>
                                            Verkeerd:
                                            <ul class="uk-list uk-list-divider uk-text-danger">
                                                <?php foreach ($errors as $error) {
                                                    echo "<li>" . htmlspecialchars($error->message) . "</li>";
                                                }
                                                libxml_clear_errors();
                                                ?>
                                            </ul>
                                        <?php } else {
                                            echo $a["answer"];
                                        } ?>
                                    </div>
                                </div>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
                <?php break;

            // Open CSS question
            case "open_css": ?>

                <div uk-filter="target: .js-filter">
                    <div class="uk-grid-small uk-flex-middle" uk-grid>
                        <div class="uk-width-expand">

                            <div class="uk-grid-small uk-grid-divider uk-child-width-auto" uk-grid>
                                <div>
                                    <ul class="uk-subnav uk-subnav-pill" uk-margin>
                                        <li class="uk-active" uk-filter-control><a href="#">Alle</a></li>
                                    </ul>
                                </div>
                                <div>
                                    <ul class="uk-subnav uk-subnav-pill" uk-margin>
                                        <!--                                        <li uk-filter-control="[data-correct='yes']"><a href="#">Goed</a></li>-->
                                        <li uk-filter-control="[data-correct='valid']"><a href="#">Valide CSS</a></li>
                                        <li uk-filter-control="[data-correct='no']"><a href="#">Fout(en) in CSS</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- The list of all the answers -->
                    <ul class="js-filter uk-child-width-auto" uk-grid>
                        <?php foreach ($answers as $a) {
                            // Check if CSS answer is correct, collect list with errors
                            // TODO Display these errors nicely
                            $errors = is_valid_css($a["answer"]);

                            ?>
                            <li data-correct="<?php echo (empty($errors) ? "valid" : "no"); ?>">
                                <div class="uk-card uk-card-default uk-card-body">
                                    <h3 class="uk-card-title">CSS antwoord van ...</h3>

                                    <div class="uk-card-default uk-padding-small">
                                        <pre><?php echo htmlspecialchars($a["answer"]); ?></pre>
                                    </div>

<!--                                    <div class="uk-card-default uk-padding-small">
                                        <?php /*if (!empty($errors)) { */?>
                                            Verkeerd:
                                            <ul class="uk-list uk-list-divider uk-text-danger">
                                                <?php /*foreach ($errors as $error) {
                                                    echo "<li>" . htmlspecialchars($error) . "</li>";
                                                }
                                                */?>
                                            </ul>
                                        <?php /*} else {
                                        } */?>
                                    </div>
-->
                                </div>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
                <?php break;

            // Open PHP question
            case "open_php": ?>

                <div uk-filter="target: .js-filter">
                    <div class="uk-grid-small uk-flex-middle" uk-grid>
                        <div class="uk-width-expand">

                            <div class="uk-grid-small uk-grid-divider uk-child-width-auto" uk-grid>
                                <div>
                                    <ul class="uk-subnav uk-subnav-pill" uk-margin>
                                        <li class="uk-active" uk-filter-control><a href="#">Alle</a></li>
                                    </ul>
                                </div>
                                <div>
                                    <ul class="uk-subnav uk-subnav-pill" uk-margin>
                                        <li uk-filter-control="[data-correct='correct']"><a href="#">Goed</a></li>
                                        <li uk-filter-control="[data-correct='no-error']"><a href="#">Geen errors</a></li>
                                        <li uk-filter-control="[data-correct='compile-error']"><a href="#">Compile error</a></li>
                                        <li uk-filter-control="[data-correct='runtime-error']"><a href="#">Runtime error</a></li>
                                        <li uk-filter-control="[data-correct='unknown']"><a href="#">Onbekend</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- The list of all the answers -->
                    <ul class="js-filter uk-child-width-auto" uk-grid>
                        <?php foreach ($answers as $a) {
                            $a = json_decode($a["answer"]);
                            if (!empty($a->result)) {
                                if (!empty($a->result->compile_error))
                                    $correct = "compile-error";
                                elseif (!empty($a->result->runtime_error))
                                    $correct = "runtime-error";
                                elseif (trim($a->result->output) === trim($question->expected_output))
                                    $correct = "correct";
                                else
                                    $correct = "no-error";
                            } else {
                                $correct = "unknown"; 
                            } ?>    
                            <li data-correct="<?php echo $correct; ?>">
                                <div class="uk-card uk-card-default uk-card-body">
                                    <h3 class="uk-card-title">Antwoord 
                                        <?php switch ($correct) {
                                            case "correct": echo "(Goed)"; break;
                                            case "no-error": echo "(Geen errors)"; break;
                                            case "compile-error": echo "(Compile error)"; break;
                                            case "runtime-error": echo "(Runtime error)"; break;
                                        } ?>
                                    </h3>

                                    <div class="uk-card-default uk-padding-small">
                                        <pre><?php echo htmlspecialchars($a->answer); ?></pre>
                                    </div>

                                    <div class="uk-card-default uk-padding-small">
                                        <?php if ($correct === "compile-error" || $correct === "runtime-error") { ?>
                                            Error:
                                            <pre class="uk-text-danger"><?php 
                                                echo htmlspecialchars($a->result->compile_error); 
                                                echo htmlspecialchars($a->result->runtime_error); 
                                            ?></pre>
                                        <?php } elseif (!empty($a->result->output)) {
                                            echo $a->result->output;
                                        } ?>
                                    </div>
                                </div>
                            </li>
                            
                        <?php } ?>
                    </ul>
                </div>
                <?php break;
        }
        ?>

    <?php } ?>

    <form method="POST" action="/admin/quizrun_next.php" class="uk-margin-small-left">
        <input type="hidden" name="quizrun" value="<?php echo $quizrun_id ?>"/>
        <input type="submit" class="uk-button uk-button-primary"
               value="<?php echo ($current_question) ? "Volgende vraag" : "Start Quiz"; ?>"/>
    </form>

<?php } else { ?>
    <h1>Einde</h1>
<?php } ?>
</body>
</html>