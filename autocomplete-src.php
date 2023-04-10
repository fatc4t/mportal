<?php
//autocomplete-src.php
$availableTags = array(
    "ActionScript", "AppleScript", "Asp", "BASIC", "C",
    "C++", "Clojure", "COBOL", "ColdFusion", "Erlang",
    "Fortran", "Groovy", "Haskell", "Java", "JavaScript",
    "Lisp", "Perl", "PHP", "Python", "Ruby",
    "Scala", "Scheme"
);
 
if ($_POST['param1']) {
    $tags = array();
    foreach ($availableTags as $tag) {
        //if (strpos($tag, $_POST['param1']) === 0) {    //大小文字区別する
        if (stripos($tag, $_POST['param1']) === 0) {    //大小文字区別しない
            $tags[] = $tag;
        }
    }
    echo json_encode($tags);
} else {
    echo json_encode(array());
}
?>
