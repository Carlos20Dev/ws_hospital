<?php
class Conexion
{
  private $host = 'tcp:serverhospital.database.windows.net';
  private $database = 'hospital';
  private $user = 'utpadmin';
  private $pass = 'GrupoUTP2024';
  private $connection;

  public function __construct()
  {
    try {
      $this->connection = new PDO(
        "mysql:host={$this->host};dbname={$this->database};charset=utf8",
        $this->user,
        $this->pass
      );
      // Configura PDO para lanzar excepciones en caso de error
      $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      echo "Conexión exitosa a la base de datos.";
    } catch (PDOException $e) {
      echo "Error de conexión: " . $e->getMessage();
      die(); // Termina la ejecución si no se puede conectar
    }
  }

  // Función para obtener la conexión
  public function getConnection()
  {
    return $this->connection;
  }

  // Cierra la conexión
  public function closeConnection()
  {
    $this->connection = null;
  }
}
