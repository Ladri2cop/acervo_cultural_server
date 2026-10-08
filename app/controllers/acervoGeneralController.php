<?php

class acervoGeneralController extends Controller
{
    public function index()
    {
        $piezas = AcervoGeneralModel::getAll();
        View::render('acervoGeneral', ['piezas' => $piezas]);
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

            $id_modulo = AcervoGeneralModel::getModuloId();
            $data['id_modulo'] = $id_modulo;
            $id = AcervoGeneralModel::addPieza($data);

            // Auditoría
            $auditoria = [
                'id_usuario' => 1,
                'id_modulo' => $id_modulo,
                'nombre_tabla' => 'acervo_general',
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
            $ok = AcervoGeneralModel::updatePieza($id, $data);
            $id_modulo = AcervoGeneralModel::getModuloId();
            // Auditoría
            $auditoria = [
                'id_usuario' => 1,
                'id_modulo' => $id_modulo,
                'nombre_tabla' => 'acervo_general',
                'id_pieza' => $id,
                'tipo_accion' => 'UPDATE',
                'observaciones' => 'Edición de pieza: ' . $data['codigo_interno']
            ];
            Model::add('auditoria', $auditoria);
            echo json_encode(['success' => (bool)$ok]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Código interno requerido']);
        }
    }

    // Endpoint para eliminar pieza vía AJAX/JSON
    public function api_eliminar($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ok = AcervoGeneralModel::deletePieza($id);
            $id_modulo = AcervoGeneralModel::getModuloId();

            // Auditoría
            $auditoria = [
                'id_usuario' => 1,
                'id_modulo' => $id_modulo,
                'nombre_tabla' => 'acervo_general',
                'id_pieza' => $id,
                'tipo_accion' => 'DELETE',
                'observaciones' => 'Eliminación de pieza ID: ' . $id
            ];

            Model::add('auditoria', $auditoria);
            echo json_encode(['success' => (bool)$ok]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        }
    }

    // Endpoint para obtener todas las piezas en formato JSON
    public function api_piezas()
    {
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $per_page = isset($_GET['per_page']) ? max(1, intval($_GET['per_page'])) : 10;
        $offset = ($page - 1) * $per_page;
        $total = AcervoGeneralModel::getTotal();
        $piezas = AcervoGeneralModel::getAll($per_page, $offset);
        header('Content-Type: application/json');
        echo json_encode([
            'data' => $piezas,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ]);
        exit;
    }

    private function validarDatosPieza($data)
    {
        $errores = [];
        if (empty($data['codigo_interno'])) {
            $errores[] = 'El código interno de la pieza es obligatorio.';
        }
        // Agregar más validaciones según sea necesario
        return $errores;
    }
}
