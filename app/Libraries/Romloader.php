<?php
// app/Libraries/ROMLoader.php

namespace App\Libraries;

class ROMLoader
{
    private $contenidoBinario;
    private $tamanoBytes;
    private $hash;
    private $metadata = [];
    
    /**
     * Carga una ROM completa en memoria
     * @param string $rutaArchivo Ruta completa al archivo ROM
     * @return bool true si se cargó correctamente
     */
    public function cargarROM(string $rutaArchivo): bool
    {
        if (!file_exists($rutaArchivo)) {
            throw new \Exception("Archivo no encontrado: $rutaArchivo");
        }
        
        // ⭐ 1. Cargar TODO el archivo en memoria
        $this->contenidoBinario = file_get_contents($rutaArchivo);
        // EQUIVALENTE A: byte[] buffer = readFile("rom.gba");
        // En este buffer ya tienes: Gráficos, Punteros, Texto, Música, Relleno, etc.
        // En PHP, el contenido binario se maneja como un string donde cada byte es un carácter.
        // Ejemplo: Si el byte 0x41 está en la ROM, PHP lo ve como el carácter 'A'.
        
        if ($this->contenidoBinario === false) {
            return false;
        }
        
        // 2. Obtener metadatos
        $this->tamanoBytes = strlen($this->contenidoBinario);
        $this->hash = md5($this->contenidoBinario);
        
        // 3. Analizar estructura básica
        $this->analizarCabecera();
        
        return true;
    }
    
    /**
     * Obtiene el contenido binario crudo
     * @return string Contenido binario completo
     */
    public function obtenerBinario(): string
    {
        return $this->contenidoBinario;
    }
    
    /**
     * Obtiene el tamaño en bytes
     * @return int Tamaño del archivo en bytes
     */
    public function obtenerTamano(): int
    {
        return $this->tamanoBytes;
    }
    
    /**
     * Obtiene el contenido en hexadecimal
     * @return string Representación hexadecimal del archivo completo
     */
    public function obtenerHexadecimal(): string
    {
        return bin2hex($this->contenidoBinario);
    }
    
    /**
     * Obtiene un byte específico
     * @param int $offset Posición del byte (0-based)
     * @return int Valor del byte (0-255)
     */
    public function obtenerByte(int $offset): int
    {
        if ($offset < 0 || $offset >= $this->tamanoBytes) {
            throw new \Exception("Offset fuera de rango: $offset");
        }
        
        // Cada carácter del string es un byte
        return ord($this->contenidoBinario[$offset]);
    }
    
    /**
     * Obtiene un rango de bytes
     * @param int $inicio Offset inicial
     * @param int $longitud Número de bytes a obtener
     * @return string Bytes como string binario
     */
    public function obtenerRango(int $inicio, int $longitud): string
    {
        return substr($this->contenidoBinario, $inicio, $longitud);
    }
    
    /**
     * Analiza la cabecera de la ROM (ejemplo para GBA)
     */
    private function analizarCabecera(): void
    {
        if ($this->tamanoBytes < 192) {
            return; // ROM demasiado pequeña para tener cabecera GBA
        }
        
        $this->metadata = [
            'titulo' => trim(substr($this->contenidoBinario, 0xA0, 12)),
            'codigo_juego' => substr($this->contenidoBinario, 0xAC, 4),
            'codigo_fabricante' => substr($this->contenidoBinario, 0xB0, 2),
            'version' => ord($this->contenidoBinario[0xBC]),
            'checksum_cabecera' => ord($this->contenidoBinario[0xBD]),
            'tipo_dispositivo' => $this->detectarTipoROM()
        ];
    }
    
    /**
     * Detecta el tipo de ROM por análisis simple
     */
    private function detectarTipoROM(): string
    {
        // Análisis básico de firmas
        if ($this->tamanoBytes % 1024 == 0) {
            return 'Posible ROM NES/SNES (tamaño múltiplo de 1024)';
        }
        
        // Verificar cabecera GBA
        if ($this->tamanoBytes >= 192) {
            $entryPoint = substr($this->contenidoBinario, 0xE0, 4);
            if (bin2hex($entryPoint) !== '00000000') {
                return 'Posible ROM GBA';
            }
        }
        
        return 'Tipo desconocido';
    }
    
    /**
     * Calcula el checksum de toda la ROM
     */
    public function calcularChecksum(): string
    {
        return hash('sha256', $this->contenidoBinario);
    }
    
    /**
     * Busca un patrón hexadecimal en la ROM
     * @param string $patronHex Patrón en hexadecimal (ej: "A0B1C2")
     * @return array Posiciones donde se encuentra
     */
    public function buscarPatron(string $patronHex): array
    {
        $patronBin = hex2bin($patronHex);
        $posiciones = [];
        $offset = 0;
        
        while (($pos = strpos($this->contenidoBinario, $patronBin, $offset)) !== false) {
            $posiciones[] = $pos;
            $offset = $pos + 1;
        }
        
        return $posiciones;
    }
    
    /**
     * Obtiene estadísticas de la ROM
     */
    public function obtenerEstadisticas(): array
    {
        $conteoBytes = array_fill(0, 256, 0);
        
        for ($i = 0; $i < $this->tamanoBytes; $i++) {
            $byteVal = ord($this->contenidoBinario[$i]);
            $conteoBytes[$byteVal]++;
        }
        
        return [
            'tamano_bytes' => $this->tamanoBytes,
            'tamano_kb' => round($this->tamanoBytes / 1024, 2),
            'tamano_mb' => round($this->tamanoBytes / (1024 * 1024), 2),
            'hash_md5' => $this->hash,
            'hash_sha256' => $this->calcularChecksum(),
            'byte_mas_comun' => array_search(max($conteoBytes), $conteoBytes),
            'byte_menos_comun' => array_search(min(array_filter($conteoBytes)), $conteoBytes),
            'conteo_bytes' => $conteoBytes,
            'metadata' => $this->metadata
        ];
    }

    /**
     * ⭐ B) Divide el buffer en filas de 16 bytes (Vista Hexadecimal)
     * Genera la estructura visual típica de editores como 010 Editor.
     * 
     * @param int $offset Byte de inicio (desplazamiento)
     * @param int $filas Cantidad de filas a generar
     * @return array Array estructurado con Offset, Hex y ASCII
     */
    public function obtenerVistaHexadecimal(int $offset = 0, int $filas = 16): array
    {
        // 1. Extraer solo el segmento necesario (buffer pequeño)
        $longitud = $filas * 16;
        $segmento = substr($this->contenidoBinario, $offset, $longitud);
        
        if ($segmento === false || $segmento === '') {
            return [];
        }

        // 2. Convertir string binario a array de bytes (0-255)
        $byteArray = array_values(unpack('C*', $segmento));

        // ⭐ 3. Dividir en filas de 16 bytes (array_chunk)
        $rows = array_chunk($byteArray, 16);
        
        $vista = [];
        foreach ($rows as $index => $row) {
            $fila = [
                'offset' => $offset + ($index * 16),
                'hex' => [],
                'ascii' => ''
            ];
            
            foreach ($row as $byte) {
                // HEX: Convertir a hexadecimal mayúscula
                $fila['hex'][] = strtoupper(str_pad(dechex($byte), 2, '0', STR_PAD_LEFT));
                
                // ASCII: Caracteres imprimibles o punto
                $fila['ascii'] .= ($byte >= 32 && $byte <= 126) ? chr($byte) : '.';
            }
            
            // Relleno visual si la fila está incompleta (final del archivo)
            while (count($fila['hex']) < 16) {
                $fila['hex'][] = '  ';
            }
            
            $vista[] = $fila;
        }
        
        return $vista;
    }
}
?>