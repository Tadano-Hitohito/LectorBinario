<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca de ROMs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    <style>
        body { background-color: #121212; color: #e0e0e0; font-family: sans-serif; padding: 20px; }
        .rom-card {
            background-color: #1e1e1e;
            border: 1px solid #333;
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden; /* Para que la imagen no se salga al hacer zoom */
        }
        .rom-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.5);
            border-color: #3498db;
        }
        
        /* Estilos para la portada */
        .rom-cover-wrapper {
            height: 180px;
            background-color: #2c3e50;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .rom-cover-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        .rom-card:hover .rom-cover-img {
            transform: scale(1.1); /* Efecto zoom suave al pasar el mouse */
        }
        .rom-icon-placeholder { font-size: 4rem; opacity: 0.5; }

        /* Botón flotante para subir foto */
        .btn-upload-cover {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0; /* Oculto por defecto */
            transition: opacity 0.2s, background 0.2s;
            z-index: 10;
        }
        .btn-upload-cover:hover { background: #3498db; }
        .rom-card:hover .btn-upload-cover { opacity: 1; /* Mostrar al pasar mouse sobre la tarjeta */ }
        
        /* Reutilizamos tus estilos de botones */
        .btn-glow {
            background: linear-gradient(45deg, #ff6b6b, #feca57);
            border: none; color: white; padding: 8px 20px; border-radius: 25px;
            font-weight: bold; text-decoration: none; display: inline-block;
        }
        .btn-nav-modern {
            background-color: #34495e; color: #ecf0f1; border: 1px solid #465c71;
            padding: 6px 15px; border-radius: 6px; text-decoration: none;
        }
        .header-panel {
            background-color: #1e1e1e;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #333;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

<div class="container-fluid" style="max-width: 96%;">
    <div class="d-flex justify-content-center align-items-center header-panel position-relative">
        <div class="d-flex align-items-center">
            <img src="<?= base_url('gif1/animecat.gif') ?>" alt="Anime Cat" style="height: 70px; margin-right: 20px;">
            <div class="text-center">
                <h1 class="m-0">📚 Biblioteca de ROMs</h1>
                <p class="text-muted m-0">Selecciona un archivo para editar</p>
            </div>
        </div>
        <div class="position-absolute end-0 me-4">
            <a href="<?= site_url('analizar_roms') ?>" class="btn-nav-modern">⬅️ Volver al Inicio</a>
            <div class="d-inline-block ms-3 text-end">
                <small class="d-block text-muted">Usuario</small>
                <span class="fw-bold text-info"><?= esc($nombre) ?></span>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid" style="max-width: 96%;">
    <div class="row">
        <!-- Columna Izquierda: GIF -->
        <div class="col-md-2 d-none d-md-block text-center">
            <div style="position: sticky; top: 20px;">
                <img src="<?= base_url('gif1/anime23.gif') ?>" alt="Anime GIF" class="img-fluid rounded" style="max-height: 400px; opacity: 0.9;">
            </div>
        </div>

        <!-- Columna Derecha: Lista de ROMs -->
        <div class="col-md-10">
            <?php if(empty($roms)): ?>
                <div class="alert alert-dark text-center py-5">
                    <h3>📭 No hay ROMs guardadas</h3>
                    <p>Sube un archivo desde la página principal para verlo aquí.</p>
                    <a href="<?= site_url('analizar_roms') ?>" class="btn-glow mt-3">🚀 Subir ROM</a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach($roms as $rom): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card rom-card h-100 text-white">
                                
                                <!-- Área de Portada -->
                                <div class="rom-cover-wrapper">
                                    <?php if($rom['has_cover']): ?>
                                        <img src="<?= site_url('analizar_roms/imagen/' . urlencode($rom['nombre'])) ?>" class="rom-cover-img" alt="Portada">
                                    <?php else: ?>
                                        <div class="rom-icon-placeholder">👾</div>
                                    <?php endif; ?>

                                    <!-- Formulario oculto para subir imagen -->
                                    <form action="<?= site_url('analizar_roms/subir_portada') ?>" method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="rom_name" value="<?= esc($rom['nombre']) ?>">
                                        <label class="btn-upload-cover" title="Cambiar imagen de portada">
                                            📷 <input type="file" name="cover_file" style="display: none;" accept="image/*" onchange="this.form.submit()">
                                        </label>
                                    </form>
                                </div>

                                <div class="card-body text-center d-flex flex-column">
                                    <div class="d-flex justify-content-center align-items-center mb-2">
                                        <h5 class="card-title text-truncate m-0" title="<?= esc($rom['nombre']) ?>" style="max-width: 85%;">
                                            <?= esc($rom['nombre']) ?>
                                        </h5>
                                        <button type="button" class="btn btn-sm btn-link text-warning p-0 ms-2" onclick="renombrarRom('<?= esc($rom['nombre']) ?>')" title="Renombrar">✏️</button>
                                    </div>
                                    <div class="mt-auto pt-3">
                                        <p class="card-text small text-white mb-3">
                                            📦 <?= number_format($rom['tamano'] / 1024, 2) ?> KB<br>
                                            📅 <?= $rom['fecha'] ?>
                                        </p>
                                        <a href="<?= site_url('analizar_roms/seleccionar/' . urlencode($rom['nombre'])) ?>" class="btn-glow w-100">🛠️ Editar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Renombrar -->
<div class="modal fade" id="modalRenombrar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-white border-secondary">
      <div class="modal-header border-secondary">
        <h5 class="modal-title">Renombrar ROM</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?= site_url('analizar_roms/renombrar') ?>" method="post">
          <div class="modal-body">
            <input type="hidden" name="old_name" id="input_old_name">
            <div class="mb-3">
                <label for="new_name" class="form-label">Nuevo nombre:</label>
                <input type="text" class="form-control bg-secondary text-white border-0" name="new_name" id="input_new_name" required>
                <div class="form-text text-muted">Se mantendrá la extensión original del archivo.</div>
            </div>
          </div>
          <div class="modal-footer border-secondary">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function renombrarRom(nombreActual) {
        document.getElementById('input_old_name').value = nombreActual;
        // Quitar extensión para mostrar en el input y facilitar la edición
        const partes = nombreActual.split('.');
        if (partes.length > 1) partes.pop();
        document.getElementById('input_new_name').value = partes.join('.');
        
        const modal = new bootstrap.Modal(document.getElementById('modalRenombrar'));
        modal.show();
    }
</script>
</body>
</html>