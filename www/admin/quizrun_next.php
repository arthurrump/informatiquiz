<?php
    include("../helpers/db.php");

    if (!empty($_POST)) {
        if (!($quizrun = get_quizrun($_POST["quizrun"]))) {
            http_response_code(404);
            echo "This quizrun doesn't exist.";
            exit;
        }

        $questions = get_questions_for_quizrun($quizrun["id"]);

        if ($quizrun["current_question"] == 0) {
            set_quizrun_current_question($quizrun["id"], $questions[0]["id"]);
        } else {
            $found = false; $i = 0;
            while (!$found && $i < count($questions)) {
                if ($questions[$i]["id"] == $quizrun["current_question"]) $found = true;
                $i++;
            }
            if ($i < count($questions)) {
                set_quizrun_current_question($quizrun["id"], $questions[$i]["id"]);
            } else {
                set_quizrun_active($quizrun["id"], false);
            }
        }

        header("location: /admin/quizrun.php?id=" . $quizrun["id"]);
        exit;
    }
?>