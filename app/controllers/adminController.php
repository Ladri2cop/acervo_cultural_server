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

    require_once APP . 'models/acervoGeneralModel.php';
    require_once APP . 'models/acervoArqueologicoModel.php';
    require_once APP . 'models/acervoNumismaticaModel.php';

    $totalGeneral = AcervoGeneralModel::getTotal();
    $totalArq     = AcervoArqueologicoModel::getTotal();
    $totalNum     = AcervoNumismaticaModel::getTotal();

    $totalRegistrados = $totalGeneral + $totalArq + $totalNum;

    $this->setTitle('Administración');
    $this->addToData('totalRegistrados', $totalRegistrados);
    $this->addToData('totalGeneral', $totalGeneral);
    $this->addToData('totalArq', $totalArq);
    $this->addToData('totalNum', $totalNum);

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
      'materia'               => $_POST['materia'] ?? '',
      'fotografia'            => '', // Se actualizará si se sube archivo
      'autor'                 => $_POST['autor'] ?? '',
      'anio'                  => $_POST['anio'] ?? '',
      'epoca'                 => $_POST['epoca'] ?? '',
      'tecnica'               => $_POST['tecnica'] ?? '',
      'origen'                => $_POST['origen'] ?? '',
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
    $ubicacionId = isset($_REQUEST['ubicacion']) ? trim($_REQUEST['ubicacion']) : '';
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
    $anioVal = isset($_REQUEST['anio']) ? trim($_REQUEST['anio']) : '';
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
    $culturaId = isset($_REQUEST['cultura']) ? trim($_REQUEST['cultura']) : '';
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

  private function get_filtered_acervo_data($tipoAcervo)
  {
    $search = isset($_REQUEST['search']) ? trim($_REQUEST['search']) : '';

    if ($tipoAcervo === 'arqueologico') {
      require_once APP . 'models/acervoArqueologicoModel.php';
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
    } elseif ($tipoAcervo === 'numismatica') {
      require_once APP . 'models/acervoNumismaticaModel.php';
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
    } else {
      // General
      require_once APP . 'models/acervoGeneralModel.php';
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
    }

    return $this->filtrarAcervoPorPropiedades($all);
  }

  public function get_acervo_general()
  {
    $page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
    $perPage = isset($_REQUEST['per_page']) ? (int)$_REQUEST['per_page'] : 10;
    $offset = ($page - 1) * $perPage;

    $all = $this->get_filtered_acervo_data('general');
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
    $page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
    $perPage = isset($_REQUEST['per_page']) ? (int)$_REQUEST['per_page'] : 10;
    $offset = ($page - 1) * $perPage;

    $all = $this->get_filtered_acervo_data('arqueologico');
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
    $page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
    $perPage = isset($_REQUEST['per_page']) ? (int)$_REQUEST['per_page'] : 10;
    $offset = ($page - 1) * $perPage;

    $all = $this->get_filtered_acervo_data('numismatica');
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

  public function exportar_excel()
  {
    $tipo = isset($_REQUEST['tipo_registro']) ? trim($_REQUEST['tipo_registro']) : 'general';
    $all = $this->get_filtered_acervo_data($tipo);

    $filename = "acervo_" . $tipo . "_" . date('Y-m-d_H-i-s') . ".csv";

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo "\xEF\xBB\xBF";

    $output = fopen('php://output', 'w');

    if ($tipo === 'arqueologico') {
      fputcsv($output, [
        'ID', 
        'Código Interno', 
        'No. Inventario SCYT', 
        'No. Registro INAH', 
        'Otros Registros', 
        'Nombre / Título', 
        'Pieza por Lote', 
        'Época', 
        'Procedencia', 
        'Material', 
        'Medidas', 
        'Forma', 
        'Técnica Manufactura', 
        'Técnica Decorativa', 
        'Colección', 
        'Forma de Obtención', 
        'Ubicación Física', 
        'Estado de Conservación', 
        'Descripción', 
        'Representación', 
        'Observaciones', 
        'Fotografía'
      ]);
      foreach ($all as $p) {
        fputcsv($output, [
          $p['id_acervo_arq'] ?? '-',
          $p['codigo_interno'] ?? '-',
          $p['no_inventario_scyt'] ?? '-',
          $p['no_registro_inah'] ?? '-',
          $p['otros'] ?? '-',
          $p['nombre_titulo_pieza'] ?? '-',
          $p['numero_pieza_por_lote'] ?? '-',
          $p['epoca'] ?? '-',
          $p['procedencia'] ?? '-',
          $p['material'] ?? ($p['Material'] ?? '-'),
          $p['medidas'] ?? '-',
          $p['forma'] ?? '-',
          $p['tecnica_manufactura'] ?? '-',
          $p['tecnica_decorativa'] ?? '-',
          $p['coleccion'] ?? '-',
          $p['obtencion'] ?? '-',
          $p['ubicacion_fisica'] ?? '-',
          $p['estado_conservacion'] ?? '-',
          $p['descripcion'] ?? '-',
          $p['representacion'] ?? '-',
          $p['observaciones'] ?? '-',
          $p['fotografia'] ?? '-'
        ]);
      }
    } elseif ($tipo === 'numismatica') {
      fputcsv($output, [
        'ID', 
        'Código Interno', 
        'No. Inventario', 
        'Tipo de Obra', 
        'Ensayador', 
        'Denominación', 
        'Material', 
        'Época', 
        'Dimensiones (cm)', 
        'Ubicación Física', 
        'Estado de Conservación', 
        'Descripción Cara A', 
        'Descripción Cara B', 
        'Observaciones', 
        'Fotografía'
      ]);
      foreach ($all as $p) {
        fputcsv($output, [
          $p['id_acervo_numismatica'] ?? '-',
          $p['codigo_interno'] ?? '-',
          $p['no_inventario'] ?? '-',
          $p['tipo_obra'] ?? '-',
          $p['ensayador'] ?? '-',
          $p['denominacion'] ?? '-',
          $p['material'] ?? '-',
          $p['fecha_epoca'] ?? '-',
          $p['dimensiones'] ?? '-',
          $p['ubicacion_fisica'] ?? '-',
          $p['estado_conservacion'] ?? '-',
          $p['descripcion_cara_a'] ?? '-',
          $p['descripcion_cara_b'] ?? '-',
          $p['observaciones'] ?? '-',
          $p['fotografia'] ?? '-'
        ]);
      }
    } else {
      fputcsv($output, [
        'ID', 
        'Código Interno', 
        'No. Inventario', 
        'Nombre / Título', 
        'Centímetros (cm)', 
        'Materia', 
        'Autor', 
        'Año', 
        'Época', 
        'Técnica', 
        'Origen', 
        'Material', 
        'Medidas', 
        'Lote', 
        'Peso (kg)', 
        'Colección', 
        'Tipo de Obra', 
        'Ubicación Física', 
        'Estado de Conservación', 
        'Descripción', 
        'Observaciones', 
        'Fotografía'
      ]);
      foreach ($all as $p) {
        fputcsv($output, [
          $p['id_acervo_general'] ?? '-',
          $p['codigo_interno'] ?? '-',
          $p['no_inventario'] ?? '-',
          $p['nombre_titulo_pieza'] ?? '-',
          $p['cm'] ?? '-',
          $p['materia'] ?? '-',
          $p['autor'] ?? '-',
          $p['anio'] ?? '-',
          $p['epoca'] ?? '-',
          $p['tecnica'] ?? '-',
          $p['origen'] ?? '-',
          $p['material'] ?? '-',
          $p['medidas'] ?? '-',
          $p['lote'] ?? '-',
          $p['peso'] ?? '-',
          $p['coleccion'] ?? '-',
          $p['tipo'] ?? '-',
          $p['ubicacion_fisica'] ?? '-',
          $p['estado_conservacion'] ?? '-',
          $p['descripcion'] ?? '-',
          $p['observaciones'] ?? '-',
          $p['fotografia'] ?? '-'
        ]);
      }
    }

    fclose($output);
    exit;
  }

  public function exportar_pdf()
  {
    // Aumentar límites temporales para el procesamiento de PDF
    ini_set('memory_limit', '512M');
    set_time_limit(180);

    $tipo = isset($_REQUEST['tipo_registro']) ? trim($_REQUEST['tipo_registro']) : 'general';
    $all = $this->get_filtered_acervo_data($tipo);

    $totalRegistros = count($all);
    $limit = 1000;
    if ($totalRegistros > $limit) {
      $all = array_slice($all, 0, $limit);
      $aviso = "Mostrando los primeros " . number_format($limit) . " registros de un total de " . number_format($totalRegistros) . " (use los filtros de búsqueda en el sistema para limitar los resultados).";
    } else {
      $aviso = "Total de registros: " . number_format($totalRegistros);
    }

    $titulo = "Reporte de Acervo - " . ucfirst($tipo === 'numismatica' ? 'Numismático' : ($tipo === 'arqueologico' ? 'Arqueológico' : 'General'));

    $html = '
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="UTF-8">
      <title>' . $titulo . '</title>
      <style>
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; color: #4e73df; }
        .header p { margin: 5px 0 0 0; font-size: 12px; color: #666; }
        .aviso { font-size: 11px; font-weight: bold; color: #e74a3b; text-align: center; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; vertical-align: top; word-wrap: break-word; overflow: hidden; }
        th { background-color: #f2f2f2; font-weight: bold; color: #333; }
        .footer { position: fixed; bottom: -20px; left: 0px; right: 0px; height: 20px; text-align: center; font-size: 8px; color: #999; }
      </style>
    </head>
    <body>
      <div class="header">
        <h1>' . $titulo . '</h1>
        <p>Generado el ' . date("d/m/Y H:i:s") . '</p>
      </div>
      <div class="aviso">' . $aviso . '</div>
      <table>
        <thead>';

    if ($tipo === 'arqueologico') {
      $html .= '
          <tr>
            <th style="width: 15%;">Código Interno</th>
            <th style="width: 25%;">Nombre</th>
            <th style="width: 15%;">No. INAH</th>
            <th style="width: 15%;">Procedencia</th>
            <th style="width: 30%;">Descripción</th>
          </tr>
        </thead>
        <tbody>';
      foreach ($all as $p) {
        $html .= '
          <tr>
            <td>' . htmlspecialchars($p['codigo_interno'] ?? '-') . '</td>
            <td>' . htmlspecialchars($p['nombre_titulo_pieza'] ?? '-') . '</td>
            <td>' . htmlspecialchars($p['no_registro_inah'] ?? '-') . '</td>
            <td>' . htmlspecialchars($p['procedencia'] ?? '-') . '</td>
            <td>' . htmlspecialchars($p['descripcion'] ?? '-') . '</td>
          </tr>';
      }
    } elseif ($tipo === 'numismatica') {
      $html .= '
          <tr>
            <th style="width: 15%;">Código Interno</th>
            <th style="width: 25%;">Denominación</th>
            <th style="width: 20%;">Ubicación Física</th>
            <th style="width: 15%;">Material</th>
            <th style="width: 15%;">Época</th>
            <th style="width: 10%;">Estado</th>
          </tr>
        </thead>
        <tbody>';
      foreach ($all as $p) {
        $html .= '
          <tr>
            <td>' . htmlspecialchars($p['codigo_interno'] ?? '-') . '</td>
            <td>' . htmlspecialchars($p['denominacion'] ?? '-') . '</td>
            <td>' . htmlspecialchars($p['ubicacion_fisica'] ?? '-') . '</td>
            <td>' . htmlspecialchars($p['material'] ?? '-') . '</td>
            <td>' . htmlspecialchars($p['fecha_epoca'] ?? '-') . '</td>
            <td>' . htmlspecialchars($p['estado_conservacion'] ?? '-') . '</td>
          </tr>';
      }
    } else {
      $html .= '
          <tr>
            <th style="width: 15%;">Código Interno</th>
            <th style="width: 25%;">Nombre</th>
            <th style="width: 15%;">Autor</th>
            <th style="width: 15%;">Materia</th>
            <th style="width: 30%;">Descripción</th>
          </tr>
        </thead>
        <tbody>';
      foreach ($all as $p) {
        $html .= '
          <tr>
            <td>' . htmlspecialchars($p['codigo_interno'] ?? '-') . '</td>
            <td>' . htmlspecialchars($p['nombre_titulo_pieza'] ?? '-') . '</td>
            <td>' . htmlspecialchars($p['autor'] ?? '-') . '</td>
            <td>' . htmlspecialchars($p['materia'] ?? '-') . '</td>
            <td>' . htmlspecialchars($p['descripcion'] ?? '-') . '</td>
          </tr>';
      }
    }

    $html .= '
        </tbody>
      </table>
    </body>
    </html>';

    require_once APP . 'classes/BeePdf.php';
    $pdf = new BeePdf();
    $pdf->streamPdf(true);
    $pdf->setOrientation('landscape');
    $pdf->create('reporte_acervo_' . $tipo, $html, true);
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
  }

  public function ficha_tecnica()
  {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $tipo = isset($_GET['tipo_acervo']) ? trim($_GET['tipo_acervo']) : '';

    if ($id <= 0 || empty($tipo)) {
      die('ID o tipo de acervo inválidos.');
    }

    $pieza = null;
    $tipoLabel = '';

    if ($tipo === 'arqueologico') {
      require_once APP . 'models/acervoArqueologicoModel.php';
      $rows = AcervoArqueologicoModel::getById($id);
      if (!empty($rows)) {
        $pieza = $rows[0];
        $tipoLabel = 'Acervo Arqueológico';
      }
    } elseif ($tipo === 'numismatica') {
      require_once APP . 'models/acervoNumismaticaModel.php';
      $rows = AcervoNumismaticaModel::getById($id);
      if (!empty($rows)) {
        $pieza = $rows[0];
        $tipoLabel = 'Acervo Numismático';
      }
    } else {
      require_once APP . 'models/acervoGeneralModel.php';
      $rows = AcervoGeneralModel::getById($id);
      if (!empty($rows)) {
        $pieza = $rows[0];
        $tipoLabel = 'Acervo General';
      }
    }

    if (!$pieza) {
      die('No se encontró el registro especificado.');
    }

    // Procesar la imagen en base64 para Dompdf
    $imgHtml = '<div class="no-img">Sin imagen</div>';
    if (!empty($pieza['fotografia']) && file_exists(UPLOADS . $pieza['fotografia'])) {
      $imgData = base64_encode(file_get_contents(UPLOADS . $pieza['fotografia']));
      $imgSrc = 'data:image/' . pathinfo($pieza['fotografia'], PATHINFO_EXTENSION) . ';base64,' . $imgData;
      $imgHtml = '<img src="' . $imgSrc . '" alt="Imagen de la pieza" class="img-pieza" />';
    }

    $titulo = "Ficha Técnica - " . ($pieza['nombre_titulo_pieza'] ?? $pieza['denominacion'] ?? 'Pieza sin título');

    // Mapeo de propiedades a mostrar según el tipo de acervo
    $propiedades = [];
    $descripciones = [];

    if ($tipo === 'arqueologico') {
      $propiedades = [
        'Código Interno' => $pieza['codigo_interno'] ?? '-',
        'No. Inventario SCYT' => $pieza['no_inventario_scyt'] ?? '-',
        'No. Registro INAH' => $pieza['no_registro_inah'] ?? '-',
        'Otros Registros' => $pieza['otros'] ?? '-',
        'Pieza por Lote' => $pieza['numero_pieza_por_lote'] ?? '-',
        'Época' => $pieza['epoca'] ?? '-',
        'Procedencia' => $pieza['procedencia'] ?? '-',
        'Material' => $pieza['material'] ?? '-',
        'Medidas' => $pieza['medidas'] ?? '-',
        'Forma' => $pieza['forma'] ?? '-',
        'Técnica Manufactura' => $pieza['tecnica_manufactura'] ?? '-',
        'Técnica Decorativa' => $pieza['tecnica_decorativa'] ?? '-',
        'Colección' => $pieza['coleccion'] ?? '-',
        'Forma de Obtención' => $pieza['obtencion'] ?? '-',
        'Ubicación Física' => $pieza['ubicacion_fisica'] ?? '-',
        'Estado de Conservación' => $pieza['estado_conservacion'] ?? '-',
      ];
      $descripciones = [
        'Descripción' => $pieza['descripcion'] ?? '-',
        'Representación' => $pieza['representacion'] ?? '-',
        'Observaciones' => $pieza['observaciones'] ?? '-',
      ];
    } elseif ($tipo === 'numismatica') {
      $propiedades = [
        'Código Interno' => $pieza['codigo_interno'] ?? '-',
        'Número de Inventario' => $pieza['no_inventario'] ?? '-',
        'Tipo de Obra' => $pieza['tipo_obra'] ?? '-',
        'Ensayador' => $pieza['ensayador'] ?? '-',
        'Denominación' => $pieza['denominacion'] ?? '-',
        'Material' => $pieza['material'] ?? '-',
        'Época' => $pieza['fecha_epoca'] ?? '-',
        'Dimensiones (cm)' => $pieza['dimensiones'] ?? '-',
        'Ubicación Física' => $pieza['ubicacion_fisica'] ?? '-',
        'Estado de Conservación' => $pieza['estado_conservacion'] ?? '-',
      ];
      $descripciones = [
        'Descripción Cara A' => $pieza['descripcion_cara_a'] ?? '-',
        'Descripción Cara B' => $pieza['descripcion_cara_b'] ?? '-',
        'Observaciones' => $pieza['observaciones'] ?? '-',
      ];
    } else {
      // General
      $propiedades = [
        'Código Interno' => $pieza['codigo_interno'] ?? '-',
        'No. Inventario' => $pieza['no_inventario'] ?? '-',
        'Materia' => $pieza['materia'] ?? '-',
        'Autor' => $pieza['autor'] ?? '-',
        'Año' => $pieza['anio'] ?? '-',
        'Época' => $pieza['epoca'] ?? '-',
        'Técnica' => $pieza['tecnica'] ?? '-',
        'Origen' => $pieza['origen'] ?? '-',
        'Material' => $pieza['material'] ?? '-',
        'Medidas' => $pieza['medidas'] ?? '-',
        'Lote' => $pieza['lote'] ?? '-',
        'Peso' => $pieza['peso'] ?? '-',
        'Colección' => $pieza['coleccion'] ?? '-',
        'Tipo de obra' => $pieza['tipo'] ?? '-',
        'Ubicación Física' => $pieza['ubicacion_fisica'] ?? '-',
        'Estado de Conservación' => $pieza['estado_conservacion'] ?? '-',
      ];
      $descripciones = [
        'Descripción' => $pieza['descripcion'] ?? '-',
        'Observaciones' => $pieza['observaciones'] ?? '-',
      ];
    }

    $html = '
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="UTF-8">
      <title>' . $titulo . '</title>
      <style>
        @page { margin: 25px; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .container { border: 2px solid #333; padding: 15px; position: relative; min-height: 94%; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 8px; margin-bottom: 15px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; letter-spacing: 1px; color: #1a1a1a; }
        .header h2 { margin: 3px 0 0 0; font-size: 12px; font-weight: normal; color: #555; text-transform: uppercase; }
        
        .main-grid { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .img-col { width: 45%; text-align: center; vertical-align: middle; border: 1px solid #ccc; padding: 10px; background-color: #fafafa; }
        .img-pieza { max-width: 100%; max-height: 250px; object-fit: contain; }
        .no-img { font-size: 13px; color: #999; font-style: italic; padding: 60px 0; }
        
        .data-col { width: 55%; vertical-align: top; padding-left: 15px; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table td { padding: 4px 5px; border-bottom: 1px solid #eee; font-size: 10.5px; }
        .label { font-weight: bold; color: #111; width: 45%; }
        .value { color: #444; width: 55%; }
        
        .section-title { font-size: 11px; font-weight: bold; background-color: #e9e9e9; padding: 4px 6px; border-left: 3px solid #333; margin-top: 15px; margin-bottom: 8px; text-transform: uppercase; }
        
        .text-block { margin-bottom: 8px; padding: 0 5px; }
        .text-title { font-weight: bold; margin-bottom: 2px; color: #222; text-decoration: underline; }
        .text-content { text-align: justify; color: #444; }
        
        .footer { text-align: center; font-size: 8px; color: #777; border-top: 1px solid #ccc; padding-top: 5px; margin-top: 25px; }
      </style>
    </head>
    <body>
      <div class="container">
        <div class="header">
          <h1>Ficha Técnica de Registro</h1>
          <h2>' . htmlspecialchars($tipoLabel) . '</h2>
        </div>
        
        <table class="main-grid">
          <tr>
            <td class="img-col">' . $imgHtml . '</td>
            <td class="data-col">
              <table class="data-table">';
              
    foreach ($propiedades as $lbl => $val) {
      $html .= '
                <tr>
                  <td class="label">' . htmlspecialchars($lbl) . '</td>
                  <td class="value">' . htmlspecialchars($val) . '</td>
                </tr>';
    }

    $html .= '
              </table>
            </td>
          </tr>
        </table>
        
        <div class="section-title">Detalles y Descripciones</div>';
        
    foreach ($descripciones as $title => $content) {
      if (!empty($content) && $content !== '-') {
        $html .= '
        <div class="text-block">
          <div class="text-title">' . htmlspecialchars($title) . '</div>
          <div class="text-content">' . nl2br(htmlspecialchars($content)) . '</div>
        </div>';
      }
    }

    $html .= '
        <div class="footer">
          Sistema de Inventario Para el Acervo Cultural - Ficha de Registro Oficial - Generado el ' . date('d/m/Y H:i') . '
        </div>
      </div>
    </body>
    </html>';

    require_once APP . 'classes/BeePdf.php';
    $pdf = new BeePdf();
    $pdf->streamPdf(true);
    $pdf->setOrientation('portrait');
    $pdf->create('ficha_tecnica_' . $tipo . '_' . $id, $html, false);
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
      'name' => 'materia',
      'label' => 'Materia',
      'id' => 'materia',
      'class' => 'form-control',
      'required' => false,
      'default_value' => '',
      'placeholder' => 'Ej. Óleo sobre lienzo',
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
      'type' => 'text',
      'name' => 'origen',
      'label' => 'Origen',
      'id' => 'origen',
      'class' => 'form-control',
      'required' => false,
      'default_value' => '',
      'placeholder' => 'Ej. México',
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