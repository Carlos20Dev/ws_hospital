<?php
header('Content-Type: application/json');
include 'conexion.php';

$pdo = (new Conexion())->getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// Función para obtener todos los registros de una tabla
function get_data($pdo, $tabla)
{
  try {
    $sql = "SELECT * FROM " . $tabla;
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    return ["error" => "Error al obtener datos: " . $e->getMessage()];
  }
}

// Procesar métodos HTTP
switch ($method) {
  case 'GET':
    if (isset($_GET['tabla'])) {
      $tabla = $_GET['tabla'];

      try {
        if (isset($_GET['id'])) {
          // Consulta de un solo registro por ID
          $sql = "SELECT * FROM " . $tabla . " WHERE id = :id";
          $stmt = $pdo->prepare($sql);
          $stmt->execute([':id' => $_GET['id']]);
          $data = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
          // Consultar todos los registros
          $data = get_data($pdo, $tabla);
        }

        echo json_encode($data);
      } catch (PDOException $e) {
        echo json_encode(["error" => "Error en la consulta: " . $e->getMessage()]);
      }
    } else {
      echo json_encode(["error" => "Falta parámetro 'tabla'"]);
    }
    break;

  case 'POST':
    if (isset($_GET['tabla'])) {
      $tabla = $_GET['tabla'];
      $data = json_decode(file_get_contents("php://input"), true);

      // Insertar nuevo registro
      $columns = implode(", ", array_keys($data));
      $values = ":" . implode(", :", array_keys($data));
      $sql = "INSERT INTO $tabla ($columns) VALUES ($values)";
      $stmt = $pdo->prepare($sql);

      foreach ($data as $key => &$val) {
        $stmt->bindParam(":$key", $val);
      }

      try {
        $stmt->execute();
        echo json_encode(["mensaje" => "Registro creado con éxito en la tabla $tabla"]);
      } catch (PDOException $e) {
        echo json_encode(["error" => "Error al crear registro: " . $e->getMessage()]);
      }
    }
    break;

  case 'PUT':
    if (isset($_GET['tabla']) && isset($_GET['id'])) {
      $tabla = $_GET['tabla'];
      $id = $_GET['id'];
      $data = json_decode(file_get_contents("php://input"), true);

      // Actualizar registro
      $fields = "";
      foreach ($data as $key => $value) {
        $fields .= "$key = :$key, ";
      }
      $fields = rtrim($fields, ", ");
      $sql = "UPDATE $tabla SET $fields WHERE id = :id";
      $stmt = $pdo->prepare($sql);

      foreach ($data as $key => &$val) {
        $stmt->bindParam(":$key", $val);
      }
      $stmt->bindParam(":id", $id);

      try {
        $stmt->execute();
        echo json_encode(["mensaje" => "Registro actualizado con éxito en la tabla $tabla"]);
      } catch (PDOException $e) {
        echo json_encode(["error" => "Error al actualizar registro: " . $e->getMessage()]);
      }
    }
    break;

  case 'DELETE':
    if (isset($_GET['tabla']) && isset($_GET['id'])) {
      $tabla = $_GET['tabla'];
      $id = $_GET['id'];

      // Eliminar registro
      $sql = "DELETE FROM $tabla WHERE id = :id";
      $stmt = $pdo->prepare($sql);
      $stmt->bindParam(':id', $id);

      try {
        $stmt->execute();
        echo json_encode(["mensaje" => "Registro eliminado con éxito de la tabla $tabla"]);
      } catch (PDOException $e) {
        echo json_encode(["error" => "Error al eliminar registro: " . $e->getMessage()]);
      }
    }
    break;

  default:
    echo json_encode(["error" => "Método HTTP no soportado"]);
    break;
}

// Cierra la conexión
$pdo = null;
