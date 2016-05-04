<?php
function getDatesFromRange($start, $end) {
    $interval = new DateInterval('P1D');

    $realEnd = new DateTime($end);
    $realEnd->add($interval);

    $period = new DatePeriod(
         new DateTime($start),
         $interval,
         $realEnd
    );
    
    $i=0;
    
    foreach($period as $date) { 
        $array[] = $date->format('Y-m-d'); 
        ++$i;
       
    }
    echo $i . " Results </br>";
    return $array;
}

// Call the function
$dates = getDatesFromRange('2016-04-01', '2016-07-01');

// Print the output
$test = implode("<br/>", $dates);

echo $test . "test";

//Gut fecked version 2

