<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor Hexadecimal - <?= esc($filename) ?></title>
    <!-- Bootstrap CSS (Cargar al inicio para evitar que la vista se rompa) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #121212; color: #e0e0e0; }
        .container { max-width: 1200px; margin: 0 auto; }
        
        /* Estilos copiados y adaptados para el modo oscuro del editor */
        .btn-glow {
            background: linear-gradient(45deg, #ff6b6b, #feca57);
            border: none; color: white; padding: 8px 20px; border-radius: 25px;
            font-weight: bold; box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
            text-decoration: none; display: inline-flex; align-items: center; gap: 5px;
        }
        .btn-nav-modern {
            background-color: #34495e; color: #ecf0f1; border: 1px solid #465c71;
            padding: 6px 15px; border-radius: 6px; text-decoration: none;
            display: inline-flex; align-items: center; gap: 5px; font-size: 0.9rem;
        }
        .btn-nav-modern:hover { background-color: #3498db; color: white; }
        .toolbar-box {
            background: rgba(255,255,255,0.05); padding: 8px; border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .hex-view {
            font-family: 'Courier New', monospace; font-size: 0.95em; line-height: 1.5;
            max-height: 75vh; overflow-y: auto; background-color: #1e1e1e;
            border: 1px solid #333;
        }
        .hex-byte { display: inline-block; padding: 2px 5px; margin: 0 1px; transition: background 0.1s; }
        .hex-byte:hover { background-color: #007bff; color: white; cursor: crosshair; transform: scale(1.1); }
        
        .inspector-panel {
            position: sticky; top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Cabecera Simple -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
        <div>
            <h2 class="m-0">🛠️ Editor Hexadecimal</h2>
            <small class="text-muted">Archivo: <?= esc($filename) ?></small>
        </div>
        <div>
            <a href="<?= site_url('analizar_roms/resultados') ?>" class="btn-nav-modern">⬅️ Volver al Análisis</a>
            <a href="<?= site_url('analizar_roms/limpiar') ?>" class="btn-glow ms-2">❌ Cerrar ROM</a>
        </div>
    </div>

    <!-- Barra de Herramientas de Navegación -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 p-2 rounded" style="background-color: #1a1d20;">
        <h5 class="m-0 text-warning">📍 Navegación <span class="badge bg-secondary text-white ms-2" style="font-size: 0.7em;">Pág <?= $paginacion['actual'] ?> / <?= number_format($paginacion['total']) ?></span></h5>
        
        <div class="d-flex align-items-center gap-2 mt-2 mt-md-0 toolbar-box">
            <!-- Ir a página -->
            <form action="<?= site_url('analizar_roms/resultados') ?>" method="get" class="d-flex align-items-center">
                <input type="hidden" name="modo" value="edicion">
                <input type="number" name="pagina" class="form-control form-control-sm bg-dark text-white border-secondary text-center" 
                       style="width: 70px;" placeholder="Pág" min="1" max="<?= $paginacion['total'] ?>" value="<?= $paginacion['actual'] ?>">
                <button type="submit" class="btn btn-sm btn-info ms-1">📄</button>
            </form>

            <div class="vr bg-secondary mx-2" style="width: 1px; height: 20px;"></div>

            <!-- Buscador Hex -->
            <form action="<?= site_url('analizar_roms/resultados') ?>" method="get" class="d-flex align-items-center">
                <input type="hidden" name="modo" value="edicion">
                <input type="text" name="direccion_hex" class="form-control form-control-sm bg-dark text-white border-secondary" 
                       style="width: 120px;" placeholder="Hex (ej. A0)">
                <button type="submit" class="btn btn-sm btn-warning ms-1">🔍</button>
            </form>

            <div class="vr bg-secondary mx-2" style="width: 1px; height: 20px;"></div>

            <div class="btn-group shadow-sm">
                <?php if($paginacion['actual'] > 1): ?>
                    <a href="<?= site_url('analizar_roms/resultados?modo=edicion&pagina=' . ($paginacion['actual'] - 1)) ?>" class="btn-nav-modern">⬅️ Prev</a>
                <?php endif; ?>
                <?php if($paginacion['actual'] < $paginacion['total']): ?>
                    <a href="<?= site_url('analizar_roms/resultados?modo=edicion&pagina=' . ($paginacion['actual'] + 1)) ?>" class="btn-nav-modern">Next ➡️</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Columna Izquierda: Inspector de Datos -->
        <div class="col-md-3">
            <div class="card text-white h-100 inspector-panel" style="background-color: #1c1c1c; border: 1px solid #444;">
                <div class="card-body">
                    <h6 class="border-bottom pb-2 mb-3 text-info">ℹ️ Inspector</h6>
                    
                    <div class="mb-3">
                        <label class="small text-muted">Hexadecimal</label>
                        <div id="insp-hex" class="font-monospace fs-4 text-white">--</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="small text-muted">Decimal (Unsigned)</label>
                        <div id="insp-dec" class="font-monospace fs-5 text-warning">--</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="small text-muted">Binario</label>
                        <div id="insp-bin" class="font-monospace small text-muted">--</div>
                    </div>
                    
                    <div class="mb-3 text-center">
                        <label class="small text-muted d-block text-start">ASCII</label>
                        <span id="insp-ascii" class="d-inline-block border rounded bg-dark py-2 px-4 mt-1 fs-2 text-success fw-bold">.</span>
                    </div>

                    <div class="mt-4 border-top pt-3">
                        <small class="text-muted d-block mb-1">Contexto (Fila):</small>
                        <div id="insp-row-text" class="p-2 bg-dark border border-secondary rounded text-warning font-monospace" style="min-height: 38px; letter-spacing: 1px; font-size: 0.8rem;">--</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Vista Hexadecimal -->
        <div class="col-md-9">
            <div class="hex-view p-3 rounded shadow-lg">
                <?php foreach($analisis['vista_hex'] as $fila): ?>
                    <div class="mb-1 d-flex" data-ascii="<?= htmlspecialchars($fila['ascii']) ?>">
                        <!-- Offset -->
                        <span class="text-info me-3 select-none" style="user-select: none;"><?= str_pad(dechex($fila['offset']), 6, '0', STR_PAD_LEFT) ?>:</span>
                        
                        <!-- Bytes -->
                        <div class="flex-grow-1">
                            <?php foreach($fila['hex'] as $hex): ?>
                                <span class="hex-byte"><?= $hex ?></span>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- ASCII Preview -->
                        <span class="text-warning ms-3 border-start border-secondary ps-2" style="min-width: 150px; letter-spacing: 1px;">
                            <?= str_replace(' ', '&nbsp;', htmlspecialchars($fila['ascii'])) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Inspector de Datos (Hover en Hex View)
    document.addEventListener('DOMContentLoaded', function() {
        const hexBytes = document.querySelectorAll('.hex-byte');
        
        hexBytes.forEach(byte => {
            byte.addEventListener('mouseover', function() {
                const hex = this.innerText.trim();
                if(hex === '') return;

                const dec = parseInt(hex, 16);
                
                // Actualizar panel inspector
                document.getElementById('insp-hex').innerText = '0x' + hex;
                document.getElementById('insp-dec').innerText = dec;
                document.getElementById('insp-bin').innerText = dec.toString(2).padStart(8, '0');
                document.getElementById('insp-ascii').innerText = (dec >= 32 && dec <= 126) ? String.fromCharCode(dec) : '.';
                
                // Mostrar texto de toda la fila
                const rowAscii = this.closest('.mb-1').getAttribute('data-ascii');
                document.getElementById('insp-row-text').innerText = rowAscii;
                
                // Efecto visual
                this.style.backgroundColor = '#ff6b6b';
            });

            byte.addEventListener('mouseout', function() {
                this.style.backgroundColor = ''; // Restaurar color
            });
        });
    });
</script>
</body>
</html>