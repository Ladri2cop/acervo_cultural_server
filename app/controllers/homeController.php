<?php

class homeController extends Controller implements ControllerInterface
{
  function __construct()
  {
    // Ejecutar la funcionalidad del Controller padre
    parent::__construct();
  }

  function index()
  {
    if (Auth::validate()) {
      if (is_admin_user()) {
        Redirect::to('admin');
      } else {
        Redirect::to('admin/acervo');
      }
    } else {
      Redirect::to('login');
    }
  }
}
