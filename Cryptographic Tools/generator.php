<html>
	<body>
		<form action='./generator.php' method='POST'>
			<label>Driver Name:</label>
			<select name="driver">
				<option value="mysql">MySQL</option>
				<option value="pgsql">PostgreSQL</option>
			</select>
			<br />
			<label>Database Address:</label>
			<input type="text" name="address" />
			<br />
			<label>Database Name:</label>
			<input type="text" name="dbname" />
			<br />
			<label>Database User:</label>
			<input type="text" name="dbuser" />
			<br />
			<label>Database Password:</label>
			<input type="text" name="dbpass" />
			<br />
			<label>Crypt Key:</label>
			<input type="text" name="key" />
			<br />
			<input type="submit" value="Generate"/>
			<button type="reset" >
				Clear
			</button>

		</form>
	</body>
</html>
<?php
include ("crypt.php");

if (!isset($_POST["driver"]))
    ;
else {
    $driver = $_POST['driver'];
    $addr_db = $_POST['address'];
    $dbname = $_POST['dbname'];
    $user = $_POST['dbuser'];
    $pass = $_POST['dbpass'];
    $key = $_POST['key'];
    $string = sprintf("%s:host=%s;dbname=%s, %s, %s", $driver, $addr_db, $dbname, $user, $pass);
    echo "DB string:<input size=200 type=\"text\" value=\"" . $string . "\">";
    echo "<br />";
    echo "encoded key:<input size=200 type=\"text\" value=\"" . base64_encode($key) . "\">";
    echo "<br />";
    $k = encrypt("$string", $key);
    echo "Enconded DB string:<input size=200 type=\"text\" value=\"" . base64_encode($k) . "\">";
    echo "<br />";
}
?>
