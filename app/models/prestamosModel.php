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
                       ud.username AS usuario_devolucion_nombre,
                       udict.username AS usuario_dictamen_nombre,
                       m_orig.nombre_museo AS museo_origen_nombre,
                       m_dest.nombre_museo AS museo_destino_nombre
                FROM " . self::$table . " pt
                LEFT JOIN bee_users ur ON pt.id_usuario_registro = ur.id
                LEFT JOIN bee_users ud ON pt.id_usuario_devolucion = ud.id
                LEFT JOIN bee_users udict ON pt.id_usuario_dictamen = udict.id
                LEFT JOIN museos m_orig ON pt.id_museo_origen = m_orig.id_museo
                LEFT JOIN museos m_dest ON pt.id_museo_destino = m_dest.id_museo
                WHERE pt." . self::$primaryKey . " = :id LIMIT 1";
        $rows = parent::query($sql, ['id' => $id]);
        if (!$rows || !isset($rows[0])) {
            return null;
        }

        $mov = $rows[0];
        $pieza = self::obtenerDetallePieza($mov['tipo_acervo'], $mov['id_pieza']);
        $mov['pieza_actual'] = $pieza;
        $mov['fotografia_url'] = ($pieza && !empty($pieza['fotografia'])) ? 'assets/uploads/' . $pieza['fotografia'] : '';
        $mov['archivo_oficio_url'] = !empty($mov['archivo_oficio']) ? 'assets/uploads/oficios_movimientos/' . $mov['archivo_oficio'] : '';
        $mov['puede_dictaminar'] = function_exists('user_can_dictate_movement') ? user_can_dictate_movement($mov) : false;
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
                       ud.username AS usuario_devolucion_nombre,
                       udict.username AS usuario_dictamen_nombre,
                       m_orig.nombre_museo AS museo_origen_nombre,
                       m_dest.nombre_museo AS museo_destino_nombre
                FROM " . self::$table . " pt
                LEFT JOIN bee_users ur ON pt.id_usuario_registro = ur.id
                LEFT JOIN bee_users ud ON pt.id_usuario_devolucion = ud.id
                LEFT JOIN bee_users udict ON pt.id_usuario_dictamen = udict.id
                LEFT JOIN museos m_orig ON pt.id_museo_origen = m_orig.id_museo
                LEFT JOIN museos m_dest ON pt.id_museo_destino = m_dest.id_museo
                WHERE pt.folio = :folio LIMIT 1";
        $rows = parent::query($sql, ['folio' => $folio]);
        if (!$rows || !isset($rows[0])) {
            return null;
        }
        $mov = $rows[0];
        $pieza = self::obtenerDetallePieza($mov['tipo_acervo'], $mov['id_pieza']);
        $mov['pieza_actual'] = $pieza;
        $mov['fotografia_url'] = ($pieza && !empty($pieza['fotografia'])) ? 'assets/uploads/' . $pieza['fotografia'] : '';
        $mov['archivo_oficio_url'] = !empty($mov['archivo_oficio']) ? 'assets/uploads/oficios_movimientos/' . $mov['archivo_oficio'] : '';
        $mov['puede_dictaminar'] = function_exists('user_can_dictate_movement') ? user_can_dictate_movement($mov) : false;
        $mov['es_vencido'] = ($mov['tipo_movimiento'] === 'prestamo' && 
                              $mov['estatus'] === 'activo' && 
                              !empty($mov['fecha_devolucion_estimada']) && 
                              strtotime($mov['fecha_devolucion_estimada']) < strtotime(date('Y-m-d')));
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
     * Soporta filtrado por museo asignado para administradores
     */
    public static function getKPIs($idMuseo = null, $nombreMuseo = null, $esSuperAdmin = null)
    {
        $db = Db::connect();
        $today = date('Y-m-d');
        $where = [];
        $params = ['today' => $today];

        $numArgs = func_num_args();
        if ($esSuperAdmin === null && $numArgs < 3) {
            $esSuperAdmin = function_exists('is_super_admin') ? is_super_admin() : false;
        }

        // Si no es super administrador, aplicar restricción estricta de museo
        if (!$esSuperAdmin) {
            if ($idMuseo === null && $numArgs === 0 && function_exists('get_user_museum_id')) {
                $idMuseo = get_user_museum_id();
            }
            if ($nombreMuseo === null && $numArgs === 0 && function_exists('get_user_museum_name')) {
                $nombreMuseo = get_user_museum_name();
            }

            if (!empty($idMuseo)) {
                $idM = (int)$idMuseo;
                $nomM = !empty($nombreMuseo) ? trim($nombreMuseo) : '';
                if (!empty($nomM)) {
                    $where[] = "(id_museo_origen = :kpi_m_id1 
                                OR id_museo_destino = :kpi_m_id2 
                                OR lugar_origen = :kpi_m_nom1 
                                OR lugar_destino = :kpi_m_nom2 
                                OR lugar_origen LIKE :kpi_m_like1 
                                OR lugar_destino LIKE :kpi_m_like2)";
                    $params['kpi_m_id1'] = $idM;
                    $params['kpi_m_id2'] = $idM;
                    $params['kpi_m_nom1'] = $nomM;
                    $params['kpi_m_nom2'] = $nomM;
                    $params['kpi_m_like1'] = '%' . $nomM . '%';
                    $params['kpi_m_like2'] = '%' . $nomM . '%';
                } else {
                    $where[] = "(id_museo_origen = :kpi_m_id1 OR id_museo_destino = :kpi_m_id2)";
                    $params['kpi_m_id1'] = $idM;
                    $params['kpi_m_id2'] = $idM;
                }
            } else {
                // Administrador sin museo asignado: no tiene acceso a ningún indicador global
                return [
                    'total'                 => 0,
                    'pendientes_aprobacion' => 0,
                    'prestamos_activos'     => 0,
                    'prestamos_vencidos'    => 0,
                    'prestamos_devueltos'   => 0,
                    'transferencias'        => 0,
                    'declinados'            => 0
                ];
            }
        }

        $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN estatus = 'pendiente_aprobacion' THEN 1 ELSE 0 END) AS pendientes_aprobacion,
                SUM(CASE WHEN tipo_movimiento = 'prestamo' AND estatus = 'activo' THEN 1 ELSE 0 END) AS prestamos_activos,
                SUM(CASE WHEN tipo_movimiento = 'prestamo' AND estatus = 'activo' AND fecha_devolucion_estimada IS NOT NULL AND fecha_devolucion_estimada < :today THEN 1 ELSE 0 END) AS prestamos_vencidos,
                SUM(CASE WHEN tipo_movimiento = 'prestamo' AND estatus = 'devuelto' THEN 1 ELSE 0 END) AS prestamos_devueltos,
                SUM(CASE WHEN tipo_movimiento = 'transferencia' AND estatus = 'completada' THEN 1 ELSE 0 END) AS transferencias,
                SUM(CASE WHEN estatus = 'declinado' THEN 1 ELSE 0 END) AS declinados
            FROM " . self::$table . "
            {$whereSql}
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total'                 => (int)($res['total'] ?? 0),
            'pendientes_aprobacion' => (int)($res['pendientes_aprobacion'] ?? 0),
            'prestamos_activos'     => (int)($res['prestamos_activos'] ?? 0),
            'prestamos_vencidos'    => (int)($res['prestamos_vencidos'] ?? 0),
            'prestamos_devueltos'   => (int)($res['prestamos_devueltos'] ?? 0),
            'transferencias'        => (int)($res['transferencias'] ?? 0),
            'declinados'            => (int)($res['declinados'] ?? 0)
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

        // Filtro de alcance territorial por museo asignado (para rol Administrador)
        $esSuperAdmin = isset($filters['es_super_admin'])
            ? (bool)$filters['es_super_admin']
            : (function_exists('is_super_admin') ? is_super_admin() : false);

        if (!$esSuperAdmin) {
            $idMUser = array_key_exists('id_museo_usuario', $filters)
                ? (!empty($filters['id_museo_usuario']) ? (int)$filters['id_museo_usuario'] : null)
                : (function_exists('get_user_museum_id') ? get_user_museum_id() : null);

            $nomMUser = array_key_exists('nombre_museo_usuario', $filters)
                ? trim((string)($filters['nombre_museo_usuario'] ?? ''))
                : (function_exists('get_user_museum_name') ? (string)get_user_museum_name() : '');

            if (!empty($idMUser)) {
                if (!empty($nomMUser)) {
                    $where[] = "(pt.id_museo_origen = :user_m_id1 
                                OR pt.id_museo_destino = :user_m_id2 
                                OR pt.lugar_origen = :user_m_nom1 
                                OR pt.lugar_destino = :user_m_nom2 
                                OR pt.lugar_origen LIKE :user_m_like1 
                                OR pt.lugar_destino LIKE :user_m_like2)";
                    $params['user_m_id1'] = $idMUser;
                    $params['user_m_id2'] = $idMUser;
                    $params['user_m_nom1'] = $nomMUser;
                    $params['user_m_nom2'] = $nomMUser;
                    $params['user_m_like1'] = '%' . $nomMUser . '%';
                    $params['user_m_like2'] = '%' . $nomMUser . '%';
                } else {
                    $where[] = "(pt.id_museo_origen = :user_m_id1 OR pt.id_museo_destino = :user_m_id2)";
                    $params['user_m_id1'] = $idMUser;
                    $params['user_m_id2'] = $idMUser;
                }
            } else {
                // Si el administrador no tiene museo asignado, NO debe ver registros
                $where[] = "1 = 0";
            }
        }

        if (!empty($filters['tipo_movimiento']) && in_array($filters['tipo_movimiento'], ['prestamo', 'transferencia'])) {
            $where[] = "pt.tipo_movimiento = :tipo_movimiento";
            $params['tipo_movimiento'] = $filters['tipo_movimiento'];
        }

        if (!empty($filters['estatus'])) {
            if ($filters['estatus'] === 'vencido') {
                $where[] = "pt.tipo_movimiento = 'prestamo' AND pt.estatus = 'activo' AND pt.fecha_devolucion_estimada IS NOT NULL AND pt.fecha_devolucion_estimada < :today_venc";
                $params['today_venc'] = date('Y-m-d');
            } elseif (in_array($filters['estatus'], ['pendiente_aprobacion', 'activo', 'devuelto', 'completada', 'declinado', 'cancelado'])) {
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
                   ud.username AS usuario_devolucion_nombre,
                   udict.username AS usuario_dictamen_nombre,
                   m_orig.nombre_museo AS museo_origen_nombre,
                   m_dest.nombre_museo AS museo_destino_nombre
            FROM " . self::$table . " pt
            LEFT JOIN bee_users ur ON pt.id_usuario_registro = ur.id
            LEFT JOIN bee_users ud ON pt.id_usuario_devolucion = ud.id
            LEFT JOIN bee_users udict ON pt.id_usuario_dictamen = udict.id
            LEFT JOIN museos m_orig ON pt.id_museo_origen = m_orig.id_museo
            LEFT JOIN museos m_dest ON pt.id_museo_destino = m_dest.id_museo
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
            $r['archivo_oficio_url'] = !empty($r['archivo_oficio']) ? 'assets/uploads/oficios_movimientos/' . $r['archivo_oficio'] : '';
            $r['puede_dictaminar'] = function_exists('user_can_dictate_movement') ? user_can_dictate_movement($r) : false;
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
                       COALESCE(NULLIF(coleccion, ''), 'Acervo General') AS coleccion,
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
                       COALESCE(NULLIF(coleccion, ''), 'Acervo Arqueológico') AS coleccion,
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
                       'Acervo Numismático' AS coleccion,
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
            $r['origen_detectado'] = self::resolverOrigenPieza($r['ubicacion_fisica'] ?? '', $r['tipo_acervo'], $r['id_pieza']);
        }

        return $rows;
    }

    /**
     * Resuelve el origen institucional de una pieza a partir de su ubicación física
     * y su historial de préstamos/transferencias
     */
    public static function resolverOrigenPieza($ubicacionFisica, $tipoAcervo = null, $idPieza = null)
    {
        $db = Db::connect();

        // 1. Si se proporcionan catálogo e ID de pieza, verificar si tuvo un movimiento previo completado o activo
        if (!empty($tipoAcervo) && !empty($idPieza)) {
            $stmtMov = $db->prepare("
                SELECT id_movimiento, tipo_movimiento, tipo_destino, lugar_destino, id_museo_destino, responsable_destino, estatus
                FROM prestamos_transferencias
                WHERE tipo_acervo = :t AND id_pieza = :id AND estatus IN ('activo', 'completada')
                ORDER BY id_movimiento DESC
                LIMIT 1
            ");
            $stmtMov->execute(['t' => $tipoAcervo, 'id' => (int)$idPieza]);
            $ultMov = $stmtMov->fetch(PDO::FETCH_ASSOC);

            if ($ultMov && !empty($ultMov['id_museo_destino'])) {
                $stmtM = $db->prepare("SELECT id_museo, nombre_museo, responsable_museo FROM museos WHERE id_museo = :id LIMIT 1");
                $stmtM->execute(['id' => $ultMov['id_museo_destino']]);
                $museoMov = $stmtM->fetch(PDO::FETCH_ASSOC);
                if ($museoMov) {
                    return [
                        'tipo_origen' => 'museo',
                        'id_museo' => (int)$museoMov['id_museo'],
                        'nombre_museo' => $museoMov['nombre_museo'],
                        'responsable_origen' => $museoMov['responsable_museo'] ?? ($ultMov['responsable_destino'] ?? ''),
                        'lugar_origen' => $museoMov['nombre_museo'],
                        'ubicacion_original' => $ubicacionFisica,
                        'fuente' => 'movimiento_previo'
                    ];
                }
            }
        }

        $ubicacionLim = trim($ubicacionFisica ?? '');
        if (empty($ubicacionLim)) {
            return [
                'tipo_origen' => 'museo',
                'id_museo' => null,
                'nombre_museo' => '',
                'responsable_origen' => '',
                'lugar_origen' => '',
                'ubicacion_original' => '',
                'fuente' => 'vacio'
            ];
        }

        // 2. Normalización para búsqueda de texto sin acentos
        $unwanted = ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n','Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u','Ü'=>'u','Ñ'=>'n'];
        $norm = function($str) use ($unwanted) {
            return strtolower(strtr(trim($str), $unwanted));
        };

        $locNorm = $norm($ubicacionLim);

        // Obtener todos los museos activos
        $stmtAll = $db->query("SELECT id_museo, nombre_museo, responsable_museo FROM museos WHERE status = 1 ORDER BY id_museo ASC");
        $museos = $stmtAll ? $stmtAll->fetchAll(PDO::FETCH_ASSOC) : [];
        $museosById = [];
        foreach ($museos as $m) {
            $museosById[$m['id_museo']] = $m;
        }

        // A. Coincidencia exacta o contenida con el nombre oficial del museo
        foreach ($museos as $m) {
            $mNorm = $norm($m['nombre_museo']);
            if ($locNorm === $mNorm || strpos($locNorm, $mNorm) !== false || strpos($mNorm, $locNorm) !== false) {
                return [
                    'tipo_origen' => 'museo',
                    'id_museo' => (int)$m['id_museo'],
                    'nombre_museo' => $m['nombre_museo'],
                    'responsable_origen' => $m['responsable_museo'] ?? '',
                    'lugar_origen' => $m['nombre_museo'],
                    'ubicacion_original' => $ubicacionLim,
                    'fuente' => 'coincidencia_nombre'
                ];
            }
        }

        // B. Diccionario de alias / acrónimos históricos para piezas registradas
        $aliases = [
            1 => ['acuarela'],
            2 => ['adolfo lopez mateos', 'lopez mateos'],
            3 => ['antonio ruiz perez', 'ruiz perez'],
            4 => ['antropologia', 'mahem', 'antropologia e historia'],
            5 => ['apaxco', 'arqueologico de apaxco'],
            6 => ['arte moderno', 'mam'],
            7 => ['bellas artes'],
            8 => ['plaza estado de mexico'],
            9 => ['ciencias naturales'],
            10 => ['cosmovitral', 'jardin botanico'],
            11 => ['culturas populares'],
            12 => ['jose ma. luis mora', 'luis mora', 'jose maria luis mora'],
            13 => ['roman pina chan', 'pina chan', 'tenango'],
            14 => ['estampa', 'la estampa'],
            15 => ['felipe santiago'],
            16 => ['pagaza', 'joaquin arcadio'],
            17 => ['velasco', 'jose maria velasco'],
            18 => ['mineria del oro', 'el oro'],
            19 => ['nishizawa'],
            20 => ['numismatica'],
            21 => ['ocuilan'],
            22 => ['virreinal', 'zinacantepec'],
            23 => ['isidro fabela', 'atlacomulco'],
            24 => ['torres bicentenario'],
            25 => ['casa toluca', 'boveda casa toluca'],
            26 => ['patrimonio cultural'],
            27 => ['acervo cultural'],
            28 => ['departamento de museos'],
            29 => ['biblioteca publica'],
            30 => ['archivo historico'],
            31 => ['cineteca']
        ];

        foreach ($aliases as $idM => $aliasList) {
            foreach ($aliasList as $al) {
                if (strpos($locNorm, $norm($al)) !== false) {
                    $m = $museosById[$idM] ?? null;
                    if ($m) {
                        return [
                            'tipo_origen' => 'museo',
                            'id_museo' => (int)$m['id_museo'],
                            'nombre_museo' => $m['nombre_museo'],
                            'responsable_origen' => $m['responsable_museo'] ?? '',
                            'lugar_origen' => $m['nombre_museo'],
                            'ubicacion_original' => $ubicacionLim,
                            'fuente' => 'alias_museo'
                        ];
                    }
                }
            }
        }

        // C. No coincide con museo: asignar como unidad o dependencia libre
        return [
            'tipo_origen' => 'unidad',
            'id_museo' => null,
            'nombre_museo' => '',
            'responsable_origen' => '',
            'lugar_origen' => $ubicacionLim,
            'ubicacion_original' => $ubicacionLim,
            'fuente' => 'ubicacion_personalizada'
        ];
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
