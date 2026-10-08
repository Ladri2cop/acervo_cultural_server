<?php

class acervoArqueologicoController extends Controller
{
    public function index()
    {
        $piezas = AcervoArqueologicoModel::getAll();
        View::render('acervoArqueologico', ['piezas' => $piezas]);
    }

    // Endpoint para agregar pieza vía AJAX/JSON
    public function api_agregar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['codigo_interno'])) {
            $data = $_POST;
            $errores = $this->validarDatosPieza($data);
            if (!empty($errores)) {
                echo json_encode(['success' => false, 'error' => implode(' ', $errores)]);
                return;
            }

            $id_modulo = AcervoArqueologicoModel::getModuloId();
            $data['id_modulo'] = $id_modulo;
            $id = AcervoArqueologicoModel::addPieza($data);

            // Auditoría
            $auditoria = [
                'id_usuario' => 1,
                'id_modulo' => $id_modulo,
                'nombre_tabla' => 'acervo_arqueologico',
                'id_pieza' => $id,
                'tipo_accion' => 'INSERT',
                'observaciones' => 'Registro de nueva pieza: ' . $data['codigo_interno']
            ];
            // Registro en tabla de registro_piezas
            $registro_pieza = [
                'id_modulo' => $id_modulo,
                'id_pieza' => $id,
                'status' => 1,
            ];
            Model::add('auditoria', $auditoria);
            Model::add('registro_piezas', $registro_pieza);
            echo json_encode(['success' => true, 'id' => $id]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Código interno requerido']);
        }
    }

    // Endpoint para editar pieza vía AJAX/JSON
    public function api_editar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['codigo_interno'])) {
            $data = $_POST;
            $errores = $this->validarDatosPieza($data);
            if (!empty($errores)) {
                echo json_encode(['success' => false, 'error' => implode(' ', $errores)]);
                exit;
            }
            $ok = AcervoArqueologicoModel::updatePieza($id, $data);
            $id_modulo = AcervoArqueologicoModel::getModuloId();
            // Auditoría
            $auditoria = [
                'id_usuario' => 1,
                'id_modulo' => $id_modulo,
                'nombre_tabla' => 'acervo_arqueologico',
                'id_pieza' => $id,
                'tipo_accion' => 'UPDATE',
                'observaciones' => 'Edición de pieza: ' . $data['codigo_interno']
            ];
            Model::add('auditoria', $auditoria);
            echo json_encode(['success' => $ok]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Código interno requerido']);
        }
    }

    public function api_eliminar($id)
    {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ok = AcervoArqueologicoModel::deletePieza($id);
            $id_modulo = AcervoArqueologicoModel::getModuloId();
            // Auditoría
            $auditoria = [
                'id_usuario' => 1,
                'id_modulo' => $id_modulo,
                'nombre_tabla' => 'acervo_arqueologico',
                'id_pieza' => $id,
                'tipo_accion' => 'DELETE',
                'observaciones' => 'Eliminación de pieza con ID: ' . $id
            ];

            Model::add('auditoria', $auditoria);
            echo json_encode(['success' => (bool)$ok]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        }
    }

    public function api_piezas()
    {
        $page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
        $perPage = isset($_GET['per_page']) ? max(1,intval($_GET['per_page'])) : 10;
        $offset = ($page - 1) * $perPage;
        $total = AcervoArqueologicoModel::getTotal();
        $piezas = AcervoArqueologicoModel::getAll($perPage, $offset);
        header('Content-Type: application/json');
        echo json_encode([
            'data' => $piezas,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ]);
        exit;
    }

    private function validarDatosPieza($data)
    {
        $errores = [];
        if (empty($data['codigo_interno'])) {
            $errores[] = 'El código interno es obligatorio.';
        }
        if (empty($data['nombre_titulo_pieza'])) {
            $errores[] = 'El nombre o título de la pieza es obligatorio.';
        }
        if (empty($data['ubicacion_fisica'])) {
            $errores[] = 'La ubicación física es obligatoria.';
        }
        return $errores;
    }
}