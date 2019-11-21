<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Informatiquiz</title>
    <link rel="stylesheet" type="text/css" href="/style.css"/>
</head>
<body>
    <h1>Informatiquiz</h1>

    <?php
        // Check if user entered wrong Quiz code
        if ($err = htmlentities($_GET["err"])) {
            echo "<h1>Quiz code $err bestaat niet!</h1>";
        }
    ?>

    <form submit="GET" action="quiz.php">
        <fieldset>
            <legend>Quizcode</legend>
            <input type="number" name="quiz"/>
            <br>
            <input type="submit" value="Doe mee"/>
        </fieldset>
    </form>
</body>
</html>