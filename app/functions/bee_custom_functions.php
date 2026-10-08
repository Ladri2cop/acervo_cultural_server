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
      'ip_address'    => $ip_address
    ];

    return Model::add('auditoria', $data) ? true : false;
  } catch (Exception $e) {
    logger('Error al registrar auditoría: ' . $e->getMessage(), 'auditoria_err');
    return false;
  }
}