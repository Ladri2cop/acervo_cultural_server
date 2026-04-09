<?php

class AcervoGeneralModel extends Model
{
    protected static $table = 'acervo_general';
    protected static $primaryKey = 'id_acervo_general';

    public static function getById($id)
    {
        $params = [self::$primaryKey => $id];
        return parent::list(self::$table, $params, 1);
    }

    /**
     * Obtiene el id_modulo buscando por nombre de módulo (LIKE)
     * @param string $search
     * @return int|false
     */
    public static function getModuloId($search = 'Acervo General')
    {
        $stmt = "SELECT id_modulo FROM modulos WHERE nombre_modulo LIKE :q LIMIT 1";
        $params = ['q' => '%' . $search . '%'];
        $rows = parent::query($stmt, $params);
        if (!$rows || !isset($rows[0]['id_modulo'])) return false;
        return (int)$rows[0]['id_modulo'];
    }

    public static function addPieza($data)
    {
        $data['status'] = 1;
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
        // $sql = "SELECT * FROM " . self::$table . " WHERE status = 1 ORDER BY id_acervo_general DESC LIMIT 100";
        $sql = "SELECT id_acervo_general, nombre_titulo_pieza, fotografia, ubicacion_fisica, anio, descripcion FROM " . self::$table . " WHERE status = 1 ORDER BY id_acervo_general DESC";
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
        $sql = "SELECT COUNT(*) as total FROM " . self::$table . " WHERE status = 1";
        $rows = parent::query($sql);
        return $rows && isset($rows[0]['total']) ? (int)$rows[0]['total'] : 0;
    }
}
