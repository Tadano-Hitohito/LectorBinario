<?php

namespace App\Controllers;

class AnalizarRoms extends BaseController
{
    public function index()
    {
        // Verificar si el usuario ha iniciado sesión
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $data = [
            'username' => session()->get('username'),
            'nombre'   => session()->get('nombre')
        ];

        return view('analizar_roms', $data);
    }

    /**
     * Método para procesar ROM con álgebra booleana
     */
    public function procesarBooleano()
    {
        // Verificar que el usuario esté logueado
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Validar que se subió un archivo
        if (! $this->request->getFile('rom_file')) {
            return redirect()->back()->with('error', 'No se seleccionó archivo');
        }

        $file = $this->request->getFile('rom_file');

        // Validar extensión
        $extension = $file->getClientExtension();
        if (! in_array($extension, ['gb', 'gbc', 'gba', 'bin'])) {
            return redirect()->back()->with('error', 'Formato no válido. Use .gb, .gbc, .gba, .bin');
        }

        // Validar tamaño (32MB máximo)
        if ($file->getSize() > 33554432) {
            return redirect()->back()->with('error', 'Archivo muy grande (máx 32MB)');
        }

        // Usar nombre original del archivo
        $filename = $file->getClientName();

        $uploadPath = WRITEPATH . 'uploads/roms';

        // Crear la carpeta si no existe
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        // Mover a la carpeta de uploads
        if ($file->move($uploadPath, $filename, true)) {
            // Guardar en sesión
            session()->set('rom_actual', $filename);

            // Redirigir a resultados
            return redirect()->to('/analizar_roms/resultados');
        }

        return redirect()->back()->with('error', 'Error al subir el archivo');
    }

    /**
     * Mostrar resultados del análisis booleano
     */
    public function resultados()
    {
        // Verificar login y que haya ROM
        if (! session()->get('isLoggedIn') || ! session()->has('rom_actual')) {
            return redirect()->to('/analizar_roms');
        }

        $filename = session()->get('rom_actual');
        $filepath = WRITEPATH . 'uploads/roms/' . $filename;

        if (! file_exists($filepath)) {
            session()->remove('rom_actual');
            return redirect()->to('/analizar_roms')->with('error', 'ROM no encontrada');
        }

        // Cargar el loader de ROMs para obtener metadatos y estructura
        $romLoader = new \App\Libraries\ROMLoader();
        try {
            if (!$romLoader->cargarROM($filepath)) {
                return redirect()->to('/analizar_roms')->with('error', 'Error al leer el archivo ROM');
            }
        } catch (\Exception $e) {
            return redirect()->to('/analizar_roms')->with('error', $e->getMessage());
        }
        $romStats = $romLoader->obtenerEstadisticas();

        // Cargar el procesador booleano
        $booleanProcessor = new \App\Libraries\BooleanProcessor();

        // Leer ROM y analizar (usando el binario cargado)
        $romData = $romLoader->obtenerBinario();
        $analisis = $booleanProcessor->analizarROM($romData);
        
        // ⭐ Paginación para la vista hexadecimal (Paso 3)
        // Permite navegar por todo el archivo en bloques de 256 filas (4KB)
        $filasPorPagina = 256; 
        $bytesPorPagina = $filasPorPagina * 16;
        $totalBytes = $romLoader->obtenerTamano();
        $totalPaginas = ceil($totalBytes / $bytesPorPagina);
        if ($totalPaginas < 1) $totalPaginas = 1;

        // Lógica para saltar a dirección HEX
        $direccionHex = $this->request->getGet('direccion_hex');
        if ($direccionHex) {
            // Limpiar input (quitar 0x, espacios) y convertir a decimal
            $offsetBuscado = hexdec(str_replace(['0x', ' '], '', $direccionHex));
            $pagina = floor($offsetBuscado / $bytesPorPagina) + 1;
        } else {
            $pagina = (int) ($this->request->getGet('pagina') ?? 1);
        }

        // Validar límites de página
        if ($pagina < 1) $pagina = 1;
        if ($pagina > $totalPaginas) $pagina = $totalPaginas;
        
        $offset = ($pagina - 1) * $bytesPorPagina;
        $analisis['vista_hex'] = $romLoader->obtenerVistaHexadecimal($offset, $filasPorPagina);

        // Preparar datos para la vista
        $data = [
            'titulo'   => 'Análisis de la ROM',
            'username' => session()->get('username'),
            'nombre'   => session()->get('nombre'),
            'filename' => $filename,
            'tamano'   => filesize($filepath),
            'analisis' => $analisis,
            'rom_metadata' => $romStats, // Datos internos (Header, Checksum, Tipo)
            'paginacion' => [
                'actual' => $pagina,
                'total'  => $totalPaginas
            ]
        ];

        // Si estamos en modo edición, cargar la vista de editor
        if ($this->request->getGet('modo') === 'edicion') {
            return view('edicion_rom', $data);
        }

        // Mostrar resultados (usaremos TU vista existente mejorada)
        return view('analizar_roms', $data);
    }

    /**
     * Calcular operación booleana (para AJAX)
     */
    public function calcularBooleano()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $operacion = $this->request->getPost('operacion');
        $valor1 = (int) $this->request->getPost('valor1');
        $valor2 = (int) $this->request->getPost('valor2');

        $processor = new \App\Libraries\BooleanProcessor();

        $resultadoData = $processor->calcularOperacion($operacion, $valor1, $valor2);

        return $this->response->setJSON([
            'exito' => true,
            'resultado' => $resultadoData['resultado'],
            'hex' => $resultadoData['hex'],
            'binario' => $resultadoData['binario']
        ]);
    }

    /**
     * Limpiar sesión de ROM actual y volver al inicio
     */
    public function limpiar()
    {
        session()->remove('rom_actual');
        return redirect()->to('/analizar_roms');
    }

    /**
     * Muestra el Menú (Biblioteca) de ROMs disponibles
     */
    public function menu()
    {
        if (! session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $uploadPath = WRITEPATH . 'uploads/roms';
        $coversPath = WRITEPATH . 'uploads/covers';
        $roms = [];

        // Crear carpeta de covers si no existe
        if (!is_dir($coversPath)) {
            mkdir($coversPath, 0777, true);
        }

        // Obtener lista de portadas existentes para no usar glob() dentro del bucle
        // Esto evita errores con caracteres especiales como [!] o (U)
        $existingCovers = [];
        if (is_dir($coversPath)) {
            $coverFiles = scandir($coversPath);
            foreach ($coverFiles as $cFile) {
                if ($cFile === '.' || $cFile === '..') continue;
                // Si el archivo es "juego.gba.png", la clave será "juego.gba"
                $existingCovers[pathinfo($cFile, PATHINFO_FILENAME)] = true;
            }
        }

        if (is_dir($uploadPath)) {
            $files = scandir($uploadPath);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                
                $filePath = $uploadPath . '/' . $file;
                $roms[] = [
                    'nombre' => $file,
                    'tamano' => filesize($filePath),
                    'fecha'  => date('d/m/Y H:i', filemtime($filePath)),
                    'has_cover' => isset($existingCovers[$file]) // Verificación directa y segura
                ];
            }
        }

        $data = [
            'username' => session()->get('username'),
            'nombre'   => session()->get('nombre'),
            'roms'     => $roms
        ];

        return view('menu_roms', $data);
    }

    /**
     * Selecciona una ROM del menú y redirige a edición
     */
    public function seleccionar($filename = null)
    {
        if (!$filename) return redirect()->to('/analizar_roms/menu');
        
        // Decodificar nombre y establecer en sesión
        $filename = urldecode($filename);
        session()->set('rom_actual', $filename);
        
        // Redirigir directamente al modo edición
        return redirect()->to('/analizar_roms/resultados?modo=edicion');
    }

    /**
     * Procesa la subida de una imagen de portada
     */
    public function subirPortada()
    {
        $romName = $this->request->getPost('rom_name');
        $file = $this->request->getFile('cover_file');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Validar que sea imagen
            if (!str_contains($file->getMimeType(), 'image')) {
                 return redirect()->to('/analizar_roms/menu')->with('error', 'El archivo debe ser una imagen.');
            }

            $coversPath = WRITEPATH . 'uploads/covers';
            if (!is_dir($coversPath)) mkdir($coversPath, 0777, true);

            // Borrar portadas anteriores (buscando extensiones comunes para evitar glob)
            $exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            foreach ($exts as $ext) {
                $oldPath = $coversPath . '/' . $romName . '.' . $ext;
                if (file_exists($oldPath)) @unlink($oldPath);
            }

            // Guardar nueva portada: nombre_rom.extension (ej. shantae.gba.jpg)
            $file->move($coversPath, $romName . '.' . $file->getExtension());
            
            return redirect()->to('/analizar_roms/menu')->with('message', 'Portada actualizada correctamente.');
        }
        return redirect()->to('/analizar_roms/menu')->with('error', 'Error al subir la imagen.');
    }

    /**
     * Sirve la imagen de portada desde la carpeta writable (protegida)
     */
    public function imagen($romName)
    {
        $romName = urldecode($romName);
        $coversPath = WRITEPATH . 'uploads/covers';
        
        // Buscar archivo probando extensiones (más seguro que glob con caracteres especiales)
        $exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $path = '';
        
        foreach ($exts as $ext) {
            if (file_exists($coversPath . '/' . $romName . '.' . $ext)) {
                $path = $coversPath . '/' . $romName . '.' . $ext;
                break;
            }
        }
        
        if ($path && file_exists($path)) {
            $mime = mime_content_type($path);
            header('Content-Type: ' . $mime);
            readfile($path);
            exit;
        }
        // Si no hay imagen, no devuelve nada (el navegador mostrará el alt o error, que manejamos en la vista)
    }

    /**
     * Renombra un archivo ROM y su portada asociada
     */
    public function renombrar()
    {
        $oldName = $this->request->getPost('old_name');
        $newName = trim($this->request->getPost('new_name'));

        if (!$oldName || !$newName) {
            return redirect()->to('/analizar_roms/menu')->with('error', 'Nombre inválido.');
        }

        // Seguridad básica para evitar navegar entre directorios
        if (basename($oldName) !== $oldName || basename($newName) !== $newName) {
             return redirect()->to('/analizar_roms/menu')->with('error', 'Nombre de archivo inválido.');
        }

        $uploadPath = WRITEPATH . 'uploads/roms';
        $coversPath = WRITEPATH . 'uploads/covers';
        $oldPath = $uploadPath . '/' . $oldName;

        // Asegurar que se mantiene la extensión original
        $info = pathinfo($oldName);
        $ext = $info['extension'] ?? '';
        
        // Si el nuevo nombre no termina con la extensión correcta, se la agregamos
        if ($ext && substr($newName, -strlen('.' . $ext)) !== '.' . $ext) {
            $newName .= '.' . $ext;
        }
        $newPath = $uploadPath . '/' . $newName;

        if (file_exists($newPath)) return redirect()->to('/analizar_roms/menu')->with('error', 'Ya existe un archivo con ese nombre.');
        if (!file_exists($oldPath)) return redirect()->to('/analizar_roms/menu')->with('error', 'El archivo original no existe.');

        if (rename($oldPath, $newPath)) {
            // Intentar renombrar la portada también si existe
            $coverExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            foreach ($coverExts as $cExt) {
                $oldCover = $coversPath . '/' . $oldName . '.' . $cExt;
                if (file_exists($oldCover)) {
                    rename($oldCover, $coversPath . '/' . $newName . '.' . $cExt);
                    break;
                }
            }
            return redirect()->to('/analizar_roms/menu')->with('message', 'Archivo renombrado correctamente.');
        }

        return redirect()->to('/analizar_roms/menu')->with('error', 'Error al renombrar el archivo.');
    }
}