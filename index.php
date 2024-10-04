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
			<input type="text" name="wavelength" value=<?php echo file_get_contents("wavelength.log");?>></label><br>
			<label>
				<input type="radio" name="wlunit" value="1" <?php echo (file_get_contents("wlunit.log") == 1) ? "checked" : "";?>>nm</label>
			<label>
				<input type="radio" name="wlunit" value="1000" <?php echo (file_get_contents("wlunit.log") == 1000) ? "checked" : "";?>>um</label>
		<br>
		<label>Load resisance:<br>
			<input type="text" name="resistance" value=<?php echo file_get_contents("resistance.log");?>></label><br>
			<label>
				<input type="radio" name="runit" value="1" <?php echo (file_get_contents("runit.log") == 1) ? "checked" : "";?>>&#x3A9</label>
			<label>
				<input type="radio" name="runit" value="1000" <?php echo (file_get_contents("runit.log") == 1000) ? "checked" : "";?>>k&#x3A9</label>
			<label>
				<input type="radio" name="runit" value="1000000" <?php echo (file_get_contents("runit.log") == 1000000) ? "checked" : "";?>>M&#x3A9</label>
		<br>
		<label>Volage:<br>
			<input type="text" name="voltage" value=<?php echo file_get_contents("voltage.log");?>></label><br>
	    		<label>
				<input type="radio" name="vunit" value="1000" <?php echo (file_get_contents("vunit.log") == 1000) ? "checked" : "";?>>mV</label>
			<label>
				<input type="radio" name="vunit" value="1" <?php echo (file_get_contents("vunit.log") == 1) ? "checked" : "";?>>V</label><br><br>
        <input type="submit" value="Calculate Power">
    </form>
	<?php echo file_get_contents("result.log");?>
 	?>
	 <script>
		if (window.location.href.indexOf("POST") !== -1) {
  const detector = document.getElementById("detector").value;
  const wlunit = parseFloat(document.getElementById("wlunit").value);
  const wavelength = parseFloat(document.getElementById("wavelength").value);
  const runit = parseFloat(document.getElementById("runit").value);
  const resistance = parseFloat(document.getElementById("resistance").value);
  const vunit = parseFloat(document.getElementById("vunit").value);
  const voltage = parseFloat(document.getElementById("voltage").value);

  // Save values
  localStorage.setItem("detector", detector);
  localStorage.setItem("wlunit", wlunit);
  localStorage.setItem("wavelength", wavelength);
  localStorage.setItem("runit", runit);
  localStorage.setItem("resistance", resistance);
  localStorage.setItem("vunit", vunit);
  localStorage.setItem("voltage", voltage);

  const wavelengthInNm = wavelength * wlunit;
  const resistanceInOhm = resistance * runit;
  const voltageInV = voltage / vunit;

  // Import csv
  const csvFile = `${detector}.csv`;
  fetch(csvFile)
    .then(response => response.text())
    .then(data => {
      const rows = data.trim().split("\n");
      const xValues = [];
      const yValues = [];
      for (let i = 0; i < rows.length; i++) {
        const row = rows[i].split(",");
        xValues.push(parseFloat(row[0]));
        yValues.push(parseFloat(row[1]));
      }

      // Perform linear interpolation
      let i = 0;
      let responsivity = -1;
      while (i < xValues.length) {
        if (i === 0 && wavelengthInNm < xValues[i]) {
          break;
        } else {
          if (wavelengthInNm === xValues[i]) {
            responsivity = yValues[i];
            break;
          } else if (wavelengthInNm < xValues[i]) {
            responsivity =
              ((yValues[i + 1] - yValues[i]) / (xValues[i + 1] - xValues[i])) *
                (wavelengthInNm - xValues[i]) +
              yValues[i];
            break;
          } else {
            i++;
          }
        }
      }
      if (responsivity === -1) {
        alert("Invalid wavelength");
        return;
      }

      // Calculate the power
      const power = voltageInV / resistanceInOhm / responsivity;

      // Print power
      let result;
      if (power >= 1) {
        result = `${power.toFixed(3)} W`;
      } else if (power >= 0.001) {
        result = `${(power * 1000).toFixed(3)} mW`;
      } else if (power >= 0.000001) {
        result = `${(power * 1000000).toFixed(3)} \u03BCW`;
      } else {
        result = `${(power * 1000000000).toFixed(3)} nW`;
      }

      localStorage.setItem("result", result);
      window.location.href = "https://diode.lapping.ch";
    });
}
		</script>
</body>
</html>
