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
<?php
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			$detector = $_POST["detector"];
			$wlunit = floatval($_POST["wlunit"]);
			$wavelength = floatval($_POST["wavelength"]);
			$runit = floatval($_POST["runit"]);
			$resistance = floatval($_POST["resistance"]);
			$vunit = floatval($_POST["vunit"]);
			$voltage = floatval($_POST["voltage"]);

			// Save values
			//touch first because it might not exist
			touch("detector.log");
			file_put_contents("detector.log", $detector);
			touch("wlunit.log");
			file_put_contents("wlunit.log", $wlunit);
			touch("wavelength.log");
			file_put_contents("wavelength.log", $wavelength);
			touch("runit.log");
			file_put_contents("runit.log", $runit);
			touch("resistance.log");
			file_put_contents("resistance.log", $resistance);
			touch("vunit.log");
			file_put_contents("vunit.log", $vunit);
			touch("voltage.log");
			file_put_contents("voltage.log", $voltage);

			$wavelength = $wavelength * $wlunit;
			$resistance = $resistance * $runit;
			$voltage = $voltage / $vunit;
			
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
				$result=number_format($power, 3) . " W";
			elseif ($power >= 0.001)
				$result=number_format($power * 1000, 3) . " mW";
			elseif ($power >= 0.000001)
				$result=number_format($power * 1000000, 3) . " &#x3BCW";
			else
				$result=number_format($power * 1000000000, 3) . " nW";
			
			file_put_contents("result.log", $result);
			// header('Location: https://diode.lapping.ch');
  			// exit;
		}
		?>
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
			<input type="text" name="wavelength" value=<?php echo file_get_contents("wavelength.log");?>></label><br>
			<label>
				<input type="radio" name="wlunit" value="1" <?php echo (file_get_contents("wlunit.log") == 1) ? "checked" : "";?>>nm</label>
			<label>
				<input type="radio" name="wlunit" value="1000" <?php echo (file_get_contents("wlunit.log") == 1000) ? "checked" : "";?>>&#x3BCm</label>
		<br>
		<label>Load resistance:<br>
			<input type="text" name="resistance" value=<?php echo file_get_contents("resistance.log");?>></label><br>
			<label>
				<input type="radio" name="runit" value="1" <?php echo (file_get_contents("runit.log") == 1) ? "checked" : "";?>>&#x3A9</label>
			<label>
				<input type="radio" name="runit" value="1000" <?php echo (file_get_contents("runit.log") == 1000) ? "checked" : "";?>>k&#x3A9</label>
			<label>
				<input type="radio" name="runit" value="1000000" <?php echo (file_get_contents("runit.log") == 1000000) ? "checked" : "";?>>M&#x3A9</label>
		<br>
		<label>Voltage:<br>
			<input type="text" name="voltage" value=<?php echo file_get_contents("voltage.log");?>></label><br>
	    		<label>
				<input type="radio" name="vunit" value="1000" <?php echo (file_get_contents("vunit.log") == 1000) ? "checked" : "";?>>mV</label>
			<label>
				<input type="radio" name="vunit" value="1" <?php echo (file_get_contents("vunit.log") == 1) ? "checked" : "";?>>V</label><br><br>
        <input type="submit" value="Calculate Power">
    </form>
	
 	<?php
	echo file_get_contents("result.log");?>
</body>
</html>
