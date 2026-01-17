<?php
namespace App\Libraries;

/**
 * Procesador Booleano para Análisis de ROMs
 * Aplica operadores AND, OR, XOR, NOT a archivos binarios
 */
class BooleanProcessor
{
    /**
     * Operación AND (conjunción)
     * Ejemplo: 0xA5 AND 0xF0 = 0xA0
     */
    public function boolAND($a, $b)
    {
        return $a & $b;
    }
    
    /**
     * Operación OR (disyunción) 
     * Ejemplo: 0xA5 OR 0x0F = 0xAF
     */
    public function boolOR($a, $b)
    {
        return $a | $b;
    }
    
    /**
     * Operación XOR (o exclusivo)
     * Ejemplo: 0xA5 XOR 0xFF = 0x5A
     */
    public function boolXOR($a, $b)
    {
        return $a ^ $b;
    }
    
    /**
     * Operación NOT (negación)
     * Ejemplo: NOT 0xA5 = 0x5A
     */
    public function boolNOT($a)
    {
        return ~$a & 0xFF; // Máscara para 8 bits
    }
    
    /**
     * Analizar ROM completa
     */
    public function analizarROM($datosROM)
    {
        // Verificar que hay datos
        if (empty($datosROM)) {
            return ['error' => 'ROM vacía'];
        }
        
        return [
            'tamano_bytes' => strlen($datosROM),
            'textos_encontrados' => $this->extraerTextos($datosROM),
            'vista_hex' => $this->generarVistaHex($datosROM),
            'primeros_bytes' => $this->analizarPrimerosBytes($datosROM),
            'fecha_analisis' => date('d/m/Y H:i:s')
        ];
    }
    
    /**
     * Extraer textos de la ROM
     */
    private function extraerTextos($datos, $limite = 1000)
    {
        $textos = [];
        $longitud = min(strlen($datos), $limite);
        
        for ($i = 0; $i < $longitud; $i++) {
            $byte = ord($datos[$i]);
            
            // Buscar ASCII imprimible (32-126)
            if ($byte >= 32 && $byte <= 126) {
                $inicio = $i;
                $texto = '';
                
                // Extraer secuencia continua
                while ($i < $longitud && ($byte = ord($datos[$i])) >= 32 && $byte <= 126) {
                    $texto .= $datos[$i];
                    $i++;
                }
                
                // Solo si es texto significativo (3+ caracteres)
                if (strlen($texto) >= 3) {
                    $textos[] = [
                        'posicion' => $inicio,
                        'texto' => $texto,
                        'longitud' => strlen($texto),
                        'hex' => bin2hex($texto)
                    ];
                }
            }
        }
        
        return $textos;
    }
    
    /**
     * Generar vista hexadecimal
     */
    private function generarVistaHex($datos, $offset = 0, $bytes = 256)
    {
        $vista = [];
        $total = strlen($datos);
        $fin = min($offset + $bytes, $total);
        
        for ($i = $offset; $i < $fin; $i += 16) {
            $fila = [
                'offset' => $i,
                'hex' => [],
                'ascii' => ''
            ];
            
            for ($j = 0; $j < 16; $j++) {
                $pos = $i + $j;
                if ($pos < $total) {
                    $byte = ord($datos[$pos]);
                    $fila['hex'][] = str_pad(dechex($byte), 2, '0', STR_PAD_LEFT);
                    $fila['ascii'] .= ($byte >= 32 && $byte <= 126) ? $datos[$pos] : '.';
                } else {
                    $fila['hex'][] = '  ';
                }
            }
            
            $vista[] = $fila;
        }
        
        return $vista;
    }
    
    /**
     * Analizar primeros bytes con operaciones booleanas
     */
    private function analizarPrimerosBytes($datos, $cantidad = 32)
    {
        $analisis = [];
        $muestra = min($cantidad, strlen($datos));
        
        for ($i = 0; $i < $muestra; $i++) {
            $byte = ord($datos[$i]);
            
            $analisis[] = [
                'offset' => $i,
                'hex' => '0x' . str_pad(dechex($byte), 2, '0', STR_PAD_LEFT),
                'decimal' => $byte,
                'binario' => str_pad(decbin($byte), 8, '0', STR_PAD_LEFT),
                'and_f0' => '0x' . str_pad(dechex($this->boolAND($byte, 0xF0)), 2, '0', STR_PAD_LEFT),
                'or_0f' => '0x' . str_pad(dechex($this->boolOR($byte, 0x0F)), 2, '0', STR_PAD_LEFT),
                'xor_ff' => '0x' . str_pad(dechex($this->boolXOR($byte, 0xFF)), 2, '0', STR_PAD_LEFT)
            ];
        }
        
        return $analisis;
    }
    
    /**
     * Calcular operación booleana (para AJAX)
     */
    public function calcularOperacion($operacion, $valor1, $valor2 = null)
    {
        $valor1 = intval($valor1);
        $valor2 = $valor2 !== null ? intval($valor2) : 0;
        
        switch(strtoupper($operacion)) {
            case 'AND':
                $resultado = $this->boolAND($valor1, $valor2);
                break;
            case 'OR':
                $resultado = $this->boolOR($valor1, $valor2);
                break;
            case 'XOR':
                $resultado = $this->boolXOR($valor1, $valor2);
                break;
            case 'NOT':
                $resultado = $this->boolNOT($valor1);
                break;
            default:
                $resultado = $valor1;
        }
        
        return [
            'resultado' => $resultado,
            'hex' => dechex($resultado),
            'binario' => decbin($resultado),
            'operacion' => $operacion
        ];
    }
}
?>