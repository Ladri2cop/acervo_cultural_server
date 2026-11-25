<?php
// Funciones directamente del proyecto en curso

/**
 * Ejemplo para agregar endpoints autorizados para la API
 * Esto sólo es necesario si usarás más controladores a parte de apiController como endpoints de API
 * De lo contrario no requieres anexarlos a la lista de endpoints
 */
BeeHookManager::registerHook('init_set_up', 'setUpRoutes');

function setUpRoutes(Bee $instance)
{
  // Prueba ingresando a esta URL (depende de tu ubicación del proyecto): http://localhost:8848/Bee-Framework/reportes
  $instance->addEndpoint('reportes');
  $instance->addEndpoint('citas');
  $instance->addEndpoint('sucursales');

  $instance->addAjax('ajax2'); // http://localhost:8848/Bee-Framework/ajax2
}

/**
 * Obtiene la dirección MAC asociada al cliente o a una IP dada
 *
 * @param string|null $ip
 * @return string|null
 */
function get_user_mac(?string $ip = null): ?string
{
  $ip = $ip ?: get_user_ip();
  if (empty($ip) || $ip === 'UNKNOWN') {
    return null;
  }

  if (strpos($ip, ',') !== false) {
    $parts = explode(',', $ip);
    $ip = trim($parts[0]);
  }

  // Si la petición proviene de localhost o de la misma IP del servidor
  $serverIp = $_SERVER['SERVER_ADDR'] ?? null;
  if ($ip === '127.0.0.1' || $ip === '::1' || ($serverIp && $ip === $serverIp)) {
    foreach (glob('/sys/class/net/*/address') as $addrFile) {
      if (strpos($addrFile, '/lo/') !== false) continue;
      if (is_readable($addrFile)) {
        $mac = trim((string)@file_get_contents($addrFile));
        if (!empty($mac) && $mac !== '00:00:00:00:00:00' && preg_match('/^([0-9a-f]{2}[:-]){5}([0-9a-f]{2})$/i', $mac)) {
          return strtoupper($mac);
        }
      }
    }
  }

  // 1. Intentar consultar directamente la tabla ARP del kernel en /proc/net/arp
  if (is_readable('/proc/net/arp')) {
    $lines = @file('/proc/net/arp', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!empty($lines)) {
      foreach ($lines as $line) {
        $cols = preg_split('/\s+/', trim($line));
        if (isset($cols[0], $cols[3]) && $cols[0] === $ip) {
          $mac = trim($cols[3]);
          if (!empty($mac) && $mac !== '00:00:00:00:00:00' && preg_match('/^([0-9a-f]{2}[:-]){5}([0-9a-f]{2})$/i', $mac)) {
            return strtoupper($mac);
          }
        }
      }
    }
  }

  // 2. Si no se encontró en /proc/net/arp, intentar mediante comandos del sistema (ip neigh o arp)
  if (function_exists('shell_exec')) {
    $cleanIp = escapeshellarg($ip);
    $output = @shell_exec("ip neigh show {$cleanIp} 2>/dev/null");
    if (!empty($output) && preg_match('/lladdr\s+([0-9a-f]{2}(?::[0-9a-f]{2}){5})/i', $output, $matches)) {
      return strtoupper($matches[1]);
    }

    $outputArp = @shell_exec("arp -an {$cleanIp} 2>/dev/null");
    if (!empty($outputArp) && preg_match('/([0-9a-f]{2}(?::[0-9a-f]{2}){5})/i', $outputArp, $matches)) {
      return strtoupper($matches[1]);
    }
  }

  return null;
}

/**
 * Registra una acción en la tabla de auditoría del sistema
 *
 * @param string $tipo_accion Tipo de acción (LOGIN, LOGOUT, INSERT, UPDATE, DELETE, EXPORT, etc.)
 * @param string $nombre_tabla Tabla o sección involucrada
 * @param int|null $id_pieza ID de la pieza o registro afectado
 * @param string|null $observaciones Observaciones o detalles adicionales
 * @param int|null $id_modulo ID del módulo relacionado
 * @param int|null $id_usuario_explicito ID del usuario si aún no está en sesión (p. ej., durante LOGIN)
 * @param array|string|null $datos_antes Estado del objeto antes de la modificación
 * @param array|string|null $datos_despues Estado del objeto después de la modificación
 * @return bool
 */
function registrar_auditoria(string $tipo_accion, string $nombre_tabla, ?int $id_pieza = null, ?string $observaciones = null, ?int $id_modulo = null, ?int $id_usuario_explicito = null, $datos_antes = null, $datos_despues = null)
{
  try {
    $id_usuario = $id_usuario_explicito ?? (get_user('id') ? (int)get_user('id') : null);
    $username   = get_user('username') ? get_user('username') : (isset($_POST['usuario']) ? sanitize_input($_POST['usuario']) : 'Desconocido');
    $ip_address = get_user_ip();
    $mac_address = get_user_mac($ip_address);

    $json_antes   = is_array($datos_antes) ? json_encode($datos_antes, JSON_UNESCAPED_UNICODE) : $datos_antes;
    $json_despues = is_array($datos_despues) ? json_encode($datos_despues, JSON_UNESCAPED_UNICODE) : $datos_despues;

    $data = [
      'id_usuario'    => $id_usuario,
      'username'      => $username,
      'id_modulo'     => $id_modulo,
      'nombre_tabla'  => $nombre_tabla,
      'id_pieza'      => $id_pieza,
      'tipo_accion'   => $tipo_accion,
      'fecha_accion'  => now(),
      'observaciones' => $observaciones,
      'datos_antes'   => $json_antes,
      'datos_despues' => $json_despues,
      'ip_address'    => $ip_address,
      'mac_address'   => $mac_address
    ];

    return (class_exists('Model') && Model::add('auditoria', $data)) ? true : false;
  } catch (Throwable $e) {
    if (function_exists('logger')) {
      logger('Error al registrar auditoría: ' . $e->getMessage(), 'auditoria_err');
    }
    return false;
  }
}

/**
 * Determina si el usuario en sesión es administrador o super administrador
 *
 * @return bool
 */
function is_admin_user(): bool
{
  $userRole = get_user('id_role') ?: (isset($_SESSION['user_session']['user']['id_role']) ? $_SESSION['user_session']['user']['id_role'] : (isset($_SESSION['user']['id_role']) ? $_SESSION['user']['id_role'] : null));
  if (!$userRole) {
    $userId = get_user_id();
    if ($userId) {
      try {
        $db = Db::connect();
        $stmt = $db->prepare("SELECT id_role FROM bee_users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $userId]);
        $val = $stmt->fetchColumn();
        if ($val) $userRole = $val;
      } catch (Throwable $t) {}
    }
  }

  if (!$userRole) return false;

  if ((int)$userRole === 1 || (int)$userRole === 2) {
    return true;
  }

  try {
    $db = Db::connect();
    $stmt = $db->prepare("SELECT slug FROM bee_roles WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $userRole]);
    $roleSlug = $stmt->fetchColumn() ?: '';

    if (in_array($roleSlug, ['super-admin', 'super-administrador', 'admin', 'administrador'])) {
      return true;
    }

    $roleManager = new BeeRoleManager($roleSlug);
    return $roleManager->can('admin-access');
  } catch (Throwable $e) {
    return false;
  }
}

/**
 * Determina si el usuario en sesión es super administrador
 *
 * @return bool
 */
function is_super_admin(): bool
{
  $userRole = get_user('id_role') ?: (isset($_SESSION['user_session']['user']['id_role']) ? $_SESSION['user_session']['user']['id_role'] : (isset($_SESSION['user']['id_role']) ? $_SESSION['user']['id_role'] : null));
  if (!$userRole) {
    $userId = get_user_id();
    if ($userId) {
      try {
        $db = Db::connect();
        $stmt = $db->prepare("SELECT id_role FROM bee_users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $userId]);
        $val = $stmt->fetchColumn();
        if ($val) $userRole = $val;
      } catch (Throwable $t) {}
    }
  }

  if (!$userRole) return false;
  if ((int)$userRole === 1) return true;

  try {
    $db = Db::connect();
    $stmt = $db->prepare("SELECT slug FROM bee_roles WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $userRole]);
    $roleSlug = $stmt->fetchColumn() ?: '';
    return in_array($roleSlug, ['super-admin', 'super-administrador']);
  } catch (Throwable $e) {
    return false;
  }
}

/**
 * Obtiene el ID del museo asignado al usuario en sesión
 *
 * @return int|null
 */
function get_user_museum_id(): ?int
{
  $idMuseo = get_user('id_museo');
  if (!$idMuseo && isset($_SESSION['user_session']['user']['id_museo'])) {
    $idMuseo = $_SESSION['user_session']['user']['id_museo'];
  }
  if (!$idMuseo && isset($_SESSION['user']['id_museo'])) {
    $idMuseo = $_SESSION['user']['id_museo'];
  }

  // Si no está en sesión o es null, consultar en la base de datos para sincronizar
  if (empty($idMuseo)) {
    $userId = get_user_id();
    if ($userId) {
      try {
        $db = Db::connect();
        $stmt = $db->prepare("SELECT id_museo FROM bee_users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $userId]);
        $val = $stmt->fetchColumn();
        if ($val) {
          $idMuseo = (int)$val;
          if (isset($_SESSION['user_session']['user'])) {
            $_SESSION['user_session']['user']['id_museo'] = $idMuseo;
          }
          if (isset($_SESSION['user'])) {
            $_SESSION['user']['id_museo'] = $idMuseo;
          }
          if (isset($GLOBALS['Bee_User']) && is_array($GLOBALS['Bee_User'])) {
            $GLOBALS['Bee_User']['id_museo'] = $idMuseo;
          }
        }
      } catch (Throwable $t) {
        // Fallback silencioso
      }
    }
  }

  return !empty($idMuseo) ? (int)$idMuseo : null;
}

/**
 * Obtiene el nombre del museo asignado al usuario en sesión
 *
 * @return string|null
 */
function get_user_museum_name(): ?string
{
  $idMuseo = get_user_museum_id();
  if (!$idMuseo) return null;

  try {
    $db = Db::connect();
    $stmt = $db->prepare("SELECT nombre_museo FROM museos WHERE id_museo = :id LIMIT 1");
    $stmt->execute(['id' => $idMuseo]);
    $nombre = $stmt->fetchColumn();
    return $nombre ?: null;
  } catch (Throwable $t) {
    return null;
  }
}

/**
 * Determina si el usuario en sesión puede dictaminar (aceptar/declinar) un movimiento.
 * El Super Administrador tiene control global.
 * El Administrador debe estar asignado al museo de destino.
 *
 * @param array|object $movimiento
 * @return bool
 */
function user_can_dictate_movement($movimiento): bool
{
  if (is_super_admin()) {
    return true;
  }

  $userRole = get_user('id_role') ?: (isset($_SESSION['user_session']['user']['id_role']) ? $_SESSION['user_session']['user']['id_role'] : (isset($_SESSION['user']['id_role']) ? $_SESSION['user']['id_role'] : null));
  if ((int)$userRole !== 2) {
    return false;
  }

  $userMuseumId = get_user_museum_id();
  if (!$userMuseumId) {
    return false;
  }

  $idDestino = is_array($movimiento) ? ($movimiento['id_museo_destino'] ?? null) : ($movimiento->id_museo_destino ?? null);
  if ($idDestino && (int)$idDestino === (int)$userMuseumId) {
    return true;
  }

  $lugarDestino = is_array($movimiento) ? ($movimiento['lugar_destino'] ?? '') : ($movimiento->lugar_destino ?? '');
  $userMuseumName = get_user_museum_name();
  if (!empty($lugarDestino) && !empty($userMuseumName)) {
    if (mb_stripos(trim($lugarDestino), trim($userMuseumName)) !== false || mb_stripos(trim($userMuseumName), trim($lugarDestino)) !== false) {
      return true;
    }
  }

  return false;
}

/**
 * Determina si el rol del usuario tiene únicamente permisos de visualización
 *
 * @return bool
 */
function user_has_only_view_permissions(): bool
{
  if (is_admin_user()) {
    return false;
  }

  $userRole = get_user('id_role');
  if (!$userRole) return true;

  $roleRow = userModel::query("SELECT slug FROM bee_roles WHERE id = :id LIMIT 1", ['id' => $userRole]);
  $roleSlug = ($roleRow && isset($roleRow[0]['slug'])) ? $roleRow[0]['slug'] : '';
  if (empty($roleSlug)) return true;

  try {
    $roleManager = new BeeRoleManager($roleSlug);
    $permissions = $roleManager->getPermissions();
    if (empty($permissions)) return true;

    // Verificar si todos sus permisos son exclusivamente de visualización (empiezan con ver_)
    foreach ($permissions as $p) {
      $slug = $p['slug'] ?? '';
      if (strpos($slug, 'ver_') !== 0) {
        return false;
      }
    }

    return true;
  } catch (Exception $e) {
    return true;
  }
}

/**
 * Verifica si el usuario en sesión tiene un permiso específico
 *
 * @param string $permissionSlug
 * @return bool
 */
function user_can(string $permissionSlug): bool
{
  if (is_admin_user()) {
    return true;
  }

  $userRole = get_user('id_role');
  if (!$userRole) return false;

  $roleRow = userModel::query("SELECT slug FROM bee_roles WHERE id = :id LIMIT 1", ['id' => $userRole]);
  $roleSlug = ($roleRow && isset($roleRow[0]['slug'])) ? $roleRow[0]['slug'] : '';
  if (empty($roleSlug)) return false;

  try {
    $roleManager = new BeeRoleManager($roleSlug);
    return $roleManager->can($permissionSlug);
  } catch (Exception $e) {
    return false;
  }
}

/**
 * Determina si el usuario en sesión tiene permisos para registrar piezas
 *
 * @return bool
 */
function user_can_register(): bool
{
  if (is_admin_user()) {
    return true;
  }

  if (user_has_only_view_permissions()) {
    return false;
  }

  return user_can('registrar_acervo_general')
    || user_can('registrar_acervo_arqueologico')
    || user_can('registrar_acervo_numismatica');
}

/**
 * Determina si el usuario en sesión tiene permisos para visualizar al menos un tipo de acervo
 *
 * @return bool
 */
function user_can_view_acervo(): bool
{
  if (is_admin_user()) {
    return true;
  }

  return user_can('ver_acervo_general')
    || user_can('ver_acervo_arqueologico')
    || user_can('ver_acervo_numismatica');
}

/**
 * Determina si el usuario en sesión tiene permisos para editar un tipo de acervo específico o al menos uno
 *
 * @param string|null $tipo 'general', 'arqueologico', 'numismatica' o null
 * @return bool
 */
function user_can_edit_acervo(?string $tipo = null): bool
{
  if (is_admin_user()) {
    return true;
  }

  if ($tipo !== null) {
    return user_can('editar_acervo_' . $tipo);
  }

  return user_can('editar_acervo_general')
    || user_can('editar_acervo_arqueologico')
    || user_can('editar_acervo_numismatica');
}

/**
 * Determina si el usuario en sesión tiene permisos para eliminar piezas de un tipo de acervo específico o al menos uno
 *
 * @param string|null $tipo 'general', 'arqueologico', 'numismatica' o null
 * @return bool
 */
function user_can_delete_acervo(?string $tipo = null): bool
{
  if (is_admin_user()) {
    return true;
  }

  if ($tipo !== null) {
    return user_can('eliminar_acervo_' . $tipo);
  }

  return user_can('eliminar_acervo_general')
    || user_can('eliminar_acervo_arqueologico')
    || user_can('eliminar_acervo_numismatica');
}

/**
 * Obtiene el ID del usuario en sesión actual
 *
 * @return int|null
 */
function get_user_id(): ?int
{
  $id = get_user('id');
  if ($id) return (int)$id;

  if (isset($_SESSION['user_session']['user']['id'])) {
    return (int)$_SESSION['user_session']['user']['id'];
  }
  if (isset($_SESSION['user']['id'])) {
    return (int)$_SESSION['user']['id'];
  }

  return null;
}

// Carga de catálogos oficiales del SIPAC
require_once __DIR__ . '/acervo_catalogos.php';

/**
 * Procesa y valida de manera segura la carga de fotografías patrimoniales
 * Blindaje contra RCE, path traversal y sobreescrituras accidentales
 *
 * @param array $file Arreglo $_FILES['fotografia']
 * @param string $destDir Directorio de destino absoluto
 * @return string Nombre del archivo sanitizado y resguardado
 * @throws Exception En caso de formato inválido, exceso de tamaño o falla de guardado
 */
function procesar_fotografia_segura(array $file, string $destDir = UPLOADS): string
{
  if (!isset($file['error']) || is_array($file['error'])) {
    throw new Exception('Parámetros de subida de archivo inválidos.');
  }

  if ($file['error'] !== UPLOAD_ERR_OK) {
    throw new Exception('Error en el archivo subido (Código: ' . $file['error'] . ').');
  }

  // 1. Limitar tamaño a 15 MB
  $maxBytes = 15 * 1024 * 1024;
  if ($file['size'] > $maxBytes) {
    throw new Exception('La fotografía excede el tamaño máximo permitido de 15 MB.');
  }

  // 2. Validación de extensión en lista blanca estricta
  $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
  $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  if (!in_array($extension, $allowedExtensions, true)) {
    throw new Exception('Formato de imagen no permitido. Se aceptan exclusivamente JPG, PNG y WEBP.');
  }

  // 3. Validación estricta de encabezados binarios reales (MIME Magic Bytes)
  if (class_exists('finfo')) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $realMime = $finfo->file($file['tmp_name']);
    $allowedMimes = [
      'image/jpeg' => ['jpg', 'jpeg'],
      'image/png'  => ['png'],
      'image/webp' => ['webp']
    ];

    if (!array_key_exists($realMime, $allowedMimes) || !in_array($extension, $allowedMimes[$realMime], true)) {
      throw new Exception('El contenido binario del archivo no coincide con una imagen válida permitida.');
    }
  }

  // 4. Nombre aleatorio criptográfico inmune a colisiones y path traversal
  $nuevoNombre = 'pieza_' . bin2hex(random_bytes(16)) . '_' . time() . '.' . $extension;
  $rutaFinal = rtrim($destDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $nuevoNombre;

  if (!move_uploaded_file($file['tmp_name'], $rutaFinal)) {
    throw new Exception('Fallo al resguardar la fotografía en el servidor.');
  }

  // Asegurar permisos de lectura sin ejecución (0644)
  @chmod($rutaFinal, 0644);

  return $nuevoNombre;
}

/**
 * Valida el token CSRF enviado vía POST, GET o cabecera HTTP
 *
 * @param string|null $token
 * @return bool
 */
function check_csrf(?string $token = null): bool
{
  $t = $token ?? ($_POST['csrf'] ?? ($_REQUEST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)));
  if (!$t) return false;
  return class_exists('Csrf') && Csrf::validate($t);
}


