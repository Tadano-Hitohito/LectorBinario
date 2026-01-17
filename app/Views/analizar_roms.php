<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analizar ROMs</title>
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    <style>
        /* Estilos base por si no se carga el CSS externo */
        body { font-family: sans-serif; padding: 20px; background-color: #f4f4f9; }
        .container { max-width: 800px; margin: 0 auto; }
        .panel {
            background-color: #141313;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            color: #fff;
        }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .user-menu {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-name {
            font-size: 1.1rem;
            color: #e0e0e0;
        }
        .logout-btn {
            background-color: #ff4757;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
            transition: background 0.3s;
        }
        .logout-btn:hover {
            background-color: #ff6b81;
        }
        .btn-nice-blue {
            background-color: #3498db;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: bold;
            transition: background 0.3s;
            display: inline-block;
        }
        .btn-nice-blue:hover {
            background-color: #2980b9;
            color: white;
        }
        /* Nuevos estilos decorativos */
        .btn-glow {
            background: linear-gradient(45deg, #ff6b6b, #feca57);
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
            transition: transform 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-glow:hover {
            transform: scale(1.05);
            color: white;
        }
        .btn-nav-modern {
            background-color: #34495e;
            color: #ecf0f1;
            border: 1px solid #465c71;
            padding: 6px 15px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
        }
        .btn-nav-modern:hover {
            background-color: #3498db;
            border-color: #3498db;
            color: white;
        }
        .toolbar-box {
            background: rgba(255,255,255,0.05);
            padding: 8px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="panel">
        <div class="header">
            <h1>Analizar ROMs</h1>
            <div class="user-menu">
                <a href="<?= site_url('analizar_roms/menu') ?>" class="btn-nav-modern" style="margin-right: 10px;">📚 Menú de ROMs</a>
                <span class="user-name">Hola, <?= esc($nombre) ?></span>
                <a href="<?= site_url('cerrar_sesion') ?>" class="logout-btn">🚪 Cerrar Sesión</a>
            </div>
        </div>

        <div class="content">
            <p>Bienvenido al panel de análisis de ROMs.</p>
            
            <!-- Mensajes de éxito o error -->
            <?php if (session()->getFlashdata('message')): ?>
                <div style="color: #2ecc71; margin-bottom: 15px; font-weight: bold;">
                    <?= session()->getFlashdata('message') ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div style="color: #e74c3c; margin-bottom: 15px; font-weight: bold;">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <!-- Formulario de subida (apunta al nuevo método procesarBooleano) -->
            <form action="<?= site_url('analizar_roms/procesar') ?>" method="post" enctype="multipart/form-data" style="margin-top: 20px; border-top: 1px solid #333; padding-top: 20px;">
                <label for="rom_file" style="display: block; margin-bottom: 10px; font-weight: bold;">Subir archivo ROM:</label>
                <input type="file" name="rom_file" id="rom_file" required style="margin-bottom: 15px; color: #ccc;">
                <br>
                <button type="submit" class="btn-glow">🚀 Subir y Analizar</button>
            </form>
        </div>
    </div>
</div>

<?php
// Si hay análisis para mostrar (después de subir ROM)
if (isset($analisis)): ?>
    
    <div class="container mt-4">
        <!-- RESULTADOS DEL ANÁLISIS BOOLEANO -->
        <div class="card shadow" style="background-color: #212529; color: #fff;">
            <div class="card-header bg-success text-white" style="display: flex; justify-content: space-between; align-items: center;">
                <h4 class="mb-0">🧮 Resultados del Análisis de la Rom</h4>
                <div>
                    <a href="<?= site_url('analizar_roms/resultados?modo=edicion') ?>" class="btn-glow" style="background: linear-gradient(45deg, #3498db, #8e44ad); margin-right: 10px;">🛠️ Modo Edición</a>
                    <a href="<?= site_url('analizar_roms/limpiar') ?>" class="btn-glow">❌ Cerrar ROM</a>
                </div>
            </div>
            <div class="card-body">
                
                <!-- Información de la ROM -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <p><strong>📁 Archivo:</strong> <?= $filename ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>📊 Tamaño:</strong> <?= number_format($tamano) ?> bytes</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>📅 Fecha:</strong> <?= $analisis['fecha_analisis'] ?></p>
                    </div>
                </div>
                
                <!-- Textos encontrados -->
                <h5>📝 Textos encontrados (<?= count($analisis['textos_encontrados']) ?>)</h5>
                <?php if (!empty($analisis['textos_encontrados'])): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-dark">
                            <thead>
                                <tr>
                                    <th>Posición (Hex)</th>
                                    <th>Texto</th>
                                    <th>Longitud</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($analisis['textos_encontrados'] as $texto): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= site_url('analizar_roms/resultados?direccion_hex=' . dechex($texto['posicion'])) ?>" class="text-info text-decoration-none" title="Ir a esta dirección">
                                                <code>0x<?= strtoupper(dechex($texto['posicion'])) ?></code>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="font-monospace bg-secondary text-white p-1 rounded">
                                                <?= htmlspecialchars($texto['texto']) ?>
                                            </span>
                                        </td>
                                        <td><?= $texto['longitud'] ?> chars</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">No se encontraron textos</div>
                <?php endif; ?>
                
                <hr>
                
                <!-- Análisis Booleano de Primeros Bytes -->
                <h5>🧬 Análisis Lógico (Cabecera)</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm text-center table-dark" style="font-family: monospace; font-size: 0.9em;">
                        <thead>
                            <tr>
                                <th>Offset</th>
                                <th>Byte Original</th>
                                <th>AND 0xF0 (Máscara Alta)</th>
                                <th>OR 0x0F (Máscara Baja)</th>
                                <th>XOR 0xFF (Invertir)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach(array_slice($analisis['primeros_bytes'], 0, 10) as $byte): ?>
                                <tr>
                                    <td><?= $byte['offset'] ?></td>
                                    <td class="fw-bold"><?= $byte['hex'] ?></td>
                                    <td><?= $byte['and_f0'] ?></td>
                                    <td><?= $byte['or_0f'] ?></td>
                                    <td><?= $byte['xor_ff'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="text-muted small">* Demostración de operadores booleanos aplicados a los primeros 10 bytes del archivo.</div>
                </div>

                <hr>

                <!-- Vista hexadecimal -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 p-2 rounded" style="background-color: #1a1d20;">
                    <h5 class="m-0 text-warning">🔢 Vista Hexadecimal <span class="badge bg-secondary text-white ms-2" style="font-size: 0.7em;">Pág <?= $paginacion['actual'] ?> / <?= number_format($paginacion['total']) ?></span></h5>
                    
                    <div class="d-flex align-items-center gap-2 mt-2 mt-md-0 toolbar-box">
                        <!-- Ir a página específica -->
                        <form action="<?= site_url('analizar_roms/resultados') ?>" method="get" class="d-flex align-items-center">
                            <input type="number" name="pagina" class="form-control form-control-sm bg-dark text-white border-secondary text-center" 
                                   style="width: 70px;" placeholder="Pág" 
                                   min="1" max="<?= $paginacion['total'] ?>" 
                                   value="<?= $paginacion['actual'] ?>">
                            <button type="submit" class="btn btn-sm btn-info ms-1" title="Ir a página">📄</button>
                        </form>

                        <div class="vr bg-secondary mx-2" style="width: 1px; height: 20px;"></div>

                        <!-- Buscador Hex -->
                        <form action="<?= site_url('analizar_roms/resultados') ?>" method="get" class="d-flex align-items-center">
                            <input type="text" name="direccion_hex" class="form-control form-control-sm bg-dark text-white border-secondary" 
                                   style="width: 120px;" placeholder="Hex (ej. A0)" title="Buscar dirección Hexadecimal">
                            <button type="submit" class="btn btn-sm btn-warning ms-1" title="Buscar Offset">🔍</button>
                        </form>

                        <div class="vr bg-secondary mx-2" style="width: 1px; height: 20px;"></div>

                        <div class="btn-group shadow-sm">
                            <?php if($paginacion['actual'] > 1): ?>
                                <a href="<?= site_url('analizar_roms/resultados?pagina=' . ($paginacion['actual'] - 1)) ?>" class="btn-nav-modern">⬅️ Prev</a>
                            <?php endif; ?>
                            <?php if($paginacion['actual'] < $paginacion['total']): ?>
                                <a href="<?= site_url('analizar_roms/resultados?pagina=' . ($paginacion['actual'] + 1)) ?>" class="btn-nav-modern">Next ➡️</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Columna Izquierda: Panel de Descripción Estático -->
                    <div class="col-md-3">
                        <div class="card text-white h-100" style="background-color: #1c1c1c; border: 1px solid #444;">
                            <div class="card-body">
                                <h6 class="border-bottom pb-2 mb-3">ℹ️ Inspector de Datos</h6>
                                <div class="mb-2"><strong>HEX:</strong> <span id="insp-hex" class="text-info font-monospace fs-5">--</span></div>
                                <div class="mb-2"><strong>DEC:</strong> <span id="insp-dec" class="text-warning font-monospace">--</span></div>
                                <div class="mb-2"><strong>BIN:</strong> <span id="insp-bin" class="text-muted font-monospace small">--</span></div>
                                <div class="mb-2"><strong>ASCII:</strong> <span id="insp-ascii" class="text-success fw-bold fs-2 d-block text-center border rounded bg-dark py-2 mt-2">.</span></div>
                                <p class="small text-muted mt-3">Pasa el mouse sobre los bytes de la derecha para ver sus detalles.</p>
                                
                                <div class="mt-3 border-top pt-3">
                                    <small class="text-muted d-block mb-1">Contexto (Fila completa):</small>
                                    <div id="insp-row-text" class="p-2 bg-dark border border-secondary rounded text-warning font-monospace" style="min-height: 38px; letter-spacing: 2px;">--</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Vista Hexadecimal -->
                    <div class="col-md-9">
                        <div class="hex-view font-monospace bg-dark text-light p-3 rounded">
                            <?php foreach($analisis['vista_hex'] as $fila): ?>
                                <div class="mb-1" data-ascii="<?= htmlspecialchars($fila['ascii']) ?>">
                                    <span class="text-info"><?= str_pad(dechex($fila['offset']), 6, '0', STR_PAD_LEFT) ?>:</span>
                                    <?php foreach($fila['hex'] as $hex): ?>
                                        <span class="hex-byte"><?= $hex ?></span>
                                    <?php endforeach; ?>
                                    <span class="text-warning ms-3">|<?= $fila['ascii'] ?>|</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
        <div class="mt-3">
            <a href="<?= site_url('analizar_roms/limpiar') ?>" class="btn-nice-blue">📂 Subir otra ROM</a>
        </div>
    </div>
    
    <!-- JavaScript para la calculadora -->
    <script>
    // Inspector de Datos (Hover en Hex View)
    document.addEventListener('DOMContentLoaded', function() {
        const hexBytes = document.querySelectorAll('.hex-byte');
        hexBytes.forEach(byte => {
            byte.addEventListener('mouseover', function() {
                const hex = this.innerText;
                const dec = parseInt(hex, 16);
                
                document.getElementById('insp-hex').innerText = hex;
                document.getElementById('insp-dec').innerText = dec;
                document.getElementById('insp-bin').innerText = dec.toString(2).padStart(8, '0');
                document.getElementById('insp-ascii').innerText = (dec >= 32 && dec <= 126) ? String.fromCharCode(dec) : '.';
                
                // Mostrar texto de toda la fila para dar contexto
                const rowAscii = this.closest('.mb-1').getAttribute('data-ascii');
                document.getElementById('insp-row-text').innerText = rowAscii;
            });
        });
    });
    </script>
    
<?php endif; ?>

<!-- ESTILOS PARA EL ANÁLISIS -->
<style>
.hex-view {
    font-family: 'Courier New', monospace;
    font-size: 0.9em;
    line-height: 1.4;
    max-height: 600px; /* Altura máxima para scroll */
    overflow-y: auto;  /* Barra de desplazamiento vertical */
}
.hex-byte {
    display: inline-block;
    padding: 2px 4px;
    margin: 0 1px;
}
.hex-byte:hover {
    background-color: #333;
    cursor: pointer;
}
</style>

</body>
</html>