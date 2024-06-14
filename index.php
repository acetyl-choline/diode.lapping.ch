<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculate Power</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }
        form {
            margin: 20px auto;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <h1>Calculate Power</h1>
    <form action="" method="post">
		<label>Detector:</label><br>
		<select id="detector" name="detector">
			<option value="det10a">DET10A (200 - 1100 nm)</option>  
			<option value="det36a" selected>DET36A (350 - 1100 nm)</option>
			<option value="det100a">DET100A (320 - 1100 nm)</option>
		</select><br><br>
		<label>Wavelength:<br>
			<input type="text" name="wavelength"></label><br>
			<label>
				<input type="radio" name="wlunit" value="nm">nm</label>
			<label>
				<input type="radio" name="wlunit" value="um">um</label>
		<br>
		<label>Load resisance (Ohm):<br>
			<input type="text" name="resistance"></label><br>
			<label>
				<input type="radio" name="runit" value="Ohm">Ohm</label>
			<label>
				<input type="radio" name="runit" value="kOhm">kOhm</label>
			<label>
				<input type="radio" name="runit" value="MOhm">MOhm</label>
		<br>
		<label>Volage (V):<br>
			<input type="text" name="voltage"></label><br>
	    		<label>
				<input type="radio" name="vunit" value="mV">mV</label>
			<label>
				<input type="radio" name="vunit" value="mV">mV</label><br><br>
        <input type="submit" value="Calculate Power">
    </form>
	<?php
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			$detector = $_POST["detector"];
			$wavelength = floatval($_POST["wavelength"]);
			$resistance = floatval($_POST["resistance"]);
			$voltage = floatval($_POST["voltage"]);
			
			// Import csv
			$csvFile = $detector . ".csv";
			$data = array_map('str_getcsv', file($csvFile));
			$xValues = [];
			$yValues = [];
			foreach ($data as $row) {
				$xValues[] = floatval($row[0]);
				$yValues[] = floatval($row[1]);
			}
			
			// Perform spline interpolation
			//$spline = new Splines($xValues, $yValues);
			//$responsivity = $spline->interpolate($wavelength);
			
			// Perform linear interpolation
			$i = 0;
			$responsivity = -1;
			foreach ($xValues as $wltable) {
				if ($i == 0 && $wavelength < $wltable) {
					break;
				}
				else {
					if ($wavelength == $wltable) {
						$responsivity = $yValues[$i];
						break;
					}
					elseif ($wavelength < $wltable) {
						$responsivity = ($yValues[$i+1] - $yValues[$i]) / ($xValues[$i+1] - $xValues[$i]) * ($wavelength - $xValues[$i]) + $yValues[i];
						break;
					}
					else {
						$i++;
					}
				}
			}
			if ($responsivity == -1) {
				echo "Invalid wavelength";
				exit;
			}
			
       		// Calculate the power
			$power = $voltage / $resistance / $responsivity;
			
			// Print power
			if ($power >= 1)
				echo number_format($power, 3) . " W";
			elseif ($power >= 0.001)
				echo number_format($power * 1000, 3) . " mW";
			elseif ($power >= 0.000001)
				echo number_format($power * 1000000, 3) . " uW";
			else
				echo number_format($power * 1000000000, 3) . " nW";
		}
 	?>
</body>
</html>
