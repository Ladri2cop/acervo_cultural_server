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

      if (persistent_session() === false) {
        $user =
          [
            'id'       => 123,
            'name'     => 'Bee Default',
            'email'    => 'hellow@joystick.com.mx',
            'avatar'   => 'myavatar.jpg',
            'tel'      => '11223344',
            'color'    => '#112233',
            'username' => 'bee',
            'password' => '$2y$10$xHEI5cJ3q7rBJaL.M9qBRe909ahHvIZVTfRRxlLqfnWwAYwWQE/Wu' // 123456
          ];

        if ($usuario !== $user['username'] || !password_verify($password . AUTH_SALT, $user['password'])) {
          throw new Exception('Las credenciales no son correctas.');
        }

        Auth::login($user['id'], $user);
      } else {
        if (!$user = Model::list(BEE_USERS_TABLE, ['username' => $usuario], 1)) {
          throw new Exception('Las credenciales no son correctas.');
        }

        if (!password_verify($password . AUTH_SALT, $user['password'])) {
          throw new Exception('Las credenciales no son correctas.');
        }

        BeeSession::new_session($user['id']);
        $user = Model::list(BEE_USERS_TABLE, ['id' => $user['id']], 1);
        Auth::login($user['id'], $user);
      }

      Redirect::to('admin');
    } catch (Exception $e) {
      Flasher::error($e->getMessage());
      Redirect::back();
    }
  }
}