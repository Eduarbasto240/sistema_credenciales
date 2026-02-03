// En tu archivo database.php, agrega este método a la clase CredencialManager
public function obtenerEstadisticas() {
    $estadisticas = [
        'total_personas' => 0,
        'total_cargos' => 0,
        'total_responsables' => 0,
        'registros_hoy' => 0
    ];
    
    try {
        // Total de personas registradas
        $sql = "SELECT COUNT(*) as total FROM personas WHERE estado = 'activo'";
        $stmt = sqlsrv_query($this->conn, $sql);
        if ($stmt && sqlsrv_fetch($stmt)) {
            $estadisticas['total_personas'] = sqlsrv_get_field($stmt, 0);
        }
        
        // Total de cargos únicos
        $sql = "SELECT COUNT(DISTINCT cargo) as total FROM personas WHERE estado = 'activo'";
        $stmt = sqlsrv_query($this->conn, $sql);
        if ($stmt && sqlsrv_fetch($stmt)) {
            $estadisticas['total_cargos'] = sqlsrv_get_field($stmt, 0);
        }
        
        // Total de responsables únicos
        $sql = "SELECT COUNT(DISTINCT persona_responsable) as total FROM personas WHERE estado = 'activo'";
        $stmt = sqlsrv_query($this->conn, $sql);
        if ($stmt && sqlsrv_fetch($stmt)) {
            $estadisticas['total_responsables'] = sqlsrv_get_field($stmt, 0);
        }
        
        // Registros de hoy
        $hoy = date('Y-m-d');
        $sql = "SELECT COUNT(*) as total FROM personas WHERE CAST(fecha_registro AS DATE) = ?";
        $params = array($hoy);
        $stmt = sqlsrv_prepare($this->conn, $sql, $params);
        if ($stmt && sqlsrv_execute($stmt) && sqlsrv_fetch($stmt)) {
            $estadisticas['registros_hoy'] = sqlsrv_get_field($stmt, 0);
        }
        
    } catch (Exception $e) {
        error_log("Error al obtener estadísticas: " . $e->getMessage());
    }
    
    return $estadisticas;
}