<!DOCTYPE html>
<html lang="nl">
<head>
    <title>Informatiquiz</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- UIkit CSS -->
    <link rel="stylesheet" href="../uikit-3.2.3/css/uikit.min.css"/>

    <!-- UIkit JS -->
    <script src="../uikit-3.2.3/js/uikit.min.js"></script>
    <script src="../uikit-3.2.3/js/uikit-icons.min.js"></script>
</head>
<body>
<div class="uk-position-center">

    <h1 class="uk-text-center uk-heading-medium">Informatiquiz</h1>

    <form class="uk-padding-large uk-background-muted" action="quiz.php">
        <?php
        // Check if user entered wrong Quiz code
        if ($err = htmlentities($_GET["err"])) { ?>
            <div class="uk-alert-danger uk-animation-shake" uk-alert>
                <a class="uk-alert-close" uk-close></a>
                <b>Quiz code <?php echo htmlspecialchars($err) ?> bestaat niet (meer).</b>
            </div>
        <?php } ?>

        <h2>Doe mee met een quiz!</h2>

        <div class="uk-margin">
            <label class="uk-form-label" for="quiz_code">Quiz code</label>
            <div class="uk-form-controls">
                <input class="uk-input" id="quiz_code" type="number" name="quiz" min="10000" max="99999" required
                       autofocus/>
            </div>
        </div>

        <div class="uk-margin">
            <label class="uk-form-label" for="name">Voornaam</label>
            <div class="uk-form-controls">
                <input class="uk-input" id="name" type="text" name="name" required/>
            </div>
        </div>

        <input class="uk-button uk-button-primary" type="submit" value="Doe mee"/>
    </form>
</div>
</body>
</html>