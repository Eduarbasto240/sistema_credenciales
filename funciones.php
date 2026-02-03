<?php
// funciones.php - VERSIÓN SIMPLIFICADA Y CORREGIDA
require_once 'config.php';

class CredencialManager {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // ========== REGISTRAR PERSONA ==========
    
    public function registrarPersona($datos, $foto = null) {
        try {
            // Validar datos
            $this->validarDatosPersona($datos);
            
            // Generar código
            $codigo = $this->generarCodigo($datos['estado']);
            
            // Procesar foto
            $fotoNombre = null;
            if ($foto && $foto['error'] === 0) {
                $fotoNombre = $this->procesarFoto($foto);
            }
            
            // Generar QR
            $qrUrl = SITE_URL . 'verificar.php?codigo=' . urlencode($codigo);
            $qrImagen = $this->generarQR($qrUrl, $codigo);
            
            // Insertar en BD
            $sql = "INSERT INTO personas (
                codigo_credencial, primer_nombre, segundo_nombre, 
                primer_apellido, segundo_apellido, cedula_identidad, 
                telefono, estado, municipio, parroquia, cargo, 
                persona_responsable, foto, qr_url, qr_imagen
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $codigo,
                sanitizar($datos['primer_nombre'] ?? ''),
                sanitizar($datos['segundo_nombre'] ?? ''),
                sanitizar($datos['primer_apellido'] ?? ''),
                sanitizar($datos['segundo_apellido'] ?? ''),
                sanitizar($datos['cedula'] ?? ''),
                sanitizar($datos['telefono'] ?? ''),
                sanitizar($datos['estado'] ?? ''),
                sanitizar($datos['municipio'] ?? ''),
                sanitizar($datos['parroquia'] ?? ''),
                sanitizar($datos['cargo'] ?? ''),
                sanitizar($datos['persona_responsable'] ?? ''),
                $fotoNombre,
                $qrUrl,
                $qrImagen
            ]);
            
            return [
                'success' => true,
                'codigo' => $codigo,
                'qr_url' => $qrUrl,
                'qr_imagen' => QR_URL . $qrImagen,
                'foto_url' => $fotoNombre ? FOTOS_URL . $fotoNombre : null,
                'mensaje' => '✅ Registro exitoso'
            ];
            
        } catch(Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'mensaje' => '❌ Error: ' . $e->getMessage()
            ];
        }
    }
    
    private function validarDatosPersona($datos) {
        $requeridos = ['primer_nombre', 'primer_apellido', 'cedula', 'telefono', 'estado', 'municipio', 'cargo'];
        
        foreach ($requeridos as $campo) {
            if (empty(trim($datos[$campo] ?? ''))) {
                throw new Exception("Falta el campo: $campo");
            }
        }
        
        // Verificar cédula única
        $stmt = $this->db->prepare("SELECT id FROM personas WHERE cedula_identidad = ?");
        $stmt->execute([$datos['cedula']]);
        if ($stmt->fetch()) {
            throw new Exception("La cédula ya está registrada");
        }
    }
    
    private function generarCodigo($estado) {
        $prefijo = strtoupper(substr($estado, 0, 3));
        
        // Obtener correlativo
        $stmt = $this->db->prepare("SELECT correlativo FROM correlativos_estado WHERE estado = ?");
        $stmt->execute([$estado]);
        $result = $stmt->fetch();
        
        if ($result) {
            $correlativo = $result['correlativo'] + 1;
            $stmt = $this->db->prepare("UPDATE correlativos_estado SET correlativo = ? WHERE estado = ?");
            $stmt->execute([$correlativo, $estado]);
        } else {
            $correlativo = 1;
            $stmt = $this->db->prepare("INSERT INTO correlativos_estado (estado, correlativo) VALUES (?, ?)");
            $stmt->execute([$estado, $correlativo]);
        }
        
        return $prefijo . '-' . str_pad($correlativo, 4, '0', STR_PAD_LEFT);
    }
    
    private function procesarFoto($foto) {
        // Validar
        if ($foto['size'] > MAX_FILE_SIZE) {
            throw new Exception("La foto es muy grande (máximo 5MB)");
        }
        
        $ext = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_TYPES)) {
            throw new Exception("Tipo no permitido. Use JPG, PNG o GIF");
        }
        
        // Guardar
        $nombre = 'foto_' . uniqid() . '.' . $ext;
        $destino = FOTOS_DIR . '/' . $nombre;
        
        if (!move_uploaded_file($foto['tmp_name'], $destino)) {
            throw new Exception("Error al guardar la foto");
        }
        
        return $nombre;
    }
    
    private function generarQR($url, $codigo) {
        // Usar API de Google
        $qrUrl = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . urlencode($url);
        $nombre = 'qr_' . $codigo . '.png';
        $destino = QR_DIR . '/' . $nombre;
        
        $qrData = @file_get_contents($qrUrl);
        if ($qrData) {
            file_put_contents($destino, $qrData);
        } else {
            // Crear QR simple
            $imagen = imagecreate(300, 300);
            $fondo = imagecolorallocate($imagen, 255, 255, 255);
            $texto = imagecolorallocate($imagen, 0, 0, 0);
            imagestring($imagen, 5, 50, 140, "Código: " . $codigo, $texto);
            imagestring($imagen, 3, 40, 160, "Escanea con cámara", $texto);
            imagepng($imagen, $destino);
            imagedestroy($imagen);
        }
        
        return $nombre;
    }
    
    // ========== CONSULTAS ==========
    
    public function buscarPersona($codigo) {
        $stmt = $this->db->prepare("SELECT * FROM personas WHERE codigo_credencial = ? OR cedula_identidad = ? LIMIT 1");
        $stmt->execute([$codigo, $codigo]);
        $persona = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($persona) {
            if ($persona['foto']) {
                $persona['foto_url'] = FOTOS_URL . $persona['foto'];
            }
            if ($persona['qr_imagen']) {
                $persona['qr_imagen_url'] = QR_URL . $persona['qr_imagen'];
            }
            $persona['estado_texto'] = $persona['activo'] ? 'ACTIVA' : 'INACTIVA';
            $persona['estado_clase'] = $persona['activo'] ? 'success' : 'danger';
        }
        
        return $persona;
    }
    
    public function obtenerPersonas($filtros = []) {
        $sql = "SELECT * FROM personas WHERE 1=1";
        $params = [];
        
        if (!empty($filtros['estado'])) {
            $sql .= " AND estado = ?";
            $params[] = $filtros['estado'];
        }
        
        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (codigo_credencial LIKE ? OR cedula_identidad LIKE ? OR primer_nombre LIKE ?)";
            $busqueda = '%' . $filtros['busqueda'] . '%';
            $params[] = $busqueda;
            $params[] = $busqueda;
            $params[] = $busqueda;
        }
        
        $sql .= " ORDER BY fecha_registro DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $personas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Agregar URLs
        foreach ($personas as &$p) {
            if ($p['foto']) {
                $p['foto_url'] = FOTOS_URL . $p['foto'];
            }
            if ($p['qr_imagen']) {
                $p['qr_imagen_url'] = QR_URL . $p['qr_imagen'];
            }
            $p['estado_texto'] = $p['activo'] ? 'Activa' : 'Inactiva';
            $p['estado_clase'] = $p['activo'] ? 'success' : 'danger';
        }
        
        return $personas;
    }
    
    public function obtenerEstadisticas() {
        $stats = [];
        
        try {
            // Total
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM personas");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total'] = $result['total'] ?? 0;
            
            // Activas
            $stmt = $this->db->query("SELECT COUNT(*) as activas FROM personas WHERE activo = 1");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['activas'] = $result['activas'] ?? 0;
            
            // Por estado
            $stmt = $this->db->query("SELECT estado, COUNT(*) as cantidad FROM personas GROUP BY estado ORDER BY cantidad DESC");
            $stats['por_estado'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Último registro
            $stmt = $this->db->query("SELECT fecha_registro FROM personas ORDER BY fecha_registro DESC LIMIT 1");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['ultimo_registro'] = $result['fecha_registro'] ?? null;
            
        } catch(Exception $e) {
            $stats['total'] = 0;
            $stats['activas'] = 0;
            $stats['por_estado'] = [];
            $stats['ultimo_registro'] = null;
        }
        
        return $stats;
    }
}
?>