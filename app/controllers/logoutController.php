<?php 

class logoutController extends Controller implements ControllerInterface
{
  function __construct()
  {
    // Validar la sesión del usuario
    if (!Auth::validate()) {
      Flasher::new('No hay una sesión iniciada, no podemos cerrarla.', 'danger');
      Redirect::to('login');
    }

    // Ejecutar la funcionalidad del Controller padre
    parent::__construct();
  }

  function index()
  {
    // Registrar en auditoría antes de destruir sesión
    registrar_auditoria('LOGOUT', 'bee_users', null, 'Cierre de sesión del usuario', null, (int)get_user('id'));

    // Limpiar cookies de sesión en el cliente si existen
    if (function_exists('destroy_cookie') && defined('BEE_COOKIE_ID')) {
      destroy_cookie(BEE_COOKIE_ID, BEE_COOKIE_PATH, BEE_COOKIE_DOMAIN);
      destroy_cookie(BEE_COOKIE_TOKEN, BEE_COOKIE_PATH, BEE_COOKIE_DOMAIN);
    }

    // Borrar toda la información de $_SESSION
    Auth::logout();
    Redirect::to('login');
  }
}