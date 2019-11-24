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
    <!--  TODO   <meta http-equiv="Content-Security-Policy" content="default-src 'self'">-->
    <link rel="stylesheet" type="text/css" href="/style.css"/>

    <!-- UIkit CSS -->
    <link rel="stylesheet" href="../uikit-3.2.3/css/uikit.min.css"/>

    <!-- UIkit JS -->
    <script src="../uikit-3.2.3/js/uikit.min.js"></script>
    <script src="../uikit-3.2.3/js/uikit-icons.min.js"></script>
</head>
<body class="admin-run">
<h1 class="uk-heading-medium">Quiz Code: <?php echo $quizrun["access_code"]; ?></h1>

<?php if ($quizrun["active"]) {
    if (!!($current_question = get_question($quizrun["current_question"]))) {
        $question = json_decode($current_question); ?>
        <div class="uk-margin uk-card uk-card-default uk-padding">
            <h3 class="uk-card-title">
                <?php echo "Vraag " . $quizrun["current_question"] . ": " . $parsedown->text($question->question); ?>
            </h3>
            <ol type="a">
                <?php
                if ($question->type === "mc") {
                    foreach ($question->answers as $answer) { ?>
                        <li class="uk-text-large">
                            <?php echo $parsedown->line($answer); ?>
                        </li>
                    <?php }
                }
                ?>
            </ol>

            <!-- JavaScript is currently completely disabled, so this is a work around to reload te page -->
            <form method="post">
                <input type="submit" class="uk-button uk-button-primary" value="Toon resultaten">
            </form>

        </div>
    <?php }
    if (!!($answers = get_answers_for_quizrun_question($quizrun["id"], $quizrun["current_question"]))) {
        echo "<h2>Resultaten (" . array_sum(array_map(function ($a) {
                return $a["count"];
            }, $answers)) . ")</h2>";

        // Check if mc:
        if ($question->type === "mc") {
            echo "<ul class='uk-list'>";
            foreach ($answers as $a) { ?>
                <li>
                    <?php echo chr(intval($a["answer"]) + 97) . "(" . $a["count"] . ")" ?> :
                    <progress class="uk-progress uk-animation-slide-left" value="<?php echo $a["count"] ?>"
                              max="<?php echo sizeof($answers) ?>"><?php echo sizeof($answers) ?>">
                    </progress>
                </li>
            <?php }
            echo "</ul>";
        } elseif ($question->type === "html") { ?>

            <!-- Filtering results on correctness: -->
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

                    <?php libxml_use_internal_errors(true);

                    foreach ($answers as $a) {
                        // Check here for correctness of the answer; yes/valid/no
                        $doc = new DOMDocument();
                        $doc->loadXML($a["answer"]);

                        // TODO Check for valid xml scheme at question submission
                        if (libxml_get_errors()) {
                            $correct = "no";
                        } elseif ($doc->schemaValidateSource($question->correct)) {
                            $correct = "yes";
                        } else {
                            $correct = "valid";
                        }
                        ?>
                        <li data-correct="<?php echo $correct; ?>">
                            <div class="uk-card uk-card-default uk-card-body">
                                <h3 class="uk-card-title">HTML antwoord van ...</h3>

                                <div class="uk-card-default uk-padding-small">
                                    <code>
                                        <?php echo htmlspecialchars($a["answer"]); ?>
                                    </code>
                                </div>

                                <div class="uk-card-default uk-padding-small">
                                    <?php
                                    if ($errors = libxml_get_errors()) { ?>
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
        <?php }
    }
    ?>

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