<?php

class AcervoArqueologicoModel extends Model
{
    protected static $table = 'acervo_arqueologico';
    protected static $primaryKey = 'id_acervo_arq';

    public static function getById($id)
    {
        $sql = "SELECT * FROM " . self::$table . " WHERE " . self::$primaryKey . " = :id LIMIT 1";
        $params = ['id' => $id];
        $rows = parent::query($sql, $params);
        return $rows ? $rows : [];
    }

    /**
     * Obtiene el id_modulo buscando por nombre de módulo (LIKE)
     * @param string $search
     * @return int|false
     */
    public static function getModuloId($search = 'Acervo Arqueológico')
    {
        $stmt = "SELECT id_modulo FROM modulos WHERE nombre_modulo LIKE :q LIMIT 1";
        $params = ['q' => '%' . $search . '%'];
        $rows = parent::query($stmt, $params);
        if (!$rows || !isset($rows[0]['id_modulo'])) return false;
        return (int)$rows[0]['id_modulo'];
    }

    public static function addPieza($data)
    {
        if (!isset($data['status'])) {
            $data['status'] = 0;
        }
        if (!isset($data['estatus_revision'])) {
            $data['estatus_revision'] = 'pendiente';
        }
        if (!isset($data['id_usuario_creador'])) {
            $data['id_usuario_creador'] = get_user('id') ?: null;
        }
        $data['created_at'] = now();
        return parent::add(self::$table, $data);
    }

    public static function updatePieza($id, $data)
    {
        $haystack = [self::$primaryKey => $id];
        $data['updated_at'] = now();
        return parent::update(self::$table, $haystack, $data);
    }

    public static function deletePieza($id)
    {
        $haystack = [self::$primaryKey => $id];
        $data = ['status' => 0];
        $data['updated_at'] = now();
        return parent::update(self::$table, $haystack, $data);
    }

    /**
     * Obtiene piezas paginadas
     * @param int|null $limit
     * @param int|null $offset
     * @return array
     */
    public static function getAll($limit = null, $offset = null)
    {
        $sql = "SELECT * FROM " . self::$table . " WHERE status = 1 AND estatus_revision = 'aprobado' ORDER BY id_acervo_arq DESC";
        $params = [];
        if ($limit !== null && $offset !== null) {
            $sql .= " LIMIT :offset, :limit";
            $params['offset'] = (int)$offset;
            $params['limit'] = (int)$limit;
        }
        $rows = parent::query($sql, $params);
        return $rows ? $rows : [];
    }

    /**
     * Total de piezas
     * @return int
     */
    public static function getTotal()
    {
        $sql = "SELECT COUNT(*) as total FROM " . self::$table . " WHERE status = 1 AND estatus_revision = 'aprobado'";
        $rows = parent::query($sql);
        return $rows && isset($rows[0]['total']) ? (int)$rows[0]['total'] : 0;
    }

    /**
     * Obtiene piezas filtradas y paginadas directamente desde la base de datos (Optimización SQL)
     *
     * @param array $filtros
     * @param int $limit
     * @param int $offset
     * @return array ['piezas' => array, 'total' => int]
     */
    public static function getPiezasFiltradas(array $filtros = [], int $limit = 10, int $offset = 0): array
    {
        $db = Db::connect();
        $whereParts = ["status = 1", "estatus_revision = 'aprobado'"];
        $params = [];

        // 1. Búsqueda por término
        if (!empty($filtros['search'])) {
            $whereParts[] = "(codigo_interno LIKE :s1 OR nombre_titulo_pieza LIKE :s2 OR no_registro_inah LIKE :s3 OR procedencia LIKE :s4 OR descripcion LIKE :s5 OR observaciones LIKE :s6)";
            $term = '%' . trim($filtros['search']) . '%';
            $params['s1'] = $term;
            $params['s2'] = $term;
            $params['s3'] = $term;
            $params['s4'] = $term;
            $params['s5'] = $term;
            $params['s6'] = $term;
        }

        // 2. Filtro por ubicación física
        if (!empty($filtros['ubicacion'])) {
            $mapUbi = [
                '1' => 'Acambay',
                '2' => 'Tenancingo',
                '3' => 'Toluca',
                '4' => 'Zinacantepec'
            ];
            $ubiText = $mapUbi[$filtros['ubicacion']] ?? $filtros['ubicacion'];
            $whereParts[] = "(ubicacion_fisica LIKE :ubi OR nombre_titulo_pieza LIKE :ubi2 OR descripcion LIKE :ubi3)";
            $params['ubi'] = '%' . trim($ubiText) . '%';
            $params['ubi2'] = '%' . trim($ubiText) . '%';
            $params['ubi3'] = '%' . trim($ubiText) . '%';
        }

        // 3. Filtro por año / época
        if (!empty($filtros['anio'])) {
            $whereParts[] = "(epoca LIKE :epoca OR no_registro_inah LIKE :noReg OR descripcion LIKE :anioDesc)";
            $params['epoca'] = '%' . trim($filtros['anio']) . '%';
            $params['noReg'] = '%' . trim($filtros['anio']) . '%';
            $params['anioDesc'] = '%' . trim($filtros['anio']) . '%';
        }

        $whereSql = implode(' AND ', $whereParts);

        // Conteo eficiente a nivel SQL
        $countSql = "SELECT COUNT(*) FROM " . self::$table . " WHERE {$whereSql}";
        $stmtCount = $db->prepare($countSql);
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetchColumn();

        // Extracción acotada de columnas requeridas para vista
        $dataSql = "SELECT id_acervo_arq, nombre_titulo_pieza, codigo_interno, no_registro_inah, ubicacion_fisica, descripcion, fotografia 
                    FROM " . self::$table . " 
                    WHERE {$whereSql} 
                    ORDER BY id_acervo_arq DESC 
                    LIMIT :offset, :limit";
        $stmtData = $db->prepare($dataSql);
        foreach ($params as $k => $v) {
            $stmtData->bindValue($k, $v, PDO::PARAM_STR);
        }
        $stmtData->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmtData->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmtData->execute();

        return [
            'piezas' => $stmtData->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'total'  => $total
        ];
    }
}