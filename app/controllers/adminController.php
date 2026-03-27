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
    // Formulario para agregar nuevo registro
    // $form = new BeeFormBuilder('agregar-producto', 'agregar-producto', ['needs-validation'], 'admin/post_productos', true, true);

    // // Inputs
    // $form->addCustomFields(insert_inputs());
    // $form->addTextField('nombre', 'Nombre del producto', ['form-control'], 'product-name', true);
    // $form->addTextField('sku', 'SKU o número de rastreo', ['form-control'], 'product-sku');
    // $form->addTextareaField('descripcion', 'Descripción del producto', 4, 5, ['form-control'], 'product-description');
    // $form->addNumberField('precio', 'Precio principal', 1, 999999999, 'any', null, ['form-control'], 'product-price', true);
    // $form->addNumberField('precio_comparacion', 'Precio de comparación', 1, 999999999, 'any', null, ['form-control'], 'product-compare-price');

    // $form->addFileField('imagen', 'Imagen principal del producto', ['form-control'], 'product-imagen', true);

    // $form->addCustomFields('<hr>');

    // $form->addCheckboxField('rastrear_stock', 'Seguimiento de stock', 'true', ['form-check-input'], 'trackStock', false);
    // $form->addNumberField('stock', 'Unidades disponibles', 1, 999999999, 1, null, ['form-control'], 'stock', false);

    // $form->addButton('submit', 'submit', 'Agregar producto', ['btn btn-success'], 'submit-button');

    $this->setTitle('Acervo');
    // $this->addToData('form', $form->getFormHtml());
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
    // Formulario para agregar nuevo registro
    // $form = new BeeFormBuilder('nuevo-registro', 'nuevo-registro', ['needs-validation'], 'admin/post_catalogos', true, false);

    // // Inputs
    // $form->addCustomFields(insert_inputs());
    // $form->addTextField('nombre', 'Nombre la pieza', ['form-control'], 'product-name', true);
    // $form->addTextField('sku', 'Número de inventario', ['form-control'], 'product-sku', true);
    // $form->addTextareaField('descripcion', 'Descripción de la pieza', 4, 5, ['form-control'], 'product-description');
    // $form->addNumberField('precio', 'Precio principal', 1, 999999999, 'any', null, ['form-control'], 'product-price', true);
    // $form->addNumberField('precio_comparacion', 'Precio de comparación', 1, 999999999, 'any', null, ['form-control'], 'product-compare-price');

    // $form->addFileField('imagen', 'Imagen principal del producto', ['form-control'], 'product-imagen', true);

    // $form->addCustomFields('<hr>');

    // $form->addCheckboxField('rastrear_stock', 'Seguimiento de stock', 'true', ['form-check-input'], 'trackStock', false);
    // $form->addNumberField('stock', 'Unidades disponibles', 1, 999999999, 1, null, ['form-control'], 'stock', false);

    // $form->addButton('submit', 'submit', 'Registrar pieza', ['btn btn__add'], 'submit-button');

    $this->setTitle('Catálogos');
    // $this->addToData('form', $form->getFormHtml());
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
  // function registrar()
  // {
  //   $form = new BeeFormBuilder('nuevo-registro', 'nuevo-registro', ['needs-validation'], 'admin/post_catalogos', true, false);

  //   // Inputs
  //   $form->addCustomFields(insert_inputs());
  //   $form->addTextField('nombre', 'Nombre de la pieza', ['form-control'], 'product-name', true, '', 'Ej. Vasija prehispánica');
  //   $form->addTextField('sku', 'Número de inventario', ['form-control'], 'product-sku', true, '', 'Ej. 0001');
  //   $form->addTextareaField('descripcion', 'Descripción de la pieza', 4, 5, ['form-control'], 'product-description', true, '', 'Descripción detallada de la pieza');
  //   $form->addNumberField('precio', 'Precio principal', 1, 999999999, 'any', null, ['form-control'], 'product-price', true, '0.00');
  //   $form->addNumberField('precio_comparacion', 'Precio de comparación', 1, 999999999, 'any', '', ['form-control'], 'product-compare-price', true, '0.00');

  //   $form->addFileField('imagen', 'Imagen principal del producto', ['form-control'], 'product-imagen', true);

  //   $form->addCustomFields('<hr>');

  //   $form->addCheckboxField('rastrear_stock', 'Seguimiento de stock', 'true', ['form-check-input'], 'trackStock', false);
  //   $form->addNumberField('unidades_disponibles', 'Unidades disponibles', 1, 999999999, 'any', null, ['form-control'], 'stock', false, '0');

  //   $form->addButton('submit', 'submit', 'Registrar pieza', ['btn btn__add'], 'submit-button');

  //   $this->addToData('form', $form->getFormHtml());
  //   $this->setTitle('Registrar Acervo');
  //   $this->setView('registrar/registrar');
  //   $this->render();
  // }
  // Función para insertar inputs personalizados

  public function get_formulario_tipo()
  {
    $tipo = $_POST['tipo_acervo'] ?? '';
    $campos = [];

    switch ($tipo) {
      case 1:
        $campos = obtenerCamposAcervoGeneral();
        break;
      case 2:
        $campos = obtenerCamposAcervoToluca();
        break;
      case 3:
        $campos = obtenerCamposAcervoMetepec();
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
    $form = new BeeFormBuilder('nuevo-registro', 'nuevo-registro', ['needs-validation'], 'admin/post_registro', true, false);
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

    // $form->addCustomFields(insert_inputs());
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

    // Validación básica
    // if (empty($data['codigo_interno']) || empty($data['no_inventario']) || empty($data['nombre_titulo_pieza'])) {
    //   header('Content-Type: application/json');
    //   echo json_encode([
    //     'status' => 400,
    //     'msg' => 'Faltan campos obligatorios.'
    //   ]);
    //   exit;
    // }

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
      'label' => 'Centímetros (idk)', // Este campo es un misterio, pero lo dejamos por si acaso
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

function obtenerCamposAcervoToluca()
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
      'label' => 'Nombre o título de la pieza',
      'id' => 'nombre-titulo-pieza',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. Escultura de barro',
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
      'name' => 'epoca',
      'label' => 'Época',
      'id' => 'epoca',
      'class' => 'form-select',
      'required' => false,
      'options' => ['Prehispánica', 'Colonial', 'Moderna', 'Contemporánea'],
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'number',
      'name' => 'alto',
      'label' => 'Alto (cm)',
      'id' => 'alto',
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
      'type' => 'number',
      'name' => 'ancho',
      'label' => 'Ancho (cm)',
      'id' => 'ancho',
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
      'type' => 'number',
      'name' => 'profundidad',
      'label' => 'Profundidad (cm)',
      'id' => 'profundidad',
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
      'type' => 'text',
      'name' => 'status_estado',
      'label' => 'Estado actual',
      'id' => 'status-estado',
      'class' => 'form-control',
      'required' => false,
      'default_value' => '',
      'placeholder' => 'Ej. En exhibición',
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
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

function obtenerCamposAcervoMetepec()
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
      'name' => 'nombre_pieza',
      'label' => 'Nombre de la pieza',
      'id' => 'nombre-pieza',
      'class' => 'form-control',
      'required' => true,
      'default_value' => '',
      'placeholder' => 'Ej. Escultura de barro',
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
      'name' => 'tipo_obra',
      'label' => 'Tipo de obra',
      'id' => 'tipo-obra',
      'class' => 'form-select',
      'required' => true,
      'options' => ['Pintura', 'Escultura', 'Fotografía', 'Objeto'],
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
      'name' => 'epoca',
      'label' => 'Época',
      'id' => 'epoca',
      'class' => 'form-select',
      'required' => false,
      'options' => ['Prehispánica', 'Colonial', 'Moderna', 'Contemporánea'],
      'column_class' => 'col-12 col-sm-6 col-lg-4 mb-3'
    ],
    [
      'type' => 'number',
      'name' => 'alto',
      'label' => 'Alto (cm)',
      'id' => 'alto',
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
      'type' => 'number',
      'name' => 'ancho',
      'label' => 'Ancho (cm)',
      'id' => 'ancho',
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
      'type' => 'number',
      'name' => 'profundidad',
      'label' => 'Profundidad (cm)',
      'id' => 'profundidad',
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
      'type' => 'select',
      'name' => 'coleccion',
      'label' => 'Colección',
      'id' => 'coleccion',
      'class' => 'form-select',
      'required' => false,
      'options' => ['Colección permanente', 'Colección temporal', 'Donación'],
      'column_class' => 'col-12 mb-3'
    ]
  ];

  return $campos;
}
