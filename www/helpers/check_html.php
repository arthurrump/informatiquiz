<?php
// Check here for correctness of the answer; return yes/valid/no
function is_correct_html_answer($xsd, $answer)
{
    libxml_use_internal_errors(true);

    $doc = new DOMDocument();
    $doc->loadHTML($answer);

    if (libxml_get_errors()) {
        return "no";
    } elseif ($doc->schemaValidateSource($xsd)) {
        return "yes";
    } else {
        return "valid";
    }
}

?>