<?php

/**
 * Clase para crear sesiones seguras de usuarios
 */
class Auth
{
  /**
   * El nombre de la variable de sesión o de la clave en si
   *
   * @var string
   */
  private $var    = 'user_session';

  /**
   * Determina si el usuario está loggueado o no
   *
   * @var boolean
   */
  private $logged = false;

  /**
   * El token de acceso del usuario en curso
   *
   * @var string
   */
  private $token  = null;

  /**
   * El ID del usuario en curso
   *
   * @var mixed
   */
  private $id     = null;

  /**
   * El session_id del usuario en curso
   *
   * @var string
   */
  private $ssid   = null;

  /**
   * Toda la información registrada del usuario
   *
   * @var array
   */
  private $user   = [];

  public function __construct()
  {
    if (isset($_SESSION[$this->var])) {
      return $this;
    }

    $session =
      [
        'logged' => $this->logged,
        'token'  => $this->token,
        'id'     => $this->id,
        'ssid'   => $this->ssid,
        'user'   => $this->user
      ];

    $_SESSION[$this->var] = $session;
    return $this;
  }

  /**
   * Crea la sesión de un usuario
   *
   * @param mixed $user_id
   * @param array $user_data
   * @return bool
   */
  public static function login(mixed $user_id, array $user_data = [])
  {
    $self         = new self();
    $self->logged = true;
    $session      =
      [
        'logged'        => $self->logged,
        'token'         => generate_token(),
        'id'            => $user_id,
        'ssid'          => session_id(),
        'user'          => $user_data,
        'last_activity' => time() // ⏱️ Guardamos el tiempo actual
      ];

    $_SESSION[$self->var] = $session;
    return true;
  }


  /**
   * Realizar la validación de la sesión del usuario en curso
   *
   * @return bool
   */
  public static function validate()
  {
    $self = new self();

    if (!isset($_SESSION[$self->var])) {
      return false;
    }

    $session = $_SESSION[$self->var];

    // Verificar tiempo de inactividad (30 minutos = 1800 segundos por defecto)
    $timeout = defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 1800;

    if (isset($session['last_activity']) && (time() - $session['last_activity'] > $timeout)) {
      $userId = !empty($session['id']) ? (int)$session['id'] : null;

      // Registrar en auditoría el cierre por inactividad
      if (function_exists('registrar_auditoria') && class_exists('Model') && $userId) {
        try {
          registrar_auditoria('LOGOUT', 'bee_users', null, 'Cierre automático de sesión por inactividad (30 minutos)', null, $userId);
        } catch (Throwable $t) {}
      }

      // Destruir cookies persistentes en el cliente si existieran
      if (function_exists('destroy_cookie') && defined('BEE_COOKIE_ID')) {
        destroy_cookie(BEE_COOKIE_ID, BEE_COOKIE_PATH, BEE_COOKIE_DOMAIN);
        destroy_cookie(BEE_COOKIE_TOKEN, BEE_COOKIE_PATH, BEE_COOKIE_DOMAIN);
      }

      self::logout();

      // Detectar si la petición es asíncrona / AJAX / API
      $isAjax = (function_exists('is_ajax') && is_ajax())
        || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
        || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);

      if ($isAjax) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode([
          'status' => 401,
          'msg' => 'Su sesión ha expirado por 30 minutos de inactividad. Por favor, inicie sesión nuevamente.',
          'expired' => true,
          'redirect' => 'login?timeout=1'
        ]);
        exit;
      }

      if (class_exists('Flasher')) {
        Flasher::new('Tu sesión se ha cerrado automáticamente después de 30 minutos de inactividad.', 'warning');
      }
      Redirect::to('login?timeout=1');
      return false;
    }

    // Actualizar tiempo de actividad si está logueado
    if (!empty($session['logged'])) {
      $_SESSION[$self->var]['last_activity'] = time();
    }

    return $session['logged'] === true &&
      $session['ssid'] === session_id() &&
      $session['token'] !== null;
  }


  /**
   * Cierra la sesión del usuario en curso
   *
   * @return bool
   */
  public static function logout()
  {
    // Verificar si la sesión está activa
    if (session_status() === PHP_SESSION_ACTIVE) {
      // Limpiar todas las variables de sesión
      $_SESSION = [];

      // Eliminar todas las variables de sesión registradas
      session_unset();

      // Destruir la sesión completamente
      session_destroy();
    }

    return true;
  }


  public function __get($var)
  {
    if (!isset($this->{$var})) return false;
    return $this->{$var};
  }
}
