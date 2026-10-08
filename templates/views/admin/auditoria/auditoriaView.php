<?php require_once INCLUDES . 'admin/dashboardTop.php'; ?>

<style>
  .diff-table {
    width: 100%;
    font-size: 0.85rem;
    border-collapse: collapse;
  }
  .diff-table th, .diff-table td {
    padding: 8px 12px;
    border: 1px solid #e3e6f0;
    vertical-align: top;
  }
  .diff-table th {
    background-color: #f8f9fc;
    font-weight: 600;
  }
  .diff-changed {
    background-color: #fff3cd !important;
    font-weight: 600;
  }
  .json-box {
    max-height: 400px;
    overflow-y: auto;
    background: #f8f9fc;
    padding: 12px;
    border-radius: 6px;
    border: 1px solid #e3e6f0;
    font-family: monospace;
    font-size: 0.82rem;
  }
</style>

<div class="row">
  <div class="col-12">
    <div class="card shadow mb-4">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">
          <i class="bx bx-history me-1"></i> Registro de Auditoría del Sistema
        </h6>
      </div>
      <div class="card-body">
        <?php if (!empty($d->auditoria->rows)): ?>
          <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle" width="100%" cellspacing="0">
              <thead class="table-dark">
                <tr>
                  <th style="width: 60px;">ID</th>
                  <th>Fecha/Hora</th>
                  <th>Usuario</th>
                  <th>Acción</th>
                  <th>Tabla / Sección</th>
                  <th>ID Registro</th>
                  <th>IP</th>
                  <th>Observaciones / Comparación</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($d->auditoria->rows as $r): ?>
                  <?php 
                    $row = is_object($r) ? (array)$r : $r;
                    $badgeClass = 'bg-secondary';
                    switch (strtoupper($row['tipo_accion'])) {
                      case 'LOGIN':
                        $badgeClass = 'bg-success';
                        break;
                      case 'LOGOUT':
                        $badgeClass = 'bg-dark';
                        break;
                      case 'INSERT':
                        $badgeClass = 'bg-info text-white';
                        break;
                      case 'UPDATE':
                        $badgeClass = 'bg-warning text-dark';
                        break;
                      case 'DELETE':
                      case 'DESTRUIR_SESION':
                        $badgeClass = 'bg-danger';
                        break;
                      case 'EXPORT_EXCEL':
                      case 'EXPORT_PDF':
                        $badgeClass = 'bg-primary';
                        break;
                    }
                    $tieneDiff = !empty($row['datos_antes']) || !empty($row['datos_despues']);
                    $modalId = 'diffModal_' . $row['id_auditoria'];
                  ?>
                  <tr>
                    <td><strong>#<?php echo $row['id_auditoria']; ?></strong></td>
                    <td><small><?php echo date('d/m/Y H:i:s', strtotime($row['fecha_accion'])); ?></small></td>
                    <td>
                      <strong><?php echo htmlspecialchars($row['username'] ?? 'Sistema'); ?></strong><br>
                      <small class="text-muted">ID User: <?php echo $row['id_usuario'] ?? 'N/A'; ?></small>
                    </td>
                    <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($row['tipo_accion']); ?></span></td>
                    <td><code><?php echo htmlspecialchars($row['nombre_tabla']); ?></code></td>
                    <td><?php echo $row['id_pieza'] ? '#' . $row['id_pieza'] : '-'; ?></td>
                    <td><small class="text-muted"><?php echo htmlspecialchars($row['ip_address'] ?? '-'); ?></small></td>
                    <td>
                      <div><?php echo htmlspecialchars($row['observaciones'] ?? ''); ?></div>
                      <?php if ($tieneDiff): ?>
                        <button class="btn btn-sm btn-outline-primary mt-1 py-0 px-2" data-toggle="modal" data-target="#<?php echo $modalId; ?>">
                          <i class="bx bx-git-compare me-1"></i> Ver Cambios (Antes / Después)
                        </button>

                        <!-- Modal Comparativo Dos Columnas -->
                        <div class="modal fade" id="<?php echo $modalId; ?>" tabindex="-1" role="dialog" aria-labelledby="<?php echo $modalId; ?>Label" aria-hidden="true">
                          <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
                            <div class="modal-content">
                              <div class="modal-header bg-primary text-white py-2">
                                <h5 class="modal-title font-weight-bold" id="<?php echo $modalId; ?>Label">
                                  <i class="bx bx-detail me-1"></i> Comparativa de Cambios - Registro Auditoría #<?php echo $row['id_auditoria']; ?>
                                </h5>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                  <span aria-hidden="true">&times;</span>
                                </button>
                              </div>
                              <div class="modal-body p-4">
                                <div class="alert alert-info py-2 mb-3">
                                  <i class="bx bx-info-circle me-1"></i>
                                  <strong>Acción:</strong> <?php echo htmlspecialchars($row['tipo_accion']); ?> | 
                                  <strong>Tabla:</strong> <?php echo htmlspecialchars($row['nombre_tabla']); ?> | 
                                  <strong>ID Registro:</strong> <?php echo $row['id_pieza'] ?? 'N/A'; ?> | 
                                  <strong>Usuario:</strong> <?php echo htmlspecialchars($row['username'] ?? 'N/A'); ?>
                                </div>

                                <?php
                                  $antes = !empty($row['datos_antes']) ? json_decode($row['datos_antes'], true) : [];
                                  $despues = !empty($row['datos_despues']) ? json_decode($row['datos_despues'], true) : [];

                                  if (!is_array($antes)) $antes = [];
                                  if (!is_array($despues)) $despues = [];

                                  // Obtener todas las llaves involucradas
                                  $allKeys = array_unique(array_merge(array_keys($antes), array_keys($despues)));
                                ?>

                                <?php if (!empty($allKeys)): ?>
                                  <div class="table-responsive">
                                    <table class="diff-table">
                                      <thead>
                                        <tr>
                                          <th style="width: 20%;">Campo</th>
                                          <th style="width: 40%;" class="bg-light text-danger">
                                            <i class="bx bx-left-arrow-alt me-1"></i> Objeto Antes de Editar
                                          </th>
                                          <th style="width: 40%;" class="bg-light text-success">
                                            <i class="bx bx-right-arrow-alt me-1"></i> Objeto Después de Editar
                                          </th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                        <?php foreach ($allKeys as $key): ?>
                                          <?php
                                            $valAntes = isset($antes[$key]) ? (is_array($antes[$key]) ? json_encode($antes[$key], JSON_UNESCAPED_UNICODE) : (string)$antes[$key]) : null;
                                            $valDespues = isset($despues[$key]) ? (is_array($despues[$key]) ? json_encode($despues[$key], JSON_UNESCAPED_UNICODE) : (string)$despues[$key]) : null;
                                            $isDifferent = ($valAntes !== $valDespues);
                                          ?>
                                          <tr class="<?php echo $isDifferent ? 'diff-changed' : ''; ?>">
                                            <td><strong><?php echo htmlspecialchars($key); ?></strong></td>
                                            <td class="text-break">
                                              <?php if ($valAntes !== null): ?>
                                                <?php echo htmlspecialchars($valAntes); ?>
                                              <?php else: ?>
                                                <em class="text-muted">&lt;no definido&gt;</em>
                                              <?php endif; ?>
                                            </td>
                                            <td class="text-break">
                                              <?php if ($valDespues !== null): ?>
                                                <?php echo htmlspecialchars($valDespues); ?>
                                              <?php else: ?>
                                                <em class="text-muted">&lt;no definido&gt;</em>
                                              <?php endif; ?>
                                            </td>
                                          </tr>
                                        <?php endforeach; ?>
                                      </tbody>
                                    </table>
                                  </div>
                                <?php else: ?>
                                  <div class="row">
                                    <div class="col-md-6">
                                      <h6 class="font-weight-bold text-danger">Objeto Antes:</h6>
                                      <pre class="json-box"><?php echo htmlspecialchars($row['datos_antes'] ?? 'Sin datos registrados'); ?></pre>
                                    </div>
                                    <div class="col-md-6">
                                      <h6 class="font-weight-bold text-success">Objeto Después:</h6>
                                      <pre class="json-box"><?php echo htmlspecialchars($row['datos_despues'] ?? 'Sin datos registrados'); ?></pre>
                                    </div>
                                  </div>
                                <?php endif; ?>
                              </div>
                              <div class="modal-footer py-2">
                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                              </div>
                            </div>
                          </div>
                        </div>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Paginación -->
          <div class="d-flex justify-content-center mt-3">
            <?php echo $d->auditoria->pagination; ?>
          </div>
        <?php else: ?>
          <div class="alert alert-info text-center my-4">
            <i class="bx bx-info-circle me-1"></i> No se han encontrado registros de auditoría.
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>
