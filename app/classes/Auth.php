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

    // Verificar tiempo de inactividad (10 minutos)
    if (isset($session['last_activity']) && (time() - $session['last_activity'] > 600)) {
      self::logout();
      Redirect::to('login');
      return false;
    }

    // Actualizar tiempo de actividad
    $_SESSION[$self->var]['last_activity'] = time();

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
