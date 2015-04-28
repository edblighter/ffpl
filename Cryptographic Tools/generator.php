<html>
	<body>
		<p>
			Database encode
		</p>
		<form action='<?php echo $_SERVER['PHP_SELF']; ?>' method='POST'>
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
		<p>
			Password hash
		</p>
		<form action='<?php echo $_SERVER['PHP_SELF']; ?>' method='POST'>
			<label>Hash Function Name:</label>
			<select name="hash">
				<option value="default">Default</option>
				<option value="php">PHP</option>
			</select>
			<label>Password:</label>
			<input type="text" name="password" />
			<br />

			<input type="submit" value="Generate"/>
			<button type="reset" >
				Clear
			</button>
		</form>
	</body>
</html>
<?php

if (!isset($_POST["driver"])) :
	if (isset($_POST["hash"])) :
		$hash = $_POST["hash"];
		$password=$_POST["password"];
		$salt = Utils::generateSalt();
		echo "Salt:<input size=200 type=\"text\" value=\"" . $salt . "\">";
		echo "Encoded Password:<input size=200 type=\"text\" value=\"" . Utils::hashPassword($password, $salt) . "\">";
	endif;
else :
	$util = new Utils;
	$driver = $_POST['driver'];
	$addr_db = $_POST['address'];
	$dbname = $_POST['dbname'];
	$user = $_POST['dbuser'];
	$pass = $_POST['dbpass'];
	$key = $_POST['key'];
	$string = sprintf("%s:host=%s;dbname=%s, %s, %s", $driver, $addr_db, $dbname, $user, $pass);
	echo "DB string:<input size=200 type=\"text\" value=\"" . $string . "\">";
	echo "<br />";
	echo "encoded key:<input size=200 type=\"text\" value=\"" . $util -> encodeKey($key) . "\">";
	echo "<br />";
	$k = $util -> encrypt($string, $key);
	echo "Enconded DB string:<input size=200 type=\"text\" value=\"" . $k . "\">";
	echo "<br />";

endif;
?>
