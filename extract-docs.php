<?php
// replace some text with [_Text_][]

$docs = [];

$files = [
    __DIR__ . "/src/ResponseStruct.php",
    __DIR__ . "/src/ResponseHeadersCollection.php",
    __DIR__ . "/src/ResponseBodyHandler.php",
    __DIR__ . "/src/ResponseCookieHelperService.php",
    __DIR__ . "/src/ResponseSenderService.php",
    __DIR__ . "/src/ResponseThrowable.php",
    __DIR__ . "/src/ResponseTypeAliases.php",
];

foreach ($files as $file) {
    require $file;
    $interfaces = get_declared_interfaces();
    $interface = end($interfaces);
    $class = new ReflectionClass($interface);
    $namespace = $class->getNamespaceName();
    $docs[] = "### _" . stripNamespace($namespace, $class->getName()) . "_";
    $docs[] = "";
    addNarrative($docs, $class, '', '');
    $docs[] = "";

    $properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);

    if ($properties) {
        $docs[] = "- Properties:";
        $docs[] = "";

        foreach ($properties as $property) {
            $annotations = getAnnotations($property);
            preg_match("/@var (.*)/", $annotations, $matches);
            $type = stripNamespace($namespace, $matches[1] ?? $property->getType());
            $name = $property->getName();
            $docs[] = "    - ```php";
            $docs[] = "      public {$type} \${$name} { get; set; }"; // reflect on get/set
            $docs[] = "      ```";
            addNarrative($docs, $property, "        ", "- ");
            $docs[] = "";
        }
    }

    $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

    if ($methods) {
        $docs[] = "- Methods:";
        $docs[] = "";

        // @TODO must show default values for params
        foreach ($methods as $method) {
            if ($method->getDeclaringClass() != $class) {
                continue;
            }

            $annotations = getAnnotations($method);
            $signature = "public function " . $method->getName() . "(";
            foreach ($method->getParameters() as $parameter) {
                $name = $parameter->getName();
                preg_match("/@param (.*) \\\${$name}/m", $annotations, $matches);
                $type = stripNamespace($namespace, $matches[1] ?? $parameter->getType());
                $signature .= "{$type} \${$name}, ";
            }
            $signature = rtrim($signature, ", ");
            preg_match("/@return (.*)/", $annotations, $matches);
            $type = stripNamespace($namespace, $matches[1] ?? $method->getReturnType()); // if null, blow up, need a return
            $signature .= ") : {$type};";

            if (strlen($signature) > 80) {
                $signature = wrapSignature($signature);
            }

            $docs[] = "    - ```php";
            $docs[] = "      {$signature}";
            $docs[] = "      ```";
            addNarrative($docs, $method, "        ", "- ");
            $docs[] = "";
        }
    }
}

$docs = implode(PHP_EOL, $docs) . PHP_EOL;
$readme = file_get_contents(__DIR__ . '/README.tpl.md');
$readme = str_replace('{{= docs }}', $docs, $readme);
$readme = preg_replace('/^\s+$/m', '', $readme);
$readme = preg_replace('/^- Methods:\n\n###/m', "###", $readme);

file_put_contents(__DIR__ . '/README.md', $readme);

function cleanComment($r)
{
    $comment = $r->getDocComment();
    $comment = preg_replace("/^[ ]{0,}\/\*\*/m", "", $comment);
    $comment = preg_replace("/^[ ]{0,}\*\//m", "", $comment);
    $comment = preg_replace("/^[ ]{0,}\*[ ]{0,1}/m", "", $comment);
    return trim($comment);
}

function getAnnotations($r)
{
    $comment = cleanComment($r);
    $pos = strpos($comment, PHP_EOL . "@");

    if ($pos === false) {
        return "";
    }

    return substr($comment, $pos);
}

function addNarrative(&$docs, $r, $indent, $prefix)
{
    $comment = cleanComment($r);
    $pos = strpos($comment, PHP_EOL . "@");

    if ($pos !== false) {
        $comment = substr($comment, 0, $pos);
    }

    $comment = $prefix . $comment;
    $lines = explode(PHP_EOL, $comment);

    foreach ($lines as &$line) {
        $docs[] = $indent . $line;
    }
}

function stripNamespace(string $namespace, string $type) : string
{
    return str_replace(
        $namespace . "\\",
        "",
        $type
    );
}

function wrapSignature(string $signature) : string
{
    [$params, $return] = explode(' : ', $signature);

    if (strpos($signature, '()') === false) {
        $params = str_replace("(", "(\n          ", $params);
        $params = str_replace(",", ",\n         ", $params);
        $params = str_replace(")", ",\n      )", $params);
    }

    // $return = str_replace("|", "\n          |", $return);
    return "{$params} : {$return}";
}
