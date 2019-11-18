<?php
function get_database_connection() {
    $db = mysqli_connect("db", "root", "password", "phpquiz");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        exit;
    }
    return $db;
}

function db_error($db, $message) {
    echo "$message: (" . $db->errno . ") " . $db->error;
    $db->close();
    exit;
}

function find_user($username) {
    $db = get_database_connection();

    if (!($stmt = $db->prepare("SELECT id, username, pw_hash FROM users WHERE username = ?"))) {
        db_error($db, "Preparing failed");
    }

    if (!$stmt->bind_param("s", $username)) {
        db_error($db, "Binding parameters failed");
    }

    if (!$stmt->execute()) {
        db_error($db, "Execute failed");
    }

    if (!$stmt->bind_result($id, $username, $pw_hash)) {
        db_error($db, "Binding output parameters failed");
    }

    if (!$stmt->fetch()) {
        $stmt->close();
        $db->close();
        return false;
    } else {
        $stmt->close();
        $db->close();
        return array("id" => $id, "username" => $username, "pw_hash" => $pw_hash);
    }
}

function create_user($username, $password) {
    $pw_hash = password_hash($password, PASSWORD_BCRYPT);
    $db = get_database_connection();

    if (!($stmt = $db->prepare("INSERT INTO users(username, pw_hash) VALUES (?, ?)"))) {
        db_error($db, "Preparing failed");
    }

    if (!$stmt->bind_param("ss", $username, $pw_hash)) {
        db_error($db, "Binding parameters failed");
    }

    if (!$stmt->execute()) {
        db_error($db, "Execute failed");
    }

    $id = $db->insert_id;
    $stmt->close();
    $db->close();
    return $id;
}

function get_quizes_for_user($user_id) {
    $db = get_database_connection();

    if (!($stmt = $db->prepare("SELECT id, title FROM quizes WHERE user_id = ?"))) {
        db_error($db, "Preparing failed");
    }

    if (!$stmt->bind_param("i", $user_id)) {
        db_error($db, "Binding parameters failed");
    }

    if (!$stmt->execute()) {
        db_error($db, "Execute failed");
    }

    if (!$stmt->bind_result($id, $title)) {
        db_error($db, "Binding output parameters failed");
    }

    $results = array();
    while ($stmt->fetch()) {
        $results[] = array("id" => $id, "title" => $title);
    }

    $stmt->close();
    $db->close();

    return $results;
}

function create_quiz_for_user($user_id, $title) {
    $db = get_database_connection();

    if (!($stmt = $db->prepare("INSERT INTO quizes(user_id, title) VALUES (?, ?)"))) {
        db_error($db, "Preparing failed");
    }

    if (!$stmt->bind_param("is", $user_id, $title)) {
        db_error($db, "Binding parameters failed");
    }

    if (!$stmt->execute()) {
        db_error($db, "Execute failed");
    }
    
    $id = $db->insert_id;
    $stmt->close();
    $db->close();
    return $id;
}

function delete_quiz($user_id, $quiz_id) {
    $db = get_database_connection();

    if (!($stmt = $db->prepare("DELETE FROM quizes WHERE user_id = ? AND id = ?"))) {
        db_error($db, "Preparing failed");
    }

    if (!$stmt->bind_param("ii", $user_id, $quiz_id)) {
        db_error($db, "Binding parameters failed");
    }

    if (!$stmt->execute()) {
        db_error($db, "Execute failed");
    }

    $stmt->close();
    $db->close();
}

function get_quiz_title($quiz_id) {
    $db = get_database_connection();

    if (!($stmt = $db->prepare("SELECT title FROM quizes WHERE id = ?"))) {
        db_error($db, "Preparing failed");
    }

    if (!$stmt->bind_param("i", $quiz_id)) {
        db_error($db, "Binding parameters failed");
    }

    if (!$stmt->execute()) {
        db_error($db, "Execute failed");
    }

    if (!$stmt->bind_result($title)) {
        db_error($db, "Binding output parameters failed");
    }

    if (!$stmt->fetch()) {
        $stmt->close();
        $db->close();
        return false;
    } else {
        $stmt->close();
        $db->close();
        return $title;
    }
}

function get_questions_for_quiz($quiz_id) {
    $db = get_database_connection();

    if (!($stmt = $db->prepare("SELECT id, question, `order` FROM questions WHERE quiz_id = ? ORDER BY `order`"))) {
        db_error($db, "Preparing failed");
    }

    if (!$stmt->bind_param("i", $quiz_id)) {
        db_error($db, "Binding parameters failed");
    }

    if (!$stmt->execute()) {
        db_error($db, "Execute failed");
    }

    if (!$stmt->bind_result($id, $question, $order)) {
        db_error($db, "Binding output parameters failed");
    }

    $results = array();
    while ($stmt->fetch()) {
        $results[] = array("id" => $id, "question" => $question, "order" => $order);
    }

    $stmt->close();
    $db->close();

    return $results;
}

function create_question_for_quiz($quiz_id, $question) {
    $db = get_database_connection();

    if (!($stmt = $db->prepare("INSERT INTO questions(quiz_id, question) VALUES (?, ?)"))) {
        db_error($db, "Preparing failed");
    }

    if (!$stmt->bind_param("is", $quiz_id, $question)) {
        db_error($db, "Binding parameters failed");
    }

    if (!$stmt->execute()) {
        db_error($db, "Execute failed");
    }
    
    $id = $db->insert_id;
    $stmt->close();
    $db->close();
    return $id;
}

function delete_question($question_id) {
    $db = get_database_connection();

    if (!($stmt = $db->prepare("DELETE FROM question WHERE id = ?"))) {
        db_error($db, "Preparing failed");
    }

    if (!$stmt->bind_param("i", $question_id)) {
        db_error($db, "Binding parameters failed");
    }

    if (!$stmt->execute()) {
        db_error($db, "Execute failed");
    }

    $stmt->close();
    $db->close();
}

function get_question($question_id) {
    $db = get_database_connection();

    if (!($stmt = $db->prepare("SELECT question FROM questions WHERE id = ?"))) {
        db_error($db, "Preparing failed");
    }

    if (!$stmt->bind_param("i", $question_id)) {
        db_error($db, "Binding parameters failed");
    }

    if (!$stmt->execute()) {
        db_error($db, "Execute failed");
    }

    if (!$stmt->bind_result($question)) {
        db_error($db, "Binding output parameters failed");
    }

    if (!$stmt->fetch()) {
        $stmt->close();
        $db->close();
        return false;
    } else {
        $stmt->close();
        $db->close();
        return $question;
    }
}

function create_quizrun_for_quiz($quiz_id, $access_code, $active) {
    $db = get_database_connection();

    if (!($stmt = $db->prepare("INSERT INTO quizrun(access_code, active, quiz_id) VALUES (?, ?, ?)"))) {
        db_error($db, "Preparing failed");
    }

    if (!$stmt->bind_param("iii", $access_code, $active, $quiz_id)) {
        db_error($db, "Binding parameters failed");
    }

    if (!$stmt->execute()) {
        db_error($db, "Execute failed");
    }
    
    $id = $db->insert_id;
    $stmt->close();
    $db->close();
    return $id;
}

function set_quizrun_active($quizrun_id, $active) {
    $db = get_database_connection();

    if (!($stmt = $db->prepare("UPDATE quizrun SET active = ? WHERE id = ?"))) {
        db_error($db, "Preparing failed");
    }

    if (!$stmt->bind_param("ii", $active, $quizrun_id)) {
        db_error($db, "Binding parameters failed");
    }

    if (!$stmt->execute()) {
        db_error($db, "Execute failed");
    }
    
    $stmt->close();
    $db->close();
}

function set_quizrun_current_question($quizrun_id, $question_id) {
    $db = get_database_connection();

    if (!($stmt = $db->prepare("UPDATE quizrun SET current_question = ? WHERE id = ?"))) {
        db_error($db, "Preparing failed");
    }

    if (!$stmt->bind_param("ii", $question_id, $quizrun_id)) {
        db_error($db, "Binding parameters failed");
    }

    if (!$stmt->execute()) {
        db_error($db, "Execute failed");
    }
    
    $stmt->close();
    $db->close();
}

function get_quizrun($quizrun_id) {
    $db = get_database_connection();

    if (!($stmt = $db->prepare("SELECT id, access_code, active, quiz_id, current_question FROM quizrun WHERE id = ?"))) {
        db_error($db, "Preparing failed");
    }

    if (!$stmt->bind_param("s", $quizrun_id)) {
        db_error($db, "Binding parameters failed");
    }

    if (!$stmt->execute()) {
        db_error($db, "Execute failed");
    }

    if (!$stmt->bind_result($id, $access_code, $active, $quiz_id, $current_question)) {
        db_error($db, "Binding output parameters failed");
    }

    if (!$stmt->fetch()) {
        $stmt->close();
        $db->close();
        return false;
    } else {
        $stmt->close();
        $db->close();
        return array("id" => $id, "access_code" => $access_code, "active" => $active, "quiz_id" => $quiz_id, "current_question" => $current_question);
    }
}

function get_active_quizrun_by_code($access_code) {
    $db = get_database_connection();

    if (!($stmt = $db->prepare("SELECT id, access_code, active, quiz_id, current_question FROM quizrun WHERE access_code = ? AND active = true"))) {
        db_error($db, "Preparing failed");
    }

    if (!$stmt->bind_param("s", $access_code)) {
        db_error($db, "Binding parameters failed");
    }

    if (!$stmt->execute()) {
        db_error($db, "Execute failed");
    }

    if (!$stmt->bind_result($id, $access_code, $active, $quiz_id, $current_question)) {
        db_error($db, "Binding output parameters failed");
    }

    if (!$stmt->fetch()) {
        $stmt->close();
        $db->close();
        return false;
    } else {
        $stmt->close();
        $db->close();
        return array("id" => $id, "access_code" => $access_code, "active" => $active, "quiz_id" => $quiz_id, "current_question" => $current_question);
    }
}

function add_answer($quizrun_id, $question_id, $user, $answer) {
    $db = get_database_connection();

    if (!($stmt = $db->prepare("INSERT INTO answers(question_id, quizrun_id, user, answer) VALUES (?, ?, ?, ?)"))) {
        db_error($db, "Preparing failed");
    }

    if (!$stmt->bind_param("iiss", $question_id, $quizrun_id, $user, $answer)) {
        db_error($db, "Binding parameters failed");
    }

    if (!$stmt->execute()) {
        db_error($db, "Execute failed");
    }
    
    $id = $db->insert_id;
    $stmt->close();
    $db->close();
    return $id;
}

function get_questions_for_quizrun($quizrun_id) {
    $db = get_database_connection();

    if (!($stmt = $db->prepare("SELECT questions.id, question, `order` FROM questions, quizrun WHERE questions.quiz_id = quizrun.quiz_id AND quizrun.id = ? ORDER BY `order`"))) {
        db_error($db, "Preparing failed");
    }

    if (!$stmt->bind_param("i", $quizrun_id)) {
        db_error($db, "Binding parameters failed");
    }

    if (!$stmt->execute()) {
        db_error($db, "Execute failed");
    }

    if (!$stmt->bind_result($id, $question, $order)) {
        db_error($db, "Binding output parameters failed");
    }

    $results = array();
    while ($stmt->fetch()) {
        $results[] = array("id" => $id, "question" => $question, "order" => $order);
    }

    $stmt->close();
    $db->close();

    return $results;
}

function get_answers_for_quizrun_question($quizrun_id, $question_id) {
    $db = get_database_connection();

    if (!($stmt = $db->prepare("SELECT answer, COUNT(DISTINCT user) FROM answers WHERE quizrun_id = ? AND question_id = ? GROUP BY answer ORDER BY answer"))) {
        db_error($db, "Preparing failed");
    }

    if (!$stmt->bind_param("ii", $quizrun_id, $question_id)) {
        db_error($db, "Binding parameters failed");
    }

    if (!$stmt->execute()) {
        db_error($db, "Execute failed");
    }

    if (!$stmt->bind_result($answer, $count)) {
        db_error($db, "Binding output parameters failed");
    }

    $results = array();
    while ($stmt->fetch()) {
        $results[] = array("answer" => $answer, "count" => $count);
    }

    $stmt->close();
    $db->close();

    return $results;
}
?>