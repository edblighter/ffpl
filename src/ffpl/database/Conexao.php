<?php
namespace FFPL {
	/**
	 * Class Conexao usando PDO.
	 *
	 * This class uses the main base of PDO and implements a cryptographic functions
	 * to hide the PDO DSN
	 *
	 * @author FFPL (fued.felipe@hotmail.com)
	 *
	 * @version 0.1a
	 **/

	class Conexao extends \PDO {

		/** @var string Base64 of the cryptographic key to access the database */
		private static $u64key;
		/** @var string Base64 da string de chave de criptogafia */
		private static $cryptKey;
		/** @var string Base64 da string criptografada de acesso ao banco */
		private static $u64data;
		/** @var string String descriptografada de acesso ao banco */
		private static $databaseData = null;
		/** @var array general options */
		private static $options;
		/** @var string Mensagem de erro*/
		private static $e;
		/** @var array Contem todas as mensagens de erro*/
		private static $msg = array();
		/** @var PDO Instancia de conexao */
		private static $conexao;

		/**
		 * This function handle errors and set persanalized error messages
		 * @return boolean true if has any error / false if not
		 * */
		private static function has_error() {
			if (sizeof(self::$e) > 0) {
				foreach (self::$e as $val => $key) {
					switch($val) {
						case 'get_file_connect' :
							if ($key == '404') {
								$string_erro = "Não foi possivel obter o arquivo de conexão";
								array_push(self::$msg, $string_erro);
							}
							if ($key == 'OK')
								;
							if ($key == '403') {
								$string_erro = "Caminho para o arquivo de conexão nao encontrado";
								array_push(self::$msg, $string_erro);
							}
							break;
						case 'get_file_key' :
							if ($key == '404') {
								$string_erro = "Não foi possivel obter o arquivo de chave";
								array_push(self::$msg, $string_erro);
							}
							if ($key == 'OK')
								;
							if ($key == '403') {
								$string_erro = "Caminho para o arquivo de chave nao encontrado";
								array_push(self::$msg, $string_erro);
							}
							break;
						case '42S22' :
							$string_erro = "Coluna nao encontrada:" . $this -> e['42S22'];
							array_push(self::$msg, $string_erro);
							break;
					}
				}
				if (sizeof(self::$msg) > 0)
					return true;
				else
					return false;
			} else
				return false;
		}

		/**
		 * Get the content of connection file
		 * @return string or boolean
		 * */
		private static function getFileCon($connection_file) {
			if (is_file($connection_file))
				if (($k = file_get_contents($connection_file)) === false)
					self::setErro('get_file_connect', "404");
				else
					self::setErro('get_file_connect', "OK");
			else
				self::setErro('get_file_connect', "403");

			if (self::has_error())
				return false;
			else
				return $k;
		}

		/**
		 * Get the content of key file
		 * @return string or boolean
		 * */
		private static function getFileKey($key) {
			if (is_file($key))
				if (($p = file_get_contents($key)) === false)
					self::setErro('get_file_key', "404");
				else
					self::setErro('get_file_key', "OK");
			else
				self::setErro('get_file_key', "403");

			if (self::has_error())
				return false;
			else
				return $p;
		}

		private static function setErro($key, $value) {
			self::$e[$key] = $value;
		}

		/**
		 * Função abre dois aquivos de conexão para o banco de dados.
		 * @var string caminho do arquivo criptografado de conexão do banco de dados.
		 * @var string caminho do arquivo de chave criptografica.
		 * @return boolean false if error occur
		 */
		public function __construct($connection_file, $key, Array $options = array()) {
			if (empty($options['algo']) && empty($options['mode'])) :
				$util = new Utils;
			else :
				self::$options['algorithm'] = $options['algo'];
				self::$options['mode'] = $options['mode'];
				$util = new Utils(self::$options['algorithm'], self::$options['mode']);
			endif;
			if (empty($options['fetch_mode'])) :
				self::$options['fetch_mode'] = PDO::FETCH_ASSOC;
			else :
				self::$options['fetch_mode'] = $options['fetch_mode'];
			endif;
			if ((self::$u64key = self::getFileKey($key)) === false) :
				return false;
			endif;
			if ((self::$u64data = self::getFileCon($connection_file)) === false) :
				return false;
			endif;
			self::$databaseData = explode(",", $util -> decrypt(self::$u64data, $util -> decodeKey(self::$u64key)));
		}

		/**
		 * Função retorna todos os erros, caso não haja erros retorna NULL.
		 * @return string
		 * @access public
		 */
		public function show_all_error() {
			return self::$msg;
		}

		/**
		 * Função retorna os erros, caso não haja erros retorna NULL.
		 * @return string
		 **/
		public function show_error() {
			return array_pop(self::$msg);
		}

		/**
		 * Conecta o banco da dados.
		 * @return PDO
		 **/
		public function connect() {
			try {
				if (self::$conexao == null) :
					self::$conexao = new PDO(trim(self::$databaseData[0]), trim(self::$databaseData[1]), trim(self::$databaseData[2]), array(PDO::ATTR_PERSISTENT => TRUE));
					self::$conexao -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				endif;
			} catch(PDOException $error) {
				array_push(self::$msg, $error -> getMessage());
				return false;
			}
			return self::$conexao;
		}

		/**
		 * Executa o comando SQL pedido no determinado caso da função.
		 * @var string requer um SQL para execução.
		 * @var string pede o modo para executar. Exemplo 'exec', 'query', 'prepare'.
		 * @return PDOStatment
		 **/
		public function ex($sql, $mode) {
			switch($mode) :
				case 'exec' :
					return self::$conexao -> exec($sql);
					break;
				case 'query' :
					return self::$conexao -> query($sql);
					break;
				case 'prepare' :
					return self::$conexao -> prepare($sql);
					break;
			endswitch;

		}

		/**
		 * Executa um rollBack no banco da dados.
		 * @return string
		 * @access public
		 **/
		public function volta() {
			self::$conexao -> rollBack();
		}

		/**
		 * Select all fields of the table.
		 * @var string The name of table to get data
		 * @var array The terms to limit the select: array("WHERE" => array(":a
		 * :condition :b", ":operator", ":c :condition :d"), "ORDER BY" => array(":column
		 * => :mode"), "LIMIT" => ":limit", "OFFSET" => ":offset");
		 * @return array Returns the data of the select - The form of return may be set
		 * on the constructor of the class ex: PDO::FETCH_OBJ or PDO::FETCH_ASSOC
		 **/

		public function getAll($table, $terms = null) {
			try {
				if (!is_null($terms)) :
					$Terms = $this -> mountTerms($terms);
					$query = "SELECT * FROM {$table}{$Terms}";
				else :
					$query = sprintf("SELECT * FROM %s", $table);
				endif;
				$k = $this -> ex($query, 'query');
				if ($k !== false || is_object($k)) {
					$k -> execute();
					return $k -> fetchAll(self::$options['fetch_mode']);
				} else {
					self::$e = $this -> connect() -> errorCode();
					self::has_error();
					return false;
				}
			} catch(PDOException $error) {
				self::$e[$error -> getCode()] = $error -> getMessage();
				self::has_error();
				return false;
			}
		}

		/**
		 * Imprime os campos da tabela passados pelo array.
		 * @var array Pede uma array com o nome dos campos da tabela.
		 * @var string Pede o nome da Tabela a ser consultada.
		 * @return array
		 **/
		public function getSpecific($array, $table, $terms = null) {
			$p = sizeof($array);
			$campos = '';
			for ($i = 0; $i < $p; $i++)
				if ($i < $p - 1)
					$campos .= $array[$i] . ",";
				else
					$campos .= $array[$i];
			try {
				if ($terms == null) :
					$k = $this -> ex("SELECT $campos FROM $table", 'prepare');
				else :
					$Terms = $this -> mountTerms($terms);
					$k = $this -> ex("SELECT {$campos} FROM {$table}{$Terms}", 'prepare');
				endif;
				if ($k !== false || is_object($k)) {
					$k -> execute();
					return $k -> fetchAll(self::$options['fetch_mode']);
				} else {
					$this -> e = $this -> connect() -> errorCode();
					$this -> has_error();
					return false;
				}
			} catch(PDOException $error) {
				$this -> e[$error -> getCode()] = $error -> getMessage();
				$this -> has_error();
				return false;
			}

		}

		/**
		 * Insere dados na tabela.
		 * @var array Pede o nome dos campos seguido das variaveis a serem inseridas nos
		 * mesmos.
		 * @var string Pede o nome da tabela.
		 * @return array
		 * This functions needs to be upgraded
		 * FIXME
		 * -Improve PDO
		 */
		/*	private function insert($array, $table) {
		 $p = sizeof($array);
		 $campos = '';
		 $values = array();
		 $bind = '';
		 $i = 0;
		 foreach ($array as $key => $val) {
		 if ($i < $p - 1) {
		 $campos .= $key . ",";
		 array_push($values, $val);
		 $bind .= '? ,';
		 } else {
		 $campos .= $key;
		 array_push($values, $val);
		 $bind .= '?';
		 }
		 $i++;
		 }
		 self::$conexao -> beginTransaction();
		 try {

		 $k = $this -> ex("INSERT INTO {$table}({$campos}) VALUES({$bind})", 'prepare');

		 $k -> execute($values);
		 self::$conexao -> commit();
		 return true;
		 } catch(PDOException $error) {
		 $this -> volta();
		 $this -> e[$error -> getCode()] = $error -> getMessage();
		 $this -> has_error();
		 return false;
		 }

		 }
		 */
		/**
		 * Retorna o ultimo ID.
		 * @return int
		 */
		public function getLastID() {
			return self::$conexao -> lastInsertId();
		}

		/**
		 * Delets ONE ROW based on parameters passed on $terms
		 * @var string nome da tabela.
		 * @var
		 * @return boolean true if delete is ok.
		 **/
		public function delete_item($table, $terms) {
			self::$conexao -> beginTransaction();
			try {
				$Terms = $this -> mountTerms($terms);
				$k = $this -> ex("DELETE FROM {$table} {$Terms}", 'prepare');
				$k -> execute();
				$this -> conexao -> commit();
				return true;
			} catch(PDOException $error) {
				$this -> volta();
				$this -> e[$error -> getCode()] = $error -> getMessage();
				$this -> has_error();
				return false;
			}

		}

		/**
		 * This function mounts the terms to be utilized in functions like getAll,
		 * getSpecific, deleteItem
		 * @var array The variable $terms may be based in this : $terms_structure =
		 * array("WHERE" => array(":a :condition :b", ":operator", ":c :condition :d"),
		 * "ORDER BY" => array(":column :mode"), "LIMIT" => ":limit", "OFFSET" =>
		 * ":offset");
		 * @return string the formated string containing the elements the $terms
		 * */
		private function mountTerms($terms) {

			$operator = array("AND", "OR", "NOT", "IS NULL", "UNIQUE");
			$conditions = array(">", "<", "=", "+", "-", "*", "/", "%", "!=", "<>", ">=", "<=", "!<", "!>", "BETWEEN", "EXISTS", "IN", "LIKE");
			$modes = array("ASC", "DESC");
			$string = '';
			if (array_key_exists("WHERE", $terms)) :
				$string = sprintf(" WHERE ");
				$where = $terms["WHERE"];
				$count = sizeof($where);

				if ($count > 1) :
					for ($i = 0; $i < $count; $i++) :
						if (in_array($where[$i], $operator)) :
							$string .= sprintf(" %s ", $where[$i]);
						else :
							$parts = explode(" ", $where[$i]);
							if (in_array($parts[1], $conditions)) :
								if (is_numeric($parts[2])) :
									$string .= sprintf("`%s` %s %d", $parts[0], $parts[1], $parts[2]);
								else :
									$string .= sprintf("`%s` %s '%s'", $parts[0], $parts[1], $parts[2]);
								endif;
							endif;
						endif;
					endfor;
				else :
					$parts = explode(" ", $where[0]);
					$string .= sprintf("`%s` %s '%s'", $parts[0], $parts[1], $parts[2]);
					Utils::debugMulti($string);
				endif;
			endif;
			if (array_key_exists("ORDER BY", $terms)) :
				$order = sprintf("ORDER BY");
				$parts = $terms["ORDER BY"];
				$count = sizeof($parts);
				$i = 0;

				if ($count > 1) :
					$string .= sprintf(" %s ", $order);
					foreach ($parts as $var => $value) :
						$i++;
						if ($i == $count) :
							$string .= sprintf("`%s` %s", $var, $value);
							break;
						else :
							$string .= sprintf("`%s` %s, ", $var, $value);
						endif;
					endforeach;
				else :
					if (in_array(array_values($parts)[0], $modes)) :
						$string .= sprintf(" %s `%s` %s ", $order, array_keys($parts)[0], array_values($parts)[0]);
					endif;
				endif;
			endif;
			if (array_key_exists("LIMIT", $terms)) :
				$string .= sprintf(" LIMIT %d", $terms["LIMIT"]);
			endif;
			if (array_key_exists("OFFSET", $terms)) :
				$string .= sprintf(" OFFSET %d", $terms["OFFSET"]);
			endif;

			return $string;
		}

	}

}
?>