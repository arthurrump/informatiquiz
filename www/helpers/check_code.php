<?php
/**
 * Checks for correctness of a HTML answer with an given XSD schema
 * @param $xsd The XSD schema
 * @param $answer The HTML answer
 * @return string no / yes / valid
 */
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


/**
 * Checks if the given CSS code is valid CSS
 * @param $css The CSS code
 * @return array Returns an array with CSS validation errors, if any
 */
function is_valid_css($css) {
    $res = simplexml_load_string(file_get_contents("http://jigsaw.w3.org/css-validator/validator?lang=nl&output=soap12&text=" . urlencode($css)));

    $res->registerXPathNamespace('senv', 'http://schemas.xmlsoap.org/soap/envelope/');
    $res->registerXPathNamespace('m', "http://www.w3.org/2005/07/css-validator");

    $error_list = $res->xpath('/env:Envelope/env:Body/m:cssvalidationresponse/m:result/m:errors/m:errorlist');

    $errors = array();

    foreach ($error_list as $err) {
        $errors[] = $err->asXML();
    }

    return $errors;
}

?>