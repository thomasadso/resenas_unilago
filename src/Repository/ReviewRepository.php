<?php
namespace Src\Repository;

use PDO;
use MongoDB\Driver\BulkWrite;
use Exception;

class ReviewRepository {
    private PDO $db;
    private $mongo;
    private string $mongoNS = 'unilago.resenas';

    public function __construct(PDO $postgresDb, $mongoDb) {
        $this->db = $postgresDb;
        $this->mongo = $mongoDb;
    }

    /**
     * Registra la reseña en PostgreSQL y genera un respaldo en MongoDB Atlas
     */
    public function saveReview(array $data): bool {
        // 1. Persistencia Obligatoria en Core Relacional
        $sql = "INSERT INTO resenas_equipos (marca, modelo, category, especificaciones, puntuacion, comentario) 
                VALUES (:marca, :modelo, :categoria, :especificaciones, :puntuacion, :comentario)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':marca'            => $data['marca'],
            ':modelo'           => $data['modelo'],
            ':categoria'        => $data['categoria'],
            ':especificaciones' => $data['especificaciones'] ?: null,
            ':puntuacion'       => (int)$data['puntuacion'],
            ':comentario'       => $data['comentario']
        ]);

        if ($result) {
            // 2. Respaldo Asíncrono/Graceful en Mongo Atlas
            try {
                $bulk = new BulkWrite();
                $bulk->insert([
                    'marca'            => $data['marca'],
                    'modelo'           => $data['modelo'],
                    'categoria'        => $data['categoria'],
                    'especificaciones' => $data['especificaciones'],
                    'puntuacion'       => (int)$data['puntuacion'],
                    'comentario'       => $data['comentario'],
                    'synchronized_at'  => date('c')
                ]);
                $this->mongo->executeBulkWrite($this->mongoNS, $bulk);
            } catch (Exception $e) {
                // Registramos el fallo en logs pero no alteramos el flujo del usuario
                error_log("NOSQL SYNC WARNING: Falló el respaldo en Mongo Atlas: " . $e->getMessage());
            }
            return true;
        }
        return false;
    }

    /**
     * Obtiene todos los registros optimizados por índice
     */
    public function getAllReviews(): array {
        $sql = "SELECT id, marca, modelo, category as categoria, especificaciones, puntuacion, comentario, creado_en 
                FROM resenas_equipos 
                ORDER BY creado_en DESC 
                LIMIT 100"; // Paginación preventiva para optimizar carga
        return $this->db->query($sql)->fetchAll();
    }
}