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
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Admin - PHPQuiz</title>
    <link rel="stylesheet" type="text/css" href="/style.css" />
</head>
<body class="admin-run">
    <span class="access-code"><?php echo $quizrun["access_code"]; ?></span>
</body>
</html>