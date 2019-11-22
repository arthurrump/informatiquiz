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
        echo "<h2>Resultaten (" . sizeof($answers) . ")</h2>";

// Check if mc:
        if ($question->type === "mc") {
            echo "<ul>";
            var_dump($answers);
            foreach ($answers as $a) {
                echo "<li>" . chr(intval($a["answer"]) + 97) . ": " . $a["count"] . "</li>";
            }

            echo "</ul>";
        } elseif ($question->type === "html") {
            libxml_use_internal_errors(true); ?>

<!--            <div uk-filter="target: .js-filter">-->
<!---->
<!--                <div class="uk-grid-small uk-flex-middle" uk-grid>-->
<!--                    <div class="uk-width-expand">-->
<!---->
<!--                        <div class="uk-grid-small uk-grid-divider uk-child-width-auto" uk-grid>-->
<!--                            <div>-->
<!--                                <ul class="uk-subnav uk-subnav-pill" uk-margin>-->
<!--                                    <li class="uk-active" uk-filter-control><a href="#">All</a></li>-->
<!--                                </ul>-->
<!--                            </div>-->
<!--                            <div>-->
<!--                                <ul class="uk-subnav uk-subnav-pill" uk-margin>-->
<!--                                    <li uk-filter-control="[data-color='white']"><a href="#">White</a></li>-->
<!--                                    <li uk-filter-control="[data-color='blue']"><a href="#">Blue</a></li>-->
<!--                                    <li uk-filter-control="[data-color='black']"><a href="#">Black</a></li>-->
<!--                                </ul>-->
<!--                            </div>-->
<!--                        </div>-->
<!---->
<!--                    </div>-->
<!--                </div>-->
<!---->
<!--                <ul class="js-filter uk-child-width-1-2 uk-child-width-1-3@m uk-text-center" uk-grid="masonry: true">-->
<!--                    <li data-color="white" data-size="large" data-name="A">-->
<!--                        <div class="uk-card uk-card-default uk-card-body">-->
<!--                            <canvas width="600" height="800"></canvas>-->
<!--                            <div class="uk-position-center">A</div>-->
<!--                        </div>-->
<!--                    </li>-->
<!--                    <li data-color="blue" data-size="small" data-name="B">-->
<!--                        <div class="uk-card uk-card-primary uk-card-body">-->
<!--                            <canvas width="600" height="400"></canvas>-->
<!--                            <div class="uk-position-center">B</div>-->
<!--                        </div>-->
<!--                    </li>-->
<!--      -->
<!---->
<!--                    <li data-color="black" data-size="medium" data-name="E">-->
<!--                        <div class="uk-card uk-card-secondary uk-card-body">-->
<!--                            <canvas width="600" height="600"></canvas>-->
<!--                            <div class="uk-position-center">E</div>-->
<!--                        </div>-->
<!--                    </li>-->
<!--                </ul>-->
<!--            </div>-->



            <div class="uk-child-width-auto uk-grid-column-small uk-grid-row-small" uk-grid>
                <?php foreach ($answers as $a) { ?>
                    <div class="uk-card uk-card-default uk-card-body">
                        <h3 class="uk-card-title">HTML antwoord van ...</h3>

                        <div class="uk-card-default uk-padding-small">
                            <code>
                                <?php echo htmlspecialchars($a["answer"]); ?>
                            </code>
                        </div>

                        <div class="uk-card-default uk-padding-small">
                            <h3 class="uk-card-title">Browser:</h3>

                            <?php
                            $doc = new DOMDocument();
                            $doc->loadHTML($a["answer"]);
                            if ($errors = libxml_get_errors()) { ?>
                                Geen valide HTML!! <br>
                                <ul class="uk-list-bullet uk-text-danger" uk-list>
                                <?php foreach ($errors as $error) {
                                    echo "<li>$error->message</li>";
                                    ?>
                                    </ul>
                                <?php }
                                libxml_clear_errors();
                            } else {
                                echo $a["answer"];
                            }
                            ?>
                        </div>
                    </div>
                <?php } ?>
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