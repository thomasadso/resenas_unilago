<?php
require_once(__DIR__ . '/../config/Database.php');
require_once __DIR__ . '/../src/Repository/ReviewRepository.php';

use Config\Database;
use Src\Repository\ReviewRepository;

$reviews = [];
$errorMsg = "";

try {
    $postgresDb = Database::getPostgresConnection();
    // Pasamos null a Mongo en lecturas si solo queremos consumir Postgres velozmente
    $repository = new ReviewRepository($postgresDb, null);
    $reviews = $repository->getAllReviews();
} catch (Exception $e) {
    $errorMsg = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniLago S.A. - Repositorio Global de Reseñas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; color: #1e293b; }
        .card-review { border: 1px solid #e2e8f0; background: #ffffff; border-radius: 8px; transition: 0.2s; }
        .card-review:hover { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
        .badge-corp { background-color: #f1f5f9; color: #334155; font-weight: 600; padding: 6px 12px; border-radius: 6px; }
        .badge-sync { font-size: 0.75rem; background-color: #dcfce7; color: #15803d; font-weight: 700; padding: 4px 8px; border-radius: 4px; }
        .specs-box { background-color: #0f172a; color: #38bdf8; font-family: 'SFMono-Regular', monospace; font-size: 0.85rem; padding: 12px; border-radius: 6px; border-left: 4px solid #0284c7; }
    </style>
</head>
<body>
    <nav class="navbar navbar-light bg-white border-bottom py-3">
        <div class="container">
            <span class="navbar-brand fw-bold">🏢 UNILAGO <span class="text-primary">Enterprise</span></span>
            <a href="registro.php" class="btn btn-primary btn-sm fw-semibold px-3">+ Registrar Entrada</a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">Módulo Analítico de Hardware</h1>
                <p class="text-muted small mb-0">Datos consolidados en tiempo real desde el nodo PostgreSQL primario</p>
            </div>
            <span class="badge-sync">🔄 ACID COMPLIANT & REPLICA READY</span>
        </div>

        <?php if (!empty($errorMsg)): ?>
            <div class="alert alert-danger fw-semibold"><?php echo $errorMsg; ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <?php if (count($reviews) === 0): ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted fs-5">No se registran auditorías ni reseñas en el sistema de almacenamiento corporativo.</p>
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $r): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-review p-4 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge-corp"><?php echo htmlspecialchars($r['marca']); ?></span>
                                    <span class="text-warning fw-bold"><?php echo str_repeat('★', $r['puntuacion']); ?></span>
                                </div>
                                <h3 class="h5 fw-bold text-dark mb-1"><?php echo htmlspecialchars($r['modelo']); ?></h3>
                                <p class="text-muted small mb-3">Categoría: <?php echo htmlspecialchars($r['categoria']); ?></p>
                                
                                <?php if (!empty($r['especificaciones'])): ?>
                                    <div class="specs-box mb-3">
                                        <?php echo htmlspecialchars($r['especificaciones']); ?>
                                    </div>
                                <?php endif; ?>

                                <p class="text-secondary small mb-4" style="line-height: 1.6;">
                                    "<?php echo htmlspecialchars($r['comentario']); ?>"
                                </p>
                            </div>
                            <div class="border-top pt-2 mt-auto text-end">
                                <small class="text-muted" style="font-size: 0.75rem;">F. Auditoría: <?php echo htmlspecialchars($r['creado_en']); ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>