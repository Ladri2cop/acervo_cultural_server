<?php

class BeeFormBuilder
{
  /**
   * El código html final del formulario
   *
   * @var string
   */
  private $formHtml     = '';

  /**
   * El nombre del formulario, puede ser usado para seleccionar por javascript
   *
   * @var string
   */
  private $formName     = '';

  /**
   * El identificador único del formulario
   *
   * @var string
   */
  private $id           = '';

  /**
   * La ruta de acción del formulario, dónde enviará la información
   *
   * @var string
   */
  private $action       = '';

  /**
   * Todas las clases del formulario
   *
   * @var array
   */
  private $classes      = [];

  /**
   * El método a utilizar, puede ser sólo GET o POST
   *
   * @var string
   */
  private $method       = '';

  /**
   * Método de encriptación o content-type para enviar la información
   *
   * @var string
   */
  private $encType      = '';

  /**
   * Todos los campos personalizados insertados
   *
   * @var array
   */
  private $customFields = [];

  /**
   * Todos los campos agregados al formulario
   *
   * @var array
   */
  private $fields       = [];

  /***
   * Todos los botones registrados para el formulario
   */
  private $buttons      = [];

  /**
   * Todos los campos de tipo file registrados para el formulario
   *
   * @var array
   */
  private $files        = [];

  /**
   * Todos los campos de tipo range o slider registrados para el formulario
   *
   * @var array
   */
  private $sliders      = [];

  // TODO: Agregar los campos de seguridad de forma dinámica: CSRF etc
  // TODO: Agregar textos de ayuda abajo de los campos por si es requerido
  // TODO: Agregar grupos de inputs en los formularios (para que no se vea tan líneal el formulario)

  /**
   * Inicialización del formulario
   *
   * @param string $name
   * @param string $id
   * @param array $classes
   * @param string $action
   * @param boolean $post
   * @param boolean $sendFiles
   */
  function __construct($name, $id = null, $classes = [], $action = null, $post = true, $sendFiles = false)
  {
    $this->formName = $name;
    $this->id       = $id;
    $this->classes  = $classes;
    $this->action   = $action;
    $this->method   = $post === true ? 'POST' : 'GET';
    $this->encType  = $sendFiles === true ? 'multipart/form-data' : '';
  }

  public function addField(array $field)
  {
    $this->fields[] = $field;
  }

  /**
   * Agrega un botón al formulario
   *
   * @param string $name
   * @param string $type
   * @param string $value
   * @param array $classes
   * @param string $id
   * @return void
   */
  public function addButton($name, $type, $value, $classes = [], $id = null)
  {
    $button =
      [
        'name'    => $name,
        'type'    => $type,
        'value'   => $value,
        'id'      => $id,
        'classes' => $classes
      ];

    $this->buttons[] = $button;
  }

  /**
   * Agrega un campo personalizado o varios, básicamente inserta código html en el formulario
   *
   * @param string $fields
   * @return void
   */
  function addCustomFields($fields)
  {
    $customField =
      [
        'name'         => null,
        'type'         => 'custom',
        'label'        => null,
        'classes'      => [],
        'id'           => null,
        'options'      => [],
        'defaultValue' => null,
        'required'     => null,
        'content'      => $fields
      ];

    $this->fields[]       = $customField;

    $this->customFields[] = $fields;
  }


  /**
   * Procesa todos los campos y elementos agregados del formulario
   *
   * @return void
   */
  private function buildForm()
  {
    $this->formHtml = sprintf(
      '<form id="%s" data-form-name="%s" class="%s" action="%s" method="%s" %s>',
      $this->id,
      $this->formName,
      implode(' ', $this->classes),
      $this->action,
      $this->method,
      !empty($this->encType) ? sprintf('enctype="%s"', $this->encType) : ''
    );

    $this->formHtml .= '<div class="row">'; //Aquí quiero que se defina el tamaño de las columnas

    foreach ($this->fields as $field) {
      $fieldName    = $field['name'];
      $fieldType    = $field['type'];
      $fieldLabel   = $field['label'];
      $fieldClasses = $field['class'];
      $fieldId      = $field['id'];
      $fieldOptions = $field['options'] ?? [];
      $fieldValue   = $field['value'] ?? '';
      $defaultValue = $field['defaultValue'] ?? '';
      $required     = $field['required'] ? 'required' : '';
      $placeholder  = !empty($field['placeholder']) ? sprintf('placeholder="%s"', htmlspecialchars($field['placeholder'])) : '';

      $specialTypes = ['hidden', 'checkbox', 'radio', 'custom'];

      if (!in_array($fieldType, $specialTypes)) {
        $columnClass = !empty($field['column_class'])
          ? $field['column_class']
          : (!empty($field['full_width']) && $field['full_width'] === true
            ? 'col-12 mb-3'
            : 'col-12 col-md-6 col-lg-4 mb-3');

        $this->formHtml .= '<div class="' . $columnClass . '">';
        $this->formHtml .= sprintf(
          '<label for="%s">%s%s</label>',
          $fieldId,
          $fieldLabel,
          $required ? ' <span class="text-danger">*</span>' : ''
        );
      }

      switch ($fieldType) {
        case 'select':
          $this->formHtml .= sprintf(
            '<select name="%s" id="%s" class="%s" %s>',
            $fieldName,
            $fieldId,
            $fieldClasses,
            $required
          );
          foreach ($fieldOptions as $key => $value) {
            $optionValue = is_numeric($key) ? $value : $key;
            $optionLabel = $value;
            $selected = ($optionValue === $defaultValue) ? 'selected' : '';
            $this->formHtml .= sprintf(
              '<option value="%s" %s>%s</option>',
              htmlspecialchars($optionValue),
              $selected,
              htmlspecialchars($optionLabel)
            );
          }
          $this->formHtml .= '</select>';
          break;

        case 'textarea':
          $this->formHtml .= sprintf(
            '<textarea name="%s" id="%s" rows="%s" cols="%s" class="%s" %s %s>%s</textarea>',
            $fieldName,
            $fieldId,
            $field['rows'],
            $field['cols'],
            $fieldClasses,
            $required,
            $placeholder,
            htmlspecialchars($defaultValue)
          );
          break;

        case 'checkbox':
        case 'radio':
          $checked = !empty($field['checked']) ? 'checked' : '';
          $this->formHtml .= '<div class="form-check mb-3">';
          $this->formHtml .= sprintf(
            '<input type="%s" name="%s" id="%s" value="%s" %s class="%s" %s>',
            $fieldType,
            $fieldName,
            $fieldId,
            $fieldValue,
            $checked,
            $fieldClasses,
            $required
          );
          $this->formHtml .= sprintf(
            '<label class="form-check-label" for="%s">%s</label>',
            $fieldId,
            $fieldLabel
          );
          $this->formHtml .= '</div>';
          break;

        case 'hidden':
        case 'email':
        case 'password':
        case 'url':
        case 'phone':
        case 'text':
          $this->formHtml .= sprintf(
            '<input type="%s" name="%s" id="%s" class="%s" value="%s" %s %s>',
            $fieldType,
            $fieldName,
            $fieldId,
            $fieldClasses,
            htmlspecialchars($defaultValue),
            $required,
            $placeholder
          );
          break;

        case 'file':
          $this->formHtml .= sprintf(
            '<input type="file" name="%s" id="%s" class="%s" %s>',
            $fieldName,
            $fieldId,
            $fieldClasses,
            $required
          );
          break;

        case 'slider':
          $sliderValue = $field['defaultValue'] ?? $field['min'];
          $this->formHtml .= sprintf(
            '<input type="range" name="%s" id="%s" min="%s" max="%s" step="%s" value="%s" class="%s" %s>',
            $fieldName,
            $fieldId,
            $field['min'],
            $field['max'],
            $field['step'],
            $sliderValue,
            $fieldClasses,
            $required
          );
          break;

        case 'custom':
          $this->formHtml .= $field['content'];
          break;

        case 'color':
          $this->formHtml .= sprintf(
            '<input type="color" name="%s" id="%s" value="%s" class="%s" %s>',
            $fieldName,
            $fieldId,
            $defaultValue,
            $fieldClasses,
            $required
          );
          break;

        case 'number':
          $defaultValue = $field['defaultValue'] ?? '';
          $valueAttr = $defaultValue !== '' ? 'value="' . htmlspecialchars($defaultValue) . '"' : '';
          $placeholderAttr = !empty($field['placeholder']) ? 'placeholder="' . htmlspecialchars($field['placeholder']) . '"' : '';

          $this->formHtml .= sprintf(
            '<input type="number" name="%s" id="%s" %s min="%s" max="%s" step="%s" class="%s" %s %s>',
            $fieldName,
            $fieldId,
            $valueAttr,
            $field['min'],
            $field['max'],
            $field['step'],
            $fieldClasses,
            $required,
            $placeholderAttr
          );

          break;

        case 'date':
          $valueAttr = $defaultValue !== '' ? 'value="' . htmlspecialchars($defaultValue) . '"' : '';
          $this->formHtml .= sprintf(
            '<input type="date" name="%s" id="%s" %s class="%s" %s>',
            $fieldName,
            $fieldId,
            $valueAttr,
            $fieldClasses,
            $required
          );
          break;
      }

      if (!in_array($fieldType, $specialTypes)) {
        $this->formHtml .= '</div>';
      }
    }

    $this->formHtml .= '</div>'; // cerrar .row

    foreach ($this->buttons as $button) {
      $buttonName    = $button['name'];
      $buttonType    = $button['type'];
      $buttonValue   = $button['value'];
      $buttonClasses = implode(' ', $button['classes']);
      $buttonId      = $button['id'];

      $this->formHtml .= sprintf(
        '<button type="%s" name="%s" id="%s" class="%s">%s</button>',
        $buttonType,
        $buttonName,
        $buttonId,
        $buttonClasses,
        $buttonValue
      );
    }

    $this->formHtml .= '</form>';

    return $this->formHtml;
  }

  /**
   * Regresa todo el código html del formulario listo para ser utilizado
   *
   * @return string
   */
  function getFormHtml()
  {
    $this->buildForm();

    return $this->formHtml;
  }

  /**
   * Renderiza en pantalla el formulario
   *
   * @return void
   */
  function renderForm()
  {
    echo $this->getFormHtml();
  }

  /**
   * Regresa el nombre de nuestro formulario formateado para usar
   * en Javascript sin problema alguno
   *
   * @return string
   */
  private function formatFormName()
  {
    // Remover espacios, guiones "-" y otros caracteres no permitidos en nombres de funciones
    $formattedId = preg_replace('/[^a-zA-Z0-9_]/', '_', $this->formName);

    // Asegurarse de que el nombre de la función comience con una letra o guión bajo "_"
    $formattedId = preg_replace('/^[^a-zA-Z_]/', '_', $formattedId);

    return $formattedId;
  }

  /**
   * Genera de forma dinámica un bloque de código javascript con una función encargada de envia la información
   * a una ruta específica
   *
   * @param string $url
   * @param string $accessToken
   * @param boolean $addEventListener
   * @return string
   */
  public function generateFetchScript($url, $accessToken = null, $addEventListener = true)
  {
    $functionName = sprintf('submitForm_%s', $this->formatFormName());
    $script  = "<script>";
    $script .= $addEventListener ? "document.getElementById('%s').addEventListener('submit', %s);" : "";
    $script .=
      "async function %s(e) {
      e.preventDefault();
      const form     = document.getElementById('%s');
      const formData = new FormData(form);
      const res      = await fetch('%s', {
        %s
        method     : 'POST',
        body       : formData
      })
      .then(res => res.json())
      .catch(err => console.log(err));

      if (res.status === 200) {
        toastr.success(res.msg, '¡Excelente!');
        form.reset();
        form.classList.remove('was-validated');
      } else {
        toastr.error(res.msg, '¡Hubo un error!');
      }
    }";
    $script .= "</script>";

    return sprintf(
      $script,
      $this->id,
      $functionName,
      $functionName,
      $this->id,
      $url,
      $accessToken !== null ? sprintf(
        'headers: { "Authorization": "Bearer %s"},',
        $accessToken
      ) : ''
    );
  }
}

function agregarCamposDinamicos($form, $campos)
{
  foreach ($campos as $campo) {
    $form->addField($campo);
  }
}
