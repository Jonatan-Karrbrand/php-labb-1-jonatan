<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Rätt tidszon
date_default_timezone_set("Europe/Stockholm");

echo date('l \t\h\e jS Y ');
echo date('G \: i : s');

// Hämta filen
$csv = array_map('str_getcsv', file('info.csv'));

if (isset($_POST['submit'])) {
  $array = explode(' ' , $_POST['landskoder']);
  findCSV($array);
}


function findCSV($natCode){
  // Variabler
  global $csv;
  $allNumbers = [];
  $allNatCodes = [];
  $allNatCodesOne = [];
  $result = 0;
  $fails = 0;
  $failNatCode = [];
  $existingNatCode;
  $date = date('ymd-Gis');
  $argsUnConverted = func_get_args();
  $args = [];

  // En foreach loop som gör arrayer med tillgängliga landskoder
  foreach ($csv as $key => $value) {
    $singleItem = mb_substr($value[0], 1, 2);
    array_push($allNatCodes, $singleItem);

    $singleItemOne = mb_substr($value[0], 1, 1);
    array_push($allNatCodesOne, $singleItemOne);
  }
  // Denna loop gör att alla landskoder blir till stora bokstäver
  foreach ($argsUnConverted[0] as $key => $value){
    $singleArg = strtoupper($value);
    array_push($args, $singleArg);
  }

  // Här är delen som kollar om landskoden finns och räknar ihop
  foreach ($csv as $key => $value) {
    $item = mb_substr($value[0], 1, 2);
    $itemOneWord = mb_substr($value[0], 1, 1);

    $argsLenght = count($args);
    $failNatCodeLenght = count($failNatCode);

    for ($i=0; $i < $argsLenght; $i++) {
      // Om landskoden finns ->
      if ($item == $args[$i] || $itemOneWord == $args[$i]) {
        $existingNatCode = $args[$i];
        // Gångrar antal med pris
        $number = $value[1] * $value[2];
        array_push($allNumbers, $number);
        // Gör en koll om antal och pris är siffror, om dom inte är det ökar en variabel som skrivs ut.
        if (!is_numeric($value[1]) || !is_numeric($value[2])) {
          $fails = $fails + 1;
        }
      }
      // Om landskoden inte finns ->
      elseif (!in_array($args[$i], $allNatCodes) && !in_array($args[$i], $allNatCodesOne)) {
        // Gör en koll om den icke existerande landskoden finns i en array.
        if (in_array($args[$i], $failNatCode)) {
          // Gör inget, fortsätt loopa
        }
        else {
          // Skicka in den felaktiga landskoden till failNatCode. För att man ska få reda på vad som är fel.
          array_push($failNatCode, $args[$i]);
        }
      }

    }
  }

  // Den här loopen plussar ihop summan mellan de olika landskoderna
  foreach ($allNumbers as $key => $value) {
    $result = $result + $value;
  }

  // Här börjar kollen av vad som ska skrivas ut på skärmen och i dokumentet
  if ($existingNatCode) {

      // Om kriterierna lyckas. Det ska vara åtminstone 1 landskod som lyckas
      $fileHandle = fopen("$existingNatCode-$date.csv", "w+");

      echo "<br><h2>Resultat</h2>";
      echo "<h3 style='color: green;'>Success</h3>";

      echo "Du har sökt efter: ";
      foreach ($args as $key => $value) {
        echo "$value ";
      }

      // När det finns minst 1 landskod, men någon felaktig landskod
      if ($failNatCode) {
        // Den felaktiga landskoden skrivs ut
        echo "<br>Felaktig landskod: ";
        fwrite($fileHandle, "Success, $existingNatCode, $result Kr, Felaktig landskod:");
        for ($i=0; $i < $failNatCodeLenght; $i++) {
          echo "$failNatCode[$i] ";
          fwrite($fileHandle, " $failNatCode[$i] ");
        }
      }
      // När det inte finns någon felaktig landskod
      else {
        fwrite($fileHandle, "Success, $existingNatCode, $result Kr");
      }

      // Om det finns något felaktigt värde, tex en bokstav när det ska vara siffra
      if ($fails) {
        echo "<br><p>Antal fel: $fails. Värden som är av fel typ!</p>";
        fwrite($fileHandle, ", Värden som är av fel typ: $fails");
      }

      fclose($fileHandle);

      echo "<br>";
      echo $result . " Kr";
  }

  else {
    // Failure

    $fileHandle = fopen("$args[0]-$date.csv", "w+");

    echo "<br><h2>Resultat</h2>";
    echo "<h3 style='color: red;'>Failure</h3>";

    if ($fails < 0) {
      echo "<br><p>Antal fel: $fails. Värden som är av fel typ!</p>";
      fwrite($fileHandle, "Failure $args[0] $result, Värden som är av fel typ: $fails");
    }
    else {
      echo "<br><p>Den landskoden finns ej</p>";
      fwrite($fileHandle, "Failure, $args[0]: Ogiltlig landskod ");
    }

    fclose($fileHandle);
  }
}

//knapp
echo "<a style='position: relative; left: -65px; top:50px; color: white; background-color: lightblue; padding: 10px;' href='home.php'>Tillbaka</a>";
?>
