<?php foreach ($errors as $err) { ?>
    <div class="uk-alert-danger uk-animation-shake" uk-alert>
        <a class="uk-alert-close" uk-close></a>
        <b><?php echo $err; ?></b>
    </div>
<?php } ?>