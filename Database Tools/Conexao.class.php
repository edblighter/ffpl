<?php
/*
 Class Conexao usando PDO.
 Start:27/05/2013

 v0.1(27/05/2013)
 - Criado padrao de criptografia para os dados do banco.
 - Criado o metodo connect,show_error,show_all_error,ex($sql,$mode),getAll($table),volta.
 v0.2(28/05/2013)
 - Adicionado metodo ver, credits,getSpecific($array,$table).
 v0.3(08/07/2013)
 - Adicionado delete_item($colum_name,$key,$table),delete_itens($colum_data,$table)

 v0.4(12/09/2013)
 -Adicionado documentacao das funcoes
 -Corrigido bug da funcao delete_item.
 TODO
 -Paginator
 -LIMIT nos SELECT
 -Adicionar mais codigos de erro em has_error

 */

class Conexao extends PDO {

	/** @var string Base64 da chave de criptografia de acesso ao banco */
	private static $u64key;
	/** @var string Base64 da string de chave de criptogafia */
	private static $cryptKey;
	/** @var string Base64 da string criptografada de acesso ao banco */
	private static $u64data;
	/** @var string String descriptografada de acesso ao banco */
	private static $databaseData;
	/** @var string Mensagem de erro*/
	private $e;
	/** @var array Contem todas as mensagens de erro*/
	private $msg = array();
	/** @var PDO Instancia de conexao */
	private static $conexao;
	/** @var float Versao da classe */
	private $versao;
	/** @var string Contem os creditos da classe */
	private $credits;

	/** Setter do valor da versao da classe
	 * @return float
	 */
	public function ver() {
		$this -> versao = '0.4';
		return $this -> versao;
	}

	/** Imprime os creditos da Classe contendo a versao da classe.
	 * @return string
	 */
	public function credits() {
		$this -> credits = sprintf("\nConexao class ver %s\nCreated by FFPL.\n", $this -> ver());
		return $this -> credits;
	}

	/**
	 *
	 *
	 * */
	private function has_error() {
		if (sizeof($this -> e) > 0) {
			foreach ($this->e as $val => $key) {
				switch($val) {
					case 'get_file_connect' :
						if ($key == '404') {
							$string_erro = "Não foi possivel obter o arquivo de conexão";
							array_push($this -> msg, $string_erro);
						}
						if ($key == 'OK')
							;
						if ($key == '403') {
							$string_erro = "Caminho para o arquivo de conexão nao encontrado";
							array_push($this -> msg, $string_erro);
						}
						break;
					case 'get_file_key' :
						if ($key == '404') {
							$string_erro = "Não foi possivel obter o arquivo de chave";
							array_push($this -> msg, $string_erro);
						}
						if ($key == 'OK')
							;
						if ($key == '403') {
							$string_erro = "Caminho para o arquivo de chave nao encontrado";
							array_push($this -> msg, $string_erro);
						}
						break;
					case '42S22' :
						$string_erro = "Coluna nao encontrada:" . $this -> e['42S22'];
						array_push($this -> msg, $string_erro);
						break;
				}
			}
			if (sizeof($this -> msg) > 0)
				return true;
			else
				return false;
		} else
			return false;
	}

	/**
	 * Função abre dois aquivos de conexão para o banco de dados.
	 * @param pede arquivo de conexão do banco de dados criptografado .
	 * @param pede arquivo key criptografado.
	 * @return boolean
	 */
	public function __construct($connection_file, $key) {
		if (is_file($connection_file))
			if (($k = file_get_contents($connection_file)) === false)
				$this -> e['get_file_connect'] = "404";
			else
				$this -> e['get_file_connect'] = "OK";
		else
			$this -> e['get_file_connect'] = "403";

		if ($this -> has_error())
			return false;

		if (is_file($key))
			if (($p = file_get_contents($key)) === false)
				$this -> e['get_file_key'] = "404";
			else
				$this -> e['get_file_key'] = "OK";
		else
			$this -> e['get_file_key'] = "403";

		if ($this -> has_error())
			return false;

		self::$u64key = base64_decode($p);
		self::$u64data = base64_decode($k);
		self::$databaseData = explode(",", Utils::decrypt(self::$u64data, self::$u64key));
	}

	/**
	 * Função retorna todos os erros, caso não haja erros retorna NULL.
	 * @return string
	 * @access public
	 */
	public function show_all_error() {
		return $this -> msg;

	}

	/**
	 * Função retorna os erros, caso não haja erros retorna NULL.
	 * @return string
	 * @access public
	 */
	public function show_error() {
		return array_pop($this -> msg);
	}

	/**
	 * Conecta o banco da dados.
	 * @return string
	 * @access public
	 */
	public function connect() {
		try {
			self::$conexao = new PDO(trim(self::$databaseData[0]), trim(self::$databaseData[1]), trim(self::$databaseData[2]), array(PDO::ATTR_PERSISTENT => true));
			self::$conexao -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $error) {
			array_push($this -> msg, $error -> getMessage());
			return false;
		}
		return self::$conexao;
	}

	/**
	 * Executa o comando SQL pedido no determinado caso da função.
	 * @param requer um SQL para execução.
	 * @param pede o modo para executar. Exemplo 'exec', 'query', 'prepare'.
	 * @return array
	 * @access public
	 */
	public function ex($sql, $mode) {
		switch($mode) {
			case 'exec' :
				return $this -> conexao -> exec($sql);
				break;
			case 'query' :
				return $this -> conexao -> query($sql);
				break;
			case 'prepare' :
				return $this -> conexao -> prepare($sql);
				break;
		}

	}

	/**
	 * Executa um rollBack no banco da dados.
	 * @return string
	 * @access public
	 */
	public function volta() {
		$this -> conexao -> rollBack();
	}

	/**
	 * Imprime toda a tabela.
	 * @param O nome da tabela do banco da dados a ser imprimida.
	 * @return array
	 * @access public
	 */
	public function getAll($table) {
		try {
			$k = $this -> ex("SELECT * FROM $table", 'query');
			if ($k !== false || is_object($k)) {
				$k -> execute();
				return $k -> fetchAll();
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
	 * Imprime os campos da tabela passados pelo array.
	 * @param Pede uma array com o nome dos campos da tabela.
	 * @param Pede o nome da Tabela a ser consultada.
	 * @return array
	 * @access public
	 */
	public function getSpecific($array, $table) {
		$p = sizeof($array);
		$campos = '';
		for ($i = 0; $i < $p; $i++)
			if ($i < $p - 1)
				$campos .= $array[$i] . ",";
			else
				$campos .= $array[$i];
		try {
			$k = $this -> ex("SELECT $campos FROM $table", 'prepare');
			if ($k !== false || is_object($k)) {
				$k -> execute();
				return $k -> fetchAll();
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
	 * @param Pede o nome dos campos seguido das variaveis a serem inseridas nos mesmos.
	 * @param Pede o nome da tabela.
	 * @return array
	 * @access public
	 */
	public function insert($array, $table) {
		$p = sizeof($array);
		$campos = '';
		$values = '';
		$i = 0;
		foreach ($array as $key => $val) {
			if ($i < $p - 1) {
				$campos .= $key . ",";
				$values .= "'" . $val . "',";
			} else {
				$campos .= $key;
				$values .= "'" . $val . "'";
			}
			$i++;
		}
		$this -> conexao -> beginTransaction();
		try {

			$k = $this -> ex("INSERT INTO $table($campos) VALUES($values)", 'prepare');
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
	 * Retorna o ultimo ID.
	 * @return ID
	 * @access public
	 */
	public function getLastID() {
		return $this -> conexao -> lastInsertId();
	}

	/**
	 * Deleta a linha cujo a key é passa como parametro.
	 * @param nome da coluno onde a key está.
	 * @param nome da key que será excluida.
	 * @param nome da tabela.
	 * @return booleano
	 * @access public
	 */
	public function delete_item($colum_name, $key, $table) {
		$this -> conexao -> beginTransaction();
		try {
			$k = $this -> ex("DELETE FROM $table WHERE $colum_name='$key' LIMIT 1", 'prepare');
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
	 * Deleta itens da linha da tabela cujo parametro passado se encontra
	 * @param nome do item a ser localizado na linha.
	 * @param nome da tabela.
	 * @return booleano
	 * @access public
	 */
	public function delete_itens($colum_data, $table) {
		$this -> conexao -> beginTransaction();
		try {
			foreach ($colum_data as $val => $value) {
				$k = $this -> ex("DELETE FROM $table WHERE $val='$value' LIMIT 1", 'prepare');
				$k -> execute();
			}
			$this -> conexao -> commit();
			return true;
		} catch(PDOException $error) {
			$this -> volta();
			$this -> e[$error -> getCode()] = $error -> getMessage();
			$this -> has_error();
			return false;
		}

	}

}
?>