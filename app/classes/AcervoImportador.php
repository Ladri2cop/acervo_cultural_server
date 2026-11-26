<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * Servicio de Importación y Generación de Plantillas Masivas de Acervo Cultural
 * Soporta Acervo General, Arqueológico y Numismático en formatos XLSX y CSV.
 */
class AcervoImportador
{
  /**
   * Tipos de acervo soportados
   */
  public const TIPOS_VALIDOS = ['general', 'arqueologico', 'numismatica'];

  /**
   * Retorna la configuración completa de columnas, encabezados y ejemplos para cada tipo de acervo
   *
   * @param string $tipo
   * @return array
   * @throws InvalidArgumentException
   */
  public static function getDefinicionAcervo(string $tipo): array
  {
    switch ($tipo) {
      case 'general':
        return [
          'tipo'          => 'general',
          'nombre'        => 'Acervo General (Plástica, Utilitaria, Mobiliario)',
          'tabla'         => 'acervo_general',
          'pk'            => 'id_acervo_general',
          'id_modulo'     => 5,
          'campo_titulo'  => 'nombre_titulo_pieza',
          'campos_clave'  => ['no_inventario', 'codigo_interno'],
          'columnas'      => [
            [
              'key'         => 'codigo_interno',
              'titulo'      => 'Código Interno',
              'requerido'   => false,
              'guia'        => 'Código alfanumérico interno (Ej. AG-2026-001)',
              'ejemplo_1'   => 'AG-2026-001',
              'ejemplo_2'   => 'AG-2026-002',
              'ejemplo_3'   => 'AG-2026-003'
            ],
            [
              'key'         => 'no_inventario',
              'titulo'      => 'No. de Inventario',
              'requerido'   => false,
              'guia'        => 'Clave oficial de inventario patrimonial (Ej. INV-MBART-0104)',
              'ejemplo_1'   => 'INV-MBART-0104',
              'ejemplo_2'   => 'INV-CP-2055',
              'ejemplo_3'   => 'INV-VNZ-089'
            ],
            [
              'key'         => 'nombre_titulo_pieza',
              'titulo'      => 'Nombre o Título de la Pieza (*)',
              'requerido'   => true,
              'guia'        => 'Obligatorio. Título formal o denominación descriptiva',
              'ejemplo_1'   => 'El Valle de México desde el Cerro del Tepeyac',
              'ejemplo_2'   => 'Rebozo tradicional de bolita con rapacejo fino',
              'ejemplo_3'   => 'Escultura tallada de San Francisco de Asís'
            ],
            [
              'key'         => 'ubicacion_fisica',
              'titulo'      => 'Ubicación Física / Sede (*)',
              'requerido'   => true,
              'guia'        => 'Obligatorio. Debe coincidir con un museo activo del sistema',
              'ejemplo_1'   => 'Museo del Paisaje José María Velasco',
              'ejemplo_2'   => 'Museo de Culturas Populares',
              'ejemplo_3'   => 'Museo Virreinal de Zinacantepec'
            ],
            [
              'key'         => 'ubicacion_topografica',
              'titulo'      => 'Ubicación Topográfica',
              'requerido'   => false,
              'guia'        => 'Sala, vitrina, muro, pasillo o bodega de resguardo',
              'ejemplo_1'   => 'Sala Principal, Muro Sur',
              'ejemplo_2'   => 'Sala 2, Vitrina Textil 03',
              'ejemplo_3'   => 'Capilla Abierta, Nicho 1'
            ],
            [
              'key'         => 'tipo',
              'titulo'      => 'Tipo de Obra',
              'requerido'   => false,
              'guia'        => 'Pintura, Escultura, Mobiliario, Cerámica, Textil, Objeto, etc.',
              'ejemplo_1'   => 'Pintura',
              'ejemplo_2'   => 'Textil',
              'ejemplo_3'   => 'Escultura'
            ],
            [
              'key'         => 'autor',
              'titulo'      => 'Autor / Artista',
              'requerido'   => false,
              'guia'        => 'Nombre del artista, taller o artesano (o Anónimo)',
              'ejemplo_1'   => 'José María Velasco',
              'ejemplo_2'   => 'Familia Gómez / Tenancingo',
              'ejemplo_3'   => 'Anónimo novohispano'
            ],
            [
              'key'         => 'epoca',
              'titulo'      => 'Época / Periodo',
              'requerido'   => false,
              'guia'        => 'Siglo XIX, Siglo XX, Virreinal/Colonial, Época actual, etc.',
              'ejemplo_1'   => 'Siglo XIX',
              'ejemplo_2'   => 'Siglo XX',
              'ejemplo_3'   => 'Virreinal/Colonial'
            ],
            [
              'key'         => 'anio',
              'titulo'      => 'Año de Creación',
              'requerido'   => false,
              'guia'        => 'Año numérico o aproximado (Ej. 1894, ca. 1750)',
              'ejemplo_1'   => '1894',
              'ejemplo_2'   => '1972',
              'ejemplo_3'   => 'ca. 1740'
            ],
            [
              'key'         => 'tecnica',
              'titulo'      => 'Técnica',
              'requerido'   => false,
              'guia'        => 'Óleo sobre tela, Talla en madera, Telar de cintura, Acuarela, etc.',
              'ejemplo_1'   => 'Óleo sobre tela',
              'ejemplo_2'   => 'Telar de cintura',
              'ejemplo_3'   => 'Talla en madera y estofado'
            ],
            [
              'key'         => 'material',
              'titulo'      => 'Material',
              'requerido'   => false,
              'guia'        => 'Lienzo, Algodón, Madera de cedro, Bronce, etc.',
              'ejemplo_1'   => 'Lino y pigmentos al óleo',
              'ejemplo_2'   => 'Algodón mercerizado teñido con añil',
              'ejemplo_3'   => 'Madera policromada y lámina de oro'
            ],
            [
              'key'         => 'cm',
              'titulo'      => 'Dimensiones Bidimensionales (cm)',
              'requerido'   => false,
              'guia'        => 'Alto x Ancho en centímetros (Ej. 102 x 155 cm)',
              'ejemplo_1'   => '102 x 155 cm',
              'ejemplo_2'   => '210 x 85 cm',
              'ejemplo_3'   => ''
            ],
            [
              'key'         => 'medidas',
              'titulo'      => 'Medidas Tridimensionales',
              'requerido'   => false,
              'guia'        => 'Alto x Ancho x Profundidad (Ej. 85 x 40 x 35 cm)',
              'ejemplo_1'   => '',
              'ejemplo_2'   => '',
              'ejemplo_3'   => '85 x 40 x 35 cm'
            ],
            [
              'key'         => 'peso',
              'titulo'      => 'Peso',
              'requerido'   => false,
              'guia'        => 'Peso aproximado con unidad (Ej. 14.5 kg, 650 g)',
              'ejemplo_1'   => '14.5 kg con marco',
              'ejemplo_2'   => '680 g',
              'ejemplo_3'   => '18.2 kg'
            ],
            [
              'key'         => 'lote',
              'titulo'      => 'No. de Lote',
              'requerido'   => false,
              'guia'        => 'Identificador de lote de ingreso o subasta',
              'ejemplo_1'   => 'LOT-1894-JMV',
              'ejemplo_2'   => 'LOT-TEX-1972',
              'ejemplo_3'   => 'LOT-NOV-001'
            ],
            [
              'key'         => 'origen',
              'titulo'      => 'Origen / Procedencia',
              'requerido'   => false,
              'guia'        => 'Ciudad, estado o región de procedencia original',
              'ejemplo_1'   => 'Temascalcingo, Estado de México',
              'ejemplo_2'   => 'Tenancingo, Estado de México',
              'ejemplo_3'   => 'Zinacantepec, Estado de México'
            ],
            [
              'key'         => 'estado_conservacion',
              'titulo'      => 'Estado de Conservación',
              'requerido'   => false,
              'guia'        => 'Bueno, Regular o Malo',
              'ejemplo_1'   => 'Bueno',
              'ejemplo_2'   => 'Bueno',
              'ejemplo_3'   => 'Regular'
            ],
            [
              'key'         => 'descripcion',
              'titulo'      => 'Descripción Detallada',
              'requerido'   => false,
              'guia'        => 'Descripción visual, iconográfica y características estilísticas',
              'ejemplo_1'   => 'Vista panorámica campestre del Valle lacustre de México con perspectiva aérea luminosa.',
              'ejemplo_2'   => 'Pieza tejida en telar tradicional con diseño clásico jaspeado y empuntado artesanal.',
              'ejemplo_3'   => 'Talla escultórica exenta con vestiduras estofadas y encarnaciones al óleo.'
            ],
            [
              'key'         => 'observaciones',
              'titulo'      => 'Observaciones',
              'requerido'   => false,
              'guia'        => 'Condiciones especiales, intervenciones previas o notas de registro',
              'ejemplo_1'   => 'Restaurado en 2018 por el taller de conservación institucional.',
              'ejemplo_2'   => 'Pieza galardonada en certamen estatal de artesanías.',
              'ejemplo_3'   => 'Presenta leve pérdida de policromía en la base posterior.'
            ]
          ]
        ];

      case 'arqueologico':
        return [
          'tipo'          => 'arqueologico',
          'nombre'        => 'Acervo Arqueológico (Prehispánico y Lítico)',
          'tabla'         => 'acervo_arqueologico',
          'pk'            => 'id_acervo_arq',
          'id_modulo'     => 3,
          'campo_titulo'  => 'nombre_titulo_pieza',
          'campos_clave'  => ['no_inventario_scyt', 'no_registro_inah', 'codigo_interno'],
          'columnas'      => [
            [
              'key'         => 'codigo_interno',
              'titulo'      => 'Código Interno',
              'requerido'   => false,
              'guia'        => 'Código alfanumérico interno (Ej. ARQ-APX-012)',
              'ejemplo_1'   => 'ARQ-APX-012',
              'ejemplo_2'   => 'ARQ-CALI-084',
              'ejemplo_3'   => 'ARQ-TEOT-301'
            ],
            [
              'key'         => 'no_inventario_scyt',
              'titulo'      => 'No. Inventario SCyT',
              'requerido'   => false,
              'guia'        => 'Inventario de la Secretaría de Cultura y Turismo',
              'ejemplo_1'   => 'SCYT-ARQ-0045',
              'ejemplo_2'   => 'SCYT-ARQ-0182',
              'ejemplo_3'   => 'SCYT-ARQ-0520'
            ],
            [
              'key'         => 'no_registro_inah',
              'titulo'      => 'No. Registro INAH',
              'requerido'   => false,
              'guia'        => 'Cédula o folio oficial de registro ante el INAH',
              'ejemplo_1'   => 'INAH-MEX-28910',
              'ejemplo_2'   => 'INAH-MEX-15403',
              'ejemplo_3'   => 'INAH-MEX-44912'
            ],
            [
              'key'         => 'otros',
              'titulo'      => 'Otros Códigos / Marcas',
              'requerido'   => false,
              'guia'        => 'Etiquetas de campo, números de excavación o catálogo antiguo',
              'ejemplo_1'   => 'POZO-B-CAPA-3',
              'ejemplo_2'   => 'TEMP-MAY-04',
              'ejemplo_3'   => 'CAT-ANT-1960'
            ],
            [
              'key'         => 'nombre_titulo_pieza',
              'titulo'      => 'Nombre o Título de la Pieza (*)',
              'requerido'   => true,
              'guia'        => 'Obligatorio. Denominación del artefacto prehispánico',
              'ejemplo_1'   => 'Vasija trípode con efigie de Tláloc',
              'ejemplo_2'   => 'Figurilla antropomorfa femenina de barro',
              'ejemplo_3'   => 'Punta de proyectil foliácea de obsidiana'
            ],
            [
              'key'         => 'ubicacion_fisica',
              'titulo'      => 'Ubicación Física / Museo (*)',
              'requerido'   => true,
              'guia'        => 'Obligatorio. Debe coincidir con un museo activo',
              'ejemplo_1'   => 'Museo de Antropología e Historia del Estado de México',
              'ejemplo_2'   => 'Museo Arqueológico de Apaxco',
              'ejemplo_3'   => 'Museo Dr. Román Piña Chan'
            ],
            [
              'key'         => 'ubicacion_topografica',
              'titulo'      => 'Ubicación Topográfica',
              'requerido'   => false,
              'guia'        => 'Sala temática, vitrina o gaveta de reserva',
              'ejemplo_1'   => 'Sala Clásico, Vitrina 4',
              'ejemplo_2'   => 'Vitrina Central Cerámica',
              'ejemplo_3'   => 'Gaveta Lítica 02'
            ],
            [
              'key'         => 'epoca',
              'titulo'      => 'Época / Horizonte Cultural',
              'requerido'   => false,
              'guia'        => 'Prehispánica, Periodo Clásico, Pleistoceno, etc.',
              'ejemplo_1'   => 'Periodo Clásico',
              'ejemplo_2'   => 'Prehispánica',
              'ejemplo_3'   => 'Prehispánica'
            ],
            [
              'key'         => 'procedencia',
              'titulo'      => 'Procedencia / Sitio Arqueológico',
              'requerido'   => false,
              'guia'        => 'Yacimiento, municipio o zona de salvamento',
              'ejemplo_1'   => 'Teotihuacán, Estado de México',
              'ejemplo_2'   => 'Calixtlahuaca, Toluca',
              'ejemplo_3'   => 'Malinalco, Estado de México'
            ],
            [
              'key'         => 'material',
              'titulo'      => 'Material',
              'requerido'   => false,
              'guia'        => 'Barro cocido, Obsidiana gris, Basalto, Jadeíta, Concha, etc.',
              'ejemplo_1'   => 'Barro cocido y pigmentos minerales',
              'ejemplo_2'   => 'Barro cocido anaranjado fino',
              'ejemplo_3'   => 'Obsidiana verde dorada'
            ],
            [
              'key'         => 'medidas',
              'titulo'      => 'Medidas (Alto x Ancho x Profundidad)',
              'requerido'   => false,
              'guia'        => 'Dimensiones en cm (Ej. 18.5 x 14.2 x 13.0 cm)',
              'ejemplo_1'   => '18.5 x 14.2 x 13.0 cm',
              'ejemplo_2'   => '12.0 x 6.5 x 4.0 cm',
              'ejemplo_3'   => '7.2 x 2.8 x 0.6 cm'
            ],
            [
              'key'         => 'forma',
              'titulo'      => 'Forma / Tipología',
              'requerido'   => false,
              'guia'        => 'Olla, Cuenco, Cajete, Figurilla, Hacha, Vaso trípode, etc.',
              'ejemplo_1'   => 'Vaso trípode',
              'ejemplo_2'   => 'Figurilla',
              'ejemplo_3'   => 'Punta de proyectil'
            ],
            [
              'key'         => 'tecnica_manufactura',
              'titulo'      => 'Técnica de Manufactura',
              'requerido'   => false,
              'guia'        => 'Modelado a mano, Moldeado, Pastillaje, Percusión, etc.',
              'ejemplo_1'   => 'Modelado a mano y pastillaje',
              'ejemplo_2'   => 'Moldeado y pastillaje al relieve',
              'ejemplo_3'   => 'Talla lítica por percusión y retoque por presión'
            ],
            [
              'key'         => 'tecnica_decorativa',
              'titulo'      => 'Técnica Decorativa',
              'requerido'   => false,
              'guia'        => 'Incisión, Bruñido, Pintura al fresco, Esgrafiado, etc.',
              'ejemplo_1'   => 'Pintura al fresco policroma e incisión',
              'ejemplo_2'   => 'Bruñido y engobe rojo',
              'ejemplo_3'   => 'Sin decoración'
            ],
            [
              'key'         => 'coleccion',
              'titulo'      => 'Colección',
              'requerido'   => false,
              'guia'        => 'Colección de origen o fondo patrimonial',
              'ejemplo_1'   => 'Colección Arqueológica Teotihuacana',
              'ejemplo_2'   => 'Colección Matlatzinca del Valle de Toluca',
              'ejemplo_3'   => 'Colección de Lítica Prehispánica'
            ],
            [
              'key'         => 'obtencion',
              'titulo'      => 'Modo de Obtención',
              'requerido'   => false,
              'guia'        => 'Excavación controlada, Donación, Decomiso o Hallazgo fortuito',
              'ejemplo_1'   => 'Proyecto de excavación arqueológica 1975',
              'ejemplo_2'   => 'Donación de particulares',
              'ejemplo_3'   => 'Salvamento arqueológico carretera'
            ],
            [
              'key'         => 'numero_pieza_por_lote',
              'titulo'      => 'No. Piezas por Lote',
              'requerido'   => false,
              'guia'        => 'Cantidad de fragmentos o piezas (1 si es objeto íntegro)',
              'ejemplo_1'   => '1',
              'ejemplo_2'   => '1',
              'ejemplo_3'   => '1'
            ],
            [
              'key'         => 'representacion',
              'titulo'      => 'Representación Iconográfica',
              'requerido'   => false,
              'guia'        => 'Antropomorfa, Zoomorfa, Fitomorfa, Deidad, etc.',
              'ejemplo_1'   => 'Deidad de la lluvia (Tláloc)',
              'ejemplo_2'   => 'Antropomorfa femenina con tocado',
              'ejemplo_3'   => 'No aplica'
            ],
            [
              'key'         => 'estado_conservacion',
              'titulo'      => 'Estado de Conservación',
              'requerido'   => false,
              'guia'        => 'Bueno, Regular o Malo',
              'ejemplo_1'   => 'Bueno',
              'ejemplo_2'   => 'Regular',
              'ejemplo_3'   => 'Bueno'
            ],
            [
              'key'         => 'descripcion',
              'titulo'      => 'Descripción',
              'requerido'   => false,
              'guia'        => 'Descripción morfológica y estilística del artefacto',
              'ejemplo_1'   => 'Recipiente globular con soportes cónicos huecos y anteojeras aplicadas al pastillaje.',
              'ejemplo_2'   => 'Representación corporal esquemática con perforaciones auriculares y collar simple.',
              'ejemplo_3'   => 'Instrumento lítico bifacial con bordes aserrados en óptimo filo de penetración.'
            ],
            [
              'key'         => 'observaciones',
              'titulo'      => 'Observaciones',
              'requerido'   => false,
              'guia'        => 'Faltantes, huellas de uso, consolidaciones o marcas',
              'ejemplo_1'   => 'Consolidación de pasta en borde superior realizada en laboratorio.',
              'ejemplo_2'   => 'Leve fractura en extremidad inferior derecha; consolidada.',
              'ejemplo_3'   => 'Pieza completa sin fracturas.'
            ]
          ]
        ];

      case 'numismatica':
        return [
          'tipo'          => 'numismatica',
          'nombre'        => 'Acervo Numismático (Monedas, Billetes, Medallas)',
          'tabla'         => 'acervo_numismatica',
          'pk'            => 'id_acervo_numismatica',
          'id_modulo'     => 4,
          'campo_titulo'  => 'denominacion',
          'campos_clave'  => ['no_inventario', 'codigo_interno'],
          'columnas'      => [
            [
              'key'         => 'codigo_interno',
              'titulo'      => 'Código Interno',
              'requerido'   => false,
              'guia'        => 'Código alfanumérico interno (Ej. NUM-MN-055)',
              'ejemplo_1'   => 'NUM-MN-055',
              'ejemplo_2'   => 'NUM-MN-118',
              'ejemplo_3'   => 'NUM-FH-009'
            ],
            [
              'key'         => 'no_inventario',
              'titulo'      => 'No. de Inventario',
              'requerido'   => false,
              'guia'        => 'Clave oficial de inventario numismático',
              'ejemplo_1'   => 'INV-NUM-0084',
              'ejemplo_2'   => 'INV-NUM-0210',
              'ejemplo_3'   => 'INV-FH-0033'
            ],
            [
              'key'         => 'denominacion',
              'titulo'      => 'Denominación / Valor Facial (*)',
              'requerido'   => true,
              'guia'        => 'Obligatorio. Valor de la pieza (Ej. 8 Reales, 1 Peso, 50 Pesos)',
              'ejemplo_1'   => '8 Reales de Carlos IV',
              'ejemplo_2'   => 'Billete de 5 Pesos del Banco de México',
              'ejemplo_3'   => 'Ficha de Hacienda de 1 Almud'
            ],
            [
              'key'         => 'tipo_obra',
              'titulo'      => 'Tipo de Obra',
              'requerido'   => false,
              'guia'        => 'Moneda, Billete, Medalla conmemorativa, Ficha de hacienda, etc.',
              'ejemplo_1'   => 'Moneda',
              'ejemplo_2'   => 'Billete',
              'ejemplo_3'   => 'Ficha de hacienda'
            ],
            [
              'key'         => 'ubicacion_fisica',
              'titulo'      => 'Ubicación Física / Museo (*)',
              'requerido'   => true,
              'guia'        => 'Obligatorio. Debe coincidir con un museo activo',
              'ejemplo_1'   => 'Museo de Numismática',
              'ejemplo_2'   => 'Museo de Numismática',
              'ejemplo_3'   => 'Museo Virreinal de Zinacantepec'
            ],
            [
              'key'         => 'ubicacion_topografica',
              'titulo'      => 'Ubicación Topográfica',
              'requerido'   => false,
              'guia'        => 'Sala, vitrina de seguridad, charola o cajón numismático',
              'ejemplo_1'   => 'Sala Virreinal, Charola 3, Espacio 12',
              'ejemplo_2'   => 'Vitrina Papel Moneda, Carpeta 01',
              'ejemplo_3'   => 'Gabinete Histórico, Charola 1'
            ],
            [
              'key'         => 'material',
              'titulo'      => 'Material / Metal',
              'requerido'   => false,
              'guia'        => 'Plata Ley .903, Oro, Cobre, Bronce, Papel moneda, etc.',
              'ejemplo_1'   => 'Plata Ley .903',
              'ejemplo_2'   => 'Papel de algodón con fibras de seguridad',
              'ejemplo_3'   => 'Latón troquelado'
            ],
            [
              'key'         => 'fecha_epoca',
              'titulo'      => 'Año de Acuñación / Época',
              'requerido'   => false,
              'guia'        => 'Año visible o periodo histórico (Ej. 1798, Siglo XIX)',
              'ejemplo_1'   => '1798',
              'ejemplo_2'   => '1925',
              'ejemplo_3'   => 'ca. 1880'
            ],
            [
              'key'         => 'ensayador',
              'titulo'      => 'Ensayador / Ceca',
              'requerido'   => false,
              'guia'        => 'Siglas de ensayador y ceca emisora (Ej. Mo FM, Mo JJ)',
              'ejemplo_1'   => 'Mo FM (Casa de Moneda de México)',
              'ejemplo_2'   => 'ABNC / México',
              'ejemplo_3'   => 'Hacienda La Providencia'
            ],
            [
              'key'         => 'cm',
              'titulo'      => 'Diámetro (cm)',
              'requerido'   => false,
              'guia'        => 'Diámetro en centímetros (Ej. 3.9 cm)',
              'ejemplo_1'   => '3.9 cm',
              'ejemplo_2'   => '',
              'ejemplo_3'   => '2.6 cm'
            ],
            [
              'key'         => 'medidas',
              'titulo'      => 'Medidas / Dimensiones (Billetes)',
              'requerido'   => false,
              'guia'        => 'Largo x Ancho en cm para papel moneda (Ej. 15.5 x 6.6 cm)',
              'ejemplo_1'   => 'Grosor: 2.5 mm; Peso: 27.07 g',
              'ejemplo_2'   => '15.5 x 6.6 cm',
              'ejemplo_3'   => 'Grosor: 1.2 mm'
            ],
            [
              'key'         => 'dimensiones',
              'titulo'      => 'Dimensiones Generales',
              'requerido'   => false,
              'guia'        => 'Resumen métrico general',
              'ejemplo_1'   => '39 mm de módulo, peso estándar 27 g',
              'ejemplo_2'   => 'Formato rectangular estándar',
              'ejemplo_3'   => 'Módulo circular 26 mm'
            ],
            [
              'key'         => 'descripcion_cara_a',
              'titulo'      => 'Descripción Anverso (Cara A)',
              'requerido'   => false,
              'guia'        => 'Busto, efigie, lema, escudo o leyenda anversa',
              'ejemplo_1'   => 'Busto laureado mirando a la derecha de Carlos IV, leyenda: CAROLUS IIII DEI GRATIA 1798.',
              'ejemplo_2'   => 'Retrato estilizado de la corregidora Josefa Ortiz de Domínguez con sello institucional.',
              'ejemplo_3'   => 'Monograma grabado con el nombre de la hacienda y orla perimetral.'
            ],
            [
              'key'         => 'descripcion_cara_b',
              'titulo'      => 'Descripción Reverso (Cara B)',
              'requerido'   => false,
              'guia'        => 'Escudo de armas, valor nominal, columnas de Hércules o reverso',
              'ejemplo_1'   => 'Escudo coronado de Castilla y León entre columnas de Hércules con Plus Ultra, leyenda: HISPAN ET IND REX Mo 8R F M.',
              'ejemplo_2'   => 'Vista panorámica de la sede central del Banco de México en la Ciudad de México.',
              'ejemplo_3'   => 'Denominación en cifra: 1 ALMUD DE MAÍZ.'
            ],
            [
              'key'         => 'estado_conservacion',
              'titulo'      => 'Estado de Conservación',
              'requerido'   => false,
              'guia'        => 'Bueno, Regular o Malo (o escala numismática: MBC, EBC, SC)',
              'ejemplo_1'   => 'Bueno',
              'ejemplo_2'   => 'Bueno',
              'ejemplo_3'   => 'Regular'
            ],
            [
              'key'         => 'observaciones',
              'titulo'      => 'Observaciones',
              'requerido'   => false,
              'guia'        => 'Pátina, marcas de cizalla, resellos de época o notas de rareza',
              'ejemplo_1'   => 'Excelente pátina de monetario y acuñación nítida en relieves.',
              'ejemplo_2'   => 'Serie sin circular conservada en mica protectora libre de ácido.',
              'ejemplo_3'   => 'Presenta leve desgaste de circulación característico en la época porfiriana.'
            ]
          ]
        ];

      default:
        throw new InvalidArgumentException("Tipo de acervo desconocido: {$tipo}");
    }
  }

  /**
   * Construye el objeto Spreadsheet de la plantilla con estilos institucionales,
   * hoja complementaria de catálogos y validaciones de datos (listas desplegables).
   *
   * @param string $tipo
   * @param string $formato
   * @return Spreadsheet
   */
  public static function construirSpreadsheetPlantilla(string $tipo, string $formato = 'xlsx'): Spreadsheet
  {
    $tipo = strtolower(trim($tipo));
    $formato = strtolower(trim($formato));

    if (!in_array($tipo, self::TIPOS_VALIDOS, true)) {
      $tipo = 'general';
    }
    if (!in_array($formato, ['xlsx', 'csv'], true)) {
      $formato = 'xlsx';
    }

    $def = self::getDefinicionAcervo($tipo);
    $columnas = $def['columnas'];

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle(substr($def['nombre'], 0, 31));

    // Estilos institucionales
    $burgundyHex = '6B1D2F';
    $goldHex     = 'B38E2E';
    $grayHeader  = '4A5568';

    // Fila 1: Títulos descriptivos para el usuario
    // Fila 2: Claves de columna para identificación del sistema
    // Fila 3: Instrucciones / Guía de llenado
    // Filas 4, 5, 6: Filas de ejemplo realistas

    $colIndex = 1;
    foreach ($columnas as $col) {
      $colLetter = Coordinate::stringFromColumnIndex($colIndex);

      // Fila 1: Título amigable
      $sheet->setCellValueExplicit("{$colLetter}1", $col['titulo'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

      // Fila 2: Clave de base de datos
      $sheet->setCellValueExplicit("{$colLetter}2", $col['key'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

      // Fila 3: Guía rápida
      $sheet->setCellValueExplicit("{$colLetter}3", $col['guia'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

      // Fila 4: Ejemplo 1
      $sheet->setCellValueExplicit("{$colLetter}4", $col['ejemplo_1'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

      // Fila 5: Ejemplo 2
      $sheet->setCellValueExplicit("{$colLetter}5", $col['ejemplo_2'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

      // Fila 6: Ejemplo 3
      $sheet->setCellValueExplicit("{$colLetter}6", $col['ejemplo_3'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

      $colIndex++;
    }

    $totalCols = count($columnas);
    $lastColLetter = Coordinate::stringFromColumnIndex($totalCols);

    // Formatear encabezados si es XLSX
    if ($formato === 'xlsx') {
      // Fila 1: Títulos
      $sheet->getStyle("A1:{$lastColLetter}1")->applyFromArray([
        'font' => [
          'bold'  => true,
          'color' => ['rgb' => 'FFFFFF'],
          'size'  => 10
        ],
        'fill' => [
          'fillType'   => Fill::FILL_SOLID,
          'startColor' => ['rgb' => $burgundyHex]
        ],
        'alignment' => [
          'horizontal' => Alignment::HORIZONTAL_CENTER,
          'vertical'   => Alignment::VERTICAL_CENTER,
          'wrapText'   => true
        ]
      ]);
      $sheet->getRowDimension(1)->setRowHeight(28);

      // Fila 2: Claves técnicas (sistema)
      $sheet->getStyle("A2:{$lastColLetter}2")->applyFromArray([
        'font' => [
          'bold'  => false,
          'italic'=> true,
          'color' => ['rgb' => '2D3748'],
          'size'  => 9
        ],
        'fill' => [
          'fillType'   => Fill::FILL_SOLID,
          'startColor' => ['rgb' => 'EDF2F7']
        ],
        'alignment' => [
          'horizontal' => Alignment::HORIZONTAL_CENTER,
          'vertical'   => Alignment::VERTICAL_CENTER
        ]
      ]);
      $sheet->getRowDimension(2)->setRowHeight(18);

      // Fila 3: Guía de llenado
      $sheet->getStyle("A3:{$lastColLetter}3")->applyFromArray([
        'font' => [
          'italic'=> true,
          'color' => ['rgb' => '744210'],
          'size'  => 8.5
        ],
        'fill' => [
          'fillType'   => Fill::FILL_SOLID,
          'startColor' => ['rgb' => 'FEFCBF'] // Amarillo sutil
        ],
        'alignment' => [
          'horizontal' => Alignment::HORIZONTAL_LEFT,
          'vertical'   => Alignment::VERTICAL_CENTER,
          'wrapText'   => true
        ]
      ]);
      $sheet->getRowDimension(3)->setRowHeight(24);

      // Filas de ejemplo 4 a 6
      $sheet->getStyle("A4:{$lastColLetter}6")->applyFromArray([
        'font' => [
          'italic'=> false,
          'color' => ['rgb' => '4A5568'],
          'size'  => 9
        ],
        'alignment' => [
          'vertical' => Alignment::VERTICAL_CENTER
        ]
      ]);

      // Bordes tenues en la cuadrícula de datos
      $sheet->getStyle("A1:{$lastColLetter}6")->getBorders()->getAllBorders()->applyFromArray([
        'borderStyle' => Border::BORDER_THIN,
        'color'       => ['rgb' => 'CBD5E0']
      ]);

      // Congelar paneles en fila 4 (para que los títulos queden fijos al hacer scroll)
      $sheet->freezePane('A4');

      // Auto-dimensionar columnas con un ancho mínimo
      for ($i = 1; $i <= $totalCols; $i++) {
        $letter = Coordinate::stringFromColumnIndex($i);
        $sheet->getColumnDimension($letter)->setAutoSize(true);
      }

      // Construir hoja complementaria de catálogos y aplicar listas desplegables (DataValidation)
      $catalogRanges = self::construirHojaCatalogos($spreadsheet, $tipo);

      for ($i = 1; $i <= $totalCols; $i++) {
        $letter = Coordinate::stringFromColumnIndex($i);
        $colKey = $columnas[$i - 1]['key'] ?? '';

        if (!empty($colKey) && isset($catalogRanges[$colKey])) {
          $valRange = $catalogRanges[$colKey];
          $validation = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
          $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
          $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
          $validation->setAllowBlank(true);
          $validation->setShowDropDown(true);
          $validation->setShowInputMessage(true);
          $validation->setShowErrorMessage(true);
          $validation->setErrorTitle('Elemento de Catálogo Oficial');
          $validation->setError('Se sugiere seleccionar una opción del catálogo oficial o verificar la ortografía.');
          $validation->setPromptTitle('Catálogo Oficial');
          $validation->setPrompt('Haga clic en la flecha para desplegar las opciones oficiales del catálogo.');
          $validation->setFormula1($valRange);

          // Aplicar la validación de la fila 4 a la 500 para agilizar la captura sin necesidad de escribir
          $sheet->setDataValidation("{$letter}4:{$letter}500", $validation);
        }
      }

      // Asegurar que la hoja activa visible sea la primera (Captura de Acervo)
      $spreadsheet->setActiveSheetIndex(0);
    }

    return $spreadsheet;
  }

  /**
   * Genera y descarga una plantilla oficial en formato XLSX o CSV
   *
   * @param string $tipo 'general', 'arqueologico', 'numismatica'
   * @param string $formato 'xlsx', 'csv'
   * @return void
   */
  public static function generarPlantilla(string $tipo, string $formato = 'xlsx'): void
  {
    $tipo = strtolower(trim($tipo));
    $formato = strtolower(trim($formato));

    if (!in_array($tipo, self::TIPOS_VALIDOS, true)) {
      $tipo = 'general';
    }
    if (!in_array($formato, ['xlsx', 'csv'], true)) {
      $formato = 'xlsx';
    }

    $spreadsheet = self::construirSpreadsheetPlantilla($tipo, $formato);

    $timestamp = date('Ymd_His');
    $fileName = "plantilla_importacion_{$tipo}_{$timestamp}.{$formato}";

    // Limpiar buffers previos para evitar corrupción binaria
    while (ob_get_level()) {
      ob_end_clean();
    }

    if ($formato === 'xlsx') {
      header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      header('Content-Disposition: attachment; filename="' . $fileName . '"');
      header('Cache-Control: max-age=0');
      header('Pragma: public');

      $writer = new Xlsx($spreadsheet);
      $writer->save('php://output');
    } else {
      // Para CSV: Enviar BOM UTF-8 (\xEF\xBB\xBF) para compatibilidad absoluta con Excel en Windows
      header('Content-Type: text/csv; charset=UTF-8');
      header('Content-Disposition: attachment; filename="' . $fileName . '"');
      header('Cache-Control: max-age=0');
      header('Pragma: public');

      echo "\xEF\xBB\xBF";
      $writer = new Csv($spreadsheet);
      $writer->setDelimiter(',');
      $writer->setEnclosure('"');
      $writer->setLineEnding("\r\n");
      $writer->setSheetIndex(0);
      $writer->save('php://output');
    }

    exit;
  }

  /**
   * Construye una hoja complementaria con los catálogos oficiales del sistema
   * y retorna el mapeo de rangos para las listas desplegables (DataValidation)
   *
   * @param Spreadsheet $spreadsheet
   * @param string $tipo
   * @return array [field_key => "'Catalogos'!$Col$2:$Col$Max"]
   */
  private static function construirHojaCatalogos(Spreadsheet $spreadsheet, string $tipo): array
  {
    $catSheet = $spreadsheet->createSheet();
    $catSheet->setTitle('Catalogos');

    $db = Db::connect();

    // 1. Catálogo común: Museos / Sedes Custodias
    $museos = [];
    try {
      $stmt = $db->query("SELECT nombre_museo FROM museos WHERE status = 1 ORDER BY nombre_museo ASC");
      $museos = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
    } catch (Throwable $e) {}
    if (empty($museos) && function_exists('obtenerOpcionesMuseos')) {
      $mArr = obtenerOpcionesMuseos();
      unset($mArr[''], $mArr['Otro']);
      $museos = array_values($mArr);
    }
    $museos[] = 'Otro';

    // 2. Catálogo común: Épocas y Periodos Históricos
    $epocas = [];
    try {
      $stmt = $db->query("SELECT nombre_epoca_periodo FROM epocas_periodos WHERE status = 1 ORDER BY id_epoca_periodo ASC");
      $epocas = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
    } catch (Throwable $e) {}
    if (empty($epocas) && function_exists('obtenerOpcionesEpocas')) {
      $eArr = obtenerOpcionesEpocas();
      unset($eArr['']);
      $epocas = array_values($eArr);
    }

    // 3. Catálogo común: Estados de Conservación
    $estadosConservacion = ['Bueno', 'Regular', 'Malo'];

    // 4. Catálogos específicos según el tipo de acervo
    $columnasCatalogos = [];
    $mapeoRangos = [];

    // Col A: Museos
    $columnasCatalogos[] = [
      'titulo' => 'Museos y Sedes (Ubicación)',
      'items'  => $museos,
      'keys'   => ['ubicacion_fisica']
    ];

    // Col B: Épocas
    $columnasCatalogos[] = [
      'titulo' => 'Épocas y Periodos',
      'items'  => $epocas,
      'keys'   => ['epoca', 'fecha_epoca']
    ];

    // Col C: Estado de Conservación
    $columnasCatalogos[] = [
      'titulo' => 'Estado de Conservación',
      'items'  => $estadosConservacion,
      'keys'   => ['estado_conservacion']
    ];

    if ($tipo === 'general') {
      // Tipos de Obra
      $tiposObra = [];
      try {
        $stmt = $db->query("SELECT nombre_tipo_obra FROM tipo_obras WHERE status = 1 ORDER BY categoria ASC, nombre_tipo_obra ASC");
        $tiposObra = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
      } catch (Throwable $e) {}
      if (empty($tiposObra)) {
        $tiposObra = ['Pintura', 'Escultura', 'Mobiliario', 'Cerámica', 'Textil', 'Dibujo', 'Grabado', 'Fotografía', 'Acuarela', 'Objeto', 'Otro'];
      }
      $columnasCatalogos[] = [
        'titulo' => 'Tipos de Obra',
        'items'  => $tiposObra,
        'keys'   => ['tipo']
      ];

      // Técnicas
      $tecnicas = [];
      try {
        $stmt = $db->query("SELECT nombre_tecnica FROM tecnicas WHERE status = 1 ORDER BY nombre_tecnica ASC");
        $tecnicas = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
      } catch (Throwable $e) {}
      if (empty($tecnicas)) {
        $tecnicas = ['Óleo sobre tela', 'Acrílico', 'Acuarela', 'Talla en madera', 'Telar de cintura', 'Vaciado sobre bronce', 'Grabado', 'Fotografía', 'Mixta', 'Otros'];
      }
      $columnasCatalogos[] = [
        'titulo' => 'Técnicas',
        'items'  => $tecnicas,
        'keys'   => ['tecnica']
      ];

      // Materiales
      $materiales = [];
      try {
        $stmt = $db->query("SELECT nombre_material FROM materiales WHERE status = 1 ORDER BY nombre_material ASC");
        $materiales = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
      } catch (Throwable $e) {}
      if (empty($materiales)) {
        $materiales = ['Lienzo', 'Madera de cedro', 'Algodón', 'Bronce', 'Mármol', 'Papel', 'Barro', 'Hierro', 'Otro'];
      }
      $columnasCatalogos[] = [
        'titulo' => 'Materiales',
        'items'  => $materiales,
        'keys'   => ['material']
      ];

    } elseif ($tipo === 'arqueologico') {
      // Formas
      $formas = function_exists('obtenerOpcionesFormasArqueologico') ? array_values(array_filter(obtenerOpcionesFormasArqueologico())) : ['Olla', 'Cuenco', 'Cajete', 'Figurilla', 'Vaso trípode', 'Punta de proyectil', 'Hacha', 'Plato', 'Incensario', 'Otro'];
      $columnasCatalogos[] = [
        'titulo' => 'Formas y Tipologías',
        'items'  => $formas,
        'keys'   => ['forma']
      ];

      // Manufactura
      $tecManuf = function_exists('obtenerOpcionesTecnicasManufacturaArqueologico') ? array_values(array_filter(obtenerOpcionesTecnicasManufacturaArqueologico())) : ['Modelado a mano', 'Moldeado', 'Pastillaje', 'Percusión', 'Presión', 'Otro'];
      $columnasCatalogos[] = [
        'titulo' => 'Técnicas de Manufactura',
        'items'  => $tecManuf,
        'keys'   => ['tecnica_manufactura']
      ];

      // Decorativa
      $tecDeco = function_exists('obtenerOpcionesTecnicasDecorativasArqueologico') ? array_values(array_filter(obtenerOpcionesTecnicasDecorativasArqueologico())) : ['Incisión', 'Bruñido', 'Pintura al fresco', 'Esgrafiado', 'Engobe', 'Sin decoración', 'Otro'];
      $columnasCatalogos[] = [
        'titulo' => 'Técnicas Decorativas',
        'items'  => $tecDeco,
        'keys'   => ['tecnica_decorativa']
      ];

      // Materiales Arqueológicos
      $matArq = function_exists('obtenerOpcionesMaterialesArqueologico') ? array_values(array_filter(obtenerOpcionesMaterialesArqueologico())) : ['Barro cocido', 'Obsidiana', 'Basalto', 'Jadeíta', 'Concha', 'Hueso', 'Piedra verde', 'Otro'];
      $columnasCatalogos[] = [
        'titulo' => 'Materiales Arqueológicos',
        'items'  => $matArq,
        'keys'   => ['material']
      ];

      // Modos de Obtención
      $obtencion = ['Proyecto de excavación arqueológica', 'Donación de particulares', 'Decomiso oficial', 'Salvamento arqueológico', 'Hallazgo fortuito', 'Otro'];
      $columnasCatalogos[] = [
        'titulo' => 'Modos de Obtención',
        'items'  => $obtencion,
        'keys'   => ['obtencion']
      ];

      // Representaciones
      $rep = ['Deidad (Tláloc, Quetzalcóatl, etc.)', 'Antropomorfa femenina', 'Antropomorfa masculina', 'Zoomorfa', 'Fitomorfa', 'Geométrica', 'No aplica', 'Otro'];
      $columnasCatalogos[] = [
        'titulo' => 'Representaciones Iconográficas',
        'items'  => $rep,
        'keys'   => ['representacion']
      ];

    } elseif ($tipo === 'numismatica') {
      // Tipos de Obra
      $tiposNum = function_exists('obtenerOpcionesTiposNumismatica') ? array_values(array_filter(obtenerOpcionesTiposNumismatica())) : ['Moneda', 'Billete o notafilia', 'Medallística', 'Tlacos', 'Ficha de hacienda', 'Condecoraciones', 'Filatelia', 'Loterofilia', 'Acciones', 'Documentos fiduciarios', 'Sellos', 'Otro'];
      $columnasCatalogos[] = [
        'titulo' => 'Tipos de Obra Numismática',
        'items'  => $tiposNum,
        'keys'   => ['tipo_obra']
      ];

      // Materiales Numismática
      $matNum = function_exists('obtenerOpcionesMaterialesNumismatica') ? array_values(array_filter(obtenerOpcionesMaterialesNumismatica())) : ['Plata Ley .903', 'Plata Ley .720', 'Plata Ley .925', 'Oro Ley .875', 'Oro Ley .900', 'Cobre', 'Bronce', 'Latón', 'Cuproníquel', 'Níquel', 'Aluminio', 'Papel de algodón con fibras de seguridad', 'Polímero', 'Otro'];
      $columnasCatalogos[] = [
        'titulo' => 'Metales y Materiales',
        'items'  => $matNum,
        'keys'   => ['material']
      ];

      // Técnicas numismáticas
      $tecNum = function_exists('obtenerOpcionesTecnicasNumismatica') ? array_values(array_filter(obtenerOpcionesTecnicasNumismatica())) : ['Acuñación a volante', 'Acuñación a prensa', 'Fundición', 'Grabado calcográfico', 'Litografía', 'Troquelado', 'Cincelado', 'Vaciado', 'Otro'];
      $columnasCatalogos[] = [
        'titulo' => 'Técnicas Numismáticas',
        'items'  => $tecNum,
        'keys'   => ['tecnica']
      ];
    }

    // Helper recursivo para aplanar opciones (soporta arrays multidimensionales y optgroups)
    $flatten = function ($arr) use (&$flatten) {
      $flat = [];
      if (!is_array($arr)) {
        $str = trim((string)$arr);
        if ($str !== '' && !str_starts_with(mb_strtolower($str), 'seleccione')) {
          $flat[] = $str;
        }
        return $flat;
      }
      foreach ($arr as $val) {
        if (is_array($val)) {
          $flat = array_merge($flat, $flatten($val));
        } else {
          $str = trim((string)$val);
          if ($str !== '' && !str_starts_with(mb_strtolower($str), 'seleccione')) {
            $flat[] = $str;
          }
        }
      }
      return $flat;
    };

    // Escribir en la hoja Catálogos
    $colIdx = 1;
    $burgundyHex = '6B1D2F';

    foreach ($columnasCatalogos as $colData) {
      $letter = Coordinate::stringFromColumnIndex($colIdx);

      // Encabezado
      $catSheet->setCellValueExplicit("{$letter}1", $colData['titulo'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

      $items = array_values(array_unique(array_filter($flatten($colData['items'] ?? []))));
      if (empty($items)) {
        $items = ['N/A'];
      }
      $totalItems = count($items);

      for ($row = 0; $row < $totalItems; $row++) {
        $rowNum = $row + 2;
        $catSheet->setCellValueExplicit("{$letter}{$rowNum}", $items[$row], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
      }

      $maxRow = max(2, $totalItems + 1);

      // Mapear cada clave a su fórmula de rango en la hoja Catalogos
      foreach ($colData['keys'] as $key) {
        $mapeoRangos[$key] = "'Catalogos'!\${$letter}\$2:\${$letter}\${$maxRow}";
      }

      // Estilizar encabezado de catálogo
      $catSheet->getStyle("{$letter}1")->applyFromArray([
        'font' => [
          'bold'  => true,
          'color' => ['rgb' => 'FFFFFF'],
          'size'  => 10
        ],
        'fill' => [
          'fillType'   => Fill::FILL_SOLID,
          'startColor' => ['rgb' => $burgundyHex]
        ],
        'alignment' => [
          'horizontal' => Alignment::HORIZONTAL_CENTER,
          'vertical'   => Alignment::VERTICAL_CENTER
        ]
      ]);

      // Bordes tenues en la lista
      $catSheet->getStyle("{$letter}1:{$letter}{$maxRow}")->getBorders()->getAllBorders()->applyFromArray([
        'borderStyle' => Border::BORDER_THIN,
        'color'       => ['rgb' => 'E2E8F0']
      ]);

      $catSheet->getColumnDimension($letter)->setAutoSize(true);
      $colIdx++;
    }

    $catSheet->getRowDimension(1)->setRowHeight(26);
    $catSheet->freezePane('A2');

    return $mapeoRangos;
  }

  /**
   * Normaliza una cadena de texto eliminando acentos, caracteres especiales y espacios redundantes
   *
   * @param string $str
   * @return string
   */
  public static function normalizarTexto(string $str): string
  {
    $str = mb_strtolower(trim($str), 'UTF-8');
    $str = str_replace(
      ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ'],
      ['a', 'e', 'i', 'o', 'u', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'u', 'n'],
      $str
    );
    // Remover asteriscos, paréntesis y caracteres de puntuación
    $str = preg_replace('/[^a-z0-9_]/i', '', $str);
    return trim($str);
  }

  /**
   * Identifica el mapeo de columnas analizando los encabezados de fila 1 y/o fila 2
   *
   * @param array $row1 Primera fila del archivo
   * @param array $row2 Segunda fila del archivo
   * @param array $definicionColumnas
   * @return array [colIndex => fieldKey]
   */
  public static function resolverMapeoColumnas(array $row1, array $row2, array $definicionColumnas): array
  {
    $mapa = [];
    $clavesValidas = array_column($definicionColumnas, 'key');

    // Mapeo inverso de sinónimos amigables
    $sinonimos = [
      'nombreotitulodelapieza'     => 'nombre_titulo_pieza',
      'nombreotitulo'             => 'nombre_titulo_pieza',
      'titulodelapieza'           => 'nombre_titulo_pieza',
      'titulo'                    => 'nombre_titulo_pieza',
      'nombre'                    => 'nombre_titulo_pieza',
      'denominacion'              => 'denominacion',
      'denominacionvalorfacial'   => 'denominacion',
      'valorfacial'               => 'denominacion',
      'codigointerno'             => 'codigo_interno',
      'codigo'                    => 'codigo_interno',
      'noinventario'              => 'no_inventario',
      'numeroinventario'          => 'no_inventario',
      'noinventarioscyt'          => 'no_inventario_scyt',
      'inventarioscyt'            => 'no_inventario_scyt',
      'noregistroinah'            => 'no_registro_inah',
      'registroinah'              => 'no_registro_inah',
      'inah'                      => 'no_registro_inah',
      'otros'                     => 'otros',
      'otroscodigos'              => 'otros',
      'ubicacionfisica'           => 'ubicacion_fisica',
      'ubicacionfisicasede'       => 'ubicacion_fisica',
      'museo'                     => 'ubicacion_fisica',
      'sede'                      => 'ubicacion_fisica',
      'ubicaciontopografica'      => 'ubicacion_topografica',
      'topografica'               => 'ubicacion_topografica',
      'tipo'                      => 'tipo',
      'tipodeobra'                => 'tipo_obra',
      'autor'                     => 'autor',
      'artista'                   => 'autor',
      'epoca'                     => 'epoca',
      'periodo'                   => 'epoca',
      'epocaperiodo'              => 'epoca',
      'horizonticultural'         => 'epoca',
      'anio'                      => 'anio',
      'ano'                       => 'anio',
      'aniodecreacion'            => 'anio',
      'anodecreacion'             => 'anio',
      'tecnica'                   => 'tecnica',
      'material'                  => 'material',
      'metal'                     => 'material',
      'cm'                        => 'cm',
      'diametro'                  => 'cm',
      'medidas'                   => 'medidas',
      'dimensiones'               => 'dimensiones',
      'dimensionesgenerales'      => 'dimensiones',
      'peso'                      => 'peso',
      'lote'                      => 'lote',
      'origen'                    => 'origen',
      'procedencia'               => 'procedencia',
      'estadodeconservacion'      => 'estado_conservacion',
      'conservacion'              => 'estado_conservacion',
      'descripcion'               => 'descripcion',
      'descripciondetallada'      => 'descripcion',
      'observaciones'             => 'observaciones',
      'forma'                     => 'forma',
      'tecnicademanufactura'      => 'tecnica_manufactura',
      'tecnicadecorativa'         => 'tecnica_decorativa',
      'coleccion'                 => 'coleccion',
      'obtencion'                 => 'obtencion',
      'mododeobtencion'           => 'obtencion',
      'piezaporlote'              => 'numero_pieza_por_lote',
      'numeropiezaporlote'        => 'numero_pieza_por_lote',
      'representacion'            => 'representacion',
      'ensayador'                 => 'ensayador',
      'ceca'                      => 'ensayador',
      'descripcioncaraa'          => 'descripcion_cara_a',
      'anverso'                   => 'descripcion_cara_a',
      'descripcionanverso'        => 'descripcion_cara_a',
      'descripcioncarab'          => 'descripcion_cara_b',
      'reverso'                   => 'descripcion_cara_b',
      'descripcionreverso'        => 'descripcion_cara_b'
    ];

    $totalCols = max(count($row1), count($row2));

    for ($i = 0; $i < $totalCols; $i++) {
      $valRow2 = isset($row2[$i]) ? trim((string)$row2[$i]) : '';
      $valRow1 = isset($row1[$i]) ? trim((string)$row1[$i]) : '';

      // 1. Revisar si la fila 2 contiene exactamente una clave de columna válida (formato plantilla oficial)
      if (!empty($valRow2) && in_array($valRow2, $clavesValidas, true)) {
        $mapa[$i] = $valRow2;
        continue;
      }

      // 2. Revisar si la fila 1 contiene la clave de base de datos directa
      if (!empty($valRow1) && in_array($valRow1, $clavesValidas, true)) {
        $mapa[$i] = $valRow1;
        continue;
      }

      // 3. Revisar normalización de fila 2
      $norm2 = self::normalizarTexto($valRow2);
      if (!empty($norm2) && isset($sinonimos[$norm2]) && in_array($sinonimos[$norm2], $clavesValidas, true)) {
        $mapa[$i] = $sinonimos[$norm2];
        continue;
      }

      // 4. Revisar normalización de fila 1
      $norm1 = self::normalizarTexto($valRow1);
      if (!empty($norm1) && isset($sinonimos[$norm1]) && in_array($sinonimos[$norm1], $clavesValidas, true)) {
        $mapa[$i] = $sinonimos[$norm1];
        continue;
      }
    }

    // Asegurar que bajo ninguna circunstancia se mapeen claves ajenas a este tipo de acervo
    return array_filter($mapa, function ($colKey) use ($clavesValidas) {
      return in_array($colKey, $clavesValidas, true);
    });
  }

  /**
   * Detecta si la plantilla cargada corresponde a un tipo de acervo distinto al seleccionado
   *
   * @param Spreadsheet $spreadsheet
   * @param array $rowsData
   * @param string $tipoAcervo 'general', 'arqueologico', 'numismatica'
   * @return array|null Retorna array con ['status' => false, 'msg' => ...] si hay discrepancia, o null si es compatible
   */
  public static function verificarDiscrepanciaPlantilla(Spreadsheet $spreadsheet, array $rowsData, string $tipoAcervo): ?array
  {
    $sheet = $spreadsheet->getActiveSheet();
    $sheetTitle = mb_strtolower(trim($sheet->getTitle()), 'UTF-8');

    $row1 = $rowsData[0] ?? [];
    $row2 = $rowsData[1] ?? [];

    $row1Str = mb_strtolower(implode(' ', array_map('strval', $row1)), 'UTF-8');
    $row2Str = mb_strtolower(implode(' ', array_map('strval', $row2)), 'UTF-8');
    $fullHeader = "{$sheetTitle} {$row1Str} {$row2Str}";

    // Firmas inequívocas de cada tipo de plantilla oficial
    $esFirmaNumismatica = (
      strpos($sheetTitle, 'numism') !== false ||
      strpos($fullHeader, 'denominacion') !== false ||
      strpos($fullHeader, 'valor facial') !== false ||
      strpos($fullHeader, 'ensayador') !== false ||
      strpos($fullHeader, 'descripcion_cara_a') !== false ||
      strpos($fullHeader, 'anverso (cara a)') !== false
    );

    $esFirmaArqueologico = (
      strpos($sheetTitle, 'arqueol') !== false ||
      strpos($fullHeader, 'inventario scyt') !== false ||
      strpos($fullHeader, 'no_inventario_scyt') !== false ||
      strpos($fullHeader, 'registro inah') !== false ||
      strpos($fullHeader, 'no_registro_inah') !== false ||
      strpos($fullHeader, 'forma / tipología') !== false ||
      strpos($fullHeader, 'tecnica_manufactura') !== false ||
      strpos($fullHeader, 'tecnica_decorativa') !== false
    );

    $esFirmaGeneral = (
      strpos($sheetTitle, 'general') !== false ||
      strpos($sheetTitle, 'plástica') !== false ||
      (
        (strpos($fullHeader, 'tipo de obra') !== false || strpos($fullHeader, 'autor / artista') !== false || strpos($fullHeader, 'año de creación') !== false) &&
        !$esFirmaArqueologico && !$esFirmaNumismatica
      )
    );

    $tipoDetectado = null;
    if ($esFirmaNumismatica) {
      $tipoDetectado = 'numismatica';
    } elseif ($esFirmaArqueologico) {
      $tipoDetectado = 'arqueologico';
    } elseif ($esFirmaGeneral) {
      $tipoDetectado = 'general';
    }

    if ($tipoDetectado !== null && $tipoDetectado !== $tipoAcervo) {
      $nombres = [
        'general'      => 'Acervo General (Plástica, Utilitaria y Escultura)',
        'arqueologico' => 'Acervo Arqueológico (Prehispánico y Lítico)',
        'numismatica'  => 'Acervo Numismático (Monedas, Billetes y Medallas)'
      ];

      $nombreDetectado = $nombres[$tipoDetectado] ?? ucfirst($tipoDetectado);
      $nombreSeleccionado = $nombres[$tipoAcervo] ?? ucfirst($tipoAcervo);

      return [
        'status' => false,
        'msg'    => "Discrepancia de plantilla detectada: El archivo cargado corresponde a una plantilla de '{$nombreDetectado}', pero actualmente tiene seleccionado '{$nombreSeleccionado}' en el sistema. Por favor, seleccione '{$nombreDetectado}' en el Paso 1 para importar este archivo, o bien descargue y utilice la plantilla oficial de '{$nombreSeleccionado}'."
      ];
    }

    return null;
  }

  /**
   * Procesa la importación masiva de un archivo Excel o CSV
   *
   * @param array $file Subida $_FILES['archivo']
   * @param string $tipoAcervo 'general', 'arqueologico', 'numismatica'
   * @param array $opciones [manejo_duplicados, auto_aprobar, id_usuario, id_museo_usuario, etc.]
   * @return array Resumen del proceso
   */
  public static function procesarImportacion(array $file, string $tipoAcervo, array $opciones = []): array
  {
    $tipoAcervo = strtolower(trim($tipoAcervo));
    if (!in_array($tipoAcervo, self::TIPOS_VALIDOS, true)) {
      return [
        'status'  => false,
        'msg'     => 'Tipo de acervo inválido o no reconocido.'
      ];
    }

    // 1. Validar subida segura de archivo
    if (!isset($file['error']) || is_array($file['error'])) {
      return ['status' => false, 'msg' => 'Parámetros de subida inválidos.'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
      $mensajesError = [
        UPLOAD_ERR_INI_SIZE   => 'El archivo excede el tamaño máximo permitido por el servidor (upload_max_filesize).',
        UPLOAD_ERR_FORM_SIZE  => 'El archivo excede el tamaño especificado en el formulario.',
        UPLOAD_ERR_PARTIAL    => 'El archivo fue subido solo parcialmente.',
        UPLOAD_ERR_NO_FILE    => 'No se seleccionó ningún archivo para subir.',
        UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal en el servidor.',
        UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en el disco.',
        UPLOAD_ERR_EXTENSION  => 'Una extensión de PHP detuvo la subida.'
      ];
      return ['status' => false, 'msg' => $mensajesError[$file['error']] ?? 'Error al subir archivo.'];
    }

    // 2. Validar tamaño (máximo 20 MB)
    $maxBytes = 20 * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
      return ['status' => false, 'msg' => 'El archivo supera el tamaño máximo permitido de 20 MB.'];
    }

    // 3. Validar extensión en lista blanca estricta
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
      return ['status' => false, 'msg' => 'Formato no soportado. Debe ser un archivo .xlsx, .xls o .csv.'];
    }

    $def = self::getDefinicionAcervo($tipoAcervo);
    $tabla = $def['tabla'];
    $pk = $def['pk'];
    $idModulo = (int)$def['id_modulo'];
    $campoTitulo = $def['campo_titulo'];

    // Opciones operativas
    $manejoDuplicados = $opciones['manejo_duplicados'] ?? 'skip'; // 'skip', 'update', 'error'
    $autoAprobar      = !empty($opciones['auto_aprobar']);
    $idUsuario        = $opciones['id_usuario'] ? (int)$opciones['id_usuario'] : (get_user_id() ?: null);
    $idMuseoUsuario   = $opciones['id_museo_usuario'] ?? null;
    $museoUsuarioNombre = $opciones['museo_usuario_nombre'] ?? null;
    $esSuperAdmin     = !empty($opciones['es_super_admin']);

    // 4. Cargar archivo con PhpSpreadsheet
    try {
      $spreadsheet = IOFactory::load($file['tmp_name']);
      $sheet = $spreadsheet->getActiveSheet();
      $rowsData = $sheet->toArray(null, true, true, false);
    } catch (Throwable $e) {
      return ['status' => false, 'msg' => 'No se pudo leer la hoja de cálculo: ' . $e->getMessage()];
    }

    if (empty($rowsData) || count($rowsData) < 2) {
      return ['status' => false, 'msg' => 'El archivo subido se encuentra vacío o no contiene filas de datos.'];
    }

    // Verificar si el archivo cargado pertenece a otro tipo de acervo
    $discrepancia = self::verificarDiscrepanciaPlantilla($spreadsheet, $rowsData, $tipoAcervo);
    if ($discrepancia !== null) {
      return $discrepancia;
    }

    // 5. Determinar fila de encabezados y mapeo
    $row1 = $rowsData[0] ?? [];
    $row2 = $rowsData[1] ?? [];
    $colMap = self::resolverMapeoColumnas($row1, $row2, $def['columnas']);

    if (empty($colMap) || !in_array($campoTitulo, $colMap, true)) {
      return [
        'status' => false,
        'msg'    => "No fue posible identificar la columna obligatoria '{$campoTitulo}' en los encabezados. Verifique que la hoja de cálculo conserve los títulos oficiales de la plantilla."
      ];
    }

    // Determinar en qué índice de fila comienzan los datos reales
    $startRowIndex = 1; // 0-based
    if (isset($rowsData[1])) {
      $valCheck2 = implode(' ', array_slice($rowsData[1], 0, 5));
      if (in_array($campoTitulo, $rowsData[1], true) || strpos($valCheck2, 'codigo_interno') !== false || strpos($valCheck2, 'nombre_titulo') !== false) {
        $startRowIndex = 2;
      }
    }
    if (isset($rowsData[2])) {
      $valCheck3 = implode(' ', array_slice($rowsData[2], 0, 5));
      if (stripos($valCheck3, 'obligatorio') !== false || stripos($valCheck3, 'guia') !== false || stripos($valCheck3, 'ejemplo') !== false) {
        $startRowIndex = 3;
      }
    }

    // 6. Pre-cargar museos autorizados para validación y consultar esquema de la tabla
    $db = Db::connect();

    // Obtener las columnas reales existentes en la tabla destino para blindaje estricto SQL 1054
    $colsTableStmt = $db->query("DESCRIBE {$tabla}");
    $columnasRealesTabla = $colsTableStmt ? $colsTableStmt->fetchAll(PDO::FETCH_COLUMN) : [];
    $validColsFlip = array_flip($columnasRealesTabla);

    $museosValidos = [];
    try {
      $stmtM = $db->query("SELECT id_museo, nombre_museo FROM museos WHERE status = 1");
      while ($rM = $stmtM->fetch(PDO::FETCH_ASSOC)) {
        $museosValidos[self::normalizarTexto($rM['nombre_museo'])] = $rM['nombre_museo'];
      }
    } catch (Throwable $e) {}

    // 7. Preparar índices en memoria para detección de duplicados en la base de datos
    $inventariosExistentes = [];
    $codigosExistentes = [];

    // Cargar inventarios existentes
    if ($tipoAcervo === 'arqueologico') {
      $stmtEx = $db->query("SELECT id_acervo_arq, no_inventario_scyt, codigo_interno, no_registro_inah FROM acervo_arqueologico WHERE status = 1 OR estatus_revision != 'rechazado'");
      while ($r = $stmtEx->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($r['no_inventario_scyt'])) $inventariosExistentes[trim($r['no_inventario_scyt'])] = $r['id_acervo_arq'];
        if (!empty($r['codigo_interno'])) $codigosExistentes[trim($r['codigo_interno'])] = $r['id_acervo_arq'];
        if (!empty($r['no_registro_inah'])) $inventariosExistentes[trim($r['no_registro_inah'])] = $r['id_acervo_arq'];
      }
    } else {
      $stmtEx = $db->query("SELECT {$pk}, no_inventario, codigo_interno FROM {$tabla} WHERE status = 1 OR estatus_revision != 'rechazado'");
      while ($r = $stmtEx->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($r['no_inventario'])) $inventariosExistentes[trim($r['no_inventario'])] = $r[$pk];
        if (!empty($r['codigo_interno'])) $codigosExistentes[trim($r['codigo_interno'])] = $r[$pk];
      }
    }

    // Estructuras de resultados
    $totalFilasLeidas = 0;
    $filasProcesadas  = 0;
    $insertadas       = 0;
    $actualizadas     = 0;
    $omitidas         = 0;
    $errores          = [];
    $advertencias     = [];

    // Control de unicidad intra-archivo (evita que un mismo archivo tenga 2 filas con el mismo código)
    $loteInventariosVistos = [];
    $loteCodigosVistos = [];

    // Iniciar transacción de base de datos para atomicidad segura
    $db->beginTransaction();

    try {
      $totalFilasArchivo = count($rowsData);

      for ($i = $startRowIndex; $i < $totalFilasArchivo; $i++) {
        $rowNumFisico = $i + 1;
        $rowCells = $rowsData[$i];

        // Verificar si la fila está completamente vacía
        $tieneContenido = false;
        foreach ($rowCells as $c) {
          if ($c !== null && trim((string)$c) !== '') {
            $tieneContenido = true;
            break;
          }
        }
        if (!$tieneContenido) {
          continue; // Saltar fila en blanco
        }

        $totalFilasLeidas++;

        // Extraer valores según mapeo
        $datosFila = [];
        foreach ($colMap as $cIndex => $fKey) {
          $val = isset($rowCells[$cIndex]) ? trim((string)$rowCells[$cIndex]) : '';
          $datosFila[$fKey] = $val;
        }

        // Si la fila contiene una repetición de instrucciones o encabezados, ignorarla
        $valTitulo = $datosFila[$campoTitulo] ?? '';
        if (stripos($valTitulo, 'obligatorio') !== false || stripos($valTitulo, 'ejemplo') !== false) {
          continue;
        }

        // VALIDACIÓN 1: Título o denominación obligatorio
        if (empty($valTitulo)) {
          $errores[] = [
            'fila'   => $rowNumFisico,
            'codigo' => $datosFila['no_inventario'] ?? ($datosFila['codigo_interno'] ?? 'S/N'),
            'error'  => "El campo obligatorio '" . ($campoTitulo === 'denominacion' ? 'Denominación' : 'Nombre o Título') . "' está vacío."
          ];
          continue;
        }

        // VALIDACIÓN 2: Ubicación física (Sede / Museo)
        $ubiFisica = trim($datosFila['ubicacion_fisica'] ?? '');
        if (empty($ubiFisica)) {
          if (!empty($museoUsuarioNombre)) {
            $ubiFisica = $museoUsuarioNombre;
            $datosFila['ubicacion_fisica'] = $ubiFisica;
          } else {
            $errores[] = [
              'fila'   => $rowNumFisico,
              'codigo' => $datosFila['no_inventario'] ?? ($datosFila['codigo_interno'] ?? $valTitulo),
              'error'  => "La 'Ubicación Física' es obligatoria. No se especificó museo custodio."
            ];
            continue;
          }
        } else {
          // Tenancy: si el usuario no es superadmin y tiene museo asignado, restringir
          if (!$esSuperAdmin && !empty($idMuseoUsuario) && !empty($museoUsuarioNombre)) {
            $normUbi = self::normalizarTexto($ubiFisica);
            $normUserM = self::normalizarTexto($museoUsuarioNombre);
            if ($normUbi !== $normUserM) {
              $errores[] = [
                'fila'   => $rowNumFisico,
                'codigo' => $datosFila['no_inventario'] ?? ($datosFila['codigo_interno'] ?? $valTitulo),
                'error'  => "Restricción de museo: Su cuenta sólo tiene permisos para registrar piezas en '{$museoUsuarioNombre}'. La fila indica '{$ubiFisica}'."
              ];
              continue;
            }
          }
        }

        // VALIDACIÓN 3: Claves y duplicados
        $noInv = trim($datosFila['no_inventario'] ?? ($datosFila['no_inventario_scyt'] ?? ''));
        $codInt = trim($datosFila['codigo_interno'] ?? '');

        // Validar colisiones intra-archivo
        if (!empty($noInv)) {
          if (isset($loteInventariosVistos[$noInv])) {
            $filaPrevia = $loteInventariosVistos[$noInv];
            $errores[] = [
              'fila'   => $rowNumFisico,
              'codigo' => $noInv,
              'error'  => "Número de inventario '{$noInv}' duplicado dentro del mismo archivo (ya apareció en la fila {$filaPrevia})."
            ];
            continue;
          }
          $loteInventariosVistos[$noInv] = $rowNumFisico;
        }

        if (!empty($codInt)) {
          if (isset($loteCodigosVistos[$codInt])) {
            $filaPrevia = $loteCodigosVistos[$codInt];
            $advertencias[] = [
              'fila'   => $rowNumFisico,
              'codigo' => $codInt,
              'msg'    => "Código interno '{$codInt}' repetido dentro del mismo archivo (fila previa {$filaPrevia})."
            ];
          }
          $loteCodigosVistos[$codInt] = $rowNumFisico;
        }

        // Verificar duplicados contra la base de datos
        $idExistente = null;
        if (!empty($noInv) && isset($inventariosExistentes[$noInv])) {
          $idExistente = $inventariosExistentes[$noInv];
        } elseif (!empty($codInt) && isset($codigosExistentes[$codInt])) {
          $idExistente = $codigosExistentes[$codInt];
        }

        if ($idExistente !== null) {
          if ($manejoDuplicados === 'skip') {
            $omitidas++;
            $advertencias[] = [
              'fila'   => $rowNumFisico,
              'codigo' => !empty($noInv) ? $noInv : $codInt,
              'msg'    => "Registro omitido: Ya existe una pieza en el sistema con la misma clave de inventario/código (ID: {$idExistente})."
            ];
            continue;
          } elseif ($manejoDuplicados === 'error') {
            $errores[] = [
              'fila'   => $rowNumFisico,
              'codigo' => !empty($noInv) ? $noInv : $codInt,
              'error'  => "Conflicto de duplicidad: Ya existe una pieza con el código/inventario '{$noInv}' en el sistema (ID {$idExistente})."
            ];
            continue;
          } elseif ($manejoDuplicados === 'update') {
            // Actualizar pieza existente
            $updatePayload = [];
            foreach ($datosFila as $k => $v) {
              if ($v !== '') {
                $updatePayload[$k] = $v;
              }
            }
            $updatePayload['updated_at'] = now();
            // Blindaje estricto: descartar cualquier columna que no exista físicamente en la tabla
            $updatePayload = array_intersect_key($updatePayload, $validColsFlip);

            $setParts = [];
            $setParams = ['id' => $idExistente];
            foreach ($updatePayload as $colName => $colVal) {
              $setParts[] = "`{$colName}` = :{$colName}";
              $setParams[$colName] = $colVal;
            }

            if (!empty($setParts)) {
              $sqlUp = "UPDATE {$tabla} SET " . implode(', ', $setParts) . " WHERE {$pk} = :id";
              $stmtUp = $db->prepare($sqlUp);
              $stmtUp->execute($setParams);
              $actualizadas++;
              $filasProcesadas++;
            }
            continue;
          }
        }

        // VALIDACIÓN 4: Inserción de nueva pieza
        $insertPayload = $datosFila;
        $insertPayload['id_modulo'] = $idModulo;
        $insertPayload['id_usuario_creador'] = $idUsuario;
        $insertPayload['created_at'] = now();

        if ($autoAprobar) {
          $insertPayload['status'] = 1;
          $insertPayload['estatus_revision'] = 'aprobado';
          $insertPayload['id_usuario_revisor'] = $idUsuario;
          $insertPayload['fecha_revision'] = now();
        } else {
          $insertPayload['status'] = 0;
          $insertPayload['estatus_revision'] = 'pendiente';
        }

        // Blindaje estricto: descartar cualquier columna que no exista físicamente en la tabla destino
        $insertPayload = array_intersect_key($insertPayload, $validColsFlip);

        // Construir consulta INSERT parametrizada
        $cols = array_keys($insertPayload);
        $escapedCols = array_map(function ($c) { return "`{$c}`"; }, $cols);
        $placeholders = array_map(function ($c) { return ":{$c}"; }, $cols);

        $sqlIns = "INSERT INTO {$tabla} (" . implode(', ', $escapedCols) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmtIns = $db->prepare($sqlIns);
        $stmtIns->execute($insertPayload);

        $nuevoId = (int)$db->lastInsertId();

        // Actualizar índices en memoria para las siguientes filas del mismo lote
        if (!empty($noInv)) $inventariosExistentes[$noInv] = $nuevoId;
        if (!empty($codInt)) $codigosExistentes[$codInt] = $nuevoId;

        $insertadas++;
        $filasProcesadas++;
      }

      // Si todo el lote se procesó, realizar commit
      $db->commit();

      // Registro en bitácora de auditoría forense
      if ($insertadas > 0 || $actualizadas > 0) {
        $modoRevisionTxt = $autoAprobar ? 'aprobadas directamente' : 'enviadas a mesa de revisión';
        $detalleAudit = sprintf(
          'Importación masiva en %s (%s): %d piezas insertadas (%s), %d actualizadas, %d omitidas, %d errores. Archivo: %s',
          $def['nombre'],
          $tabla,
          $insertadas,
          $modoRevisionTxt,
          $actualizadas,
          $omitidas,
          count($errores),
          basename($file['name'])
        );
        registrar_auditoria('IMPORT_BATCH', $tabla, null, $detalleAudit, $idModulo, $idUsuario);
      }

      return [
        'status'       => true,
        'tipo_acervo'  => $tipoAcervo,
        'nombre_acervo'=> $def['nombre'],
        'resumen'      => [
          'total_leidas' => $totalFilasLeidas,
          'procesadas'   => $filasProcesadas,
          'insertadas'   => $insertadas,
          'actualizadas' => $actualizadas,
          'omitidas'     => $omitidas,
          'errores'      => count($errores),
          'advertencias' => count($advertencias)
        ],
        'detalle_errores'      => $errores,
        'detalle_advertencias' => $advertencias,
        'msg'                  => sprintf(
          'Proceso completado: %d piezas importadas exitosamente%s.',
          $insertadas,
          ($omitidas > 0 ? ", {$omitidas} omitidas por duplicidad" : '') .
          ($actualizadas > 0 ? ", {$actualizadas} actualizadas" : '') .
          (count($errores) > 0 ? " y " . count($errores) . " filas con inconsistencias" : '')
        )
      ];

    } catch (Throwable $e) {
      if ($db->inTransaction()) {
        $db->rollBack();
      }
      return [
        'status' => false,
        'msg'    => 'Error crítico durante el procesamiento de la transacción: ' . $e->getMessage()
      ];
    }
  }
}
