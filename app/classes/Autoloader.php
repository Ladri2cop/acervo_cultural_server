<?php

class Autoloader
{
  /**
   * Método encargado de ejecutar el autocargador de forma estática
   *
   * @return void
   */
  public static function init()
  {
    spl_autoload_register([__CLASS__, 'autoload']);
  }

  /**
   * Se ejecuta cada que se requiere cargar una clase
   *
   * @param string $class_name
   * @return void
   */
  private static function autoload($class_name)
  {
    $candidates = [$class_name, lcfirst($class_name)];
    foreach ($candidates as $name) {
      if (is_file(CLASSES . $name . '.php')) {
        require_once CLASSES . $name . '.php';
        return;
      } elseif (is_file(CONTROLLERS . $name . '.php')) {
        require_once CONTROLLERS . $name . '.php';
        return;
      } elseif (is_file(MODELS . $name . '.php')) {
        require_once MODELS . $name . '.php';
        return;
      }
    }
  }
}
