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
      Redirect::to('admin');
    } else {
      Redirect::to('login');
    }
  }
}
