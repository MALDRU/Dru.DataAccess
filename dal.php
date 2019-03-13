<?php
class DAL
{
    /*PROPIEDADES*/
    private const CONEXION_BD = [
        'MOTORBD' => 'mysql',
        'SERVIDOR' => 'localhost',
        'PUERTO' => '3306',
        'BASEDATOS' => '',
        'USUARIO' => '',
        'CLAVE' => ''
    ];
    private $conexion = null;
    private $sentencia = null;
    public const CORRECTO = '-_0';
    public const INCORRECTO = 'T_T';
    public const COMMIT = 'C_C';
    public const NO_COMMIT = 'NC_NC';
    public const ROLLBACK = 'R_R';
    public const NO_ROLLBACK = 'NR_NR';
    public $error = [self::CORRECTO, self::CORRECTO, self::CORRECTO];

    /*CONSTRUCTOR Y DESTRUCTOR*/
    /**
     * DAL Permite gestionar la conexion y ejecucion de sentencias SQL
     * @* @param bool $AutoConectar Utiliza los datos de conexion internos cuando esta en true, en false no se conectara a menos que se ejecute el metodo Conectar
     */
    public function __construct($AutoConectar = true)
    {
        set_error_handler('DAL::ManejarErrores');
        if ($AutoConectar) {
            $this->Conectar(self::CONEXION_BD);
        }
    }
    public function __destruct()
    {
        unset($this->conexion, $this->error, $this->sentencia);
    }

    /*METODOS PRIVADOS*/
    /**
     * ReinicarError Reinicia los estados del error
     * @* @param bool $bueno Por defecto en true, reinicia al error a estado correcto en false a incorrecto
     */
    private function ReinicarError($bueno = true)
    {
        if ($bueno) {
            $this->error[0] = self::CORRECTO;
            $this->error[1] = self::CORRECTO;
            $this->error[2] = self::CORRECTO;
        } else {
            $this->error[0] = self::INCORRECTO;
            $this->error[1] = self::INCORRECTO;
            $this->error[1] = self::INCORRECTO;
        }
    }
    /**
     * VerificarConexion Verifica el estado de conexion e impide continuar si existe algun problema
     */
    private function VerificarConexion()
    {
        if ($this->conexion === null) {
            $this->ReinicarError(false);
            $this->error[2] = 'No existe ninguna conexion disponible al servidor de base de datos';
            return false;
        }
        return true;
    }
    /**
     * VerificarError Verifica si verdaderamente se produjo un error en la ejecucion de alguna sentencia SQL
     * @* @param bool $resultado Resultado de la ejecucion de la sentencia SQL
     * @return bool Retorna true / false segun sea el resultado
     */
    private function VerificarError($resultado)
    {
        if ($resultado === false) {
            $this->error = $this->sentencia->errorInfo();
            if ($this->error[0] === '00000' || $this->error[0] === '01000') {
                return true;
            }
        }
        return $resultado;
    }

    /*METODOS PUBLICOS*/
    /**
     * ManejarErrores Hanlder para controlar cualquier error, requerido para transacciones
     */
    public static function ManejarErrores($errno, $errstr, $errfile, $errline)
    {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
    /**
     * Conectar Permite abrir una conexion especificando los datos de conexion
     * @* @param Array $datosConexion array con los datos de conexion ['MOTORBD' => 'myql','SERVIDOR' => 'localhost','PUERTO' => '3306','BASEDATOS' => 'pizarra','USUARIO' => 'root','CLAVE' => '302061']
     */
    public function Conectar($datosConexion)
    {
        try {
            $this->conexion = new PDO(
                $datosConexion['MOTORBD'] . ':dbname=' . $datosConexion['BASEDATOS'] . ';host=' . $datosConexion['SERVIDOR'] . ';port=' . $datosConexion['PUERTO'],
                $datosConexion['USUARIO'],
                $datosConexion['CLAVE']
            );
        } catch (PDOException $e) {
            $this->ReinicarError(false);
            $this->error[2] = $e->getMessage();
        }
    }
    /**
     * Seleccionar Permite ejecutar una sentencia SQL que podria retornar registros
     * @* @param string $query Sentencia SQL
     * @* @param bool $repetirSentenciaAnterior Permite utilizar la ultima sentencia preparada si se establece en true
     * @* @param bool $obtenerTodos Description Por defecto en true, obtiene todos los registros que genere la consulta, en false obtiene el primer registro
     * @* @param ... $parametros (OPCIONAL) si la sentencia SQL contiene marcadores (?) debe referenciarlos ($paramentro1, $paramentro2, $paramentro3)
     * @return array Retorna array asociativo. Si el parametro $obtenerTodos es false, podria retornar false si no llega a encontrar ningun registro
     */
    public function Seleccionar($query, $repetirSentenciaAnterior = false, $obtenerTodos = true, ...$parametros)
    {
        $this->ReinicarError();
        if (!$this->VerificarConexion()) return false;
        if (!$repetirSentenciaAnterior || $this->sentencia === null) {
            if ($this->sentencia !== null) $this->sentencia->closeCursor();
            $this->sentencia = $this->conexion->prepare($query);
        }
        $c = 1;
        foreach ($parametros as &$valor) {
            $this->sentencia->bindParam($c, $valor);
            $c++;
        }
        $this->VerificarError($this->sentencia->execute());
        if ($obtenerTodos) return $this->sentencia->fetchAll(PDO::FETCH_ASSOC);
        return $this->sentencia->fetch(PDO::FETCH_ASSOC);
    }
    /**
     * EjecutarSentencia Permite ejecutar una sentencia SQL con valor de retorno true/false segun sea el resultado de la ejecucion
     * @* @param string $query Sentencia SQL
     * @* @param bool $ultimoID Obtener el id generado en la sentencia
     * @* @param bool $repetirSentenciaAnterior Permite utilizar la ultima sentencia preparada si se establece en true
     * @* @param ... (OPCIONAL) si la sentencia SQL contiene marcadores (?) debe referenciarlos ($paramentro1, $paramentro2, $paramentro3)
     * @return bool Retorna true/false segun sea el resultado de la ejecucion
     */
    public function EjecutarSentencia($query, $ultimoID = false, $repetirSentenciaAnterior = false, ...$parametros)
    {
        $this->ReinicarError();
        if (!$this->VerificarConexion()) return false;
        if (!$repetirSentenciaAnterior || $this->sentencia === null) {
            if ($this->sentencia !== null) $this->sentencia->closeCursor();
            $this->sentencia = $this->conexion->prepare($query);
        }
        $c = 1;
        foreach ($parametros as &$valor) {
            $this->sentencia->bindParam($c, $valor);
            $c++;
        }
        $estado = $this->VerificarError($this->sentencia->execute());
        return $ultimoID ? $this->conexion->lastInsertId() : $estado;
    }
    /**
     * IniciarTransaccion Permite Iniciar una transaccion con el servidor de base de datos
     */
    public function IniciarTransaccion()
    {
        $this->ReinicarError();
        if (!$this->VerificarConexion()) return false;
        $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            $this->ReinicarError($this->conexion->beginTransaction());
        } catch (Exception $e) {
            $this->ReinicarError(false);
            $this->error[2] = $e->getMessage();
        }
    }
    /**
     * RevertirTransaccion Permite revertir la transaccion que este activa
     */
    public function RevertirTransaccion()
    {
        $this->ReinicarError();
        if (!$this->VerificarConexion()) return false;
        try {
            $correcto = $this->conexion->rollBack();
            if ($correcto) {
                $this->error[0] = self::ROLLBACK;
            } else {
                $this->error[0] = self::NO_ROLLBACK;
            }
        } catch (Exception $e) {
            $this->error[0] = self::NO_ROLLBACK;
            $this->error[2] = $e->getMessage();
        }
    }
    /**
     * FinalizarTransaccion Permite finalizar una transaccion e intentar realizar un rollback si existiera algun error
     */
    public function FinalizarTransaccion()
    {
        $this->ReinicarError();
        if (!$this->VerificarConexion()) return false;
        try {
            $correcto = $this->conexion->commit();
            if ($correcto) {
                $this->error[0] = self::COMMIT;
            } else {
                $this->error[0] = self::NO_COMMIT;
            }
        } catch (Exception $e) {
            $this->error[2] = $e->getMessage();
            $this->RevertirTransaccion();
        }
    }
}
?>