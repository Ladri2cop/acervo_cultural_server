<?php

class PrestamosModel extends Model
{
    protected static $table = 'prestamos_transferencias';
    protected static $primaryKey = 'id_movimiento';

    /**
     * Obtiene un movimiento por su ID con información del usuario y detalles
     */
    public static function getById($id)
    {
        $sql = "SELECT pt.*, 
                       ur.username AS usuario_registro_nombre,
                       ud.username AS usuario_devolucion_nombre
                FROM " . self::$table . " pt
                LEFT JOIN bee_users ur ON pt.id_usuario_registro = ur.id
                LEFT JOIN bee_users ud ON pt.id_usuario_devolucion = ud.id
                WHERE pt." . self::$primaryKey . " = :id LIMIT 1";
        $rows = parent::query($sql, ['id' => $id]);
        if (!$rows || !isset($rows[0])) {
            return null;
        }

        $mov = $rows[0];
        $pieza = self::obtenerDetallePieza($mov['tipo_acervo'], $mov['id_pieza']);
        $mov['pieza_actual'] = $pieza;
        $mov['fotografia_url'] = ($pieza && !empty($pieza['fotografia'])) ? 'assets/uploads/' . $pieza['fotografia'] : '';
        $mov['es_vencido'] = ($mov['tipo_movimiento'] === 'prestamo' && 
                              $mov['estatus'] === 'activo' && 
                              !empty($mov['fecha_devolucion_estimada']) && 
                              strtotime($mov['fecha_devolucion_estimada']) < strtotime(date('Y-m-d')));
        return $mov;
    }

    /**
     * Obtiene un movimiento por su folio
     */
    public static function getByFolio($folio)
    {
        $sql = "SELECT pt.*, 
                       ur.username AS usuario_registro_nombre,
                       ud.username AS usuario_devolucion_nombre
                FROM " . self::$table . " pt
                LEFT JOIN bee_users ur ON pt.id_usuario_registro = ur.id
                LEFT JOIN bee_users ud ON pt.id_usuario_devolucion = ud.id
                WHERE pt.folio = :folio LIMIT 1";
        $rows = parent::query($sql, ['folio' => $folio]);
        if (!$rows || !isset($rows[0])) {
            return null;
        }
        $mov = $rows[0];
        $pieza = self::obtenerDetallePieza($mov['tipo_acervo'], $mov['id_pieza']);
        $mov['pieza_actual'] = $pieza;
        $mov['fotografia_url'] = ($pieza && !empty($pieza['fotografia'])) ? 'assets/uploads/' . $pieza['fotografia'] : '';
        return $mov;
    }

    /**
     * Genera un folio institucional único correlativo: PRE-2026-0001 o TRA-2026-0001
     */
    public static function generarFolio($tipo)
    {
        $prefijo = ($tipo === 'transferencia') ? 'TRA' : 'PRE';
        $anio = date('Y');
        $db = Db::connect();
        
        $sql = "SELECT COUNT(*) AS total_tipo FROM " . self::$table . " 
                WHERE tipo_movimiento = :tipo AND YEAR(created_at) = :anio";
        $stmt = $db->prepare($sql);
        $stmt->execute(['tipo' => $tipo, 'anio' => $anio]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $consecutivo = ($row ? (int)$row['total_tipo'] : 0) + 1;

        $folio = sprintf('%s-%s-%04d', $prefijo, $anio, $consecutivo);

        // Asegurar unicidad si ya existiese
        $check = $db->prepare("SELECT id_movimiento FROM " . self::$table . " WHERE folio = :f");
        $check->execute(['f' => $folio]);
        while ($check->fetch()) {
            $consecutivo++;
            $folio = sprintf('%s-%s-%04d', $prefijo, $anio, $consecutivo);
            $check->execute(['f' => $folio]);
        }

        return $folio;
    }

    /**
     * Obtiene los indicadores numéricos (KPIs) de préstamos y transferencias
     */
    public static function getKPIs()
    {
        $db = Db::connect();
        $today = date('Y-m-d');
        $sql = "
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN tipo_movimiento = 'prestamo' AND estatus = 'activo' THEN 1 ELSE 0 END) AS prestamos_activos,
                SUM(CASE WHEN tipo_movimiento = 'prestamo' AND estatus = 'activo' AND fecha_devolucion_estimada IS NOT NULL AND fecha_devolucion_estimada < :today THEN 1 ELSE 0 END) AS prestamos_vencidos,
                SUM(CASE WHEN tipo_movimiento = 'prestamo' AND estatus = 'devuelto' THEN 1 ELSE 0 END) AS prestamos_devueltos,
                SUM(CASE WHEN tipo_movimiento = 'transferencia' THEN 1 ELSE 0 END) AS transferencias
            FROM " . self::$table . "
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute(['today' => $today]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total'               => (int)($res['total'] ?? 0),
            'prestamos_activos'   => (int)($res['prestamos_activos'] ?? 0),
            'prestamos_vencidos'  => (int)($res['prestamos_vencidos'] ?? 0),
            'prestamos_devueltos' => (int)($res['prestamos_devueltos'] ?? 0),
            'transferencias'      => (int)($res['transferencias'] ?? 0)
        ];
    }

    /**
     * Lista movimientos con filtros, búsqueda y paginación
     */
    public static function getMovimientosList($filters = [], $page = 1, $perPage = 10)
    {
        $db = Db::connect();
        $where = [];
        $params = [];

        if (!empty($filters['tipo_movimiento']) && in_array($filters['tipo_movimiento'], ['prestamo', 'transferencia'])) {
            $where[] = "pt.tipo_movimiento = :tipo_movimiento";
            $params['tipo_movimiento'] = $filters['tipo_movimiento'];
        }

        if (!empty($filters['estatus'])) {
            if ($filters['estatus'] === 'vencido') {
                $where[] = "pt.tipo_movimiento = 'prestamo' AND pt.estatus = 'activo' AND pt.fecha_devolucion_estimada IS NOT NULL AND pt.fecha_devolucion_estimada < :today_venc";
                $params['today_venc'] = date('Y-m-d');
            } elseif (in_array($filters['estatus'], ['activo', 'devuelto', 'completada', 'cancelado'])) {
                $where[] = "pt.estatus = :estatus";
                $params['estatus'] = $filters['estatus'];
            }
        }

        if (!empty($filters['tipo_acervo']) && in_array($filters['tipo_acervo'], ['general', 'arqueologico', 'numismatica'])) {
            $where[] = "pt.tipo_acervo = :tipo_acervo";
            $params['tipo_acervo'] = $filters['tipo_acervo'];
        }

        if (!empty($filters['search'])) {
            $s = '%' . trim($filters['search']) . '%';
            $where[] = "(pt.folio LIKE :s1 
                         OR pt.codigo_interno_pieza LIKE :s2 
                         OR pt.titulo_pieza LIKE :s3 
                         OR pt.lugar_origen LIKE :s4 
                         OR pt.lugar_destino LIKE :s5 
                         OR pt.responsable_origen LIKE :s6 
                         OR pt.responsable_destino LIKE :s7 
                         OR pt.no_oficio_autorizacion LIKE :s8 
                         OR pt.motivo LIKE :s9)";
            for ($i = 1; $i <= 9; $i++) {
                $params["s$i"] = $s;
            }
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Conteo total
        $countSql = "SELECT COUNT(*) FROM " . self::$table . " pt {$whereSql}";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $totalRecords = (int)$countStmt->fetchColumn();

        // Paginación
        $offset = ($page - 1) * $perPage;
        $orderSql = "ORDER BY pt.id_movimiento DESC";

        $sql = "
            SELECT pt.*,
                   ur.username AS usuario_registro_nombre,
                   ud.username AS usuario_devolucion_nombre
            FROM " . self::$table . " pt
            LEFT JOIN bee_users ur ON pt.id_usuario_registro = ur.id
            LEFT JOIN bee_users ud ON pt.id_usuario_devolucion = ud.id
            {$whereSql}
            {$orderSql}
            LIMIT {$perPage} OFFSET {$offset}
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $today = date('Y-m-d');
        foreach ($rows as &$r) {
            $r['es_vencido'] = ($r['tipo_movimiento'] === 'prestamo' && 
                                $r['estatus'] === 'activo' && 
                                !empty($r['fecha_devolucion_estimada']) && 
                                strtotime($r['fecha_devolucion_estimada']) < strtotime($today));
            
            // Obtener foto rápida de la pieza
            $pieza = self::obtenerDetallePieza($r['tipo_acervo'], $r['id_pieza']);
            $r['fotografia_url'] = ($pieza && !empty($pieza['fotografia'])) ? 'assets/uploads/' . $pieza['fotografia'] : '';
            $r['ubicacion_actual'] = $pieza ? ($pieza['ubicacion_fisica'] ?? '') : '';
        }

        return [
            'data' => $rows,
            'total' => $totalRecords,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($totalRecords / $perPage)
        ];
    }

    /**
     * Busca piezas aprobadas del catálogo oficial para asignar a un préstamo o transferencia
     */
    public static function buscarPiezasDisponibles($search = '', $tipoAcervo = 'todos', $limit = 25)
    {
        $db = Db::connect();
        $subqueries = [];

        if ($tipoAcervo === 'todos' || $tipoAcervo === 'general') {
            $subqueries[] = "
                SELECT 'general' AS tipo_acervo, 
                       id_acervo_general AS id_pieza, 
                       codigo_interno, 
                       no_inventario, 
                       nombre_titulo_pieza AS titulo, 
                       fotografia, 
                       ubicacion_fisica, 
                       estado_conservacion 
                FROM acervo_general 
                WHERE status = 1 AND estatus_revision = 'aprobado'
            ";
        }

        if ($tipoAcervo === 'todos' || $tipoAcervo === 'arqueologico') {
            $subqueries[] = "
                SELECT 'arqueologico' AS tipo_acervo, 
                       id_acervo_arq AS id_pieza, 
                       codigo_interno, 
                       no_inventario_scyt AS no_inventario, 
                       nombre_titulo_pieza AS titulo, 
                       fotografia, 
                       ubicacion_fisica, 
                       estado_conservacion 
                FROM acervo_arqueologico 
                WHERE status = 1 AND estatus_revision = 'aprobado'
            ";
        }

        if ($tipoAcervo === 'todos' || $tipoAcervo === 'numismatica') {
            $subqueries[] = "
                SELECT 'numismatica' AS tipo_acervo, 
                       id_acervo_numismatica AS id_pieza, 
                       codigo_interno, 
                       no_inventario, 
                       denominacion AS titulo, 
                       fotografia, 
                       ubicacion_fisica, 
                       estado_conservacion 
                FROM acervo_numismatica 
                WHERE status = 1 AND estatus_revision = 'aprobado'
            ";
        }

        if (empty($subqueries)) {
            return [];
        }

        $unionSql = implode(' UNION ALL ', $subqueries);
        $where = [];
        $params = [];

        if (!empty($search)) {
            $term = '%' . trim($search) . '%';
            $where[] = "(u.codigo_interno LIKE :s1 OR u.no_inventario LIKE :s2 OR u.titulo LIKE :s3 OR u.ubicacion_fisica LIKE :s4)";
            $params['s1'] = $term;
            $params['s2'] = $term;
            $params['s3'] = $term;
            $params['s4'] = $term;
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $sql = "SELECT u.* FROM ({$unionSql}) AS u {$whereSql} ORDER BY u.titulo ASC LIMIT " . (int)$limit;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $r['fotografia_url'] = !empty($r['fotografia']) ? 'assets/uploads/' . $r['fotografia'] : '';
        }

        return $rows;
    }

    /**
     * Obtiene los datos detallados de una pieza según su catálogo
     */
    public static function obtenerDetallePieza($tipoAcervo, $idPieza)
    {
        $db = Db::connect();
        $table = '';
        $pk = '';
        $titleCol = 'nombre_titulo_pieza';

        switch ($tipoAcervo) {
            case 'general':
                $table = 'acervo_general';
                $pk = 'id_acervo_general';
                break;
            case 'arqueologico':
                $table = 'acervo_arqueologico';
                $pk = 'id_acervo_arq';
                break;
            case 'numismatica':
                $table = 'acervo_numismatica';
                $pk = 'id_acervo_numismatica';
                $titleCol = 'denominacion';
                break;
            default:
                return null;
        }

        $sql = "SELECT *, {$titleCol} AS titulo FROM {$table} WHERE {$pk} = :id LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $idPieza]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Actualiza la ubicación física de una pieza
     */
    public static function actualizarUbicacionPieza($tipoAcervo, $idPieza, $nuevaUbicacion)
    {
        $db = Db::connect();
        $table = '';
        $pk = '';

        switch ($tipoAcervo) {
            case 'general':
                $table = 'acervo_general';
                $pk = 'id_acervo_general';
                break;
            case 'arqueologico':
                $table = 'acervo_arqueologico';
                $pk = 'id_acervo_arq';
                break;
            case 'numismatica':
                $table = 'acervo_numismatica';
                $pk = 'id_acervo_numismatica';
                break;
            default:
                return false;
        }

        $sql = "UPDATE {$table} SET ubicacion_fisica = :ubicacion, updated_at = NOW() WHERE {$pk} = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            'ubicacion' => $nuevaUbicacion,
            'id' => $idPieza
        ]);
    }

    /**
     * Inserta un nuevo movimiento
     */
    public static function addMovimiento($data)
    {
        return parent::add(self::$table, $data);
    }

    /**
     * Actualiza un movimiento
     */
    public static function updateMovimiento($id, $data)
    {
        $haystack = [self::$primaryKey => $id];
        return parent::update(self::$table, $haystack, $data);
    }
}
