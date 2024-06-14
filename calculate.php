<html>
<body>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Calculate the power
	$wavelength = $_POST["wavelength"];
    $resistance = $_POST["resistance"];
    $voltage = $_POST["voltage"];
    echo $wavelength / ($voltage / $resistance);
        //$sum = 0;
        //$count = 0;
        //foreach ($data as $value) {
            //$sum += floatval($value);
            //$count++;
        //}
       // $power = ($count > 0) ? $sum / $count : 0;

        //echo "<h2>Power: " . number_format($power, 2) . "</h2>";
    //} else {
        //echo "<p>Error uploading the CSV file.</p>";
    //}
}
	header('Location: https://diode.lapping.ch/');
?>
	
</body>
</html>
