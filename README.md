## PENDIENTE DOCUMENTACION DE TRANSACCIONES

# Dru.DataAccess

## 1. Configuracion

Modificar las constantes de la clase DAL (DAL.php) con su respectiva configuracion

```php
private const MOTORBD = 'mysql';
private const SERVIDOR = 'localhost';
private const PUERTO = '3306';
private const BASEDATOS = 'Nombre_base_datos';
private const USUARIO = 'Usuario_Acceso';
private const CLAVE = 'Contraseña_Acceso';
```
## 3. Sintaxis

```php
/**
* Select Consulta SQL con retorno de array asociativo
* @param string $query Consulta SQL
* @param bool $obtenerTodos=true Por defecto en true, obtiene todos los registros que genere la consulta, en false obtiene el primer registro
* @param array &$where=null El numero de valores debe corresponder con el numero de ? en el Query
* @return array
*/
public Select(string $query [, $obtenerTodos [, $where]]) : array

/**
* Query Consulta SQL con retorno true/false (modificacion contenido o estructura)
* @param string $query Consulta SQL
* @param array &$datos El numero de valores debe corresponder con el numero de ? en el Query
* @return bool
*/
public Query(string $query, $datos) : bool

```

## 2. Utilizacion

* Crear instancia de la clase

```php
$sql = new DAL();
```

* Obtener datos

```php
//return Array Asociativo
$datos = $sql->Select('SELECT * FROM miTabla');
```

* Obtener el primer registro
```php
//return Array Asociativo o false si no se encontrara
$datos = $sql->Select('SELECT * FROM miTabla', false);
```

* Obtener datos con where

```php
$where = [
	'F',
	'Rock'
];
//return Array Asociativo
$datos = $sql->Select('SELECT * FROM miTabla WHERE genero = ? AND gustoMusical = ?', true, $where);
```

* Insertar registros 

```php
$datos = [
	'Kakaroto',
	'Milk'
];
//return bool
$sql->Query('INSERT INTO miTabla  (nombres) VALUES (?) , (?)', $datos);
```

* Actualizar registro

```php
$datos = [
	'Son Goku',
	'Kakaroto'
];
//return bool
$sql->Query('UPDATE miTabla SET nombres = ? WHERE nombres = ?', $datos);
```

* Eliminar registro

```php
$datos = [
	0
];
//return bool
$sql->Query('DELETE FROM miTabla WHERE id = ?', $datos);
```

## Creditos
Desarrollado por MALDRU [ING DEVELOPERS](http://ing-developers.com "ING DEVELOPERS")

## Licencia
[MIT](https://github.com/MALDRU/php/blob/master/Dru.DataAccess/LICENCE "MIT")