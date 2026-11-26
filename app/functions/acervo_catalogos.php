<?php
/**
 * Catálogos oficiales para SIPAC (Sistema de Inventario Para el Acervo Cultural)
 * Conforme a la Minuta de Trabajo 03 y documento oficial de Épocas, Técnicas y Materiales.
 */

/**
 * Catálogo Oficial de Museos para Ubicación Física
 */
function obtenerOpcionesMuseos(): array
{
  $opciones = ['' => 'Seleccione una ubicación...'];
  try {
    $db = Db::connect();
    $stmt = $db->query("SELECT nombre_museo FROM museos WHERE status = 1 ORDER BY nombre_museo ASC");
    $museos = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
    foreach ($museos as $m) {
      $nombre = trim($m);
      if (!empty($nombre)) {
        $opciones[$nombre] = $nombre;
      }
    }
  } catch (Throwable $e) {
    $museosStatic = ['Museo de La Acuarela', 'Museo Adolfo López Mateos', 'Museo Antonio Ruiz Pérez', 'Museo de Antropología e Historia del Estado de México', 'Museo Arqueológico de Apaxco', 'Museo de Arte Moderno', 'Museo de Bellas Artes', 'Museo Plaza Estado de México', 'Museo de Ciencias Naturales', 'Cosmovitral Jardín Botánico', 'Museo de Culturas Populares', 'Museo Dr. José Ma. Luis Mora', 'Museo Dr. Román Piña Chan', 'Museo de La Estampa', 'Museo del Retrato Felipe Santiago Gutiérrez', 'Museo Joaquín Arcadio Pagaza', 'Museo del Paisaje José María Velasco', 'Museo de Minería del Oro', 'Museo Taller Nishizawa', 'Museo de Numismática', 'Museo de Ocuilan', 'Museo Virreinal de Zinacantepec', 'Museo Isidro Fabela de Atlacomulco', 'Galería de Arte Mexiquense Torres Bicentenario', 'Museo Casa Toluca 1920', 'Dirección de Patrimonio Cultural', 'Subdirección de Acervo Cultural', 'Departamento de Museos', 'Biblioteca Pública Central Estatal', 'Archivo Histórico del Estado de México', 'Cineteca Mexiquense'];
    foreach ($museosStatic as $m) {
      $opciones[$m] = $m;
    }
  }
  $opciones['Otro'] = 'Otro (Especificar sede externa o dependencia)';
  return $opciones;
}

/**
 * Catálogo Oficial de Épocas Históricas (Dinámico desde base de datos con fallback)
 */
function obtenerOpcionesEpocas(): array
{
  $opciones = ['' => 'Seleccione época...'];
  try {
    $db = Db::connect();
    $stmt = $db->query("SELECT nombre_epoca_periodo FROM epocas_periodos WHERE status = 1 ORDER BY id_epoca_periodo ASC");
    $filas = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
    if (!empty($filas)) {
      foreach ($filas as $val) {
        $val = trim($val);
        if (!empty($val)) $opciones[$val] = $val;
      }
      return $opciones;
    }
  } catch (Throwable $e) {}

  return [
    '' => 'Seleccione época...',
    'Pleistoceno' => 'Pleistoceno',
    'Periodo Clásico' => 'Periodo Clásico',
    'Prehispánica' => 'Prehispánica',
    'Conquista' => 'Conquista',
    'Virreinal/Colonial' => 'Virreinal/Colonial',
    'Independencia' => 'Independencia',
    'México Independiente' => 'México Independiente',
    'Porfiriato' => 'Porfiriato',
    'Siglo XVI' => 'Siglo XVI',
    'Siglo XVII' => 'Siglo XVII',
    'Siglo XVIII' => 'Siglo XVIII',
    'Siglo XIX' => 'Siglo XIX',
    'Siglo XX' => 'Siglo XX',
    'Época actual' => 'Época actual',
  ];
}

/**
 * Catálogo Oficial de Estados de Conservación (Dinámico desde base de datos con fallback)
 */
function obtenerOpcionesEstadoConservacion(): array
{
  $opciones = ['' => 'Seleccione estado de conservación...'];
  try {
    $db = Db::connect();
    $stmt = $db->query("SELECT nombre_estado_conservacion FROM estado_conservacion WHERE status = 1 ORDER BY id_estado_conservacion ASC");
    $filas = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
    if (!empty($filas)) {
      foreach ($filas as $val) {
        $val = trim($val);
        if (!empty($val)) $opciones[$val] = $val;
      }
      return $opciones;
    }
  } catch (Throwable $e) {}

  return [
    '' => 'Seleccione estado de conservación...',
    'Bueno' => 'Bueno',
    'Regular' => 'Regular',
    'Malo' => 'Malo',
  ];
}

/**
 * Catálogo de Tipos de Obra para Acervo General (Agrupado por Obras Bidimensionales y Tridimensionales)
 */
function obtenerOpcionesTiposGeneral(): array
{
  try {
    $db = Db::connect();
    $stmt = $db->query("SELECT nombre_tipo_obra, categoria FROM tipo_obras WHERE status = 1 AND categoria IN ('Bidimensional', 'Tridimensional') ORDER BY categoria ASC, id_tipo_obra ASC");
    $filas = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    if (!empty($filas)) {
      $bidi = [];
      $tridi = [];
      foreach ($filas as $r) {
        $val = trim($r['nombre_tipo_obra']);
        if ($r['categoria'] === 'Bidimensional') {
          $bidi[$val] = $val;
        } else {
          $tridi[$val] = $val;
        }
      }
      if (!isset($tridi['Otro'])) {
        $tridi['Otro'] = 'Otro';
      }
      return [
        '' => 'Seleccione tipo de obra...',
        'Obras Bidimensionales'  => $bidi,
        'Obras Tridimensionales' => $tridi
      ];
    }
  } catch (Throwable $e) {}

  return [
    '' => 'Seleccione tipo de obra...',
    'Obras Bidimensionales' => [
      'Pintura'    => 'Pintura',
      'Dibujo'     => 'Dibujo',
      'Grabado'    => 'Grabado',
      'Fotografía' => 'Fotografía',
      'Acuarela'   => 'Acuarela',
      'Textil'     => 'Textil'
    ],
    'Obras Tridimensionales' => [
      'Escultura'  => 'Escultura',
      'Mobiliario' => 'Mobiliario',
      'Cerámica'   => 'Cerámica',
      'Objeto'     => 'Objeto',
      'Otro'       => 'Otro'
    ]
  ];
}

/**
 * Catálogo de Técnicas para Acervo General (Pictóricas, Culturas Populares, Ciencias Naturales)
 */
function obtenerOpcionesTecnicasGeneral(): array
{
  return [
    '' => 'Seleccione técnica...',
    'Técnicas Pictóricas' => [
      'Vaciado sobre bronce' => 'Vaciado sobre bronce',
      'Vaciado sobre hierro' => 'Vaciado sobre hierro',
      'Óleo sobre tela' => 'Óleo sobre tela',
      'Encáustica sobre tela' => 'Encáustica sobre tela',
      'Acrílico' => 'Acrílico',
      'Pastel sobre papel' => 'Pastel sobre papel',
      'Carboncillo' => 'Carboncillo',
      'Tinta sobre papel' => 'Tinta sobre papel',
      'Impresión digital' => 'Impresión digital',
      'Mixta' => 'Mixta',
      'Dibujo' => 'Dibujo',
      'Talla en madera' => 'Talla en madera',
      'Fotografía' => 'Fotografía',
      'Acuarela' => 'Acuarela',
      'Grabado' => 'Grabado',
      'Temple' => 'Temple',
      'Esmalte sobre fibracel' => 'Esmalte sobre fibracel',
      'Collage' => 'Collage',
      'Gouache sobre papel' => 'Gouache sobre papel',
      'Aguafuerte y Aguatinta' => 'Aguafuerte y Aguatinta',
      'Puntillismo' => 'Puntillismo',
      'Litografía en seco' => 'Litografía en seco',
      'Escultura' => 'Escultura',
      'Fresco sobre madera' => 'Fresco sobre madera',
      'Agua tinta al azúcar y punta seca' => 'Agua tinta al azúcar y punta seca',
      'Cerámica de alta temperatura' => 'Cerámica de alta temperatura',
      'Pintura' => 'Pintura',
      'Encausto' => 'Encausto',
      'Lasergrafía' => 'Lasergrafía',
      'Xilografía' => 'Xilografía',
      'Aceite sintético sobre triplay' => 'Aceite sintético sobre triplay',
      'Serigrafía' => 'Serigrafía',
      'Electrografía' => 'Electrografía',
      'Otros' => 'Otros',
    ],
    'Técnicas de Culturas Populares' => [
      'Vaciado sobre bronce' => 'Vaciado sobre bronce',
      'Lapidaria' => 'Lapidaria',
      'Vaciado de Hierro' => 'Vaciado de Hierro',
      'Herrería' => 'Herrería',
      'Repujado' => 'Repujado',
      'Mecanografiado' => 'Mecanografiado',
      'Cera en molde' => 'Cera en molde',
      'Manuscrito' => 'Manuscrito',
      'Madera tallada' => 'Madera tallada',
      'Telar de cintura' => 'Telar de cintura',
      'Impresión' => 'Impresión',
      'Escultura' => 'Escultura',
      'Deshilado' => 'Deshilado',
      'Óleo sobre tela' => 'Óleo sobre tela',
      'Ensamble' => 'Ensamble',
      'Grabado' => 'Grabado',
      'Decorado' => 'Decorado',
      'Cosido' => 'Cosido',
      'Planchado' => 'Planchado',
      'Piel curtida' => 'Piel curtida',
      'Mimbre Tejido' => 'Mimbre Tejido',
      'Labrado' => 'Labrado',
      'Lapidaria' => 'Lapidaria',
      'Moldeado a mano' => 'Moldeado a mano',
      'Alfeñique' => 'Alfeñique',
      'Lechuguilla' => 'Lechuguilla',
      'Alfarería' => 'Alfarería',
      'Piteado' => 'Piteado',
      'Tallado en Piedra' => 'Tallado en Piedra',
      'Alto relieve' => 'Alto relieve',
      'Torneado' => 'Torneado',
      'Prensado' => 'Prensado',
      'Miniaturas' => 'Miniaturas',
      'Cincelado' => 'Cincelado',
      'Atado' => 'Atado',
      'Trenzado' => 'Trenzado',
      'Enmarcado' => 'Enmarcado',
      'Bordado' => 'Bordado',
      'Paja atada' => 'Paja atada',
      'Metal' => 'Metal',
      'Artesanía' => 'Artesanía',
      'Fotografía' => 'Fotografía',
      'Zapatería' => 'Zapatería',
      'Acuarela' => 'Acuarela',
      'Otros' => 'Otros',
      'Escultura Macramé' => 'Escultura Macramé',
      'Mixta' => 'Mixta',
      'Tinta sobre papel' => 'Tinta sobre papel',
      'Temple' => 'Temple',
      'Acrílico' => 'Acrílico',
      'Dibujo' => 'Dibujo',
      'Lápiz en sepia' => 'Lápiz en sepia',
      'Mixta' => 'Mixta',
      'Grabado en Relieve' => 'Grabado en Relieve',
      'Chumeteada' => 'Chumeteada',
      'Talabartería' => 'Talabartería',
      'Bordado' => 'Bordado',
      'Impreso' => 'Impreso',
      'Tejido' => 'Tejido',
      'Recolección y secado' => 'Recolección y secado',
      'Carpintería' => 'Carpintería',
      'Taxidermia' => 'Taxidermia',
      'Pirotecnia' => 'Pirotecnia',
      'Moldeado y Bruñido' => 'Moldeado y Bruñido',
      'Confeccionado y Bordado' => 'Confeccionado y Bordado',
      'Cartonería' => 'Cartonería',
    ],
    'Técnicas para Ciencias Naturales' => [
      'Fosilización' => 'Fosilización',
      'Encapsulado' => 'Encapsulado',
      'Taxidermia' => 'Taxidermia',
      'Maniquí anatómico' => 'Maniquí anatómico',
      'Diorama' => 'Diorama',
      'Lepidóptera (insectos, mariposas)' => 'Lepidóptera (insectos, mariposas)',
      'Osteología' => 'Osteología',
      'Geología' => 'Geología',
      'Paleontología' => 'Paleontología',
      'Mineralogía' => 'Mineralogía',
      'Acrílico' => 'Acrílico',
      'Grabado' => 'Grabado',
      'Numismática' => 'Numismática',
      'Otros' => 'Otros',
    ],
  ];
}

/**
 * Catálogo de Materiales para Acervo General (Pictóricos, Culturas Populares, Ciencias Naturales)
 */
function obtenerOpcionesMaterialesGeneral(): array
{
  return [
    '' => 'Seleccione material...',
    'Materiales Pictóricos' => [
      'Bronce' => 'Bronce',
      'Acero negro' => 'Acero negro',
      'Jade' => 'Jade',
      'Papel Kraft' => 'Papel Kraft',
      'Hierro' => 'Hierro',
      'Gouche' => 'Gouche',
      'Cuero' => 'Cuero',
      'Acero' => 'Acero',
      'Papel' => 'Papel',
      'Arcilla' => 'Arcilla',
      'Tela' => 'Tela',
      'Masconite' => 'Masconite',
      'Plata' => 'Plata',
      'Lápiz' => 'Lápiz',
      'Tinta' => 'Tinta',
      'Yute' => 'Yute',
      'Fibra de vidrio' => 'Fibra de vidrio',
      'Esmalte' => 'Esmalte',
      'Acido' => 'Acido',
      'Aceite sintético' => 'Aceite sintético',
      'Grafito' => 'Grafito',
      'Fibracel' => 'Fibracel',
      'Yema de huevo' => 'Yema de huevo',
      'Gis' => 'Gis',
      'Laca' => 'Laca',
      'Terracota' => 'Terracota',
      'Hilo' => 'Hilo',
      'Mármol' => 'Mármol',
      'Barniz' => 'Barniz',
      'Hoja lata' => 'Hoja lata',
      'Madera' => 'Madera',
      'Pimero' => 'Pimero',
      'Lona' => 'Lona',
      'Papel fotográfico' => 'Papel fotográfico',
      'Vidrio' => 'Vidrio',
      'Agua fuerte' => 'Agua fuerte',
      'Cristal' => 'Cristal',
      'Barniz blando' => 'Barniz blando',
      'Plomo' => 'Plomo',
      'Linóleo' => 'Linóleo',
      'Estambre' => 'Estambre',
      'Aluminio' => 'Aluminio',
      'Tinta china' => 'Tinta china',
      'Otros' => 'Otros',
      'Cemento' => 'Cemento',
      'Acero inoxidable' => 'Acero inoxidable',
      'Cobre' => 'Cobre',
      'Acrílico' => 'Acrílico',
      'Resina' => 'Resina',
      'Cera' => 'Cera',
      'Yeso' => 'Yeso',
      'Metal' => 'Metal',
      'Carboncillo' => 'Carboncillo',
      'Barro' => 'Barro',
      'Cerámica' => 'Cerámica',
      'Cochinilla' => 'Cochinilla',
      'Lino' => 'Lino',
      'Grava' => 'Grava',
      'Baba de nopal' => 'Baba de nopal',
      'Cal' => 'Cal',
      'Azúcar' => 'Azúcar',
      'Lona' => 'Lona',
      'Vainilla' => 'Vainilla',
      'Lienzo' => 'Lienzo',
      'Plastilina' => 'Plastilina',
      'Cartón' => 'Cartón',
    ],
    'Materiales para Culturas Populares' => [
      'Cera' => 'Cera',
      'Hierro' => 'Hierro',
      'Madera' => 'Madera',
      'Bronce' => 'Bronce',
      'Papel' => 'Papel',
      'Tela' => 'Tela',
      'Fibra vegetal' => 'Fibra vegetal',
      'Piel' => 'Piel',
      'Palma' => 'Palma',
      'Vidrio' => 'Vidrio',
      'Ixtle' => 'Ixtle',
      'Fibra de Trigo' => 'Fibra de Trigo',
      'Barro' => 'Barro',
      'Cerámica' => 'Cerámica',
      'Pellon' => 'Pellon',
      'Vara' => 'Vara',
      'Tela Industrial' => 'Tela Industrial',
      'Plástico' => 'Plástico',
      'Vara' => 'Vara',
      'Tule' => 'Tule',
      'Popotillo' => 'Popotillo',
      'Latón' => 'Latón',
      'Otros' => 'Otros',
    ],
    'Materiales para Ciencias Naturales' => [
      'Resina epóxica' => 'Resina epóxica',
      'Muro de concreto' => 'Muro de concreto',
      'Prensa manual' => 'Prensa manual',
      'Metal' => 'Metal',
      'Tintas' => 'Tintas',
      'Placas (madera blanda, triplay, linóleo)' => 'Placas (madera blanda, triplay, linóleo)',
      'Gubias, punta seca, buril, rascador, bruñidor' => 'Gubias, punta seca, buril, rascador, bruñidor',
      'Otros' => 'Otros',
    ],
  ];
}

/**
 * Catálogo de Tipos de Acervo Numismático
 */
function obtenerOpcionesTiposNumismatica(): array
{
  return [
    '' => 'Seleccione tipo de acervo numismático...',
    'Monedas' => 'Monedas',
    'Billetes o notafilia' => 'Billetes o notafilia',
    'Filatelia' => 'Filatelia',
    'Loterofilia' => 'Loterofilia',
    'Medallística' => 'Medallística',
    'Acciones' => 'Acciones',
    'Documentos fiduciarios' => 'Documentos fiduciarios',
    'Tlacos' => 'Tlacos',
    'Sellos' => 'Sellos',
    'Condecoraciones' => 'Condecoraciones',
  ];
}

/**
 * Catálogo de Materiales para Acervo Numismático
 */
function obtenerOpcionesMaterialesNumismatica(): array
{
  return [
    '' => 'Seleccione material...',
    'Cartón' => 'Cartón',
    'Oro' => 'Oro',
    'Cobre' => 'Cobre',
    'Plata' => 'Plata',
    'Níquel' => 'Níquel',
    'Cuproníquel' => 'Cuproníquel',
    'Estaño' => 'Estaño',
    'Acero' => 'Acero',
    'Bronce' => 'Bronce',
    'Aluminio' => 'Aluminio',
    'Alpaca' => 'Alpaca',
    'Madera' => 'Madera',
    'Acrílico' => 'Acrílico',
    'Barro' => 'Barro',
    'Plomo' => 'Plomo',
    'Electro' => 'Electro',
    'Latón' => 'Latón',
    'Hierro' => 'Hierro',
    'Zinc' => 'Zinc',
    'Otros' => 'Otros',
  ];
}

/**
 * Catálogo de Técnicas de Manufactura Numismática
 */
function obtenerOpcionesTecnicasNumismatica(): array
{
  return [
    '' => 'Seleccione técnica de manufactura...',
    'Grabado' => 'Grabado',
    'Calcografía' => 'Calcografía',
    'Litografía' => 'Litografía',
    'Xilografía' => 'Xilografía',
    'Serigrafía' => 'Serigrafía',
    'Flexografía' => 'Flexografía',
    'Impresión digital' => 'Impresión digital',
    'Fundición' => 'Fundición',
    'Vaciado' => 'Vaciado',
    'Recorte' => 'Recorte',
    'Troquelado' => 'Troquelado',
    'Cincelado' => 'Cincelado',
    'Fresado' => 'Fresado',
    'Pantografía' => 'Pantografía',
    'Forjado' => 'Forjado',
    'Acuñación' => 'Acuñación',
    'Otros' => 'Otros',
  ];
}

/**
 * Catálogo Oficial de Formas para Acervo Arqueológico
 */
function obtenerOpcionesFormasArqueologico(): array
{
  return [
    '' => 'Seleccione forma arqueológica...',
    'Contenedores' => [
      'Anafre' => 'Anafre',
      'Ánfora' => 'Ánfora',
      'Apaxtle' => 'Apaxtle',
      'Botella' => 'Botella',
      'Botellón' => 'Botellón',
      'Bracero/Cuenco' => 'Bracero/Cuenco',
      'Candelero' => 'Candelero',
      'Cántaro' => 'Cántaro',
      'Cazuela' => 'Cazuela',
      'Colador' => 'Colador',
      'Comal' => 'Comal',
      'Copa' => 'Copa',
      'Crisol' => 'Crisol',
      'Cuauhxicalli' => 'Cuauhxicalli',
      'Cucharón' => 'Cucharón',
      'Florero' => 'Florero',
      'Jarra' => 'Jarra',
      'Metate' => 'Metate',
      'Molde' => 'Molde',
      'Mortero/molcajete' => 'Mortero/molcajete',
      'Octecómatl' => 'Octecómatl',
      'Olla' => 'Olla',
      'Paleta de pintor' => 'Paleta de pintor',
      'Patojo' => 'Patojo',
      'Pichancha' => 'Pichancha',
      'Pipa' => 'Pipa',
      'Plato' => 'Plato',
      'Patojo' => 'Patojo',
      'Sahumerio/Sahumador' => 'Sahumerio/Sahumador',
      'Tableta/Tablilla' => 'Tableta/Tablilla',
      'Tapa' => 'Tapa',
      'Taza' => 'Taza',
      'Tecomate' => 'Tecomate',
      'Urna' => 'Urna',
      'Vasija múltiple' => 'Vasija múltiple',
      'Vasija Silbadora' => 'Vasija Silbadora',
      'Vasijas comunicantes' => 'Vasijas comunicantes',
      'Vaso' => 'Vaso',
      'Otro' => 'Otro',
    ],
    'Esculturas figurillas' => [
      'Antropomorfa' => 'Antropomorfa',
      'Candado' => 'Candado',
      'Deidad/Ser sobrenatural' => 'Deidad/Ser sobrenatural',
      'Elemento diverso' => 'Elemento diverso',
      'Fotomorfa' => 'Fotomorfa',
      'Hacha votiva' => 'Hacha votiva',
      'Maqueta' => 'Maqueta',
      'Palma' => 'Palma',
      'Porta estandarte' => 'Porta estandarte',
      'Riel' => 'Riel',
      'Glifo/símbolo' => 'Glifo/símbolo',
      'Yugo' => 'Yugo',
      'Zppmorfa' => 'Zppmorfa',
      'Otro' => 'Otro',
    ],
    'Artefactos' => [
      'Afilador' => 'Afilador',
      'Aguja' => 'Aguja',
      'Aguzador' => 'Aguzador',
      'Alfiler' => 'Alfiler',
      'Anzuelo' => 'Anzuelo',
      'Arpón' => 'Arpón',
      'Atlatl' => 'Atlatl',
      'Azada' => 'Azada',
      'Azuela' => 'Azuela',
      'Base/Banco' => 'Base/Banco',
      'Bastón plantador/Coa' => 'Bastón plantador/Coa',
      'Bifacial' => 'Bifacial',
      'Buril' => 'Buril',
      'Cabeza de maza' => 'Cabeza de maza',
      'Cincel' => 'Cincel',
      'Cuchillo/Raedera' => 'Cuchillo/Raedera',
      'Cuchillo / Raspador' => 'Cuchillo / Raspador',
      'Cuña' => 'Cuña',
      'Desecho de talla' => 'Desecho de talla',
      'Desfibrador' => 'Desfibrador',
      'Espátula' => 'Espátula',
      'Fisga' => 'Fisga',
      'Enderezador de flechas' => 'Enderezador de flechas',
      'Hacha' => 'Hacha',
      'Huso' => 'Huso',
      'Lanza' => 'Lanza',
      'Lasca retocada' => 'Lasca retocada',
      'Macana' => 'Macana',
      'Machacador' => 'Machacador',
      'Malacate' => 'Malacate',
      'Mano de metate' => 'Mano de metate',
      'Mano de mortero' => 'Mano de mortero',
      'Navaja no prismática' => 'Navaja no prismática',
      'Núcleo' => 'Núcleo',
      'Percutor' => 'Percutor',
      'Perforador' => 'Perforador',
      'Pesas de red' => 'Pesas de red',
      'Pinza' => 'Pinza',
      'Plomada' => 'Plomada',
      'Pulidor' => 'Pulidor',
      'Punta de proyectil' => 'Punta de proyectil',
      'Punzón' => 'Punzón',
      'Readera' => 'Readera',
      'Raspador' => 'Raspador',
      'Sello/pintadera' => 'Sello/pintadera',
      'Tajadera' => 'Tajadera',
      'Tajador' => 'Tajador',
      'Tope de Atlatl' => 'Tope de Atlatl',
    ],
    'Ornamento atavío objetos votivos' => [
      'Ajorca' => 'Ajorca',
      'Anillo' => 'Anillo',
      'Anteojera' => 'Anteojera',
      'Bezote' => 'Bezote',
      'Barbiquejo' => 'Barbiquejo',
      'Botón' => 'Botón',
      'Brazalete' => 'Brazalete',
      'Caracol Trabajado/decorado' => 'Caracol Trabajado/decorado',
      'Centro' => 'Centro',
      'Collar' => 'Collar',
      'Concha trabajada o decorada' => 'Concha trabajada o decorada',
      'Cráneo trabajado' => 'Cráneo trabajado',
      'Cruciforme' => 'Cruciforme',
      'Cucillo rostro/lanceolado' => 'Cucillo rostro/lanceolado',
      'Cuenta' => 'Cuenta',
      'Horquilla' => 'Horquilla',
      'Hueso trabajado y/o decorado' => 'Hueso trabajado y/o decorado',
      'Máscara' => 'Máscara',
      'Nariguera' => 'Nariguera',
      'Orejera' => 'Orejera',
      'Pectoral' => 'Pectoral',
      'Pendiente' => 'Pendiente',
      'Placa' => 'Placa',
      'Porta mechón' => 'Porta mechón',
      'Pulsera' => 'Pulsera',
      'Tap+on de orejera' => 'Tap+on de orejera',
      'Tesela' => 'Tesela',
      'Otro' => 'Otro',
    ],
    'Elemento arquitectónico' => [
      'Almena' => 'Almena',
      'Altar' => 'Altar',
      'Aro de juego de pelota' => 'Aro de juego de pelota',
      'Banqueta' => 'Banqueta',
      'Bloque/Sillar' => 'Bloque/Sillar',
      'Caño de desagüe' => 'Caño de desagüe',
      'Cista' => 'Cista',
      'Clavo' => 'Clavo',
      'Columna/Pilar' => 'Columna/Pilar',
      'Cornisa' => 'Cornisa',
      'Dintel' => 'Dintel',
      'Escalón /Peldaño' => 'Escalón /Peldaño',
      'Estela' => 'Estela',
      'Gárgola' => 'Gárgola',
      'Gozne/Rosca' => 'Gozne/Rosca',
      'Horcón' => 'Horcón',
      'Jamba' => 'Jamba',
      'Ladrillo' => 'Ladrillo',
      'Lápida' => 'Lápida',
      'Marcador de juego de pelota' => 'Marcador de juego de pelota',
      'Medallón' => 'Medallón',
      'Petrograbado' => 'Petrograbado',
      'Piedra careada' => 'Piedra careada',
      'Piedra de sacrificio' => 'Piedra de sacrificio',
      'Pintura mural' => 'Pintura mural',
      'Pintura rupestre' => 'Pintura rupestre',
      'Relieve' => 'Relieve',
      'Trono/asiento' => 'Trono/asiento',
      'Tzompantli' => 'Tzompantli',
      'Otro' => 'Otro',
    ],
    'Instrumento musical' => [
      'Carapacho o concha de tortuga' => 'Carapacho o concha de tortuga',
      'Cascabel' => 'Cascabel',
      'Chicahuaztli' => 'Chicahuaztli',
      'Concha marina' => 'Concha marina',
      'Flauta' => 'Flauta',
      'Gamitadera' => 'Gamitadera',
      'Hueso estriado/Omechicahuaztli' => 'Hueso estriado/Omechicahuaztli',
      'Ocarina simple' => 'Ocarina simple',
      'Raspador simple' => 'Raspador simple',
      'Sonaja' => 'Sonaja',
      'Tambor' => 'Tambor',
      'Teponaxtle' => 'Teponaxtle',
      'Trompeta' => 'Trompeta',
      'Otro' => 'Otro',
    ],
    'Misceláneos' => [
      'Aro' => 'Aro',
      'Disco' => 'Disco',
      'Esfera' => 'Esfera',
      'Espejo' => 'Espejo',
      'Excéntrico' => 'Excéntrico',
      'Tejo' => 'Tejo',
      'Otro' => 'Otro',
    ],
  ];
}

/**
 * Catálogo Oficial de Técnicas de Manufactura para Acervo Arqueológico
 */
function obtenerOpcionesTecnicasManufacturaArqueologico(): array
{
  return [
    '' => 'Seleccione técnica de manufactura...',
    'Arcillas y Mezclas' => [
      'Desgaste por abrasión' => 'Desgaste por abrasión',
      'Modelado' => 'Modelado',
      'Moldeado' => 'Moldeado',
      'Torno' => 'Torno',
      'Enrollado' => 'Enrollado',
      'Otros' => 'Otros',
    ],
    'Rocas y Minerales' => [
      'Desgaste por abrasión' => 'Desgaste por abrasión',
      'Desgaste por pulido' => 'Desgaste por pulido',
      'Tallada por percusión' => 'Tallada por percusión',
      'Tallada por presión' => 'Tallada por presión',
      'Otros' => 'Otros',
    ],
    'Metales y Aleaciones' => [
      'Cera perdida' => 'Cera perdida',
      'Falsa filigrana' => 'Falsa filigrana',
      'Filigrana' => 'Filigrana',
      'Fundido' => 'Fundido',
      'Hiladura/unión por costura' => 'Hiladura/unión por costura',
      'Laminado' => 'Laminado',
      'Otros' => 'Otros',
    ],
    'Material animal' => [
      'Astillamiento' => 'Astillamiento',
      'Tallado por presión' => 'Tallado por presión',
      'Tallado por percusión' => 'Tallado por percusión',
      'Desgaste por abrasión' => 'Desgaste por abrasión',
      'Desgaste por pulido' => 'Desgaste por pulido',
      'Corte' => 'Corte',
      'Aserrado o corte desgaste' => 'Aserrado o corte desgaste',
      'Perforación cilíndrica' => 'Perforación cilíndrica',
      'Perforación por desgaste' => 'Perforación por desgaste',
      'Otros' => 'Otros',
    ],
  ];
}

/**
 * Catálogo Oficial de Técnicas Decorativas para Acervo Arqueológico
 */
function obtenerOpcionesTecnicasDecorativasArqueologico(): array
{
  return [
    '' => 'Seleccione técnica decorativa...',
    'Arcillas y Mezclas' => [
      'Acanalado o ranurado' => 'Acanalado o ranurado',
      'Alisado' => 'Alisado',
      'Aplicación modelada o patillaje' => 'Aplicación modelada o patillaje',
      'Aplicación moldeada' => 'Aplicación moldeada',
      'Baño' => 'Baño',
      'Bruñido' => 'Bruñido',
      'Calado o corte' => 'Calado o corte',
      'Cloisonnée' => 'Cloisonnée',
      'Cocción diferencial o ennegrecido' => 'Cocción diferencial o ennegrecido',
      'Con engobe' => 'Con engobe',
      'Esgrafiado' => 'Esgrafiado',
      'Estuco' => 'Estuco',
      'Excavado' => 'Excavado',
      'Excavado champlevé' => 'Excavado champlevé',
      'Grabado' => 'Grabado',
      'Impresión' => 'Impresión',
      'Impresión de sello o sellado' => 'Impresión de sello o sellado',
      'Inciso' => 'Inciso',
      'Incrustación' => 'Incrustación',
      'Pellizcado' => 'Pellizcado',
      'Perforación' => 'Perforación',
      'Pintado' => 'Pintado',
      'Pintado al negativo' => 'Pintado al negativo',
      'Pintado seco, fresco, estuco pintado' => 'Pintado seco, fresco, estuco pintado',
      'Plomiza/Plumbate' => 'Plomiza/Plumbate',
      'Protuberancia' => 'Protuberancia',
      'Pulido' => 'Pulido',
      'Pulido a palillos' => 'Pulido a palillos',
      'Punzonado' => 'Punzonado',
      'Raspado' => 'Raspado',
      'Texturizado' => 'Texturizado',
      'Vidriado' => 'Vidriado',
      'Otros' => 'Otros',
    ],
    'Rocas y Minerales' => [
      'Acanaladura tallada' => 'Acanaladura tallada',
      'Aislado' => 'Aislado',
      'Alto relieve' => 'Alto relieve',
      'Bajo relieve' => 'Bajo relieve',
      'Baño' => 'Baño',
      'Bruñido' => 'Bruñido',
      'Esgrafiado' => 'Esgrafiado',
      'Estucado' => 'Estucado',
      'Inciso' => 'Inciso',
      'Con Incrustaciones' => 'Con Incrustaciones',
      'Perforación' => 'Perforación',
      'Pintado' => 'Pintado',
      'Ranurado' => 'Ranurado',
      'Otros' => 'Otros',
    ],
    'Metales y Aleaciones' => [
      'Bruñido' => 'Bruñido',
      'Calado' => 'Calado',
      'Inciso' => 'Inciso',
      'Perforación' => 'Perforación',
      'Pintado' => 'Pintado',
      'Pulido' => 'Pulido',
      'Protuberancia' => 'Protuberancia',
      'Repujado' => 'Repujado',
      'Otros' => 'Otros',
    ],
    'Material animal' => [
      'Acanalado' => 'Acanalado',
      'Baño' => 'Baño',
      'Bruñido' => 'Bruñido',
      'Calado' => 'Calado',
      'Incrustación' => 'Incrustación',
      'Egrafiado' => 'Egrafiado',
      'Estucado' => 'Estucado',
      'Grabado' => 'Grabado',
      'Inciso' => 'Inciso',
      'Perforación' => 'Perforación',
      'Pintado' => 'Pintado',
      'Pulido' => 'Pulido',
      'Tallado en relieve' => 'Tallado en relieve',
      'Otros' => 'Otros',
    ],
  ];
}

/**
 * Catálogo Oficial de Materiales para Acervo Arqueológico
 */
function obtenerOpcionesMaterialesArqueologico(): array
{
  return [
    '' => 'Seleccione material...',
    'Barro' => [
      'Barro' => 'Barro',
      'Otro' => 'Otro',
    ],
    'Roca' => [
      'Basalto' => 'Basalto',
      'Tezontle' => 'Tezontle',
      'Andesita' => 'Andesita',
      'Serpentina' => 'Serpentina',
      'Piedra verde' => 'Piedra verde',
      'Jade' => 'Jade',
      'Jadeíta' => 'Jadeíta',
      'Turqueza' => 'Turqueza',
      'Pizarra' => 'Pizarra',
      'Caliza' => 'Caliza',
      'Canto rodado' => 'Canto rodado',
      'Piedra pómez' => 'Piedra pómez',
      'Otro' => 'Otro',
    ],
    'Lítica' => [
      'Obsidiana' => 'Obsidiana',
      'Pedernal' => 'Pedernal',
      'Cuarzo' => 'Cuarzo',
      'Cristal de roca' => 'Cristal de roca',
      'Otro' => 'Otro',
    ],
    'Cuero' => [
      'Cuero/piel' => 'Cuero/piel',
      'Otro' => 'Otro',
    ],
    'Melacológico' => [
      'Concha' => 'Concha',
      'Coral' => 'Coral',
      'Hueso animal' => 'Hueso animal',
      'Hueso humano' => 'Hueso humano',
      'Colmillo' => 'Colmillo',
      'Otro' => 'Otro',
    ],
    'Madera' => [
      'Madera' => 'Madera',
      'Otro' => 'Otro',
    ],
  ];
}

/**
 * Renderiza un arreglo de opciones (planas o con optgroups) en etiquetas <option> y <optgroup>
 */
function renderizarOpcionesSelect(array $opciones, $seleccionado = null): string
{
  $html = '';
  foreach ($opciones as $key => $val) {
    if (is_array($val)) {
      $html .= '<optgroup label="' . htmlspecialchars((string)$key) . '">';
      foreach ($val as $subKey => $subVal) {
        $optionVal = is_int($subKey) ? $subVal : $subKey;
        $optionLabel = $subVal;
        $selected = ($seleccionado !== null && (string)$seleccionado === (string)$optionVal) ? ' selected' : '';
        $html .= '<option value="' . htmlspecialchars((string)$optionVal) . '"' . $selected . '>' . htmlspecialchars((string)$optionLabel) . '</option>';
      }
      $html .= '</optgroup>';
    } else {
      $optionVal = is_int($key) ? $val : $key;
      $optionLabel = $val;
      $selected = ($seleccionado !== null && (string)$seleccionado === (string)$optionVal) ? ' selected' : '';
      $html .= '<option value="' . htmlspecialchars((string)$optionVal) . '"' . $selected . '>' . htmlspecialchars((string)$optionLabel) . '</option>';
    }
  }
  return $html;
}

