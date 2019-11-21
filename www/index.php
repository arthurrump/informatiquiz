<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Informatiquiz</title>
    <link rel="stylesheet" type="text/css" href="/css/index.css"/>
</head>
<body>

    <?php
        // Check if user entered wrong Quiz code
        if ($err = htmlentities($_GET["err"])) {
            echo "<h1 id='error'>Quiz code $err bestaat niet!</h1>";
        }
    ?>
    <h1>Informatiquiz</h1>
    <form submit="GET" action="quiz.php">
        <h2>Doe mee met een quiz!</h2>
        <label for="quiz_code">Quiz code</label>
        <br>
        <input id="quiz_code" type="number" name="quiz"  min="10000" max="99999" required autofocus/>
        <br>
        <input type="submit" value="Doe mee"/>
    </form>
</body>
</html>