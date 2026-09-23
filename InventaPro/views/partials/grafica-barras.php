<?php
/**
 * Bar chart generated 100% server-side as inline SVG.
 * Does not depend on any external library (Chart.js, etc.) — works
 * with no internet connection.
 *
 * Expected input variable: $ventasPorDia
 *   [['dia' => '2026-07-20', 'ventas' => 3, 'ingresos' => '145.00'], ...]
 */
$maxIngreso = max(array_map(fn($d) => (float) $d['ingresos'], $ventasPorDia)) ?: 1;
$cantidadDias = count($ventasPorDia);
$anchoBarra = 44;
$espacio = 18;
$alturaGrafica = 160;
$margenSuperior = 26; // room so the $ label on the tallest bar doesn't get cut off
$anchoSvg = max(320, $cantidadDias * ($anchoBarra + $espacio));

$mesesCorto = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
?>
<div class="grafica-barras-wrap">
  <svg viewBox="0 0 <?= $anchoSvg ?> <?= $alturaGrafica + $margenSuperior + 34 ?>" class="grafica-barras-svg" preserveAspectRatio="xMinYMid meet">
    <?php foreach ($ventasPorDia as $i => $d):
        $ingreso = (float) $d['ingresos'];
        $alturaBarra = $maxIngreso > 0 ? max(4, ($ingreso / $maxIngreso) * $alturaGrafica) : 4;
        $x = $i * ($anchoBarra + $espacio) + $espacio / 2;
        $y = $margenSuperior + ($alturaGrafica - $alturaBarra);
        $fecha = new DateTime($d['dia']);
        $etiqueta = $fecha->format('d') . ' ' . $mesesCorto[(int) $fecha->format('n') - 1];
    ?>
      <g>
        <title><?= $etiqueta ?>: $<?= number_format($ingreso, 2) ?> (<?= (int) $d['ventas'] ?> sale<?= $d['ventas'] == 1 ? '' : 's' ?>)</title>
        <rect
          x="<?= $x ?>" y="<?= $y ?>"
          width="<?= $anchoBarra ?>" height="<?= $alturaBarra ?>"
          rx="6"
          fill="<?= $ingreso > 0 ? 'url(#gradBarra)' : '#1f2c48' ?>"
        />
        <?php if ($ingreso > 0): ?>
        <text x="<?= $x + $anchoBarra / 2 ?>" y="<?= $y - 8 ?>" text-anchor="middle" class="grafica-valor">$<?= number_format($ingreso, 0) ?></text>
        <?php endif; ?>
        <text x="<?= $x + $anchoBarra / 2 ?>" y="<?= $margenSuperior + $alturaGrafica + 20 ?>" text-anchor="middle" class="grafica-etiqueta"><?= $etiqueta ?></text>
      </g>
    <?php endforeach; ?>

    <defs>
      <linearGradient id="gradBarra" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#06b6d4"/>
        <stop offset="100%" stop-color="#2563eb"/>
      </linearGradient>
    </defs>
  </svg>
</div>
