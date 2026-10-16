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

    return Model::add('auditoria', $data) ? true : false;
  } catch (Exception $e) {
    logger('Error al registrar auditoría: ' . $e->getMessage(), 'auditoria_err');
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
  $userRole = get_user('id_role');
  if (!$userRole) return false;

  if ((int)$userRole === 1 || (int)$userRole === 2) {
    return true;
  }

  $roleRow = userModel::query("SELECT slug FROM bee_roles WHERE id = :id LIMIT 1", ['id' => $userRole]);
  $roleSlug = ($roleRow && isset($roleRow[0]['slug'])) ? $roleRow[0]['slug'] : '';

  if (in_array($roleSlug, ['super-admin', 'super-administrador', 'admin', 'administrador'])) {
    return true;
  }

  try {
    $roleManager = new BeeRoleManager($roleSlug);
    return $roleManager->can('admin-access');
  } catch (Exception $e) {
    return false;
  }
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