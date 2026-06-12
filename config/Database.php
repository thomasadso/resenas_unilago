<?php
namespace Config;

use PDO;
use PDOException;
use MongoDB\Driver\Manager as MongoManager;
use Exception;

class Database {
    private static ?PDO $pdoInstance = null;
    private static ?MongoManager $mongoInstance = null;

    /**
     * Retorna la conexión Singleton a PostgreSQL
     */
    public static function getPostgresConnection(): PDO {
        if (self::$pdoInstance === null) {
            $host = getenv('PGHOST') ?: '127.0.0.1';
            $port = getenv('PGPORT') ?: '5432';
            $db   = getenv('PGDATABASE');
            $user = getenv('PGUSER') ?: 'postgres';
            $pass = getenv('PGPASSWORD');

            try {
                $dsn = "pgsql:host=$host;port=$port;dbname=$db";
                self::$pdoInstance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // Registro interno en el servidor, oculto al usuario externo por seguridad
                error_log("CRITICAL DB ERROR: " . $e->getMessage());
                throw new Exception("Error interno en el servidor de datos relacionales.");
            }
        }
        return self::$pdoInstance;
    }

    /**
     * Retorna el manejador de conexiones para Mongo Atlas
     */
    public static function getMongoConnection(): MongoManager {
        if (self::$mongoInstance === null) {
            $uri = getenv('MONGO_URI') ?: 'mongodb://127.0.0.1:27017';
            try {
                self::$mongoInstance = new MongoManager($uri);
            } catch (Exception $e) {
                error_log("CRITICAL NOSQL ERROR: " . $e->getMessage());
                throw new Exception("Error interno en el servidor de respaldo NoSQL.");
            }
        }
        return self::$mongoInstance;
    }
}