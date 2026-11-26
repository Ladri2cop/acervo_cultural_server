<?php 
  require_once INCLUDES . 'admin/dashboardTop.php';

  $totalRegistrados  = (int)($d->totalRegistrados ?? 0);
  $totalAprobados    = (int)($d->totalAprobados ?? 0);
  $totalGeneral      = (int)($d->totalGeneral ?? 0);
  $totalArq          = (int)($d->totalArq ?? 0);
  $totalNum          = (int)($d->totalNum ?? 0);
  $totalPendientes   = (int)($d->totalPendientes ?? 0);
  $totalCambios      = (int)($d->totalCambios ?? 0);
  $totalRechazados   = (int)($d->totalRechazados ?? 0);
  $totalEnRevision   = (int)($d->totalEnRevision ?? 0);
  $prestamosActivos  = (int)($d->prestamosActivos ?? 0);
  $prestamosVencidos = (int)($d->prestamosVencidos ?? 0);
  $prestamosTotal    = (int)($d->prestamosTotal ?? 0);
  $totalMuseos       = (int)($d->totalMuseos ?? 0);
  $topMuseos         = $d->topMuseos ?? [];
  $piezasRecientes   = $d->piezasRecientes ?? [];

  // Metas y porcentajes
  $metaEstatal = 50000;
  $porcentajeAvance = $metaEstatal > 0 ? min(100, round(($totalRegistrados / $metaEstatal) * 100, 1)) : 0;
  $pctAprobado = $totalRegistrados > 0 ? round(($totalAprobados / $totalRegistrados) * 100, 1) : 0;
  $pctRevision = $totalRegistrados > 0 ? round(($totalEnRevision / $totalRegistrados) * 100, 1) : 0;
  $pctGeneral  = $totalRegistrados > 0 ? round(($totalGeneral / $totalRegistrados) * 100, 1) : 0;
  $pctArq      = $totalRegistrados > 0 ? round(($totalArq / $totalRegistrados) * 100, 1) : 0;
  $pctNum      = $totalRegistrados > 0 ? round(($totalNum / $totalRegistrados) * 100, 1) : 0;
?>

<!-- Encabezado de Bienvenida y Fecha -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
  <div>
    <h1 class="h3 mb-0 text-gray-800 font-weight-bold">Panel de Control - SIPAC</h1>
    <p class="text-muted small mb-0">Sistema de Inventario Para el Acervo Cultural &bull; Gobierno del Estado de México</p>
  </div>
  <div class="mt-2 mt-sm-0">
    <a href="<?php echo URL; ?>admin/revision" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm mr-1">
      <i class="fas fa-clipboard-check fa-sm text-white-50 mr-1"></i> Mesa de Revisión
    </a>
    <a href="<?php echo URL; ?>admin/acervo" class="d-none d-sm-inline-block btn btn-sm btn-light border shadow-sm">
      <i class="fas fa-database fa-sm text-gray-500 mr-1"></i> Explorar Catálogo
    </a>
  </div>
</div>

<!-- FILA 1: KPIs Principales del Sistema -->
<div class="row">

  <!-- Total Acervo Registrado -->
  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-primary shadow h-100 py-2">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
              Acervo Total Registrado
            </div>
            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalRegistrados); ?></div>
            <div class="text-xs text-muted mt-1">
              Meta estatal: <?php echo number_format($metaEstatal); ?> (<?php echo $porcentajeAvance; ?>%)
            </div>
          </div>
          <div class="col-auto">
            <i class="fas fa-layer-group fa-2x text-gray-300"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Piezas Aprobadas (Oficiales) -->
  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-success shadow h-100 py-2">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
              Inventario Oficial Aprobado
            </div>
            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalAprobados); ?></div>
            <div class="text-xs text-success font-weight-bold mt-1">
              <i class="fas fa-check-circle mr-1"></i> <?php echo $pctAprobado; ?>% del inventario validado
            </div>
          </div>
          <div class="col-auto">
            <i class="fas fa-check-shield fa-2x text-gray-300"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- En Mesa de Revisión (Pendientes + Cambios) -->
  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-warning shadow h-100 py-2">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
              En Dictamen
            </div>
            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalEnRevision); ?></div>
            <div class="text-xs text-muted mt-1">
              <span class="text-primary font-weight-bold"><?php echo $totalPendientes; ?></span> pendientes &bull; 
              <span class="text-warning font-weight-bold"><?php echo $totalCambios; ?></span> con cambios
            </div>
          </div>
          <div class="col-auto">
            <a href="<?php echo URL; ?>admin/revision" title="Ver bandeja de revisión">
              <i class="fas fa-clock fa-2x text-warning"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Préstamos y Traslados Activos -->
  <div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-info shadow h-100 py-2">
      <div class="card-body">
        <div class="row no-gutters align-items-center">
          <div class="col mr-2">
            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
              Préstamos y Traslados
            </div>
            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($prestamosActivos); ?> activos</div>
            <div class="text-xs mt-1">
              <?php if ($prestamosVencidos > 0): ?>
                <span class="text-danger font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i><?php echo $prestamosVencidos; ?> vencido(s)</span>
              <?php else: ?>
                <span class="text-muted"><?php echo $prestamosTotal; ?> movimientos en historial</span>
              <?php endif; ?>
            </div>
          </div>
          <div class="col-auto">
            <a href="<?php echo URL; ?>admin/prestamos_transferencias" title="Gestionar préstamos">
              <i class="fas fa-exchange-alt fa-2x text-gray-300"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- FILA 2: Desglose por Colección Patrimonial -->
<div class="row">

  <!-- Acervo General -->
  <div class="col-xl-4 col-md-6 mb-4">
    <div class="card shadow h-100 border-0" style="border-top: 4px solid #8A2035 !important;">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #8A2035;">
              Acervo General
            </div>
            <div class="h4 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalGeneral); ?></div>
            <small class="text-muted"><?php echo $pctGeneral; ?>% de la colección estatal</small>
          </div>
          <div class="p-3 rounded-circle text-white shadow-sm" style="background-color: #8A2035;">
            <i class="fas fa-palette fa-lg"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Acervo Arqueológico -->
  <div class="col-xl-4 col-md-6 mb-4">
    <div class="card shadow h-100 border-0" style="border-top: 4px solid #B99056 !important;">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #B99056;">
              Acervo Arqueológico
            </div>
            <div class="h4 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalArq); ?></div>
            <small class="text-muted"><?php echo $pctArq; ?>% de la colección estatal</small>
          </div>
          <div class="p-3 rounded-circle text-white shadow-sm" style="background-color: #B99056;">
            <i class="fas fa-landmark fa-lg"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Acervo Numismático -->
  <div class="col-xl-4 col-md-6 mb-4">
    <div class="card shadow h-100 border-0" style="border-top: 4px solid #2e59d9 !important;">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
              Acervo Numismático
            </div>
            <div class="h4 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalNum); ?></div>
            <small class="text-muted"><?php echo $pctNum; ?>% de la colección estatal</small>
          </div>
          <div class="p-3 rounded-circle bg-primary text-white shadow-sm">
            <i class="fas fa-coins fa-lg"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- FILA 3: Gráficas Interactivas con Datos Reales -->
<div class="row">

  <!-- Gráfica de Tendencia Mensual de Captura -->
  <div class="col-xl-8 col-lg-7 mb-4">
    <div class="card shadow h-100">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white">
        <h6 class="m-0 font-weight-bold text-primary">
          <i class="fas fa-chart-area mr-1"></i> Tendencia de Registro Patrimonial (Últimos Meses)
        </h6>
        <span class="badge badge-light text-muted border">Actualizado en vivo</span>
      </div>
      <div class="card-body">
        <div class="chart-area" style="position: relative; height: 320px;">
          <canvas id="chartTendenciaMensual"></canvas>
        </div>
        <p class="text-center small text-muted mt-2 mb-0">Volumen de piezas registradas e incorporadas al padrón patrimonial por periodo mensual.</p>
      </div>
    </div>
  </div>

  <!-- Gráfica de Distribución por Colección -->
  <div class="col-xl-4 col-lg-5 mb-4">
    <div class="card shadow h-100">
      <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white">
        <h6 class="m-0 font-weight-bold text-primary">
          <i class="fas fa-chart-pie mr-1"></i> Distribución Patrimonial
        </h6>
      </div>
      <div class="card-body">
        <div class="chart-pie pt-2 pb-2" style="position: relative; height: 240px;">
          <canvas id="chartDistribucionAcervo"></canvas>
        </div>
        <div class="mt-4 text-center small">
          <span class="mr-3">
            <i class="fas fa-circle" style="color: #8A2035;"></i> General (<?php echo number_format($totalGeneral); ?>)
          </span>
          <span class="mr-3">
            <i class="fas fa-circle" style="color: #B99056;"></i> Arq. (<?php echo number_format($totalArq); ?>)
          </span>
          <span>
            <i class="fas fa-circle text-primary"></i> Numismática (<?php echo number_format($totalNum); ?>)
          </span>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- FILA 4: Actividad Reciente y Top Museos -->
<div class="row">

  <!-- Columna Izquierda: Actividad Patrimonial Reciente -->
  <div class="col-xl-7 col-lg-6 mb-4">
    <div class="card shadow h-100">
      <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
        <h6 class="m-0 font-weight-bold text-primary">
          <i class="fas fa-history mr-1"></i> Últimas Piezas Registradas en el Sistema
        </h6>
        <a href="<?php echo URL; ?>admin/acervo" class="small font-weight-bold text-primary">Ver todas &rarr;</a>
      </div>
      <div class="card-body p-0">
        <?php if (!empty($piezasRecientes)): ?>
          <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0 small">
              <thead class="thead-light">
                <tr>
                  <th style="width: 50px;">Foto</th>
                  <th>Código / Título</th>
                  <th>Colección</th>
                  <th>Sede / Museo</th>
                  <th class="text-center">Semáforo</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($piezasRecientes as $p): ?>
                  <?php 
                    $foto      = is_object($p) ? ($p->fotografia ?? null) : ($p['fotografia'] ?? null);
                    $codigo    = is_object($p) ? ($p->codigo_interno ?? '') : ($p['codigo_interno'] ?? '');
                    $titulo    = is_object($p) ? ($p->titulo ?? '') : ($p['titulo'] ?? '');
                    $tipo      = is_object($p) ? ($p->tipo ?? 'general') : ($p['tipo'] ?? 'general');
                    $ubicacion = is_object($p) ? ($p->ubicacion_fisica ?? '') : ($p['ubicacion_fisica'] ?? '');
                    $estatus   = is_object($p) ? ($p->estatus_revision ?? 'pendiente') : ($p['estatus_revision'] ?? 'pendiente');
                    $fotoRuta  = !empty($foto) ? URL . 'assets/uploads/' . $foto : null;
                  ?>
                  <tr>
                    <td class="text-center p-2">
                      <?php if ($fotoRuta): ?>
                        <img src="<?php echo htmlspecialchars($fotoRuta); ?>" alt="Miniatura" class="rounded border shadow-sm" style="width: 38px; height: 38px; object-fit: cover;" onerror="this.onerror=null; this.src='<?php echo get_user_avatar(); ?>';">
                      <?php else: ?>
                        <div class="rounded bg-light border d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                          <i class="fas fa-image text-muted"></i>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="font-weight-bold text-gray-800 d-block"><?php echo htmlspecialchars($codigo ?: 'Sin código'); ?></span>
                      <span class="text-muted text-truncate d-inline-block" style="max-width: 220px;" title="<?php echo htmlspecialchars($titulo); ?>">
                        <?php echo htmlspecialchars($titulo ?: 'Sin título registrado'); ?>
                      </span>
                    </td>
                    <td>
                      <?php if ($tipo === 'general'): ?>
                        <span class="badge badge-pill text-white px-2 py-1" style="background-color: #8A2035;">General</span>
                      <?php elseif ($tipo === 'arqueologico'): ?>
                        <span class="badge badge-pill text-white px-2 py-1" style="background-color: #B99056;">Arqueología</span>
                      <?php else: ?>
                        <span class="badge badge-pill badge-primary px-2 py-1">Numismática</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="text-muted d-inline-block text-truncate" style="max-width: 160px;" title="<?php echo htmlspecialchars($ubicacion); ?>">
                        <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i><?php echo htmlspecialchars($ubicacion ?: 'Sin asignar'); ?>
                      </span>
                    </td>
                    <td class="text-center">
                      <?php if ($estatus === 'aprobado'): ?>
                        <span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i>Aprobada</span>
                      <?php elseif ($estatus === 'pendiente'): ?>
                        <span class="badge badge-primary px-2 py-1"><i class="fas fa-clock mr-1"></i>Pendiente</span>
                      <?php elseif ($estatus === 'cambios_solicitados'): ?>
                        <span class="badge badge-warning px-2 py-1 text-dark"><i class="fas fa-exclamation-triangle mr-1"></i>Cambios</span>
                      <?php else: ?>
                        <span class="badge badge-danger px-2 py-1"><i class="fas fa-times mr-1"></i>Rechazada</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="p-4 text-center text-muted">
            <i class="fas fa-inbox fa-2x mb-2 text-gray-300"></i>
            <p class="mb-0">No hay registros recientes para mostrar.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Columna Derecha: Top Museos y Resumen de Estado -->
  <div class="col-xl-5 col-lg-6 mb-4">

    <!-- Top 5 Museos con Mayor Acervo Registrado -->
    <div class="card shadow mb-4">
      <div class="card-header py-3 bg-white">
        <h6 class="m-0 font-weight-bold text-primary">
          <i class="fas fa-landmark mr-1"></i> Sedes y Museos con Mayor Acervo
        </h6>
      </div>
      <div class="card-body">
        <?php if (!empty($topMuseos)): ?>
          <?php 
            $primerItem = reset($topMuseos);
            $primerTotal = is_object($primerItem) ? (int)($primerItem->total ?? 1) : (int)($primerItem['total'] ?? 1);
            $maxMuseo = max(1, $primerTotal);
            $coloresProgreso = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-secondary'];
          ?>
          <?php foreach ($topMuseos as $idx => $m): ?>
            <?php 
              $totalM = is_object($m) ? (int)($m->total ?? 0) : (int)($m['total'] ?? 0);
              $nombreM = is_object($m) ? ($m->museo ?? 'Sin Asignar') : ($m['museo'] ?? 'Sin Asignar');
              $pctRel = round(($totalM / $maxMuseo) * 100);
              $colorClass = $coloresProgreso[$idx % count($coloresProgreso)];
            ?>
            <div class="mb-3">
              <div class="d-flex justify-content-between small font-weight-bold text-gray-700 mb-1">
                <span class="text-truncate mr-2" style="max-width: 280px;" title="<?php echo htmlspecialchars($nombreM); ?>">
                  <?php echo ($idx + 1) . '. ' . htmlspecialchars($nombreM); ?>
                </span>
                <span class="text-primary"><?php echo number_format($totalM); ?> piezas</span>
              </div>
              <div class="progress" style="height: 8px;">
                <div class="progress-bar <?php echo $colorClass; ?>" role="progressbar" style="width: <?php echo $pctRel; ?>%" aria-valuenow="<?php echo $pctRel; ?>" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="small text-muted mb-0">Sin datos de ubicación registrados.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Resumen de Validación (Semáforo Global) -->
    <div class="card shadow mb-4">
      <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
        <h6 class="m-0 font-weight-bold text-primary">
          <i class="fas fa-traffic-light mr-1"></i> Estado del Semáforo
        </h6>
        <a href="<?php echo URL; ?>admin/revision" class="btn btn-sm btn-outline-primary py-0 px-2 font-weight-bold">Revisar</a>
      </div>
      <div class="card-body">
        <!-- Aprobadas -->
        <div class="d-flex justify-content-between small font-weight-bold mb-1">
          <span class="text-success"><i class="fas fa-check-circle mr-1"></i> Aprobadas (Oficiales)</span>
          <span><?php echo number_format($totalAprobados); ?> (<?php echo $pctAprobado; ?>%)</span>
        </div>
        <div class="progress mb-3" style="height: 8px;">
          <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $pctAprobado; ?>%"></div>
        </div>

        <!-- Pendientes -->
        <?php $pctPend = $totalRegistrados > 0 ? round(($totalPendientes / $totalRegistrados) * 100, 2) : 0; ?>
        <div class="d-flex justify-content-between small font-weight-bold mb-1">
          <span class="text-primary"><i class="fas fa-clock mr-1"></i> Pendientes de Dictamen</span>
          <span><?php echo number_format($totalPendientes); ?> (<?php echo $pctPend; ?>%)</span>
        </div>
        <div class="progress mb-3" style="height: 8px;">
          <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo max(1, $pctPend); ?>%"></div>
        </div>

        <!-- Cambios Solicitados -->
        <?php $pctCamb = $totalRegistrados > 0 ? round(($totalCambios / $totalRegistrados) * 100, 2) : 0; ?>
        <div class="d-flex justify-content-between small font-weight-bold mb-1">
          <span class="text-warning"><i class="fas fa-exclamation-circle mr-1"></i> Cambios Solicitados</span>
          <span><?php echo number_format($totalCambios); ?></span>
        </div>
        <div class="progress mb-3" style="height: 8px;">
          <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo max(1, $pctCamb); ?>%"></div>
        </div>

        <!-- Rechazadas -->
        <?php $pctRech = $totalRegistrados > 0 ? round(($totalRechazados / $totalRegistrados) * 100, 2) : 0; ?>
        <div class="d-flex justify-content-between small font-weight-bold mb-1">
          <span class="text-danger"><i class="fas fa-times-circle mr-1"></i> Rechazadas / Descartadas</span>
          <span><?php echo number_format($totalRechazados); ?></span>
        </div>
        <div class="progress" style="height: 8px;">
          <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo max(1, $pctRech); ?>%"></div>
        </div>
      </div>
    </div>

  </div>

</div>

<!-- Scripts de Gráficas Reales Chart.js -->
<script>
(function() {
  var chartTries = 0;
  function initSIPACDashboardCharts() {
    if (typeof Chart === 'undefined') {
      chartTries++;
      if (chartTries < 40) {
        setTimeout(initSIPACDashboardCharts, 100);
      }
      return;
    }

    // Configuración tipográfica de Chart.js
    Chart.defaults.global.defaultFontFamily = 'Nunito, -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
    Chart.defaults.global.defaultFontColor = '#858796';

    function formatearNumero(num) {
      return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // 1. Gráfica de Tendencia Mensual de Captura (Area / Line Chart)
    var canvasArea = document.getElementById('chartTendenciaMensual');
    if (canvasArea) {
      var mesesLabels = <?php echo json_encode($d->chartMesesLabels ?? []); ?>;
      var mesesValues = <?php echo json_encode($d->chartMesesValues ?? []); ?>;

      if (!mesesLabels || mesesLabels.length === 0) {
        mesesLabels = ['Sin datos'];
        mesesValues = [0];
      }

      new Chart(canvasArea, {
        type: 'line',
        data: {
          labels: mesesLabels,
          datasets: [{
            label: 'Piezas registradas',
            lineTension: 0.3,
            backgroundColor: 'rgba(138, 32, 53, 0.08)',
            borderColor: '#8A2035',
            pointRadius: 4,
            pointBackgroundColor: '#8A2035',
            pointBorderColor: '#ffffff',
            pointHoverRadius: 6,
            pointHoverBackgroundColor: '#8A2035',
            pointHoverBorderColor: '#ffffff',
            pointHitRadius: 10,
            pointBorderWidth: 2,
            data: mesesValues,
          }],
        },
        options: {
          maintainAspectRatio: false,
          layout: {
            padding: { left: 10, right: 25, top: 25, bottom: 0 }
          },
          scales: {
            xAxes: [{
              gridLines: { display: false, drawBorder: false },
              ticks: { maxTicksLimit: 7 }
            }],
            yAxes: [{
              ticks: {
                maxTicksLimit: 5,
                padding: 10,
                callback: function(value) { return formatearNumero(value); }
              },
              gridLines: {
                color: 'rgb(234, 236, 244)',
                zeroLineColor: 'rgb(234, 236, 244)',
                drawBorder: false,
                borderDash: [2],
                zeroLineBorderDash: [2]
              }
            }],
          },
          legend: { display: false },
          tooltips: {
            backgroundColor: 'rgb(255,255,255)',
            bodyFontColor: '#858796',
            titleMarginBottom: 10,
            titleFontColor: '#6e707e',
            titleFontSize: 14,
            borderColor: '#dddfeb',
            borderWidth: 1,
            xPadding: 15,
            yPadding: 15,
            displayColors: false,
            intersect: false,
            mode: 'index',
            caretPadding: 10,
            callbacks: {
              label: function(tooltipItem, chart) {
                var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                return datasetLabel + ': ' + formatearNumero(tooltipItem.yLabel);
              }
            }
          }
        }
      });
    }

    // 2. Gráfica de Distribución por Colección (Doughnut Chart)
    var canvasPie = document.getElementById('chartDistribucionAcervo');
    if (canvasPie) {
      var valGeneral = <?php echo (int)$totalGeneral; ?>;
      var valArq = <?php echo (int)$totalArq; ?>;
      var valNum = <?php echo (int)$totalNum; ?>;

      new Chart(canvasPie, {
        type: 'doughnut',
        data: {
          labels: ['Acervo General', 'Acervo Arqueológico', 'Acervo Numismático'],
          datasets: [{
            data: [valGeneral, valArq, valNum],
            backgroundColor: ['#8A2035', '#B99056', '#4e73df'],
            hoverBackgroundColor: ['#6a1829', '#997341', '#2e59d9'],
            hoverBorderColor: 'rgba(234, 236, 244, 1)',
          }],
        },
        options: {
          maintainAspectRatio: false,
          tooltips: {
            backgroundColor: 'rgb(255,255,255)',
            bodyFontColor: '#858796',
            borderColor: '#dddfeb',
            borderWidth: 1,
            xPadding: 15,
            yPadding: 15,
            displayColors: false,
            caretPadding: 10,
            callbacks: {
              label: function(tooltipItem, data) {
                var label = data.labels[tooltipItem.index] || '';
                var value = data.datasets[0].data[tooltipItem.index] || 0;
                return label + ': ' + formatearNumero(value) + ' piezas';
              }
            }
          },
          legend: { display: false },
          cutoutPercentage: 70,
        },
      });
    }
  }

  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    initSIPACDashboardCharts();
  } else {
    window.addEventListener('load', initSIPACDashboardCharts);
  }
})();
</script>

<?php require_once INCLUDES . 'admin/dashboardBottom.php'; ?>
