<?php 
    session_start();
    include("../helpers/session.php");
    include("../helpers/db.php");

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
    
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Admin - PHPQuiz</title>
    <link rel="stylesheet" type="text/css" href="/style.css" />
</head>
<body class="admin-run">
    <h1 class="access-code">Code: <?php echo $quizrun["access_code"]; ?></h1>
    <?php if ($quizrun["active"]) {
        $current_question = get_question($quizrun["current_question"]);
        $question = json_decode($current_question); ?>
        <div class="question">
            <p><?php echo $question->question ?></p>
            <ol>
                <?php for ($i=0; $i < count($question->answers); $i++) { 
                    echo "<li>" . $question->answers[$i] . "</li>";    
                } ?>
            </ol>
        </div>

        <form method="POST" action="/admin/quizrun_next.php">
            <input type="hidden" name="quizrun" value="<?php echo $quizrun_id ?>" />
            <input type="submit" value="volgende" />
        </form>
    <?php } else { ?>
        <h1>Einde</h1>
    <?php } ?>
</body>
</html>