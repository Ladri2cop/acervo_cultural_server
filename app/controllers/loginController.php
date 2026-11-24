<?php

class loginController extends Controller implements ControllerInterface
{
  function __construct()
  {
    // Verificar si el usuario ya tiene sesión activa
    if (Auth::validate()) {
      // Evitar redirección infinita si ya está en la página de login
      $current_uri = $_SERVER['REQUEST_URI'] ?? '';

      // Si no está en la ruta de login, redirigir al perfil
      if (strpos($current_uri, '/login') === false) {
        Flasher::new('Ya hay una sesión abierta.');
        Redirect::to('admin/perfil');
      }
    }

    // Ejecutar la funcionalidad del Controller padre
    parent::__construct();
  }

  function index()
  {
    if (isset($_GET['timeout']) && empty($_SESSION['flashes'])) {
      Flasher::new('Tu sesión se ha cerrado automáticamente después de 30 minutos de inactividad por seguridad. Por favor, vuelve a iniciar sesión.', 'warning');
    }

    $this->setTitle('BIENVENIDO');
    $this->setView('login');
    $this->render();
  }

  function post_login()
  {
    try {
      if (!Csrf::validate($_POST['csrf']) || !check_posted_data(['usuario', 'csrf', 'password'], $_POST)) {
        throw new Exception(get_bee_message(0));
      }

      $usuario  = sanitize_input($_POST['usuario']);
      $password = sanitize_input($_POST['password']);

      if (!$user = Model::list(BEE_USERS_TABLE, ['username' => $usuario], 1)) {
        throw new Exception('Las credenciales no son correctas.');
      }

      if (!password_verify($password . AUTH_SALT, $user['password'])) {
        throw new Exception('Las credenciales no son correctas.');
      }

      // Eliminar cookies persistentes obsoletas si existieran
      if (function_exists('destroy_cookie') && defined('BEE_COOKIE_ID')) {
        destroy_cookie(BEE_COOKIE_ID, BEE_COOKIE_PATH, BEE_COOKIE_DOMAIN);
        destroy_cookie(BEE_COOKIE_TOKEN, BEE_COOKIE_PATH, BEE_COOKIE_DOMAIN);
      }

      Auth::login($user['id'], $user);

      registrar_auditoria('LOGIN', 'bee_users', null, 'Inicio de sesión exitoso', null, (int)$user['id']);

      if (is_admin_user()) {
        Redirect::to('admin');
      } else {
        Redirect::to('admin/acervo');
      }
    } catch (Exception $e) {
      Flasher::error($e->getMessage());
      Redirect::back();
    }
  }
}