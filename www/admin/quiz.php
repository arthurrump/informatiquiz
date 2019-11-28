<?php
session_start();
include("../helpers/session.php");
include("../helpers/db.php");
include "../helpers/check_html.php";

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
        switch ($type = $_POST["type"]) {
            case "closed":
                $q = array(
                    "type" => $type,
                    "question" => $question,
                    "correct" => $correct,
                    "answers" => $_POST['answer']);
                break;

            case "open_html":
                // Check if a XSD schema is given
                if (empty($xsd = $_POST["xsd"])) {
                    $errors[] = "Voer een XSD schema in";
                    var_dump($errors);
                    break;
                }

                // Check here if XSD is valid for given example answer
                if (is_correct_html_answer($xsd, $correct) === "yes") {
                    $q = array(
                        "type" => $type,
                        "question" => $question,
                        "correct" => $correct,
                        "xsd" => $xsd);
                } else {
                    foreach (libxml_get_errors() as $error) {
                        $errors[] = "Fout validatie XSD: " . $error->message;
                    }
                    libxml_clear_errors();
                }

                break;

            case "open_css":
                $q = array(
                    "type" => $type,
                    "question" => $question,
                    "correct" => $correct);
                break;
        }

        if ($q) {
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

<?php include '../helpers/output_errors.php' ?>

<ul uk-tab>
    <li><a href="#">Meerkeuze</a></li>
    <li><a href="#">Open HTML</a></li>
    <li><a href="#">Open CSS</a></li>
</ul>

<ul class="uk-switcher uk-margin uk-background-muted">

    <!-- Question where student has to choice an answer -->
    <li>
        <form method="POST" class="uk-padding">

            <div class="uk-margin">
                <label class="uk-form-label" for="question">Vraag</label>
                <textarea class="uk-textarea" id="question" rows="3" placeholder="Type hier je vraag"
                          name="question" autofocus></textarea>
            </div>

            <div class="uk-padding-small uk-card-default uk-margin">
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

    <!-- Question where student has to answer with HTML code -->
    <li>
        <form method="POST" class="uk-padding">

            <label class="uk-form-label" for="question_html">Vraag</label>
            <textarea class="uk-textarea" id="question_html" rows="3" placeholder="Type hier je vraag"
                      name="question"></textarea>

            <div class="uk-margin">
                <label class="uk-form-label" for="answer_html">Voorbeeld van een goed HTML antwoord (dit antwoord moet
                    door het validatieschema hieronder worden goedgekeurd)</label>
                <textarea class="uk-textarea" id="answer_html" rows="2" name="correct"
                          placeholder="Typ hier een voorbeeld van een goed HTML antwoord"></textarea>
            </div>


            <div class="uk-margin">
                <label class="uk-form-label" for="validity_html">Validatie van HTML antwoord
                    (<a href="https://www.w3schools.com/XML/schema_intro.asp">XML validatie schema</a>)</label>
                <textarea class="uk-textarea" id="validity_html" rows="10" name="xsd">
<xs:schema attributeFormDefault="unqualified" elementFormDefault="qualified"
           xmlns:xs="http://www.w3.org/2001/XMLSchema">
  <xs:element name="html">
    <xs:complexType>
      <xs:sequence>
        <xs:element name="body">
          <xs:complexType>
            <xs:sequence>

              <!-- Typ hier de validatie voor elementen die in de body moeten komen te staan -->

            </xs:sequence>
          </xs:complexType>
        </xs:element>
      </xs:sequence>
    </xs:complexType>
  </xs:element>
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
                <label class="uk-form-label" for="answer_css">
                    Correcte CSS antwoord (antwoorden worden alleen gecontroleerd op validiteit)
                </label>
                <textarea class="uk-textarea" id="answer_css" rows="10" name="correct"
                          placeholder="Typ hier het juiste CSS antwoord"></textarea>
            </div>

            <button class="uk-width-1-1 uk-button uk-button-primary uk-button-large"
                    type="submit" name="type" value="open_css">
                Voeg open CSS vraag toe
            </button>
        </form>
    </li>
</ul>
</body>
</html>