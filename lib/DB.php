<?php
class DataSource
{

    // PHP 7.1.0 visibility modifiers are allowed for class constants.
    // when using above 7.1.0, declare the below constants as private
    const HOST = 'localhost';

    //Local Test Server
    const USERNAME = 'root';
    const PASSWORD = 'Sistemas.VEN911';
    const DATABASENAME = 'cw911';
    
    //Cloud Test Server
    //const USERNAME = 'id20856038_root';
    //const PASSWORD = 'Sistemas.VEN911';
    //const DATABASENAME = 'id20856038_ven911_ng';

    private $conn;

    /**
     * PHP implicitly takes care of cleanup for default connection types.
     * So no need to worry about closing the connection.
     *
     * Singletons not required in PHP as there is no
     * concept of shared memory.
     * Every object lives only for a request.
     *
     * Keeping things simple and that works!
     */
    function __construct()
    {
        $this->conn = $this->getConnection();
		if (!$this->conn) {$this->err=true;}
    }

    /**
     * If connection object is needed use this method and get access to it.
     * Otherwise, use the below methods for insert / update / etc.
     *
     * @return \mysqli
     */
    public function getConnection()
    {
        $conn = @new \mysqli(self::HOST, self::USERNAME, self::PASSWORD, self::DATABASENAME);
        
        if (mysqli_connect_errno()) {return false;}
 
        $conn->set_charset("utf8");
        return $conn;
    }

    /**
     * To get database results
     * @param string $query
     * @param string $paramType
     * @param array $paramArray
     * @return array
     */
    public function select($query, $paramType="", $paramArray=array())
    {
        $stmt = $this->conn->prepare($query);
        
        if(!empty($paramType) && !empty($paramArray)) {
            $this->bindQueryParams($stmt, $paramType, $paramArray);
        }
        
        if (!$stmt) {
            // Si la preparación de la consulta falla, se informa el error y se devuelve falso
            $this->err = true;
            $this->error = "Error en la preparación de la consulta: " . $this->conn->error;
            //Depurar la consulta enviando mensaje de error al cliente web
            //err($query);
            return false;
        }
        
        if (!$stmt->execute()) {
            // Si la ejecución de la consulta falla, se informa el error y se devuelve falso
            $this->err = true;
            $this->error = "Error al ejecutar la consulta: " . $stmt->error;
            return false;
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $resultset[] = $row;
            }
        }
        
        if (! empty($resultset)) {
            return $resultset;
        }
    }
    
    /**
     * To insert
     * @param string $query
     * @param string $paramType
     * @param array $paramArray
     * @return int
     */
    public function insert($query, $paramType, $paramArray)
    {
        print $query;
        $stmt = $this->conn->prepare($query);
        $this->bindQueryParams($stmt, $paramType, $paramArray);
        
        if (!$stmt) {
            // Si la preparación de la consulta falla, se informa el error y se devuelve falso
            $this->err = true;
            $this->error = "Error en la preparación de la consulta: " . $this->conn->error;
            return false;
        }
        
        if (!$stmt->execute()) {
            // Si la ejecución de la consulta falla, se informa el error y se devuelve falso
            $this->err = true;
            $this->error = "Error al ejecutar la consulta: " . $stmt->error;
            return false;
        }
        
        $insertId = $stmt->insert_id;
        return $insertId;
    }
    
    /**
     * To execute query
     * @param string $query
     * @param string $paramType
     * @param array $paramArray
     */
    public function execute($query, $paramType="", $paramArray=array())
    {
        $stmt = $this->conn->prepare($query);
        
        if(!empty($paramType) && !empty($paramArray)) {
            $this->bindQueryParams($stmt, $paramType="", $paramArray=array());
        }
        
        if (!$stmt) {
            // Si la preparación de la consulta falla, se informa el error y se devuelve falso
            $this->err = true;
            $this->error = "Error en la preparación de la consulta: " . $this->conn->error;
            return false;
        }

        if (!$stmt->execute()) {
            // Si la ejecución de la consulta falla, se informa el error y se devuelve falso
            $this->err = true;
            $this->error = "Error al ejecutar la consulta: " . $stmt->error;
            return false;
        }
		
		return true;
    }
    
    /**
     * 1. Prepares parameter binding
     * 2. Bind prameters to the sql statement
     * @param string $stmt
     * @param string $paramType
     * @param array $paramArray
     */
    public function bindQueryParams($stmt, $paramType, $paramArray=array())
    {
        $paramValueReference[] = & $paramType;
        for ($i = 0; $i < count($paramArray); $i ++) {
            $paramValueReference[] = & $paramArray[$i];
        }
        call_user_func_array(array(
            $stmt,
            'bind_param'
        ), $paramValueReference);
    }
    
    /**
     * To get database results
     * @param string $query
     * @param string $paramType
     * @param array $paramArray
     * @return array
     */
    public function numRows($query, $paramType="", $paramArray=array())
    {
        $stmt = $this->conn->prepare($query);
        
        if(!empty($paramType) && !empty($paramArray)) {
            $this->bindQueryParams($stmt, $paramType, $paramArray);
        }
        
        $stmt->execute();
        $stmt->store_result();
        $recordCount = $stmt->num_rows;
        return $recordCount;
    }
	
	/**
     * Funciones adicionales
     *	getUserData: 
	 *		Obtener toda la información del usuario (id). Limitarse a los datos solicitados si (attr) está definido.
	 *		Devuelve un diccionario que contiene tanto el valor como la definición, valores admitidos y valor procesado.
	 *	setUserData: 
	 *		Establecer información del usuario (id). Actualizar (val) si (attr+id) ya está definido.
	 *		
     */
	 
    public function getUserData($id, $attr=array())
    {
        if ($id == -1) $id= $_SESSION["uid"];
		$prestmt = $this->conn->prepare("SELECT * FROM userdata_def ORDER BY `order` ASC, `suborder` ASC");
		if (!$prestmt) {
            // Si la preparación de la consulta falla, se informa el error y se devuelve falso
            $this->err = true;
            $this->error = "Error en la preparación de la consulta: " . $this->conn->error;
            return false;
        }
		
		if (!$prestmt->execute()) {
            // Si la ejecución de la consulta falla, se informa el error y se devuelve falso
            $this->err = true;
            $this->error = "Error al ejecutar la consulta: " . $stmt->error;
            return false;
        } else {
			$result = $prestmt->get_result();
			if ($result->num_rows > 0) {
				while ($row = $result->fetch_assoc()) {
					$def[$row["attr"]] = $row["def"];
					$vals[$row["attr"]] = $row["values"];
				}
			}
		}
		
        if (!empty($attr)) {
            $stmt = $this->conn->prepare("SELECT attr, val FROM userdata WHERE ID = " .$id. " AND attr IN ('" .implode("', '", $attr). "')");
        } else {
            $stmt = $this->conn->prepare("SELECT attr, val FROM userdata WHERE ID = " .$id);
        }
		
        if (!$stmt) {
            // Si la preparación de la consulta falla, se informa el error y se devuelve falso
            $this->err = true;
            $this->error = "Error en la preparación de la consulta: " . $this->conn->error;
            return false;
        }
        
        if (!$stmt->execute()) {
            // Si la ejecución de la consulta falla, se informa el error y se devuelve falso
            $this->err = true;
            $this->error = "Error al ejecutar la consulta: " . $stmt->error;
            return false;
        }
		
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
				$usrattr[$row["attr"]] = $row["val"];
            }
        }
        
        if (! empty($usrattr)) {
            foreach ($def as $key => $value) {
                if (! empty($usrattr[$key])) {
                    $pval = $usrattr[$key]; $val = $usrattr[$key];
                    // Procesar el valor de la propiedad basado en las reglas del valor.
                    // Booleano:
                    if ($vals[$key] == "BOOLEAN") {
                        $val = ($usrattr[$key] == "1");
                        $pval = ($usrattr[$key] == "1") ? "Verdadero" : "Falso";
                    }
                    // Poblar el array con todo lo procesado
                    $resultset[$key] = array("value" => $val, "proc" => $pval, "def" => $def[$key]);
                }
            }
        }

        if (! empty($resultset)) {
            return $resultset;
        }
    }
	
	public function setUserData($id, $attr, $val)
    {
		$query = "INSERT INTO userdata (ID, attr, val) VALUES (".$id.", '".$attr."', '".$val."') ON DUPLICATE KEY UPDATE val='".$val."';";
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            // Si la preparación de la consulta falla, se informa el error y se devuelve falso
            $this->err = true;
            $this->error = "Error en la preparación de la consulta: " . $this->conn->error;
            return false;
        }

        if (!$stmt->execute()) {
            // Si la ejecución de la consulta falla, se informa el error y se devuelve falso
            $this->err = true;
            $this->error = "Error al ejecutar la consulta: " . $stmt->error;
            return false;
        }
		
		return true;
    }
    
    public function getModCFG($mod) {
        $stmt = $this->select("SELECT enabled, config, data FROM modules WHERE id='".$mod."'");
        if(!empty($stmt)) {
            return array(
                "enabled" => (boolval($stmt[0]["enabled"])),
                "config" => (array)json_decode($stmt[0]["config"]),
                "data" => (array)json_decode($stmt[0]["data"])
                );
        } else {return false;}
    }
    
    public function setModCFG($mod, $cfg) {
        $stmt = $this->execute("UPDATE modules SET enabled='".boolval($cfg["enabled"])."', config='".json_encode($cfg["config"])."', data='".json_encode($cfg["data"])."' WHERE id='".$mod."'");
        if($stmt) {
            return true;
        } else {return false;}
    }
    
    public function getMenu() 
    {
        $result = $this->select("SELECT `structure` from modules");
        if (@$this->err) {
            err("db-err", $this->error);
        }
        $result2 = $this->select("SELECT `perms` from accesslevels WHERE ID=" .$_SESSION["ual"]);
        if (@$this->err) {
            err("db-err", $this->error);
        }
        $stmt = [];
        $permsBool = [];
        foreach ($result as $key => $value) {
            $stmt = array_merge($stmt, json_decode($result[$key]["structure"], true));
        }
        $permsBool = json_decode($result2[0]["perms"], true);
        if (!$permsBool == null) $stmt = array_merge_recursive($stmt, $permsBool);
        foreach ($stmt as $key => $value) {
            if (@$stmt[$key]['enabled']) {
                foreach ($stmt[$key]["items"] as $key2 => $value2) {
                    if (!@$stmt[$key]["items"][$key2]['enabled']) {
                        unset($stmt[$key]["items"][$key2]);
                    }
                }
            } else {
                unset($stmt[$key]);
            }
        }
        
        return json_encode($stmt);
    }
    
    public function Authorize() {
        global $vars;
        $stmt = $this->select("SELECT `perms` from accesslevels WHERE ID=" .@$_SESSION["ual"]);
        if(!empty($stmt)) {
            $ALPerms = json_decode($stmt[0]["perms"], true);
            if (isset($ALPerms[$vars["modBaseName"]]) && !@$ALPerms[$vars["modBaseName"]]['enabled']) {err("module-not-authorized"); return false;}
            if (@$_GET["req"] == "module") {
                if (isset($ALPerms[$vars["modBaseName"]]['items'][$vars["modSectionName"]]) && $ALPerms[$vars["modBaseName"]]['items'][$vars["modSectionName"]]['enabled']) {
                    return true;
                }
                err("section-not-authorized");
                return false;
            } else if (@$_GET["req"] == "function") {
                return true;
            }
        } else {err("invalid-accesslevel"); return false;}
    }
}
?>