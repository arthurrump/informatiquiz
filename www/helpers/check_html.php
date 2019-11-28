<?php
// Check here for correctness of the answer; return yes/valid/no
function is_correct_html_answer($xmd, $answer)
{
    libxml_use_internal_errors(true);

    $doc = new DOMDocument();
    $doc->loadXML($answer);

// TODO Check for valid xml scheme at question submission
    if (libxml_get_errors()) {
        return "no";
    } elseif ($doc->schemaValidateSource($xmd)) {
        return "yes";
    } else {
        return "valid";
    }
}

?>