<?php

use Cocur\Slugify\Slugify;

/**
 * Plantilla general de controladores
 * @version 1.0.5
 *
 * Controlador de admin
 */
class adminController extends Controller implements ControllerInterface
{
  function __construct()
  {
    // Validación de sesión de usuario
    if (!Auth::validate()) {
      Flasher::new('Debes iniciar sesión primero.', 'danger');
      exit; // 👈 Detiene la ejecución inmediatamente
    }

    // Ejecutar la funcionalidad del Controller padre
    parent::__construct();
  }


  function index()
  {
    register_scripts([JS . 'admin/demo.js'], 'Chartjs gráficas para administración');

    $this->setTitle('Administración');
    $buttons =
      [
        [
          'url'   => 'admin',
          'class' => 'btn-danger text-white',
          'id'    => '',
          'icon'  => 'fas fa-download',
          'text'  => 'Descargar'
        ],
        [
          'url'   => 'admin',
          'class' => 'btn-success text-white',
          'id'    => '',
          'icon'  => 'fas fa-file-pdf',
          'text'  => 'Exportar'
        ]
      ];
    $this->addToData('buttons', $buttons);
    $this->render();
  }

  function perfil()
  {
    $this->setTitle('Perfil de usuario');
    $this->setView('perfil');
    $this->render();
  }

  function botones()
  {
    $this->setTitle('Botones');
    $this->setView('botones');
    $this->render();
  }

  function cartas()
  {
    $this->setTitle('Cartas');
    $this->setView('cartas');
    $this->render();
  }

  ////////////////////////////////////////////////////
  ////////////////////////////////////////////////////
  ////////////////////////////////////////////////////
  //////// USUARIOS
  ////////////////////////////////////////////////////
  ////////////////////////////////////////////////////
  ////////////////////////////////////////////////////
  function usuarios()
  {
    $this->setTitle('Usuarios');
    $this->addToData('users', userModel::all_paginated());
    $this->addToData('slug', 'usuarios');
    $this->setView('usuarios/usuarios');
    $this->render();
  }

  function post_usuarios()
  {
    try {
      if (!check_posted_data(['username', 'email', 'password'], $_POST)) {
        throw new Exception('Por favor completa el formulario.');
      }

      if (!Csrf::validate($_POST['csrf'])) {
        throw new Exception(get_bee_message(0));
      }

      // Definición de variables
      array_map('sanitize_input', $_POST);
      $username     = $_POST['username'];
      $email        = $_POST['email'];
      $password     = $_POST['password'];
      $errorMessage = '';
      $errors       = 0;

      // Verificar que no exista ya un usuario con ese username o correo electrónico
      $sql = 'SELECT * FROM bee_users WHERE username = :username OR email = :email';
      if (userModel::query($sql, ['username' => $username, 'email' => $email])) {
        throw new Exception('Ya existe un usuario registrado con ese nombre de usuario o correo electrónico.');
      }

      // Validaciones necesarias
      if (!preg_match('/^[a-zA-Z0-9]{5,20}$/', $username)) {
        $errorMessage .= '- Tu nombre de usuario debe estar formado por mínimo 5 caracteres y máximo 20.<br>';
        $errors++;
      }

      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage .= '- El correo electrónico no es válido.<br>';
        $errors++;
      }

      if (is_temporary_email($email)) {
        $errorMessage .= '- El dominio del correo electrónico no está autorizado.<br>';
        $errors++;
      }

      if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*_-])[A-Za-z\d!@#$%^&*_-]{5,20}$/', $password)) {
        $errorMessage .= '- La contraseña debe ser de entre 5 y 20 caracteres, por lo menos debe contar con: 1 letra minúscula, 1 letra mayúscula, 1 digito y 1 caracter especial de entre <b>!@#$%^&*_-</b>';
        $errors++;
      }

      if ($errors > 0) {
        throw new Exception($errorMessage);
      }

      // Agregar el nuevo usuario a la base de datos
      $user     =
        [
          'username'   => $username,
          'email'      => $email,
          'password'   => password_hash($password . AUTH_SALT, PASSWORD_BCRYPT),
          'created_at' => now()
        ];

      // Insertando el registro en la base de datos
      if (!$id = userModel::add(userModel::$t1, $user)) {
        throw new Exception('Hubo un problema al agregar el usuario.');
      }

      Flasher::success(sprintf('Nuevo usuario agregado con éxito:<br>Usuario: <b>%s</b><br>Contraseña: <b>%s</b>', $user['username'], $password));
      Redirect::back();
    } catch (Exception $e) {
      Flasher::error($e->getMessage());
      Redirect::back();
    }
  }

  function borrar_usuario($id = null)
  {
    try {
      if (!Csrf::validate($_GET['_t'])) {
        throw new Exception(get_bee_message(0));
      }

      // Verificar que exista el usuario
      if (!$user = userModel::by_id($id)) {
        throw new Exception('No existe el usuario en la base de datos.');
      }

      // Validar que no sea el propio usuario que está solicitando la petición
      if ($id == get_user('id')) {
        throw new Exception('No puedes realizar esta acción sobre ti mismo.');
      }

      // Borrando el registro de la base de datos
      if (!userModel::remove(userModel::$t1, ['id' => $id], 1)) {
        throw new Exception('Hubo un problema al borrar el usuario.');
      }

      Flasher::success(sprintf('Usuario <b>%s</b> borrado con éxito.', $user['username']));
      Redirect::back();
    } catch (Exception $e) {
      Flasher::error($e->getMessage());
      Redirect::back();
    }
  }

  function destruir_sesion($id = null)
  {
    try {
      if (!Csrf::validate($_GET['_t'])) {
        throw new Exception(get_bee_message(0));
      }

      // Verificar que exista el usuario
      if (!$user = userModel::by_id($id)) {
        throw new Exception('No existe el usuario en la base de datos.');
      }

      // Validar que no sea el propio usuario que está solicitando la petición
      if ($id == get_user('id')) {
        throw new Exception('No puedes realizar esta acción sobre ti mismo.');
      }

      // Verificar que el usuario tenga una sesión activa
      if (empty($user['auth_token']) || $user['auth_token'] == null) {
        throw new Exception('El usuario no tiene una sesión activa.');
      }

      // Cerrando su sesión
      if (!userModel::update(userModel::$t1, ['id' => $id], ['auth_token' => null])) {
        throw new Exception('Hubo un problema al actualizar el usuario.');
      }

      Flasher::success(sprintf('La sesión de <b>%s</b> ha sido cerrada con éxito.', $user['username']));
      Redirect::back();
    } catch (Exception $e) {
      Flasher::error($e->getMessage());
      Redirect::back();
    }
  }

  ////////////////////////////////////////////////////
  ////////////////////////////////////////////////////
  ////////////////////////////////////////////////////
  //////// ACERVO
  ////////////////////////////////////////////////////
  ////////////////////////////////////////////////////
  ////////////////////////////////////////////////////
  function acervo()
  {
    $this->setTitle('Acervo');
    $this->addToData('acervo', acervoModel::all_paginated());
    $this->addToData('slug', 'acervo');
    $this->setView('acervo/acervo');
    $this->render();
  }

  function post_acervo()
  {
    try {
      if (!check_posted_data(['nombre', 'sku', 'descripcion', 'precio', 'precio_comparacion', 'stock'], $_POST)) {
        throw new Exception('Por favor completa el formulario.');
      }

      if (!Csrf::validate($_POST['csrf'])) {
        throw new Exception(get_bee_message(0));
      }

      // Definición de variables
      array_map('sanitize_input', $_POST);
      $nombre             = $_POST['nombre'];
      $sku                = $_POST["sku"];
      $descripcion        = $_POST["descripcion"];
      $precio             = (float) $_POST["precio"];
      $precio_comparacion = (float) $_POST["precio_comparacion"];
      $rastrear_stock     = isset($_POST["rastrear_stock"]) ? 1 : 0;
      $stock              = (int) $_POST["stock"];
      $imagen             = $_FILES["imagen"];
      $errorMessage       = '';
      $errors             = 0;

      // Crear slug con base al nombre del producto
      $slugify = new Slugify();
      $slug    = $slugify->slugify($nombre);

      // Verificar que no exista ya un producto con el sku si es que no está vacío
      $sql = 'SELECT * FROM productos WHERE sku = :sku OR nombre = :nombre OR slug = :slug';
      if (acervoModel::query($sql, ['sku' => $sku, 'nombre' => $nombre, 'slug' => $slug])) {
        throw new Exception('Ya existe un producto registrado con el mismo SKU o nombre.');
      }

      // Validar longitud del nombre, no mayor a 150 caracteres
      if (strlen($nombre) > 150) {
        $errorMessage .= '- El nombre del producto debe ser menor a 150 caracteres.' . PHP_EOL;
        $errors++;
      }

      // Validar el precio regular del producto
      if ($precio == 0) {
        $errorMessage .= '- Ingresa un precio mayor a 0.' . PHP_EOL;
        $errors++;
      }

      // Validar el precio de comparación si no es igual a 0
      if ($precio_comparacion != 0 && $precio_comparacion < $precio) {
        $errorMessage .= '- El precio de comparación debe ser mayor al precio principal del producto.' . PHP_EOL;
        $errors++;
      }

      // Validación de la imagen
      if ($imagen['error'] !== 0) {
        $errorMessage .= '- Selecciona una imagen de producto válida por favor.' . PHP_EOL;
        $errors++;
      }

      // Procesar imagen
      $tmp_name = $imagen['tmp_name'];
      $filename = $imagen['name'];
      $type     = $imagen['type'];
      $ext      = pathinfo($filename, PATHINFO_EXTENSION);
      $new_name = generate_filename() . '.' . $ext;

      if (!move_uploaded_file($tmp_name, UPLOADS . $new_name)) {
        $errorMessage .= '- Hubo un problema al subir el archivo de imagen.' . PHP_EOL;
        $errors++;
      }

      if ($errors > 0) {
        if (is_file(UPLOADS . $new_name)) {
          unlink(UPLOADS . $new_name);
        }
        throw new Exception($errorMessage);
      }

      // Array de información del producto
      $data =
        [
          'nombre'             => $nombre,
          'slug'               => $slug,
          'sku'                => empty($sku) ? random_password(8, 'numeric') : $sku,
          'descripcion'        => $descripcion,
          'precio'             => $precio,
          'precio_comparacion' => $precio_comparacion,
          'rastrear_stock'     => $rastrear_stock,
          'stock'              => empty($stock) ? 0 : $stock,
          'imagen'             => $new_name,
          'creado'             => now()
        ];

      // Agregar producto a la base de datos
      if (!$id = acervoModel::insertOne($data)) {
        throw new Exception('Hubo un error, intenta de nuevo.');
      }

      $producto = acervoModel::by_id($id);

      Flasher::success(sprintf('Nuevo producto <b>%s</b> agregado con éxito.', $producto['nombre']));
      Redirect::back();
    } catch (Exception $e) {
      Flasher::error($e->getMessage());
      Redirect::back();
    }
  }

  ////////////////////////////////////////////////////
  ////////////////////////////////////////////////////
  ////////////////////////////////////////////////////
  //////// REGISTROS
  ////////////////////////////////////////////////////
  ////////////////////////////////////////////////////
  ////////////////////////////////////////////////////
  function catalogos()
  {
    $this->setTitle('Catálogos');
    $this->addToData('catalogos', acervoModel::all_paginated());
    $this->addToData('slug', 'catalogos');
    $this->setView('catalogos/catalogos');
    $this->render();
  }

  function post_catalogos()
  {
    try {
      if (!check_posted_data(['nombre', 'sku', 'descripcion', 'precio', 'precio_comparacion', 'stock'], $_POST)) {
        throw new Exception('Por favor completa el formulario.');
      }

      if (!Csrf::validate($_POST['csrf'])) {
        throw new Exception(get_bee_message(0));
      }

      // Definición de variables
      array_map('sanitize_input', $_POST);
      $nombre             = $_POST['nombre'];
      $sku                = $_POST["sku"];
      $descripcion        = $_POST["descripcion"];
      $precio             = (float) $_POST["precio"];
      $precio_comparacion = (float) $_POST["precio_comparacion"];
      $rastrear_stock     = isset($_POST["rastrear_stock"]) ? 1 : 0;
      $stock              = (int) $_POST["stock"];
      $imagen             = $_FILES["imagen"];
      $errorMessage       = '';
      $errors             = 0;

      // Crear slug con base al nombre del producto
      $slugify = new Slugify();
      $slug    = $slugify->slugify($nombre);

      // Verificar que no exista ya un producto con el sku si es que no está vacío
      $sql = 'SELECT * FROM productos WHERE sku = :sku OR nombre = :nombre OR slug = :slug';
      if (acervoModel::query($sql, ['sku' => $sku, 'nombre' => $nombre, 'slug' => $slug])) {
        throw new Exception('Ya existe un producto registrado con el mismo SKU o nombre.');
      }

      // Validar longitud del nombre, no mayor a 150 caracteres
      if (strlen($nombre) > 150) {
        $errorMessage .= '- El nombre del producto debe ser menor a 150 caracteres.' . PHP_EOL;
        $errors++;
      }

      // Validar el precio regular del producto
      if ($precio == 0) {
        $errorMessage .= '- Ingresa un precio mayor a 0.' . PHP_EOL;
        $errors++;
      }

      // Validar el precio de comparación si no es igual a 0
      if ($precio_comparacion != 0 && $precio_comparacion < $precio) {
        $errorMessage .= '- El precio de comparación debe ser mayor al precio principal del producto.' . PHP_EOL;
        $errors++;
      }

      // Validación de la imagen
      if ($imagen['error'] !== 0) {
        $errorMessage .= '- Selecciona una imagen de producto válida por favor.' . PHP_EOL;
        $errors++;
      }

      // Processar imagen
      $tmp_name = $imagen['tmp_name'];
      $filename = $imagen['name'];
      $type     = $imagen['type'];
      $ext      = pathinfo($filename, PATHINFO_EXTENSION);
      $new_name = generate_filename() . '.' . $ext;

      if (!move_uploaded_file($tmp_name, UPLOADS . $new_name)) {
        $errorMessage .= '- Hubo un problema al subir el archivo de imagen.' . PHP_EOL;
        $errors++;
      }

      if ($errors > 0) {
        if (is_file(UPLOADS . $new_name)) {
          unlink(UPLOADS . $new_name);
        }
        throw new Exception($errorMessage);
      }

      // Array de información del producto
      $data =
        [
          'nombre'             => $nombre,
          'slug'               => $slug,
          'sku'                => empty($sku) ? random_password(8, 'numeric') : $sku,
          'descripcion'        => $descripcion,
          'precio'             => $precio,
          'precio_comparacion' => $precio_comparacion,
          'rastrear_stock'     => $rastrear_stock,
          'stock'              => empty($stock) ? 0 : $stock,
          'imagen'             => $new_name,
          'creado'             => now()
        ];

      // Agregar producto a la base de datos
      if (!$id = acervoModel::insertOne($data)) {
        throw new Exception('Hubo un error, intenta de nuevo.');
      }

      $producto = acervoModel::by_id($id);

      Flasher::success(sprintf('Nuevo producto <b>%s</b> agregado con éxito.', $producto['nombre']));
      Redirect::back();
    } catch (Exception $e) {
      Flasher::error($e->getMessage());
      Redirect::back();
    }
  }

  ////////////////////////////////////////////////////
  ////////////////////////////////////////////////////
  ////////////////////////////////////////////////////
  //////// FORMULARIOS
  ////////////////////////////////////////////////////
  ////////////////////////////////////////////////////
  ////////////////////////////////////////////////////
  function formularios()
  {
    $this->setTitle('Formularios');
    $this->setView('formularios/formularios');
    $this->render();
  }


  ////////////////////////////////////////////////////
  ////////////////////////////////////////////////////
  ////////////////////////////////////////////////////
  //////// REGISTRAR
  ////////////////////////////////////////////////////
  ////////////////////////////////////////////////////
  ////////////////////////////////////////////////////
  public function get_formulario_tipo()
  {
    $tipo = $_POST['tipo_acervo'] ?? '';
    $campos = [];
    $action = 'admin/post_registro';

    switch ($tipo) {
      case 1:
        $campos = obtenerCamposAcervoGeneral();
        $action = 'admin/post_registro';
        break;
      case 2:
        $campos = obtenerCamposAcervoArqueologico();
        $action = 'admin/post_registro_arq';
        break;
      case 3:
        $campos = obtenerCamposAcervoNumismatica();
        $action = 'admin/post_registro_numismatica';
        break;
    }

    // Si no se encontraron campos, responder con status false
    if (empty($campos)) {
      echo json_encode([
        'status' => false,
        'message' => 'No se encontró un formulario para el tipo de acervo seleccionado.'
      ]);
      return;
    }

    // Si hay campos, construir el formulario
    $form = new BeeFormBuilder('nuevo-registro', 'nuevo-registro', ['needs-validation'], $action, true, false);
    $form->addCustomFields(insert_inputs());
    $form->addCustomFields('
      <div class="col-12 mb-4 container-preview-image">
        <!-- Input oculto -->
        <input type="file" id="imageInput" accept="image/*" class="input-file" name="fotografia" required>

        <!-- Contenedor de vista previa -->
        <div class="card shadow-sm text-center p-4 border border-secondary rounded preview-clickable" id="previewContainer">
          <i class="bx bx-image fs-1 text-muted" id="previewIcon"></i>
          <span id="previewText" class="text-muted d-block mt-2">Haz clic para seleccionar una imagen</span>
        </div>
      </div>
    ');
    agregarCamposDinamicos($form, $campos);
    $form->addCustomFields('<div class="col-12"><hr class="my-4"></div>');
    $form->addCustomFields('
    <div class="d-flex justify-content-end gap-2 mt-4">
      <button type="reset" class="btn btn-secondary" id="cancel-button">Cancelar</button>
      <button type="submit" class="btn btn-primary" id="submit-button">Registrar pieza</button>
    </div>
  ');

    echo json_encode([
      'status' => true,
      'html' => $form->getFormHtml()
    ]);
  }

  function registrar()
  {
    $form = new BeeFormBuilder('nuevo-registro', 'nuevo-registro', ['needs-validation'], 'admin/post_registro', true, false);

    $campos = [
      ['type' => 'text', 'name' => 'codigo_interno', 'label' => 'Código interno', 'id' => 'codigo_interno', 'class' => 'form-control', 'required' => false, 'placeholder' => 'Ej: 001-AQ-2026', 'column_class' => 'col-12 col-md-6 mb-3'],
      ['type' => 'text', 'name' => 'no_inventario', 'label' => 'No. Inventario', 'id' => 'no_inventario', 'class' => 'form-control', 'required' => false, 'placeholder' => 'Ej: INV-1234', 'column_class' => 'col-12 col-md-6 mb-3'],
      ['type' => 'text', 'name' => 'nombre_titulo_pieza', 'label' => 'Nombre/Título de la pieza', 'id' => 'nombre_titulo_pieza', 'class' => 'form-control', 'required' => false, 'placeholder' => 'Ej: Jarrón de cerámica', 'column_class' => 'col-12 mb-3'],
      ['type' => 'text', 'name' => 'cm', 'label' => 'CM', 'id' => 'cm', 'class' => 'form-control', 'required' => false, 'placeholder' => 'Ej: 12.5', 'column_class' => 'col-12 col-md-6 mb-3'],
      ['type' => 'text', 'name' => 'fotografia', 'label' => 'Fotografía', 'id' => 'fotografia', 'class' => 'form-control', 'required' => false, 'placeholder' => 'URL o nombre de archivo', 'column_class' => 'col-12 col-md-6 mb-3'],
      ['type' => 'text', 'name' => 'autor', 'label' => 'Autor', 'id' => 'autor', 'class' => 'form-control', 'required' => false, 'placeholder' => 'Ej: Juan Pérez', 'column_class' => 'col-12 col-md-6 mb-3'],
      ['type' => 'text', 'name' => 'anio', 'label' => 'Año', 'id' => 'anio', 'class' => 'form-control', 'required' => false, 'placeholder' => 'Ej: 1980', 'column_class' => 'col-12 col-md-6 mb-3'],
      ['type' => 'text', 'name' => 'epoca', 'label' => 'Época', 'id' => 'epoca', 'class' => 'form-control', 'required' => false, 'placeholder' => 'Ej: Siglo XX', 'column_class' => 'col-12 col-md-6 mb-3'],
      ['type' => 'text', 'name' => 'tecnica', 'label' => 'Técnica', 'id' => 'tecnica', 'class' => 'form-control', 'required' => false, 'placeholder' => 'Ej: Acuarela', 'column_class' => 'col-12 col-md-6 mb-3'],
      ['type' => 'text', 'name' => 'material', 'label' => 'Material', 'id' => 'material', 'class' => 'form-control', 'required' => false, 'placeholder' => 'Ej: Cerámica', 'column_class' => 'col-12 col-md-6 mb-3'],
      ['type' => 'text', 'name' => 'medidas', 'label' => 'Medidas', 'id' => 'medidas', 'class' => 'form-control', 'required' => false, 'placeholder' => 'Ej: 20x15x10 cm', 'column_class' => 'col-12 col-md-6 mb-3'],
      ['type' => 'text', 'name' => 'lote', 'label' => 'Lote', 'id' => 'lote', 'class' => 'form-control', 'required' => false, 'placeholder' => 'Ej: L-2026-01', 'column_class' => 'col-12 col-md-6 mb-3'],
      ['type' => 'text', 'name' => 'peso', 'label' => 'Peso', 'id' => 'peso', 'class' => 'form-control', 'required' => false, 'placeholder' => 'Ej: 2.3 kg', 'column_class' => 'col-12 col-md-6 mb-3'],
      ['type' => 'text', 'name' => 'coleccion', 'label' => 'Colección', 'id' => 'coleccion', 'class' => 'form-control', 'required' => false, 'placeholder' => 'Ej: Colección privada', 'column_class' => 'col-12 col-md-6 mb-3'],
      ['type' => 'text', 'name' => 'tipo', 'label' => 'Tipo', 'id' => 'tipo', 'class' => 'form-control', 'required' => false, 'placeholder' => 'Ej: Escultura', 'column_class' => 'col-12 col-md-6 mb-3'],
      ['type' => 'text', 'name' => 'ubicacion_fisica', 'label' => 'Ubicación física', 'id' => 'ubicacion_fisica', 'class' => 'form-control', 'required' => false, 'placeholder' => 'Ej: Sala 2, vitrina 4', 'column_class' => 'col-12 col-md-6 mb-3'],
      ['type' => 'text', 'name' => 'estado_conservacion', 'label' => 'Estado de conservación', 'id' => 'estado_conservacion', 'class' => 'form-control', 'required' => false, 'placeholder' => 'Ej: Bueno', 'column_class' => 'col-12 col-md-6 mb-3'],
      ['type' => 'text', 'name' => 'observaciones', 'label' => 'Observaciones', 'id' => 'observaciones', 'class' => 'form-control', 'required' => false, 'placeholder' => 'Notas adicionales', 'column_class' => 'col-12 mb-3'],
      ['type' => 'textarea', 'name' => 'descripcion', 'label' => 'Descripción', 'id' => 'descripcion', 'class' => 'form-control', 'required' => false, 'placeholder' => 'Descripción detallada de la pieza', 'rows' => 4, 'cols' => 5, 'column_class' => 'col-12 mb-3']
    ];

    agregarCamposDinamicos($form, $campos);
    $form->addCustomFields('<div class="col-12"><hr class="my-4"></div>');

    $form->addCustomFields('
      <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="reset" class="btn btn-secondary" id="cancel-button">Cancelar</button>
        <button type="submit" class="btn btn-primary" id="submit-button">Registrar pieza</button>
      </div>
    ');

    // HTML del formulario
    $this->addToData('form', $form->getFormHtml());

    // Script de envío con fetch
    $script = $form->generateFetchScript('admin/post_registro');
    $this->addToData('script', $script);

    $this->setTitle('Registrar Acervo');
    $this->setView('registrar/registrar');
    $this->render();
  }

  function post_registro()
  {
    // Procesar y guardar los datos del formulario de acervoGeneral
    $data = [
      'codigo_interno'        => $_POST['codigo_interno'] ?? '',
      'no_inventario'         => $_POST['no_inventario'] ?? '',
      'nombre_titulo_pieza'   => $_POST['nombre_titulo_pieza'] ?? '',
      'cm'                    => $_POST['cm'] ?? '',
      'fotografia'            => '', // Se actualizará si se sube archivo
      'autor'                 => $_POST['autor'] ?? '',
      'anio'                  => $_POST['anio'] ?? '',
      'epoca'                 => $_POST['epoca'] ?? '',
      'tecnica'               => $_POST['tecnica'] ?? '',
      'material'              => $_POST['material'] ?? '',
      'medidas'               => $_POST['medidas'] ?? '',
      'lote'                  => $_POST['lote'] ?? '',
      'peso'                  => $_POST['peso'] ?? '',
      'coleccion'             => $_POST['coleccion'] ?? '',
      'tipo'                  => $_POST['tipo'] ?? '',
      'ubicacion_fisica'      => $_POST['ubicacion_fisica'] ?? '',
      'estado_conservacion'   => $_POST['estado_conservacion'] ?? '',
      'observaciones'         => $_POST['observaciones'] ?? '',
      'descripcion'           => $_POST['descripcion'] ?? '',
      'id_modulo'             => 5
    ];

    // Procesar imagen si se envió
    if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] === 0) {
      $tmp_name = $_FILES['fotografia']['tmp_name'];
      $filename = $_FILES['fotografia']['name'];
      $upload_path = UPLOADS . $filename;
      if (move_uploaded_file($tmp_name, $upload_path)) {
        $data['fotografia'] = $filename;
      } else {
        header('Content-Type: application/json');
        echo json_encode([
          'status' => 500,
          'msg' => 'Error al subir la imagen.'
        ]);
        exit;
      }
    }

    // Guardar en la base de datos usando el modelo AcervoGeneralModel
    require_once APP . 'models/acervoGeneralModel.php';
    $id = AcervoGeneralModel::addPieza($data);

    if ($id) {
      header('Content-Type: application/json');
      echo json_encode([
        'status' => 200,
        'msg' => 'Registro guardado correctamente',
        'id' => $id
      ]);
    } else {
      header('Content-Type: application/json');
      echo json_encode([
        'status' => 500,
        'msg' => 'Error al guardar el registro'
      ]);
    }
    exit;
  }

  function post_registro_arq()
  {
    // Procesar y guardar los datos del formulario de acervoGeneral
    $data = [
      'codigo_interno'          => $_POST['codigo_interno'] ?? '',
      'no_inventario_scyt'      => $_POST['no_inventario_scyt'] ?? '',
      'no_registro_inah'        => $_POST['no_registro_inah'] ?? '',
      'otros'                   => $_POST['otros'] ?? '',
      'nombre_titulo_pieza'     => $_POST['nombre_titulo_pieza'] ?? '',
      'fotografia'              => '', // Se actualizará si se sube archivo
      'numero_pieza_por_lote'   => $_POST['numero_pieza_por_lote'] ?? '',
      'epoca'                   => $_POST['epoca'] ?? '',
      'procedencia'             => $_POST['procedencia'] ?? '',
      'material'                => $_POST['material'] ?? '',
      'medidas'                 => $_POST['medidas'] ?? '',
      'forma'                   => $_POST['forma'] ?? '',
      'tecnica_manufactura'     => $_POST['tecnica_manufactura'] ?? '',
      'tecnica_decorativa'      => $_POST['tecnica_decorativa'] ?? '',
      'coleccion'               => $_POST['coleccion'] ?? '',
      'obtencion'               => $_POST['obtencion'] ?? '',
      'ubicacion_fisica'        => $_POST['ubicacion_fisica'] ?? '',
      'estado_conservacion'     => $_POST['estado_conservacion'] ?? '',
      'observaciones'           => $_POST['observaciones'] ?? '',
      'descripcion'             => $_POST['descripcion'] ?? '',
      'representacion'          => $_POST['representacion'] ?? '',
      'id_modulo'               => 3
    ];

    // Processar imagen si se envió
    if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] === 0) {
      $tmp_name = $_FILES['fotografia']['tmp_name'];
      $filename = $_FILES['fotografia']['name'];
      $upload_path = UPLOADS . $filename;
      if (move_uploaded_file($tmp_name, $upload_path)) {
        $data['fotografia'] = $filename;
      } else {
        header('Content-Type: application/json');
        echo json_encode([
          'status' => 500,
          'msg' => 'Error al subir la imagen.'
        ]);
        exit;
      }
    }

    // Guardar en la base de datos usando el modelo AcervoArqueologicoModel
    require_once APP . 'models/acervoArqueologicoModel.php';
    $id = AcervoArqueologicoModel::addPieza($data);

    if ($id) {
      header('Content-Type: application/json');
      echo json_encode([
        'status' => 200,
        'msg' => 'Registro guardado correctamente',
        'id' => $id
      ]);
    } else {
      header('Content-Type: application/json');
      echo json_encode([
        'status' => 500,
        'msg' => 'Error al guardar el registro'
      ]);
    }
    exit;
  }

  function post_registro_numismatica()
  {
    // Procesar y guardar los datos del formulario de acervoNumismatica
    $data = [
      'codigo_interno'        => $_POST['codigo_interno'] ?? '',
      'no_inventario'         => $_POST['no_inventario'] ?? '',
      'fotografia'            => '', // Se actualizará si se sube archivo
      'tipo_obra'             => $_POST['tipo_obra'] ?? '',
      'ensayador'             => $_POST['ensayador'] ?? '',
      'denominacion'          => $_POST['denominacion'] ?? '',
      'material'              => $_POST['material'] ?? '',
      'fecha_epoca'           => $_POST['fecha_epoca'] ?? '',
      'dimensiones'           => $_POST['dimensiones'] ?? '',
      'ubicacion_fisica'      => $_POST['ubicacion_fisica'] ?? '',
      'estado_conservacion'   => $_POST['estado_conservacion'] ?? '',
      'observaciones'         => $_POST['observaciones'] ?? '',
      'descripcion_cara_a'    => $_POST['descripcion_cara_a'] ?? '',
      'descripcion_cara_b'    => $_POST['descripcion_cara_b'] ?? '',
      'id_modulo'             => 4
    ];

    // Procesar imagen si se envió
    if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] === 0) {
      $tmp_name = $_FILES['fotografia']['tmp_name'];
      $filename = $_FILES['fotografia']['name'];
      $upload_path = UPLOADS . $filename;
      if (move_uploaded_file($tmp_name, $upload_path)) {
        $data['fotografia'] = $filename;
      } else {
        header('Content-Type: application/json');
        echo json_encode([
          'status' => 500,
          'msg' => 'Error al subir la imagen.'
        ]);
        exit;
      }
    }

    // Guardar en la base de datos usando el modelo AcervoNumismaticaModel
    require_once APP . 'models/acervoNumismaticaModel.php';
    $id = AcervoNumismaticaModel::addPieza($data);

    if ($id) {
      header('Content-Type: application/json');
      echo json_encode([
        'status' => 200,
        'msg' => 'Registro guardado correctamente',
        'id' => $id
      ]);
    } else {
      header('Content-Type: application/json');
      echo json_encode([
        'status' => 500,
        'msg' => 'Error al guardar el registro'
      ]);
    }
    exit;
  }

  // Endpoint para AJAX: listado paginado de Acervo General
  private function filtrarAcervoPorPropiedades(array $all)
  {
    // Filtro por Ubicación
    $ubicacionId = isset($_POST['ubicacion']) ? trim($_POST['ubicacion']) : '';
    if ($ubicacionId !== '') {
      $mapUbi = [
        '1' => 'Acambay',
        '2' => 'Tenancingo',
        '3' => 'Toluca',
        '4' => 'Zinacantepec'
      ];
      $ubiText = $mapUbi[$ubicacionId] ?? '';
      if ($ubiText !== '') {
        $all = array_filter($all, function ($pieza) use ($ubiText) {
          $camposBuscar = ['ubicacion_fisica', 'nombre_titulo_pieza', 'descripcion', 'procedencia', 'coleccion', 'observaciones', 'codigo_interno'];
          foreach ($camposBuscar as $campo) {
            if (isset($pieza[$campo]) && stripos($pieza[$campo], $ubiText) !== false) {
              return true;
            }
          }
          return false;
        });
        $all = array_values($all);
      }
    }

    // Filtro por Año
    $anioVal = isset($_POST['anio']) ? trim($_POST['anio']) : '';
    if ($anioVal !== '') {
      $all = array_filter($all, function ($pieza) use ($anioVal) {
        $camposBuscar = ['anio', 'fecha_epoca', 'epoca', 'no_registro_inah', 'descripcion', 'observaciones', 'nombre_titulo_pieza'];
        foreach ($camposBuscar as $campo) {
          if (isset($pieza[$campo]) && stripos($pieza[$campo], $anioVal) !== false) {
            return true;
          }
        }
        return false;
      });
      $all = array_values($all);
    }

    // Filtro por Cultura
    $culturaId = isset($_POST['cultura']) ? trim($_POST['cultura']) : '';
    if ($culturaId !== '') {
      $mapCult = [
        '2' => 'Mexica',
        '3' => 'Teotihuaca',
        '4' => 'Tolteca',
        '5' => 'Chichimeca',
        '6' => 'Otomi',
        '7' => 'Matlatzinca',
        '8' => 'Mazahua',
        '9' => 'Purhepecha',
        '10' => 'Tlaxcalteca',
        '11' => 'Nahuatl',
        '12' => 'Mazateco',
        '13' => 'Mixteco',
        '14' => 'Zapoteco',
        '15' => 'Totonaca',
        '16' => 'Huasteco',
        '17' => 'Maya'
      ];
      $cultText = $mapCult[$culturaId] ?? '';
      if ($cultText !== '') {
        $all = array_filter($all, function ($pieza) use ($cultText) {
          $camposBuscar = ['descripcion', 'representacion', 'observaciones', 'nombre_titulo_pieza', 'procedencia', 'coleccion', 'material', 'tecnica'];
          foreach ($camposBuscar as $campo) {
            if (isset($pieza[$campo])) {
              $texto = $pieza[$campo];
              $textoSinAcentos = str_ireplace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u'], $texto);
              $cultSinAcentos = str_ireplace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u'], $cultText);
              
              $raiz = rtrim($cultSinAcentos, 's');
              if (substr($raiz, -1) === 'e') {
                $raizVal = substr($raiz, 0, -1);
              } else {
                $raizVal = $raiz;
              }
              
              if (stripos($textoSinAcentos, $raizVal) !== false) {
                return true;
              }
            }
          }
          return false;
        });
        $all = array_values($all);
      }
    }

    return $all;
  }

  public function get_acervo_general()
  {
    // Parámetros de paginación y búsqueda
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $perPage = isset($_POST['per_page']) ? (int)$_POST['per_page'] : 10;
    $search = isset($_POST['search']) ? trim($_POST['search']) : '';
    $offset = ($page - 1) * $perPage;

    require_once APP . 'models/acervoGeneralModel.php';

    // Filtro de búsqueda (nombre, código interno, autor, descripción u observaciones)
    $all = AcervoGeneralModel::getAll();
    if ($search !== '') {
      $all = array_filter($all, function ($pieza) use ($search) {
        return (isset($pieza['nombre_titulo_pieza']) && stripos($pieza['nombre_titulo_pieza'], $search) !== false)
          || (isset($pieza['codigo_interno']) && stripos($pieza['codigo_interno'], $search) !== false)
          || (isset($pieza['autor']) && stripos($pieza['autor'], $search) !== false)
          || (isset($pieza['descripcion']) && stripos($pieza['descripcion'], $search) !== false)
          || (isset($pieza['observaciones']) && stripos($pieza['observaciones'], $search) !== false);
      });
      $all = array_values($all);
    }
    $all = $this->filtrarAcervoPorPropiedades($all);

    $total = count($all);
    $piezas = array_slice($all, $offset, $perPage);

    // Formatear datos para la tabla
    $data = array_map(function ($pieza) {
      return [
        'image' => !empty($pieza['fotografia']) ? 'assets/uploads/' . $pieza['fotografia'] : '',
        'id' => $pieza['id_acervo_general'],
        'nombre' => !empty($pieza['nombre_titulo_pieza']) ? $pieza['nombre_titulo_pieza'] : (!empty($pieza['nombre']) ? $pieza['nombre'] : '-'),
        'codigo_interno' => !empty($pieza['codigo_interno']) ? $pieza['codigo_interno'] : '-',
        'autor' => !empty($pieza['autor']) ? $pieza['autor'] : '-',
        'descripcion' => !empty($pieza['descripcion']) ? $pieza['descripcion'] : '-',
      ];
    }, $piezas);

    // Paginación
    $pagination = [
      'current_page' => $page,
      'total_pages' => max(1, ceil($total / $perPage)),
      'total' => $total,
      'per_page' => $perPage
    ];

    header('Content-Type: application/json');
    echo json_encode([
      'status' => 200,
      'data' => $data,
      'pagination' => $pagination
    ]);
    exit;
  }

  // Endpoint para AJAX: listado paginado de Acervo Arqueológico
  public function get_acervo_arq()
  {
    // Parámetros de paginación y búsqueda
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $perPage = isset($_POST['per_page']) ? (int)$_POST['per_page'] : 10;
    $search = isset($_POST['search']) ? trim($_POST['search']) : '';
    $offset = ($page - 1) * $perPage;

    require_once APP . 'models/acervoArqueologicoModel.php';

    // Filtro de búsqueda (nombre, código interno, no_registro_inah, procedencia, descripción u observaciones)
    $all = AcervoArqueologicoModel::getAll();
    if ($search !== '') {
      $all = array_filter($all, function ($pieza) use ($search) {
        return (isset($pieza['nombre_titulo_pieza']) && stripos($pieza['nombre_titulo_pieza'], $search) !== false)
          || (isset($pieza['codigo_interno']) && stripos($pieza['codigo_interno'], $search) !== false)
          || (isset($pieza['no_registro_inah']) && stripos($pieza['no_registro_inah'], $search) !== false)
          || (isset($pieza['procedencia']) && stripos($pieza['procedencia'], $search) !== false)
          || (isset($pieza['descripcion']) && stripos($pieza['descripcion'], $search) !== false)
          || (isset($pieza['observaciones']) && stripos($pieza['observaciones'], $search) !== false);
      });
      $all = array_values($all);
    }
    $all = $this->filtrarAcervoPorPropiedades($all);

    $total = count($all);
    $piezas = array_slice($all, $offset, $perPage);

    // Formatear datos para la tabla
    $data = array_map(function ($pieza) {
      return [
        'image' => !empty($pieza['fotografia']) ? 'assets/uploads/' . $pieza['fotografia'] : '',
        'id' => $pieza['id_acervo_arq'],
        'nombre' => !empty($pieza['nombre_titulo_pieza']) ? $pieza['nombre_titulo_pieza'] : (!empty($pieza['nombre']) ? $pieza['nombre'] : '-'),
        'codigo_interno' => !empty($pieza['codigo_interno']) ? $pieza['codigo_interno'] : '-',
        'autor' => !empty($pieza['no_registro_inah']) ? $pieza['no_registro_inah'] : '-',
        'ubicacion' => !empty($pieza['ubicacion_fisica']) ? $pieza['ubicacion_fisica'] : '-',
        'descripcion' => !empty($pieza['descripcion']) ? $pieza['descripcion'] : '-',
        'fecha' => !empty($pieza['no_registro_inah']) ? $pieza['no_registro_inah'] : '-',
        'no_registro_inah' => !empty($pieza['no_registro_inah']) ? $pieza['no_registro_inah'] : '-',
      ];
    }, $piezas);

    // Paginación
    $pagination = [
      'current_page' => $page,
      'total_pages' => max(1, ceil($total / $perPage)),
      'total' => $total,
      'per_page' => $perPage
    ];

    header('Content-Type: application/json');
    echo json_encode([
      'status' => 200,
      'data' => $data,
      'pagination' => $pagination
    ]);
    exit;
  }

  // Endpoint para AJAX: listado paginado de Acervo Numismática
  public function get_acervo_numismatica()
  {
    // Parámetros de paginación y búsqueda
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $perPage = isset($_POST['per_page']) ? (int)$_POST['per_page'] : 10;
    $search = isset($_POST['search']) ? trim($_POST['search']) : '';
    $offset = ($page - 1) * $perPage;

    require_once APP . 'models/acervoNumismaticaModel.php';

    // Filtro de búsqueda (código interno, denominación, ensayador, material, descripciones u observaciones)
    $all = AcervoNumismaticaModel::getAll();
    if ($search !== '') {
      $all = array_filter($all, function ($pieza) use ($search) {
        return (isset($pieza['codigo_interno']) && stripos($pieza['codigo_interno'], $search) !== false)
          || (isset($pieza['denominacion']) && stripos($pieza['denominacion'], $search) !== false)
          || (isset($pieza['ensayador']) && stripos($pieza['ensayador'], $search) !== false)
          || (isset($pieza['material']) && stripos($pieza['material'], $search) !== false)
          || (isset($pieza['descripcion_cara_a']) && stripos($pieza['descripcion_cara_a'], $search) !== false)
          || (isset($pieza['descripcion_cara_b']) && stripos($pieza['descripcion_cara_b'], $search) !== false)
          || (isset($pieza['observaciones']) && stripos($pieza['observaciones'], $search) !== false);
      });
      $all = array_values($all);
    }
    $all = $this->filtrarAcervoPorPropiedades($all);

    $total = count($all);
    $piezas = array_slice($all, $offset, $perPage);

    // Formatear datos para la tabla
    $data = array_map(function ($pieza) {
      return [
        'image' => !empty($pieza['fotografia']) ? 'assets/uploads/' . $pieza['fotografia'] : '',
        'id' => $pieza['id_acervo_numismatica'],
        'nombre' => !empty($pieza['denominacion']) ? $pieza['denominacion'] : (!empty($pieza['tipo_obra']) ? $pieza['tipo_obra'] : 'Pieza Numismática'),
        'codigo_interno' => !empty($pieza['codigo_interno']) ? $pieza['codigo_interno'] : '-',
        'autor' => !empty($pieza['ubicacion_fisica']) ? $pieza['ubicacion_fisica'] : '-',
        'ubicacion' => !empty($pieza['ubicacion_fisica']) ? $pieza['ubicacion_fisica'] : '-',
        'descripcion' => !empty($pieza['material']) ? $pieza['material'] : '-',
        'fecha' => !empty($pieza['fecha_epoca']) ? $pieza['fecha_epoca'] : '-',
      ];
    }, $piezas);

    // Paginación
    $pagination = [
      'current_page' => $page,
      'total_pages' => max(1, ceil($total / $perPage)),
      'total' => $total,
      'per_page' => $perPage
    ];

    header('Content-Type: application/json');
    echo json_encode([
      'status' => 200,
      'data' => $data,
      'pagination' => $pagination
    ]);
    exit;
  }

  // Editar pieza de acervo general (AJAX)
  public function acervo_general_editar()
  {
    $id = isset($_POST['id_acervo_general']) ? (int)$_POST['id_acervo_general'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
    if ($id <= 0) {
      echo json_encode(['status' => 400, 'msg' => 'ID inválido']);
      exit;
    }
    require_once APP . 'models/acervoGeneralModel.php';
    $piezaActual = AcervoGeneralModel::getById($id);
    $fotoActual = ($piezaActual && isset($piezaActual[0]['fotografia'])) ? $piezaActual[0]['fotografia'] : '';

    $data = $_POST;
    unset($data['id_acervo_general']);
    unset($data['id']);
    unset($data['csrf']);
    unset($data['fotografia_actual']);

    if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] === UPLOAD_ERR_OK) {
      $tmpName = $_FILES['fotografia']['tmp_name'];
      $fileName = $_FILES['fotografia']['name'];
      $newName = basename($fileName);

      if (!move_uploaded_file($tmpName, UPLOADS . $newName)) {
        echo json_encode(['status' => 500, 'msg' => 'Error al subir la nueva fotografía']);
        exit;
      }

      $data['fotografia'] = $newName;

      if (!empty($fotoActual) && is_file(UPLOADS . $fotoActual)) {
        unlink(UPLOADS . $fotoActual);
      }
    } elseif (!empty($_POST['fotografia_actual'])) {
      $data['fotografia'] = $_POST['fotografia_actual'];
    } elseif (!empty($fotoActual)) {
      $data['fotografia'] = $fotoActual;
    }

    $ok = AcervoGeneralModel::updatePieza($id, $data);
    if ($ok) {
      echo json_encode(['status' => 200, 'msg' => 'Pieza actualizada correctamente']);
    } else {
      echo json_encode(['status' => 500, 'msg' => 'Error al actualizar la pieza']);
    }
    exit;
  }

  // Editar pieza de acervo arqueológico (AJAX)
  public function acervo_arq_editar()
  {
    $id = isset($_POST['id_acervo_arq']) ? (int)$_POST['id_acervo_arq'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
    if ($id <= 0) {
      echo json_encode(['status' => 400, 'msg' => 'ID inválido']);
      exit;
    }
    require_once APP . 'models/acervoArqueologicoModel.php';
    $piezaActual = AcervoArqueologicoModel::getById($id);
    $fotoActual = ($piezaActual && isset($piezaActual[0]['fotografia'])) ? $piezaActual[0]['fotografia'] : '';

    $data = $_POST;
    unset($data['id_acervo_arq']);
    unset($data['id']);
    unset($data['csrf']);
    unset($data['fotografia_actual']);

    if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] === UPLOAD_ERR_OK) {
      $tmpName = $_FILES['fotografia']['tmp_name'];
      $fileName = $_FILES['fotografia']['name'];
      $newName = basename($fileName);

      if (!move_uploaded_file($tmpName, UPLOADS . $newName)) {
        echo json_encode(['status' => 500, 'msg' => 'Error al subir la nueva fotografía']);
        exit;
      }

      $data['fotografia'] = $newName;

      if (!empty($fotoActual) && is_file(UPLOADS . $fotoActual)) {
        unlink(UPLOADS . $fotoActual);
      }
    } elseif (!empty($_POST['fotografia_actual'])) {
      $data['fotografia'] = $_POST['fotografia_actual'];
    } elseif (!empty($fotoActual)) {
      $data['fotografia'] = $fotoActual;
    }

    $ok = AcervoArqueologicoModel::updatePieza($id, $data);
    if ($ok) {
      echo json_encode(['status' => 200, 'msg' => 'Pieza actualizada correctamente']);
    } else {
      echo json_encode(['status' => 500, 'msg' => 'Error al actualizar la pieza']);
    }
    exit;
  }

  // Editar pieza de acervo numismático (AJAX)
  public function acervo_numismatica_editar()
  {
    $id = isset($_POST['id_acervo_numismatica']) ? (int)$_POST['id_acervo_numismatica'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
    if ($id <= 0) {
      echo json_encode(['status' => 400, 'msg' => 'ID inválido']);
      exit;
    }
    require_once APP . 'models/acervoNumismaticaModel.php';
    $piezaActual = AcervoNumismaticaModel::getById($id);
    $fotoActual = ($piezaActual && isset($piezaActual[0]['fotografia'])) ? $piezaActual[0]['fotografia'] : '';

    $data = $_POST;
    unset($data['id_acervo_numismatica']);
    unset($data['id']);
    unset($data['csrf']);
    unset($data['fotografia_actual']);

    if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] === UPLOAD_ERR_OK) {
      $tmpName = $_FILES['fotografia']['tmp_name'];
      $fileName = $_FILES['fotografia']['name'];
      $newName = basename($fileName);

      if (!move_uploaded_file($tmpName, UPLOADS . $newName)) {
        echo json_encode(['status' => 500, 'msg' => 'Error al subir la nueva fotografía']);
        exit;
      }

      $data['fotografia'] = $newName;

      if (!empty($fotoActual) && is_file(UPLOADS . $fotoActual)) {
        unlink(UPLOADS . $fotoActual);
      }
    } elseif (!empty($_POST['fotografia_actual'])) {
      $data['fotografia'] = $_POST['fotografia_actual'];
    } elseif (!empty($fotoActual)) {
      $data['fotografia'] = $fotoActual;
    }

    $ok = AcervoNumismaticaModel::updatePieza($id, $data);
    if ($ok) {
      echo json_encode(['status' => 200, 'msg' => 'Pieza actualizada correctamente']);
    } else {
      echo json_encode(['status' => 500, 'msg' => 'Error al actualizar la pieza']);
    }
    exit;
  }

  // Eliminar pieza de acervo general (AJAX)
  public function acervo_general_eliminar()
  {
    $id = isset($_POST['id_acervo_general']) ? (int)$_POST['id_acervo_general'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
    if ($id <= 0) {
      echo json_encode(['status' => 400, 'msg' => 'ID inválido']);
      exit;
    }
    require_once APP . 'models/acervoGeneralModel.php';
    $ok = AcervoGeneralModel::deletePieza($id);
    if ($ok) {
      echo json_encode(['status' => 200, 'msg' => 'Pieza eliminada correctamente']);
    } else {
      echo json_encode(['status' => 500, 'msg' => 'Error al eliminar la pieza']);
    }
    exit;
  }

  // Eliminar pieza de acervo arqueológico (AJAX)
  public function acervo_arq_eliminar()
  {
    $id = isset($_POST['id_acervo_arq']) ? (int)$_POST['id_acervo_arq'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
    if ($id <= 0) {
      echo json_encode(['status' => 400, 'msg' => 'ID inválido']);
      exit;
    }
    require_once APP . 'models/acervoArqueologicoModel.php';
    $ok = AcervoArqueologicoModel::deletePieza($id);
    if ($ok) {
      echo json_encode(['status' => 200, 'msg' => 'Pieza eliminada correctamente']);
    } else {
      echo json_encode(['status' => 500, 'msg' => 'Error al eliminar la pieza']);
    }
    exit;
  }

  // Eliminar pieza de acervo general (AJAX)
  public function acervo_numismatica_eliminar()
  {
    $id = isset($_POST['id_acervo_numismatica']) ? (int)$_POST['id_acervo_numismatica'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
    if ($id <= 0) {
      echo json_encode(['status' => 400, 'msg' => 'ID inválido']);
      exit;
    }
    require_once APP . 'models/acervoNumismaticaModel.php';
    $ok = AcervoNumismaticaModel::deletePieza($id);
    if ($ok) {
      echo json_encode(['status' => 200, 'msg' => 'Pieza eliminada correctamente']);
    } else {
      echo json_encode(['status' => 500, 'msg' => 'Error al eliminar la pieza']);
    }
    exit;
  }

  // Obtener todos los datos de una pieza de acervo general (AJAX)
  public function acervo_general_get_by_id()
  {
    $id = isset($_POST['id_acervo_general']) ? (int)$_POST['id_acervo_general'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
    if ($id <= 0) {
      echo json_encode(['status' => 400, 'msg' => 'ID inválido']);
      exit;
    }
    require_once APP . 'models/acervoGeneralModel.php';
    $pieza = AcervoGeneralModel::getById($id);
    if ($pieza && isset($pieza[0])) {
      $pieza[0]['fotografia_url'] = !empty($pieza[0]['fotografia']) ? 'assets/uploads/' . $pieza[0]['fotografia'] : '';
      echo json_encode(['status' => 200, 'data' => $pieza[0]]);
    } else {
      echo json_encode(['status' => 404, 'msg' => 'Pieza no encontrada']);
    }
    exit;
  }

  // Obtener todos los datos de una pieza de acervo arqueológico (AJAX)
  public function acervo_arq_get_by_id()
  {
    $id = isset($_POST['id_acervo_arq']) ? (int)$_POST['id_acervo_arq'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
    if ($id <= 0) {
      echo json_encode(['status' => 400, 'msg' => 'ID inválido']);
      exit;
    }
    require_once APP . 'models/acervoArqueologicoModel.php';
    $pieza = AcervoArqueologicoModel::getById($id);
    if ($pieza && isset($pieza[0])) {
      $pieza[0]['fotografia_url'] = !empty($pieza[0]['fotografia']) ? 'assets/uploads/' . $pieza[0]['fotografia'] : '';
      echo json_encode(['status' => 200, 'data' => $pieza[0]]);
    } else {
      echo json_encode(['status' => 404, 'msg' => 'Pieza no encontrada']);
    }
    exit;
  }

  // Obtener todos los datos de una pieza de acervo numismática (AJAX)
  public function acervo_numismatica_get_by_id()
  {
    $id = isset($_POST['id_acervo_numismatica']) ? (int)$_POST['id_acervo_numismatica'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
    if ($id <= 0) {
      echo json_encode(['status' => 400, 'msg' => 'ID inválido']);
      exit;
    }
    require_once APP . 'models/acervoNumismaticaModel.php';
    $pieza = AcervoNumismaticaModel::getById($id);
    if ($pieza && isset($pieza[0])) {
      $pieza[0]['fotografia_url'] = !empty($pieza[0]['fotografia']) ? 'assets/uploads/' . $pieza[0]['fotografia'] : '';
      echo json_encode(['status' => 200, 'data' => $pieza[0]]);
    } else {
      echo json_encode(['status' => 404, 'msg' => 'Pieza no encontrada']);
    }
    exit;
  }

    // Obtener lista dinámica de años y épocas registrados en todas las colecciones
  public function get_todos_anios()
  {
    try {
      $db = Db::connect();
      
      // Consultar de acervo_general (columna 'anio')
      $aniosGeneral = $db->query("SELECT DISTINCT anio FROM acervo_general WHERE anio IS NOT NULL AND anio != ''")->fetchAll(PDO::FETCH_COLUMN);
      
      // Consultar de acervo_arqueologico (columna 'epoca')
      $aniosArq = $db->query("SELECT DISTINCT epoca FROM acervo_arqueologico WHERE epoca IS NOT NULL AND epoca != ''")->fetchAll(PDO::FETCH_COLUMN);
      
      // Consultar de acervo_numismatica (columna 'fecha_epoca')
      $aniosNum = $db->query("SELECT DISTINCT fecha_epoca FROM acervo_numismatica WHERE fecha_epoca IS NOT NULL AND fecha_epoca != ''")->fetchAll(PDO::FETCH_COLUMN);
      
      $todos = array_merge($aniosGeneral, $aniosArq, $aniosNum);
      
      $todosClean = [];
      foreach ($todos as $val) {
        $val = trim($val);
        if ($val !== '' && stripos($val, 'sin ') === false && stripos($val, 'no ') === false && stripos($val, 's/a') === false && stripos($val, 's/f') === false && stripos($val, 'sin dato') === false) {
          $todosClean[] = $val;
        }
      }
      
      $todosClean = array_unique($todosClean);
      
      // Ordenar: numéricos primero (cronológicamente), luego textos (ej: épocas)
      usort($todosClean, function($a, $b) {
        $aIsNum = is_numeric($a);
        $bIsNum = is_numeric($b);
        if ($aIsNum && $bIsNum) {
          return (int)$a - (int)$b;
        }
        if ($aIsNum) return -1;
        if ($bIsNum) return 1;
        return strcasecmp($a, $b);
      });
      
      header('Content-Type: application/json');
      echo json_encode([
        'status' => 200,
        'data' => array_values($todosClean)
      ]);
     } catch (Exception $e) {
      header('Content-Type: application/json');
      echo json_encode([
        'status' => 500,
        'msg' => $e->getMessage()
      ]);
    }
    exit;
  }

}

function obtenerCamposAcervoGeneral()
{
  $campos = [
    [
      'type' => 'text',
      'name' => 'codigo_interno',
      'label' => 'Código interno',
      'id' => 'codigo-interno',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. CI-001',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'no_inventario',
      'label' => 'Número de inventario',
      'id' => 'no-inventario',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. INV-2025-001',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'nombre_titulo_pieza',
      'label' => 'Nombre de la pieza',
      'id' => 'nombre-titulo-pieza',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. Escultura de barro',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'cm',
      'label' => 'Centímetros (idk)',
      'id' => 'cm',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. 100',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],

    [
      'type' => 'text',
      'name' => 'autor',
      'label' => 'Autor',
      'id' => 'autor',
      'class' => 'form-control',
      'required' => false,
      'default_value' => '',
      'placeholder' => 'Ej. Anónimo',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'date',
      'name' => 'anio',
      'label' => 'Año',
      'id' => 'anio',
      'class' => 'form-control',
      'required' => false,
      'default_value' => '1970-01-01',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'select',
      'name' => 'epoca',
      'label' => 'Época',
      'id' => 'epoca',
      'class' => 'form-select',
      'required' => false,
      'options' => ['Prehispánica', 'Colonial', 'Moderna', 'Contemporánea'],
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'select',
      'name' => 'tecnica',
      'label' => 'Técnica',
      'id' => 'tecnica',
      'class' => 'form-select',
      'required' => true,
      'options' => ['Óleo', 'Acuarela', 'Grabado', 'Mixta'],
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'select',
      'name' => 'material',
      'label' => 'Material',
      'id' => 'material',
      'class' => 'form-select',
      'required' => true,
      'options' => ['Madera', 'Metal', 'Cerámica', 'Textil'],
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'number',
      'name' => 'medidas',
      'label' => 'Medidas (cm)',
      'id' => 'medidas',
      'class' => 'form-control',
      'required' => false,
      'default_value' => '',
      'min' => 0,
      'max' => 9999,
      'step' => 'any',
      'placeholder' => ' 1cm x 1cm x 1cm',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'lote',
      'label' => 'Lote',
      'id' => 'lote',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. 100',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'peso',
      'label' => 'Peso (kg)',
      'id' => 'peso',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. 100',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'select',
      'name' => 'coleccion',
      'label' => 'Colección',
      'id' => 'coleccion',
      'class' => 'form-select',
      'required' => false,
      'options' => ['Colección permanente', 'Colección temporal', 'Donación'],
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'select',
      'name' => 'tipo',
      'label' => 'Tipo de obra',
      'id' => 'tipo',
      'class' => 'form-select',
      'required' => true,
      'options' => ['Pintura', 'Escultura', 'Fotografía', 'Objeto'],
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'ubicacion_fisica',
      'label' => 'Ubicación física',
      'id' => 'ubicacion-fisica',
      'class' => 'form-control',
      'required' => false,
      'default_value' => '',
      'placeholder' => 'Ej. Sala 3, vitrina 5',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'select',
      'name' => 'estado_conservacion',
      'label' => 'Estado de conservación',
      'id' => 'estado-conservacion',
      'class' => 'form-select',
      'required' => false,
      'options' => ['Excelente', 'Bueno', 'Regular', 'Dañado'],
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'textarea',
      'name' => 'observaciones',
      'label' => 'Observaciones',
      'id' => 'observaciones',
      'class' => 'form-control',
      'required' => false,
      'default_value' => '',
      'placeholder' => 'Observaciones sobre la pieza',
      'rows' => 4,
      'cols' => 5,
      'column_class' => 'col-12 mb-3'
    ],
    [
      'type' => 'textarea',
      'name' => 'descripcion',
      'label' => 'Descripción',
      'id' => 'descripcion',
      'class' => 'form-control',
      'required' => false,
      'default_value' => '',
      'placeholder' => 'Descripción detallada de la pieza',
      'rows' => 4,
      'cols' => 5,
      'column_class' => 'col-12 mb-3'
    ]
  ];

  return $campos;
}

function obtenerCamposAcervoArqueologico()
{
  $campos = [
    [
      'type' => 'text',
      'name' => 'codigo_interno',
      'label' => 'Código interno',
      'id' => 'codigo-interno',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. CI-001',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'no_inventario_scyt',
      'label' => 'Número de inventario SCYT',
      'id' => 'no-inventario-scyt',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. CI-001',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'no_registro_inah',
      'label' => 'Número de registro INAH',
      'id' => 'no-registro-inah',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. INV-2025-001',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'otros',
      'label' => 'Otros',
      'id' => 'otros',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. Escultura de barro',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'nombre_titulo_pieza',
      'label' => 'Nombre o título de la pieza',
      'id' => 'nombre-titulo-pieza',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. Escultura de barro',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'numero_pieza_por_lote',
      'label' => 'Número de pieza por lote',
      'id' => 'numero-pieza-por-lote',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. Escultura de barro',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'select',
      'name' => 'epoca',
      'label' => 'Época',
      'id' => 'epoca',
      'class' => 'form-select',
      'required' => false,
      'options' => ['Prehispánica', 'Colonial', 'Moderna', 'Contemporánea'],
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'procedencia',
      'label' => 'Procedencia',
      'id' => 'procedencia',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. Escultura de barro',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'select',
      'name' => 'Material',
      'label' => 'Material',
      'id' => 'material',
      'class' => 'form-select',
      'required' => false,
      'options' => ['Madera', 'Metal', 'Cerámica', 'Textil'],
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'number',
      'name' => 'medidas',
      'label' => 'Medidas (cm)',
      'id' => 'medidas',
      'class' => 'form-control',
      'required' => false,
      'default_value' => '',
      'min' => 0,
      'max' => 9999,
      'step' => 'any',
      'placeholder' => ' 1cm x 1cm x 1cm',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'forma',
      'label' => 'Forma',
      'id' => 'forma',
      'class' => 'form-control',
      'required' => false,
      'default_value' => '',
      'placeholder' => 'Ej. Escultura de barro',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'tecnica_manufactura',
      'label' => 'Técnica de manufactura',
      'id' => 'tecnica-manufactura',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. Escultura de barro',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'tecnica_decorativa',
      'label' => 'Técnica decorativa',
      'id' => 'tecnica-decorativa',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. Escultura de barro',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'coleccion',
      'label' => 'Colección',
      'id' => 'coleccion',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. Escultura de barro',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'obtencion',
      'label' => 'Obtención',
      'id' => 'obtencion',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. Escultura de barro',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'ubicacion_fisica',
      'label' => 'Ubicación física',
      'id' => 'ubicacion-fisica',
      'class' => 'form-control',
      'required' => false,
      'default_value' => '',
      'placeholder' => 'Ej. Sala 3, vitrina 5',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'select',
      'name' => 'estado_conservacion',
      'label' => 'Estado de conservación',
      'id' => 'estado-conservacion',
      'class' => 'form-select',
      'required' => false,
      'options' => ['Excelente', 'Bueno', 'Regular', 'Dañado'],
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'textarea',
      'name' => 'observaciones',
      'label' => 'Observaciones',
      'id' => 'observaciones',
      'class' => 'form-control',
      'required' => false,
      'default_value' => '',
      'placeholder' => 'Descripción detallada de la pieza',
      'rows' => 4,
      'cols' => 5,
      'column_class' => 'col-12 mb-3'
    ],
    [
      'type' => 'textarea',
      'name' => 'descripcion',
      'label' => 'Descripción',
      'id' => 'descripcion',
      'class' => 'form-control',
      'required' => false,
      'default_value' => '',
      'placeholder' => 'Descripción detallada de la pieza',
      'rows' => 4,
      'cols' => 5,
      'column_class' => 'col-12 mb-3'
    ],
    [
      'type' => 'textarea',
      'name' => 'representacion',
      'label' => 'Representación',
      'id' => 'representacion',
      'class' => 'form-control',
      'required' => false,
      'default_value' => '',
      'placeholder' => 'Representación detallada de la pieza',
      'rows' => 4,
      'cols' => 5,
      'column_class' => 'col-12 mb-3'
    ]
  ];

  return $campos;
}

function obtenerCamposAcervoNumismatica()
{
  $campos = [
    [
      'type' => 'text',
      'name' => 'codigo_interno',
      'label' => 'Código interno',
      'id' => 'codigo-interno',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. CI-001',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'no_inventario',
      'label' => 'Número de inventario',
      'id' => 'no-inventario',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. INV-2025-001',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'select',
      'name' => 'tipo_obra',
      'label' => 'Tipo de obra',
      'id' => 'tipo-obra',
      'class' => 'form-select',
      'required' => true,
      'options' => ['Pintura', 'Escultura', 'Fotografía', 'Objeto'],
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'ensayador',
      'label' => 'Ensayador',
      'id' => 'ensayador',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. INV-2025-001',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'denominacion',
      'label' => 'Denominación',
      'id' => 'denominacion',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. INV-2025-001',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'select',
      'name' => 'material',
      'label' => 'Material',
      'id' => 'material',
      'class' => 'form-select',
      'required' => true,
      'options' => ['Madera', 'Metal', 'Cerámica', 'Textil'],
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'select',
      'name' => 'fecha_epoca',
      'label' => 'Época',
      'id' => 'fecha-epoca',
      'class' => 'form-select',
      'required' => false,
      'options' => ['Prehispánica', 'Colonial', 'Moderna', 'Contemporánea'],
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'number',
      'name' => 'dimensiones',
      'label' => 'Dimensiones (cm)',
      'id' => 'dimensiones',
      'class' => 'form-control',
      'required' => false,
      'default_value' => '',
      'min' => 0,
      'max' => 9999,
      'step' => 'any',
      'placeholder' => '0.00',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'text',
      'name' => 'ubicacion_fisica',
      'label' => 'Ubicación física',
      'id' => 'ubicacion-fisica',
      'class' => 'form-control',
      'required' => false,
      'default_value' => '',
      'placeholder' => 'Ej. Sala 3, vitrina 5',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'select',
      'name' => 'estado_conservacion',
      'label' => 'Estado de conservación',
      'id' => 'estado-conservacion',
      'class' => 'form-select',
      'required' => false,
      'options' => ['Excelente', 'Bueno', 'Regular', 'Dañado'],
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'textarea',
      'name' => 'observaciones',
      'label' => 'Observaciones',
      'id' => 'observaciones',
      'class' => 'form-control',
      'required' => false,
      'default_value' => '',
      'placeholder' => '',
      'rows' => 4,
      'cols' => 5,
      'column_class' => 'col-12 mb-3'
    ],
    [
      'type' => 'textarea',
      'name' => 'descripcion_cara_a',
      'label' => "Descripción de la cara A",
      'id' => "descripcion-cara-a",
      'class' => "form-control",
      'required' => false,
      'default_value' => "",
      'placeholder' => "",
      'rows' => 4,
      'cols' => 5,
      'column_class' => "col-12 mb-3"
    ],
    [
        "type" => "textarea",
        "name" => "descripcion_cara_b",
        "label" => "Descripción de la cara B",
        "id" => "descripcion-cara-b",
        "class" => "form-control",
        "required" => false,
        "default_value" => "",
        "placeholder" => "",
        "rows" => 4,
        "cols" => 5,
        "column_class" => "col-12 mb-3"
    ]
  ];

  return $campos;
}