<?php
echo "Hello, world!\n";

$name = "Alice";

echo "Hello, " . $name . "!\n";

echo "Hello, $name!\n";

$age = 25;
echo gettype($age);

echo "\n";

echo gettype($name); echo "\n";

$age = "twenty-five";
echo gettype($age);
echo "\n";
$x = 10; // global variable
function test() {
    global $x; // access global variable using global keyword
    echo $x; // output 10
    echo "\n";
}
test();
function test2() {
    echo $GLOBALS['x']; // access global variable using $GLOBALS array
    echo "\n";
}
test2();

function test3() {
    $y = 20; // local variable
    echo $y; // output 20
    echo "\n";
}
test3();


function test4() {
    static $z = 0; // static variable
    $z++;
    echo $z; // output 1, 2, 3, ... on each function call
    echo "\n";
}
test4();
test4();
test4();
test4();

//---------------
$a = 10;
$b = 3;
echo $a + $b; echo "\n";// output 13
echo $a - $b; echo "\n";// output 7
echo $a * $b; echo "\n";// output 30
echo $a / $b; echo "\n";// output 3.3333333333333
echo $a % $b; echo "\n";// output 1
echo $a ** $b; echo "\n";// output 1000
//------------------
$c = 5; // assign 5 to $c
$c += 2; // equivalent to $c = $c + 2;
$c -= 2; // equivalent to $c = $c - 2;
$c *= 2; // equivalent to $c = $c * 2;
$c /= 2; // equivalent to $c = $c / 2;
$c %= 2; // equivalent to $c = $c % 2;
$c **= 2; // equivalent to $c = $c ** 2;
$c .= "hello"; // equivalent to $c = $c . "hello";

echo "c vale $c\n";
//----------------------------------
$d = "10";
$e = 10;
echo ($d == $e);echo "\n"; // output true (loose equality)
echo ($d === $e); echo "\n";// output false (strict equality)
echo ($d != $e); echo "\n";// output false (loose inequality)
echo ($d !== $e); echo "\n";// output true (strict inequality)
echo ($d < $e); echo "\n";// output false (less than)
echo ($d > $e); echo "\n";// output false (greater than)
echo ($d <= $e); echo "\n";// output true (less than or equal to)
echo ($d >= $e); echo "\n";// output true (greater than or equal to)

//-----------------------
$f = true;
$g = false;
echo ($f && $g);echo "\n";// output false (logical and)
echo ($f || $g);echo "\n"; // output true (logical or)
echo ($f ^ $g);echo "\n"; // output true (logical xor)
echo (!$f); echo "\n";// output false (logical not)
//------------------------

$h = "Hello";
$i = "world";
echo ($h . " " . $i . "\n"); // output Hello world (concatenation)
$h .= " "; // append a space to $h
$h .= $i; // append $i to $h
echo ($h); // output Hello world (concatenation assignment)4

$j = array("a" => "apple", "b" => "banana");
$k = array("c" => "cherry", "d" => "durian");
print_r($j + $k); // output Array ([a] => apple [b] => banana
// [c] => cherry [d] => durian) (array union)
echo ($j == $k); echo "\n";// output false (array equality)
echo ($j === $k);echo "\n"; // output false (array identity)
echo ($j != $k); echo "\n";// output true (array inequality)
echo ($j !== $k);echo "\n"; // output true (array non-identity)
print_r($j);

$l = 10;
$m = ($l > 5) ? "greater than 5" : "not greater than 5";
echo $m; // Output: "greater than 5"
//----------------------------
$age = 15;
if ($age > 18) {
    echo "You are an adult.";
} else
{ echo "Your are not an adult";}



// Assume $age is a positive integer
if ($age < 13) {
    echo "You are a child.";
} elseif ($age < 18) {
    echo "You are a teenager.";
} elseif ($age < 65) {
    echo "You are an adult.";
} else {
    echo "You are a senior.";
}

$color="blue";
// Assume $color is a string variable
switch ($color) {
    case "red":
        echo "You like red.";
        break;
    case "blue":
        echo "You like blue.";
        break;
    case "green":
        echo "You like green.";
        break;
    default:
        echo "You don't have a favorite color.";
}
echo "\n";
$variable= "value2";

$result = match ($variable) {
    'value1' => 'Result for value1',
    'value2' => 'Result for value2',
    default => 'Default result',
};


function processInput($input)
{
    return match (true) {
        is_int($input) => "El valor es un entero: $input",
        is_float($input) => "El valor es un flotante: $input",
        is_string($input) => match ($input) {
            'hola' => "Saludo detectado: $input",
            'adios' => "Despedida detectada: $input",
            default => "Cadena desconocida: $input",
        },
        is_array($input) => "El valor es un array con " . count($input) . " elementos",
        is_object($input) => "El valor es un objeto de la clase " . get_class($input),
        default => "Tipo de dato no reconocido",
    };
}

// Ejemplos de uso
echo processInput(42) . PHP_EOL; // El valor es un entero: 42
echo processInput(3.14) . PHP_EOL; // El valor es un flotante: 3.14
echo processInput('hola') . PHP_EOL; // Saludo detectado: hola
echo processInput('mundo') . PHP_EOL; // Cadena desconocida: mundo
echo processInput([1, 2, 3]) . PHP_EOL; // El valor es un array con 3 elementos
echo processInput(new DateTime()) . PHP_EOL; // El valor es un objeto de la clase DateTime


echo $result; echo "\n";

for ($i = 0; $i < 10; $i++) {
    echo $i . " ";
}

for ($i = 1; $i <= 10; $i++) {
    echo $i . " ";
}

echo"\n";

$colors = ["red", "green", "blue"];
foreach ($colors as $key => $color) {
    echo $key . "=" . $color . " ";
}
echo "\n";
$colors = ["red" => "#FF0000", "green" => "#00FF00", "blue" => "#0000FF"];
foreach ($colors as $key => $color) {
    echo $key . "=" . $color . "\n";
}

$i = 0;
while ($i < 10) {
    echo $i . " ";
    $i++;
}
//------------------------------------------
for ($i = 0; $i < 10; $i++) {
    if ($i == 5) {
        break; // Exit the loop when $i equals 5
    }
    echo $i . " ";
}


for ($i = 0; $i < 10; $i++) {
    if ($i % 2 == 1) {
        continue; // Skip the rest of the loop for even numbers
    }
    echo $i . " ";
}
echo "\n";
function addOne($number) {
    $number++;
    echo $number; echo "\n";
}
$originalNumber = 5;
addOne($originalNumber); // Outputs: 6
echo $originalNumber; echo "\n";// Still outputs: 5

function addOneByRef(&$number) {
    $number++;
    echo $number; echo "\n";
}
$originalNumber = 5;
addOneByRef($originalNumber);
echo $originalNumber;echo "\n";

function sum($a, $b) {
    return $a + $b;
}
$total = sum(5, 3);
echo $total; // Outputs: 8


$text = "Hello, world!";
echo strlen($text); // Outputs the length of the string
echo str_replace("world", "PHP", $text); // Replaces 'world' with 'PHP'


echo $y; // error: undefined variable