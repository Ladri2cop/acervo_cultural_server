<?php

/**
 * Modelo para la Gestión Administrativa de Catálogos Maestros en SIPAC
 * Con soporte para operaciones CRUD, control de dependencias relacionales y trazabilidad forense.
 */
class catalogosModel extends Model
{
  /**
   * Catálogos maestros soportados y sus metadatos estructurales
   */
  public static function getCatalogosDisponibles(): array
  {
    return [
      'museos' => [
        'slug'         => 'museos',
        'table'        => 'museos',
        'pk'           => 'id_museo',
        'name_field'   => 'nombre_museo',
        'title'        => 'Museos y Recintos Culturales',
        'singular'     => 'Museo o Recinto',
        'icon'         => 'bx bx-building-house',
        'badge_color'  => 'primary',
        'description'  => 'Red oficial de museos y dependencias de resguardo de la Secretaría de Cultura y Turismo.',
        'fields'       => [
          'nombre_museo' => [
            'label'       => 'Nombre del Museo o Recinto',
            'type'        => 'text',
            'required'    => true,
            'placeholder' => 'Ej: Museo de Arte Moderno'
          ],
          'ubicacion' => [
            'label'       => 'Ubicación / Domicilio',
            'type'        => 'text',
            'required'    => false,
            'placeholder' => 'Ej: Jesús Reyes Heroles 302, San Buenaventura, Toluca'
          ],
          'responsable_museo' => [
            'label'       => 'Responsable / Titular',
            'type'        => 'text',
            'required'    => false,
            'placeholder' => 'Ej: Mtro. Director del Recinto'
          ],
          'telefono' => [
            'label'       => 'Teléfono de Contacto',
            'type'        => 'text',
            'required'    => false,
            'placeholder' => 'Ej: 7221234567'
          ]
        ]
      ],
      'materiales' => [
        'slug'         => 'materiales',
        'table'        => 'materiales',
        'pk'           => 'id_material',
        'name_field'   => 'nombre_material',
        'title'        => 'Materiales',
        'singular'     => 'Material',
        'icon'         => 'bx bx-cube',
        'badge_color'  => 'success',
        'description'  => 'Catálogo de materias primas y soporte físico para artes plásticas, numismática y arqueología.',
        'fields'       => [
          'nombre_material' => [
            'label'       => 'Nombre del Material',
            'type'        => 'text',
            'required'    => true,
            'placeholder' => 'Ej: Bronce patinado, Óleo sobre tela, Barro cocido'
          ],
          'categoria' => [
            'label'       => 'Categoría / Disciplina',
            'type'        => 'select',
            'required'    => true,
            'options'     => [
              'Materiales Pictóricos'              => 'Materiales Pictóricos',
              'Materiales para Culturas Populares' => 'Materiales para Culturas Populares',
              'Materiales para Ciencias Naturales' => 'Materiales para Ciencias Naturales',
              'Arqueológico'                       => 'Arqueológico',
              'Numismática'                        => 'Numismática'
            ],
            'placeholder' => 'Seleccione una categoría...'
          ]
        ]
      ],
      'tecnicas' => [
        'slug'         => 'tecnicas',
        'table'        => 'tecnicas',
        'pk'           => 'id_tecnica',
        'name_field'   => 'nombre_tecnica',
        'title'        => 'Técnicas de Manufactura y Arte',
        'singular'     => 'Técnica',
        'icon'         => 'bx bx-paint',
        'badge_color'  => 'info',
        'description'  => 'Procedimientos artísticos, decorativos y de manufactura de las piezas culturales.',
        'fields'       => [
          'nombre_tecnica' => [
            'label'       => 'Nombre de la Técnica',
            'type'        => 'text',
            'required'    => true,
            'placeholder' => 'Ej: Aguafuerte, Prensado, Modelado a mano'
          ],
          'categoria' => [
            'label'       => 'Categoría / Disciplina',
            'type'        => 'select',
            'required'    => true,
            'options'     => [
              'Técnicas Pictóricas'                => 'Técnicas Pictóricas',
              'Técnicas de Culturas Populares'     => 'Técnicas de Culturas Populares',
              'Técnicas para Ciencias Naturales'   => 'Técnicas para Ciencias Naturales',
              'Manufactura Arqueológica'           => 'Manufactura Arqueológica',
              'Técnicas Decorativas Arqueológicas' => 'Técnicas Decorativas Arqueológicas',
              'Numismática'                        => 'Numismática'
            ],
            'placeholder' => 'Seleccione una categoría...'
          ]
        ]
      ],
      'epocas_periodos' => [
        'slug'         => 'epocas_periodos',
        'table'        => 'epocas_periodos',
        'pk'           => 'id_epoca_periodo',
        'name_field'   => 'nombre_epoca_periodo',
        'title'        => 'Épocas y Periodos Históricos',
        'singular'     => 'Época / Periodo',
        'icon'         => 'bx bx-history',
        'badge_color'  => 'warning',
        'description'  => 'Líneas temporales y periodizaciones históricas oficiales del Estado de México.',
        'fields'       => [
          'nombre_epoca_periodo' => [
            'label'       => 'Nombre de la Época o Periodo',
            'type'        => 'text',
            'required'    => true,
            'placeholder' => 'Ej: Porfiriato, Prehispánica, Siglo XIX'
          ],
          'siglo' => [
            'label'       => 'Siglo (Opcional)',
            'type'        => 'text',
            'required'    => false,
            'placeholder' => 'Ej: XIX, XX, Posclásico Tardío'
          ]
        ]
      ],
      'tipo_obras' => [
        'slug'         => 'tipo_obras',
        'table'        => 'tipo_obras',
        'pk'           => 'id_tipo_obra',
        'name_field'   => 'nombre_tipo_obra',
        'title'        => 'Tipos de Obra',
        'singular'     => 'Tipo de Obra',
        'icon'         => 'bx bx-palette',
        'badge_color'  => 'danger',
        'description'  => 'Clasificación tipológica para conmutación de dimensiones y formatos de acervo.',
        'fields'       => [
          'nombre_tipo_obra' => [
            'label'       => 'Nombre del Tipo de Obra',
            'type'        => 'text',
            'required'    => true,
            'placeholder' => 'Ej: Pintura, Escultura, Cerámica, Textil'
          ],
          'categoria' => [
            'label'       => 'Clasificación / Dimensión',
            'type'        => 'select',
            'required'    => true,
            'options'     => [
              'Bidimensional'  => 'Bidimensional (cm de largo × ancho)',
              'Tridimensional' => 'Tridimensional (medidas alto × ancho × profundidad)',
              'Numismática'    => 'Numismática (Monedas, Billetes, Medallística)'
            ],
            'placeholder' => 'Seleccione una clasificación...'
          ]
        ]
      ],
      'estado_conservacion' => [
        'slug'         => 'estado_conservacion',
        'table'        => 'estado_conservacion',
        'pk'           => 'id_estado_conservacion',
        'name_field'   => 'nombre_estado_conservacion',
        'title'        => 'Estados de Conservación',
        'singular'     => 'Estado de Conservación',
        'icon'         => 'bx bx-shield-quarter',
        'badge_color'  => 'dark',
        'description'  => 'Niveles de integridad física y estado de preservación de los bienes.',
        'fields'       => [
          'nombre_estado_conservacion' => [
            'label'       => 'Estado de Conservación',
            'type'        => 'text',
            'required'    => true,
            'placeholder' => 'Ej: Bueno, Regular, Malo'
          ]
        ]
      ],
      'colecciones' => [
        'slug'         => 'colecciones',
        'table'        => 'colecciones',
        'pk'           => 'id_coleccion',
        'name_field'   => 'nombre_coleccion',
        'title'        => 'Colecciones Patrimoniales',
        'singular'     => 'Colección',
        'icon'         => 'bx bx-archive',
        'badge_color'  => 'secondary',
        'description'  => 'Fondos, colecciones temáticas y agrupaciones para préstamos y exposiciones.',
        'fields'       => [
          'nombre_coleccion' => [
            'label'       => 'Nombre de la Colección',
            'type'        => 'text',
            'required'    => true,
            'placeholder' => 'Ej: Colección Numismática Mexiquense, Arte Virreinal'
          ]
        ]
      ],
      'autor_cultura' => [
        'slug'         => 'autor_cultura',
        'table'        => 'autor_cultura',
        'pk'           => 'id_autor_cultura',
        'name_field'   => 'nombre_autor_cultura',
        'title'        => 'Autores y Culturas',
        'singular'     => 'Autor o Cultura',
        'icon'         => 'bx bx-user-circle',
        'badge_color'  => 'info',
        'description'  => 'Creadores plásticos, artistas, talleres o culturas prehispánicas asociadas.',
        'fields'       => [
          'nombre_autor_cultura' => [
            'label'       => 'Nombre del Autor o Cultura',
            'type'        => 'text',
            'required'    => true,
            'placeholder' => 'Ej: José María Velasco, Cultura Otomí, Taller Bellas Artes'
          ]
        ]
      ]
    ];
  }

  /**
   * Obtiene la configuración de un catálogo específico o null si no existe
   */
  public static function getCatalogoConfig(string $slug): ?array
  {
    $catalogos = self::getCatalogosDisponibles();
    return $catalogos[$slug] ?? null;
  }

  /**
   * Obtiene estadísticas de conteo para todos los catálogos
   */
  public static function getEstadisticas(): array
  {
    $stats = [];
    $catalogos = self::getCatalogosDisponibles();
    $db = Db::connect();

    foreach ($catalogos as $slug => $cfg) {
      $table = $cfg['table'];
      try {
        $stmt = $db->query("SELECT 
          COUNT(*) as total,
          SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as activos,
          SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as inactivos
          FROM `{$table}`");
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['total' => 0, 'activos' => 0, 'inactivos' => 0];
        $stats[$slug] = [
          'total'     => (int)($row['total'] ?? 0),
          'activos'   => (int)($row['activos'] ?? 0),
          'inactivos' => (int)($row['inactivos'] ?? 0)
        ];
      } catch (Throwable $e) {
        $stats[$slug] = ['total' => 0, 'activos' => 0, 'inactivos' => 0];
      }
    }

    return $stats;
  }

  /**
   * Obtiene las categorías únicas con conteo para un catálogo
   */
  public static function getCategorias(string $slug): array
  {
    $cfg = self::getCatalogoConfig($slug);
    if (!$cfg || !isset($cfg['fields']['categoria'])) return [];

    $table = $cfg['table'];
    try {
      $db = Db::connect();
      $stmt = $db->query("SELECT categoria, COUNT(*) as total FROM `{$table}` WHERE categoria IS NOT NULL AND categoria != '' GROUP BY categoria ORDER BY categoria ASC");
      return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    } catch (Throwable $e) {
      return [];
    }
  }

  /**
   * Obtiene los elementos de un catálogo con soporte de filtros
   */
  public static function getItems(string $slug, ?int $statusFilter = null, ?string $search = null, ?string $categoriaFilter = null): array
  {
    $cfg = self::getCatalogoConfig($slug);
    if (!$cfg) return [];

    $table = $cfg['table'];
    $pk = $cfg['pk'];
    $nameField = $cfg['name_field'];

    $where = [];
    $params = [];

    if ($statusFilter !== null && ($statusFilter === 0 || $statusFilter === 1)) {
      $where[] = "status = :status";
      $params[':status'] = $statusFilter;
    }

    if (!empty($categoriaFilter)) {
      $where[] = "categoria = :categoria";
      $params[':categoria'] = trim($categoriaFilter);
    }

    if (!empty($search)) {
      $searchLike = '%' . trim($search) . '%';
      $searchConds = ["`{$nameField}` LIKE :search_name"];
      $params[':search_name'] = $searchLike;

      if ($slug === 'museos') {
        $searchConds[] = "`ubicacion` LIKE :search_ubi";
        $searchConds[] = "`responsable_museo` LIKE :search_resp";
        $params[':search_ubi'] = $searchLike;
        $params[':search_resp'] = $searchLike;
      } elseif ($slug === 'epocas_periodos') {
        $searchConds[] = "`siglo` LIKE :search_siglo";
        $params[':search_siglo'] = $searchLike;
      }

      if (isset($cfg['fields']['categoria'])) {
        $searchConds[] = "`categoria` LIKE :search_cat";
        $params[':search_cat'] = $searchLike;
      }

      $where[] = "(" . implode(" OR ", $searchConds) . ")";
    }

    $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    $orderSql = isset($cfg['fields']['categoria']) 
      ? "ORDER BY `categoria` ASC, `{$nameField}` ASC" 
      : "ORDER BY `{$nameField}` ASC";

    $sql = "SELECT * FROM `{$table}` {$whereSql} {$orderSql}";

    $db = Db::connect();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
  }

  /**
   * Obtiene un registro por ID
   */
  public static function getItemById(string $slug, int $id): ?array
  {
    $cfg = self::getCatalogoConfig($slug);
    if (!$cfg) return null;

    $table = $cfg['table'];
    $pk = $cfg['pk'];

    $db = Db::connect();
    $stmt = $db->prepare("SELECT * FROM `{$table}` WHERE `{$pk}` = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
  }

  /**
   * Guarda o actualiza un registro en el catálogo
   */
  public static function guardar(string $slug, array $data, ?int $id = null): array
  {
    $cfg = self::getCatalogoConfig($slug);
    if (!$cfg) {
      return ['status' => false, 'message' => 'Catálogo no válido.'];
    }

    $table = $cfg['table'];
    $pk = $cfg['pk'];
    $nameField = $cfg['name_field'];

    // Validar nombre obligatorio
    $nombre = trim($data[$nameField] ?? '');
    if (empty($nombre)) {
      return ['status' => false, 'message' => 'El campo ' . $cfg['fields'][$nameField]['label'] . ' es obligatorio.'];
    }

    $db = Db::connect();

    // Validar duplicado
    $sqlDup = "SELECT `{$pk}` FROM `{$table}` WHERE LOWER(TRIM(`{$nameField}`)) = LOWER(TRIM(:nombre))";
    $paramsDup = [':nombre' => $nombre];
    if ($id !== null && $id > 0) {
      $sqlDup .= " AND `{$pk}` != :id";
      $paramsDup[':id'] = $id;
    }
    $stmtDup = $db->prepare($sqlDup);
    $stmtDup->execute($paramsDup);
    if ($stmtDup->fetch()) {
      return ['status' => false, 'message' => "Ya existe un registro con el nombre '{$nombre}' en este catálogo."];
    }

    // Preparar campos
    $fieldsToSave = [];
    $fieldsToSave[$nameField] = $nombre;

    foreach ($cfg['fields'] as $fName => $fConfig) {
      if ($fName === $nameField) continue;
      $fieldsToSave[$fName] = isset($data[$fName]) ? trim($data[$fName]) : null;
    }

    try {
      if ($id !== null && $id > 0) {
        // ACTUALIZACIÓN
        $itemAntes = self::getItemById($slug, $id);
        if (!$itemAntes) {
          return ['status' => false, 'message' => 'El registro a editar no existe.'];
        }

        $fieldsToSave['status'] = isset($data['status']) ? (int)$data['status'] : (int)($itemAntes['status'] ?? 1);

        $setPairs = [];
        $paramsUpdate = [':id' => $id];
        foreach ($fieldsToSave as $f => $val) {
          $setPairs[] = "`{$f}` = :{$f}";
          $paramsUpdate[":{$f}"] = $val;
        }

        $sql = "UPDATE `{$table}` SET " . implode(", ", $setPairs) . ", `updated_at` = NOW() WHERE `{$pk}` = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute($paramsUpdate);

        $itemDespues = self::getItemById($slug, $id);

        if (function_exists('registrar_auditoria')) {
          registrar_auditoria(
            'ACTUALIZAR_CATALOGO',
            $table,
            $id,
            "Actualización en catálogo {$cfg['title']}: '{$nombre}'",
            null,
            null,
            $itemAntes,
            $itemDespues
          );
        }

        return [
          'status'  => true,
          'message' => "Registro '{$nombre}' actualizado correctamente.",
          'id'      => $id,
          'item'    => $itemDespues
        ];
      } else {
        // INSERCIÓN: siempre se crea activo (status = 1)
        $fieldsToSave['status'] = 1;

        $cols = array_keys($fieldsToSave);
        $colPlaceholders = array_map(fn($c) => ":{$c}", $cols);

        $sql = "INSERT INTO `{$table}` (`" . implode("`, `", $cols) . "`, `created_at`) VALUES (" . implode(", ", $colPlaceholders) . ", NOW())";
        $paramsInsert = [];
        foreach ($fieldsToSave as $f => $val) {
          $paramsInsert[":{$f}"] = $val;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($paramsInsert);
        $newId = (int)$db->lastInsertId();

        $itemNuevo = self::getItemById($slug, $newId);

        if (function_exists('registrar_auditoria')) {
          registrar_auditoria(
            'CREAR_CATALOGO',
            $table,
            $newId,
            "Alta en catálogo {$cfg['title']}: '{$nombre}'",
            null,
            null,
            null,
            $itemNuevo
          );
        }

        return [
          'status'  => true,
          'message' => "Registro '{$nombre}' agregado exitosamente al catálogo.",
          'id'      => $newId,
          'item'    => $itemNuevo
        ];
      }
    } catch (Throwable $e) {
      return ['status' => false, 'message' => 'Error al guardar el registro: ' . $e->getMessage()];
    }
  }

  /**
   * Elimina lógicamente un elemento del catálogo (pasa status de 1 a 0, nada se elimina)
   */
  public static function eliminar(string $slug, int $id): array
  {
    $cfg = self::getCatalogoConfig($slug);
    if (!$cfg) {
      return ['status' => false, 'message' => 'Catálogo no válido.'];
    }

    $item = self::getItemById($slug, $id);
    if (!$item) {
      return ['status' => false, 'message' => 'El registro a eliminar no existe.'];
    }

    $table = $cfg['table'];
    $pk = $cfg['pk'];
    $nameField = $cfg['name_field'];
    $nombre = $item[$nameField];

    try {
      $db = Db::connect();
      // BORRADO LÓGICO: status pasa de 1 a 0 (nada se elimina físicamente)
      $stmt = $db->prepare("UPDATE `{$table}` SET `status` = 0, `updated_at` = NOW() WHERE `{$pk}` = :id");
      $stmt->execute([':id' => $id]);

      $itemActualizado = self::getItemById($slug, $id);

      if (function_exists('registrar_auditoria')) {
        registrar_auditoria(
          'ELIMINAR_CATALOGO',
          $table,
          $id,
          "Baja lógica (status 0) de '{$nombre}' del catálogo {$cfg['title']}",
          null,
          null,
          $item,
          $itemActualizado
        );
      }

      return [
        'status'  => true,
        'message' => "Registro '{$nombre}' eliminado del catálogo correctamente (baja lógica)."
      ];
    } catch (Throwable $e) {
      return ['status' => false, 'message' => 'Error al eliminar registro: ' . $e->getMessage()];
    }
  }

  /**
   * Restaura un elemento eliminado lógicamente (pasa status de 0 a 1)
   */
  public static function restaurar(string $slug, int $id): array
  {
    $cfg = self::getCatalogoConfig($slug);
    if (!$cfg) {
      return ['status' => false, 'message' => 'Catálogo no válido.'];
    }

    $item = self::getItemById($slug, $id);
    if (!$item) {
      return ['status' => false, 'message' => 'El registro a restaurar no existe.'];
    }

    $table = $cfg['table'];
    $pk = $cfg['pk'];
    $nameField = $cfg['name_field'];
    $nombre = $item[$nameField];

    try {
      $db = Db::connect();
      // RESTAURACIÓN LÓGICA: status pasa de 0 a 1
      $stmt = $db->prepare("UPDATE `{$table}` SET `status` = 1, `updated_at` = NOW() WHERE `{$pk}` = :id");
      $stmt->execute([':id' => $id]);

      $itemActualizado = self::getItemById($slug, $id);

      if (function_exists('registrar_auditoria')) {
        registrar_auditoria(
          'RESTAURAR_CATALOGO',
          $table,
          $id,
          "Restauración a status activo (1) de '{$nombre}' en el catálogo {$cfg['title']}",
          null,
          null,
          $item,
          $itemActualizado
        );
      }

      return [
        'status'  => true,
        'message' => "Registro '{$nombre}' restaurado exitosamente en el catálogo."
      ];
    } catch (Throwable $e) {
      return ['status' => false, 'message' => 'Error al restaurar registro: ' . $e->getMessage()];
    }
  }

  /**
   * Verifica cuántas referencias activas existen en piezas, usuarios o movimientos
   */
  private static function contarUsoRelacional(string $slug, int $id, string $nombre): int
  {
    $db = Db::connect();
    $total = 0;

    switch ($slug) {
      case 'museos':
        // Usuarios asignados
        $stmt = $db->prepare("SELECT COUNT(*) FROM bee_users WHERE id_museo = :id");
        $stmt->execute([':id' => $id]);
        $total += (int)$stmt->fetchColumn();

        // Préstamos y transferencias
        $stmt = $db->prepare("SELECT COUNT(*) FROM prestamos_transferencias WHERE id_museo_origen = :id1 OR id_museo_destino = :id2");
        $stmt->execute([':id1' => $id, ':id2' => $id]);
        $total += (int)$stmt->fetchColumn();

        // Ubicación en piezas
        foreach (['acervo_general', 'acervo_arqueologico', 'acervo_numismatica'] as $tbl) {
          $stmt = $db->prepare("SELECT COUNT(*) FROM `{$tbl}` WHERE ubicacion_fisica = :nombre");
          $stmt->execute([':nombre' => $nombre]);
          $total += (int)$stmt->fetchColumn();
        }
        break;

      case 'materiales':
        foreach (['acervo_general', 'acervo_numismatica', 'acervo_arqueologico'] as $tbl) {
          $stmt = $db->prepare("SELECT COUNT(*) FROM `{$tbl}` WHERE material LIKE :like");
          $stmt->execute([':like' => '%' . $nombre . '%']);
          $total += (int)$stmt->fetchColumn();
        }
        break;

      case 'tecnicas':
        foreach (['acervo_general', 'acervo_numismatica'] as $tbl) {
          $stmt = $db->prepare("SELECT COUNT(*) FROM `{$tbl}` WHERE tecnica LIKE :like");
          $stmt->execute([':like' => '%' . $nombre . '%']);
          $total += (int)$stmt->fetchColumn();
        }
        $stmt = $db->prepare("SELECT COUNT(*) FROM acervo_arqueologico WHERE tecnica_manufactura LIKE :like1 OR tecnica_decorativa LIKE :like2");
        $stmt->execute([':like1' => '%' . $nombre . '%', ':like2' => '%' . $nombre . '%']);
        $total += (int)$stmt->fetchColumn();
        break;

      case 'epocas_periodos':
        foreach (['acervo_general', 'acervo_arqueologico', 'acervo_numismatica'] as $tbl) {
          $stmt = $db->prepare("SELECT COUNT(*) FROM `{$tbl}` WHERE epoca = :nombre");
          $stmt->execute([':nombre' => $nombre]);
          $total += (int)$stmt->fetchColumn();
        }
        break;

      case 'tipo_obras':
        $stmt = $db->prepare("SELECT COUNT(*) FROM acervo_general WHERE tipo_obra = :nombre");
        $stmt->execute([':nombre' => $nombre]);
        $total += (int)$stmt->fetchColumn();
        break;

      case 'estado_conservacion':
        foreach (['acervo_general', 'acervo_arqueologico', 'acervo_numismatica'] as $tbl) {
          $stmt = $db->prepare("SELECT COUNT(*) FROM `{$tbl}` WHERE estado_conservacion = :nombre");
          $stmt->execute([':nombre' => $nombre]);
          $total += (int)$stmt->fetchColumn();
        }
        break;

      case 'colecciones':
        $stmt = $db->prepare("SELECT COUNT(*) FROM prestamos_transferencias WHERE coleccion = :nombre");
        $stmt->execute([':nombre' => $nombre]);
        $total += (int)$stmt->fetchColumn();
        break;

      case 'autor_cultura':
        foreach (['acervo_general', 'acervo_numismatica'] as $tbl) {
          $stmt = $db->prepare("SELECT COUNT(*) FROM `{$tbl}` WHERE autor = :nombre");
          $stmt->execute([':nombre' => $nombre]);
          $total += (int)$stmt->fetchColumn();
        }
        $stmt = $db->prepare("SELECT COUNT(*) FROM acervo_arqueologico WHERE cultura = :nombre");
        $stmt->execute([':nombre' => $nombre]);
        $total += (int)$stmt->fetchColumn();
        break;
    }

    return $total;
  }
}
