<?php
require_once(__DIR__ . '/../config/Database.php');
require_once __DIR__ . '/../src/Repository/ReviewRepository.php';

use Config\Database;
use Src\Repository\ReviewRepository;

$responseMessage = "";
$statusClass = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitización estricta de entradas
    $data = [
        'marca'            => trim(strip_tags($_POST['marca'] ?? '')),
        'modelo'           => trim(strip_tags($_POST['modelo'] ?? '')),
        'categoria'        => trim(strip_tags($_POST['categoria'] ?? '')),
        'especificaciones' => trim(strip_tags($_POST['especificaciones'] ?? '')),
        'puntuacion'       => filter_var($_POST['puntuacion'] ?? 0, FILTER_VALIDATE_INT),
        'comentario'       => trim(strip_tags($_POST['comentario'] ?? ''))
    ];

    // Validación del lado del servidor
    if (!empty($data['marca']) && !empty($data['modelo']) && !empty($data['comentario']) && $data['puntuacion'] >= 1 && $data['puntuacion'] <= 5) {
        try {
            $postgresDb = Database::getPostgresConnection();
            $mongoDb    = Database::getMongoConnection();
            
            $repository = new ReviewRepository($postgresDb, $mongoDb);
            
            if ($repository->saveReview($data)) {
                $responseMessage = "✅ Registro Exitoso: Reseña almacenada en el clúster transaccional y respaldada en NoSQL.";
                $statusClass = "alert-success";
            } else {
                $responseMessage = "❌ Error: No se pudo procesar la transacción interna.";
                $statusClass = "alert-danger";
            }
        } catch (Exception $e) {
            $responseMessage = "⚠️ " . $e->getMessage();
            $statusClass = "alert-danger";
        }
    } else {
        $responseMessage = "❌ Error: Campos obligatorios vacíos o datos fuera de rango.";
        $statusClass = "alert-danger";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniLago S.A. - Control Interno de Calidad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', system-ui, sans-serif; color: #1e293b; }
        .navbar-brand { font-weight: 700; color: #0f172a !important; letter-spacing: -0.5px; }
        .card-enterprise { border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); background: white; }
        .btn-primary-corp { background-color: #2563eb; border: none; font-weight: 600; padding: 12px; }
        .btn-primary-corp:hover { background-color: #1d4ed8; }
    </style>
</head>
<body>
    <nav class="navbar navbar-light bg-white border-bottom py-3">
        <div class="container">
            <span class="navbar-brand">🏢 UNILAGO <span class="text-primary">Enterprise</span></span>
            <a href="index.php" class="btn btn-outline-secondary btn-sm fw-semibold">Consultar Repositorio</a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card card-enterprise p-5">
                    <h2 class="mb-4 h4 fw-bold">Formulario de Control de Reseñas de Equipos</h2>
                    
                    <?php if (!empty($responseMessage)): ?>
                        <div class="alert <?php echo $statusClass; ?> fw-medium mb-4" role="alert">
                            <?php echo $responseMessage; ?>
                        </div>
                    <?php endif; ?>

                    <form action="registro.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Marca Comercial *</label>
                                <select class="form-select" name="marca" required>
                                    <option value="ASUS ROG">ASUS ROG</option>
                                    <option value="MSI Gaming">MSI Gaming</option>
                                    <option value="Lenovo Legion">Lenovo Legion</option>
                                    <option value="Apple">Apple</option>
                                    <option value="Samsung Odyssey">Samsung Odyssey</option>
                                    <option value="NVIDIA GeForce">NVIDIA GeForce</option>
                                    <option value="Corsair">Corsair</option>
                                    <option value="HP Omen">HP Omen</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Modelo del Componente / Equipo *</label>
                                <input type="text" class="form-select" name="modelo" placeholder="Ej: RTX 5090, MacBook Pro M4" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Línea de Negocio (Categoría) *</label>
                                <select class="form-select" name="categoria" required>
                                    <option value="Portátiles">Portátiles / Laptops</option>
                                    <option value="Tarjetas Gráficas">Tarjetas Gráficas (GPUs)</option>
                                    <option value="Monitores">Monitores High-End</option>
                                    <option value="Periféricos">Periféricos Profesionales</option>
                                    <option value="Procesadores">Procesadores / CPUs</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Métrica de Calidad (Puntuación) *</label>
                                <select class="form-select" name="puntuacion" required>
                                    <option value="5">⭐⭐⭐⭐⭐ (Óptimo)</option>
                                    <option value="4">⭐⭐⭐⭐ (Aceptable)</option>
                                    <option value="3">⭐⭐⭐ (Regular)</option>
                                    <option value="2">⭐⭐ (Bajo Rendimiento)</option>
                                    <option value="1">⭐ (Defectuoso)</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Especificaciones de Arquitectura Técnica</label>
                                <textarea class="form-control" name="especificaciones" rows="2" placeholder="Ej: VRAM 32GB GDDR7, Bus 512-bit, TSMC 3nm..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Análisis Crítico Técnico *</label>
                                <textarea class="form-control" name="comentario" rows="4" placeholder="Ingrese el reporte detallado del comportamiento técnico en pruebas..." required></textarea>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary btn-primary-corp w-100 text-uppercase tracking-wider">Emitir Publicación y Respaldar Nodo NoSQL</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>