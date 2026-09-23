<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shelter;
use App\Models\Supply;
use App\Models\ShelterInventory;
use App\Models\InventoryBatch;
use App\Models\InventoryMovement;
use App\Models\SupplyTransfer;
use App\Models\User;

class ShelterSupplySeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::where('rol', 'admin')->first();
        $adminId = $adminUser ? $adminUser->id : 1;

        // 1. REFUGIOS DE EMERGENCIA (VENEZUELA - DISTRITO CAPITAL, CARACAS)
        // Cada refugio posee un perfil demográfico, capacidad y características operativas únicas.
        $sheltersData = [
            [
                'codigo' => 'REF-01',
                'nombre' => 'Refugio Parque Naciones Unidas',
                'departamento' => 'Distrito Capital',
                'municipio' => 'Parroquia El Paraíso',
                'direccion' => 'Av. Páez, Complejo Parque Naciones Unidas, El Paraíso, Caracas',
                'capacidad_maxima' => 450,
                'poblacion_total' => 380,
                'poblacion_adultos' => 190,
                'poblacion_ninos' => 120,
                'poblacion_adultos_mayores' => 70,
                'poblacion_atencion_medica' => 45,
                'responsable_nombre' => 'Lic. Carlos Eduardo Rivas (Protección Civil DC)',
                'responsable_telefono' => '+58 212 481-2345',
                'responsable_email' => 'refugio.paraiso@refugios.ve',
                'estado' => 'activo',
                'observaciones' => 'Macrorrefugio familiar metropolitano. Alta proporción de lactantes y niños. Requiere monitoreo crítico en fórmulas y agua potable.',
            ],
            [
                'codigo' => 'REF-02',
                'nombre' => 'Albergue Polideportivo Fuerte Tiuna',
                'departamento' => 'Distrito Capital',
                'municipio' => 'Parroquia Coche',
                'direccion' => 'Av. Intercomunal de El Valle, Sector Fuerte Tiuna, Coche, Caracas',
                'capacidad_maxima' => 250,
                'poblacion_total' => 235,
                'poblacion_adultos' => 155,
                'poblacion_ninos' => 45,
                'poblacion_adultos_mayores' => 35,
                'poblacion_atencion_medica' => 28,
                'responsable_nombre' => 'Cap. (B) Mariana Rondón (Bomberos DC)',
                'responsable_telefono' => '+58 212 682-6789',
                'responsable_email' => 'refugio.tiuna@refugios.ve',
                'estado' => 'alerta_capacidad',
                'observaciones' => 'Centro de recepción y tránsito rápido con 94% de ocupación. Requiere raciones listas para consumo (MRE) y agua.',
            ],
            [
                'codigo' => 'REF-03',
                'nombre' => 'Refugio Escuela "República de Venezuela"',
                'departamento' => 'Distrito Capital',
                'municipio' => 'Parroquia Altagracia',
                'direccion' => 'Av. Panteón con esquina San Fernando, Altagracia, Caracas',
                'capacidad_maxima' => 180,
                'poblacion_total' => 110,
                'poblacion_adultos' => 60,
                'poblacion_ninos' => 35,
                'poblacion_adultos_mayores' => 15,
                'poblacion_atencion_medica' => 12,
                'responsable_nombre' => 'Prof. Yelitza González (Zona Educativa DC)',
                'responsable_telefono' => '+58 212 860-9988',
                'responsable_email' => 'refugio.altagracia@refugios.ve',
                'estado' => 'activo',
                'observaciones' => 'Albergue comunitario escolar con gran cocina y comedor. Alto stock de granos, harinas y víveres secos.',
            ],
            [
                'codigo' => 'REF-04',
                'nombre' => 'Albergue Polideportivo "Gato" Odreman',
                'departamento' => 'Distrito Capital',
                'municipio' => 'Parroquia Sucre (Catia)',
                'direccion' => 'Av. Sucre con Calle Real de Catia, Parroquia Sucre, Caracas',
                'capacidad_maxima' => 300,
                'poblacion_total' => 65,
                'poblacion_adultos' => 40,
                'poblacion_ninos' => 15,
                'poblacion_adultos_mayores' => 10,
                'poblacion_atencion_medica' => 8,
                'responsable_nombre' => 'Ing. José Gregorio Hernández (Almacén Estratégico)',
                'responsable_telefono' => '+58 212 871-1122',
                'responsable_email' => 'refugio.catia@refugios.ve',
                'estado' => 'activo',
                'observaciones' => 'Hub logístico y centro de reserva estratégica (21.7% ocupación). Posee superávit disponible para rebalancear a otros refugios.',
            ],
            [
                'codigo' => 'REF-05',
                'nombre' => 'Albergue Humanitario Cruz Roja Venezolana',
                'departamento' => 'Distrito Capital',
                'municipio' => 'Parroquia La Candelaria',
                'direccion' => 'Av. Andrés Bello con Av. Este 2, La Candelaria, Caracas',
                'capacidad_maxima' => 140,
                'poblacion_total' => 125,
                'poblacion_adultos' => 55,
                'poblacion_ninos' => 20,
                'poblacion_adultos_mayores' => 50,
                'poblacion_atencion_medica' => 85,
                'responsable_nombre' => 'Dr. Fernando Quiñónez (Cruz Roja DC)',
                'responsable_telefono' => '+58 212 571-4433',
                'responsable_email' => 'refugio.candelaria@refugios.ve',
                'estado' => 'activo',
                'observaciones' => 'Centro clínico especializado y traumatología. 68% de los albergados requiere curaciones, sueros intravenosos y antibióticos diarios.',
            ],
        ];

        $shelters = [];
        foreach ($sheltersData as $data) {
            $shelters[$data['codigo']] = Shelter::updateOrCreate(['codigo' => $data['codigo']], $data);
        }

        // 2. CATÁLOGO MAESTRO DE INSUMOS (ALIMENTOS E INSUMOS MÉDICOS EXPANDIDO)
        $suppliesData = [
            // ALIMENTOS
            [
                'codigo' => 'ALI-001',
                'nombre' => 'Harina de Maíz Nixtamalizada (Maseca)',
                'descripcion' => 'Suministro básico calórico para elaboración de tortillas, arepas y atoles.',
                'categoria' => 'alimento',
                'subcategoria' => 'Granos y Harinas',
                'unidad_medida' => 'kg',
                'clasificacion_abc' => 'B',
                'es_perecedero' => false,
                'dias_vida_util_promedio' => 360,
                'costo_unitario' => 1.25,
                'costo_pedido' => 20.00,
                'costo_mantenimiento_anual' => 0.40,
                'tiempo_entrega_dias' => 2.5,
                'consumo_diario_referencia' => 0.35,
                'stock_seguridad_sugerido' => 150.0,
                'costo_escasez_newsvendor' => 4.50,
                'costo_desecho_newsvendor' => 0.80,
            ],
            [
                'codigo' => 'ALI-002',
                'nombre' => 'Frijol Negro en Grano Especial',
                'descripcion' => 'Fuente primaria de proteína vegetal en la dieta del albergado.',
                'categoria' => 'alimento',
                'subcategoria' => 'Granos y Harinas',
                'unidad_medida' => 'kg',
                'clasificacion_abc' => 'B',
                'es_perecedero' => false,
                'dias_vida_util_promedio' => 540,
                'costo_unitario' => 1.80,
                'costo_pedido' => 20.00,
                'costo_mantenimiento_anual' => 0.50,
                'tiempo_entrega_dias' => 3.0,
                'consumo_diario_referencia' => 0.20,
                'stock_seguridad_sugerido' => 100.0,
                'costo_escasez_newsvendor' => 5.00,
                'costo_desecho_newsvendor' => 1.10,
            ],
            [
                'codigo' => 'ALI-003',
                'nombre' => 'Fórmula Infantil de Inicio (Latas 400g)',
                'descripcion' => 'Nutrición infantil para lactantes y niños menores de 1 año.',
                'categoria' => 'alimento',
                'subcategoria' => 'Nutrición Infantil',
                'unidad_medida' => 'latas',
                'clasificacion_abc' => 'A', // CRÍTICO VITAL
                'es_perecedero' => true,
                'dias_vida_util_promedio' => 180,
                'costo_unitario' => 14.50,
                'costo_pedido' => 35.00,
                'costo_mantenimiento_anual' => 3.20,
                'tiempo_entrega_dias' => 4.0,
                'consumo_diario_referencia' => 0.08,
                'stock_seguridad_sugerido' => 40.0,
                'costo_escasez_newsvendor' => 45.00,
                'costo_desecho_newsvendor' => 12.00,
            ],
            [
                'codigo' => 'ALI-004',
                'nombre' => 'Agua Potable Purificada en Garrafón (20L)',
                'descripcion' => 'Hidratación y preparación segura de alimentos en comedores.',
                'categoria' => 'alimento',
                'subcategoria' => 'Agua y Bebidas',
                'unidad_medida' => 'garrafones',
                'clasificacion_abc' => 'A', // CRÍTICO VITAL
                'es_perecedero' => false,
                'dias_vida_util_promedio' => 90,
                'costo_unitario' => 2.50,
                'costo_pedido' => 15.00,
                'costo_mantenimiento_anual' => 0.90,
                'tiempo_entrega_dias' => 1.0,
                'consumo_diario_referencia' => 0.15,
                'stock_seguridad_sugerido' => 80.0,
                'costo_escasez_newsvendor' => 18.00,
                'costo_desecho_newsvendor' => 1.50,
            ],
            [
                'codigo' => 'ALI-005',
                'nombre' => 'Raciones de Emergencia Listas para Comer (MRE)',
                'descripcion' => 'Raciones nutricionales compactas hipercalóricas de 2500 kcal.',
                'categoria' => 'alimento',
                'subcategoria' => 'Raciones de Emergencia',
                'unidad_medida' => 'raciones',
                'clasificacion_abc' => 'A',
                'es_perecedero' => false,
                'dias_vida_util_promedio' => 720,
                'costo_unitario' => 8.00,
                'costo_pedido' => 40.00,
                'costo_mantenimiento_anual' => 1.80,
                'tiempo_entrega_dias' => 5.0,
                'consumo_diario_referencia' => 0.10,
                'stock_seguridad_sugerido' => 120.0,
                'costo_escasez_newsvendor' => 25.00,
                'costo_desecho_newsvendor' => 5.00,
            ],
            [
                'codigo' => 'ALI-006',
                'nombre' => 'Atún en Agua enlatado (170g)',
                'descripcion' => 'Proteína lista para consumir sin necesidad de cocción previa.',
                'categoria' => 'alimento',
                'subcategoria' => 'Enlatados',
                'unidad_medida' => 'latas',
                'clasificacion_abc' => 'B',
                'es_perecedero' => false,
                'dias_vida_util_promedio' => 1000,
                'costo_unitario' => 1.40,
                'costo_pedido' => 18.00,
                'costo_mantenimiento_anual' => 0.35,
                'tiempo_entrega_dias' => 2.0,
                'consumo_diario_referencia' => 0.25,
                'stock_seguridad_sugerido' => 100.0,
                'costo_escasez_newsvendor' => 3.80,
                'costo_desecho_newsvendor' => 0.90,
            ],
            [
                'codigo' => 'ALI-007',
                'nombre' => 'Arroz Blanco de Grano Largo',
                'descripcion' => 'Cereal base para almuerzos y cenas en comedores de refugios.',
                'categoria' => 'alimento',
                'subcategoria' => 'Granos y Harinas',
                'unidad_medida' => 'kg',
                'clasificacion_abc' => 'B',
                'es_perecedero' => false,
                'dias_vida_util_promedio' => 500,
                'costo_unitario' => 1.30,
                'costo_pedido' => 18.00,
                'costo_mantenimiento_anual' => 0.35,
                'tiempo_entrega_dias' => 2.0,
                'consumo_diario_referencia' => 0.25,
                'stock_seguridad_sugerido' => 120.0,
                'costo_escasez_newsvendor' => 4.00,
                'costo_desecho_newsvendor' => 0.70,
            ],
            [
                'codigo' => 'ALI-008',
                'nombre' => 'Aceite Vegetal Comestible (1L)',
                'descripcion' => 'Aporte lipídico esencial para cocción en comedores colectivos.',
                'categoria' => 'alimento',
                'subcategoria' => 'Aceites y Grasas',
                'unidad_medida' => 'litros',
                'clasificacion_abc' => 'B',
                'es_perecedero' => false,
                'dias_vida_util_promedio' => 365,
                'costo_unitario' => 2.20,
                'costo_pedido' => 16.00,
                'costo_mantenimiento_anual' => 0.50,
                'tiempo_entrega_dias' => 2.5,
                'consumo_diario_referencia' => 0.05,
                'stock_seguridad_sugerido' => 40.0,
                'costo_escasez_newsvendor' => 6.50,
                'costo_desecho_newsvendor' => 1.20,
            ],
            [
                'codigo' => 'ALI-009',
                'nombre' => 'Leche Completa en Polvo (Sobre 1kg)',
                'descripcion' => 'Nutrición para niños en edad escolar y adultos mayores.',
                'categoria' => 'alimento',
                'subcategoria' => 'Lácteos',
                'unidad_medida' => 'sobres',
                'clasificacion_abc' => 'A',
                'es_perecedero' => true,
                'dias_vida_util_promedio' => 240,
                'costo_unitario' => 6.50,
                'costo_pedido' => 25.00,
                'costo_mantenimiento_anual' => 1.50,
                'tiempo_entrega_dias' => 3.0,
                'consumo_diario_referencia' => 0.06,
                'stock_seguridad_sugerido' => 50.0,
                'costo_escasez_newsvendor' => 18.00,
                'costo_desecho_newsvendor' => 4.00,
            ],
            [
                'codigo' => 'ALI-010',
                'nombre' => 'Lentejas Seleccionadas',
                'descripcion' => 'Legumbre de alta densidad proteica y mineral.',
                'categoria' => 'alimento',
                'subcategoria' => 'Granos y Harinas',
                'unidad_medida' => 'kg',
                'clasificacion_abc' => 'C',
                'es_perecedero' => false,
                'dias_vida_util_promedio' => 400,
                'costo_unitario' => 1.60,
                'costo_pedido' => 15.00,
                'costo_mantenimiento_anual' => 0.30,
                'tiempo_entrega_dias' => 3.0,
                'consumo_diario_referencia' => 0.10,
                'stock_seguridad_sugerido' => 60.0,
                'costo_escasez_newsvendor' => 4.20,
                'costo_desecho_newsvendor' => 0.80,
            ],

            // INSUMOS MÉDICOS
            [
                'codigo' => 'MED-001',
                'nombre' => 'Sales de Rehidratación Oral (Sobres OMS)',
                'descripcion' => 'Prevención y tratamiento de deshidratación por diarrea en refugios.',
                'categoria' => 'medico',
                'subcategoria' => 'Sueros y Rehidratación',
                'unidad_medida' => 'sobres',
                'clasificacion_abc' => 'A', // CRÍTICO
                'es_perecedero' => true,
                'dias_vida_util_promedio' => 365,
                'costo_unitario' => 0.60,
                'costo_pedido' => 12.00,
                'costo_mantenimiento_anual' => 0.15,
                'tiempo_entrega_dias' => 2.0,
                'consumo_diario_referencia' => 0.12,
                'stock_seguridad_sugerido' => 100.0,
                'costo_escasez_newsvendor' => 8.00,
                'costo_desecho_newsvendor' => 0.40,
            ],
            [
                'codigo' => 'MED-002',
                'nombre' => 'Paracetamol 500mg (Caja x 100 tabletas)',
                'descripcion' => 'Analgésico y antipirético fundamental en control febril y dolor.',
                'categoria' => 'medico',
                'subcategoria' => 'Analgésicos',
                'unidad_medida' => 'cajas',
                'clasificacion_abc' => 'B',
                'es_perecedero' => true,
                'dias_vida_util_promedio' => 700,
                'costo_unitario' => 4.50,
                'costo_pedido' => 15.00,
                'costo_mantenimiento_anual' => 0.80,
                'tiempo_entrega_dias' => 2.0,
                'consumo_diario_referencia' => 0.05,
                'stock_seguridad_sugerido' => 25.0,
                'costo_escasez_newsvendor' => 12.00,
                'costo_desecho_newsvendor' => 3.00,
            ],
            [
                'codigo' => 'MED-003',
                'nombre' => 'Amoxicilina + Clavulánico 500/125mg (Caja x 30)',
                'descripcion' => 'Antibiótico de amplio espectro para infecciones respiratorias y dérmicas.',
                'categoria' => 'medico',
                'subcategoria' => 'Antibióticos',
                'unidad_medida' => 'cajas',
                'clasificacion_abc' => 'A', // CRÍTICO
                'es_perecedero' => true,
                'dias_vida_util_promedio' => 360,
                'costo_unitario' => 16.80,
                'costo_pedido' => 30.00,
                'costo_mantenimiento_anual' => 3.50,
                'tiempo_entrega_dias' => 3.5,
                'consumo_diario_referencia' => 0.04,
                'stock_seguridad_sugerido' => 20.0,
                'costo_escasez_newsvendor' => 40.00,
                'costo_desecho_newsvendor' => 12.00,
            ],
            [
                'codigo' => 'MED-004',
                'nombre' => 'Kit de Curación y Traumatología Menor',
                'descripcion' => 'Material estéril para atención de heridas, suturas y quemaduras.',
                'categoria' => 'medico',
                'subcategoria' => 'Material de Curación',
                'unidad_medida' => 'kits',
                'clasificacion_abc' => 'A',
                'es_perecedero' => false,
                'dias_vida_util_promedio' => 720,
                'costo_unitario' => 7.50,
                'costo_pedido' => 22.00,
                'costo_mantenimiento_anual' => 1.40,
                'tiempo_entrega_dias' => 2.5,
                'consumo_diario_referencia' => 0.06,
                'stock_seguridad_sugerido' => 30.0,
                'costo_escasez_newsvendor' => 22.00,
                'costo_desecho_newsvendor' => 5.00,
            ],
            [
                'codigo' => 'MED-005',
                'nombre' => 'Vendas Elásticas 4 pulgadas',
                'descripcion' => 'Inmovilización de extremidades, esguinces y soporte compresivo.',
                'categoria' => 'medico',
                'subcategoria' => 'Material de Curación',
                'unidad_medida' => 'unidades',
                'clasificacion_abc' => 'C',
                'es_perecedero' => false,
                'dias_vida_util_promedio' => 1000,
                'costo_unitario' => 0.85,
                'costo_pedido' => 10.00,
                'costo_mantenimiento_anual' => 0.15,
                'tiempo_entrega_dias' => 2.0,
                'consumo_diario_referencia' => 0.05,
                'stock_seguridad_sugerido' => 40.0,
                'costo_escasez_newsvendor' => 2.50,
                'costo_desecho_newsvendor' => 0.50,
            ],
            [
                'codigo' => 'MED-006',
                'nombre' => 'Solución Salina 0.9% para Infusión (1000ml)',
                'descripcion' => 'Líquido para reanimación hemodinámica y vehículo intravenoso.',
                'categoria' => 'medico',
                'subcategoria' => 'Sueros y Rehidratación',
                'unidad_medida' => 'bolsas',
                'clasificacion_abc' => 'A',
                'es_perecedero' => true,
                'dias_vida_util_promedio' => 360,
                'costo_unitario' => 3.20,
                'costo_pedido' => 25.00,
                'costo_mantenimiento_anual' => 0.80,
                'tiempo_entrega_dias' => 2.0,
                'consumo_diario_referencia' => 0.04,
                'stock_seguridad_sugerido' => 25.0,
                'costo_escasez_newsvendor' => 15.00,
                'costo_desecho_newsvendor' => 2.50,
            ],
            [
                'codigo' => 'MED-007',
                'nombre' => 'Pañales Desechables Pediátricos (Paq x 30)',
                'descripcion' => 'Higiene de lactantes y niños en albergues de emergencia.',
                'categoria' => 'medico',
                'subcategoria' => 'Higiene y Cuidado',
                'unidad_medida' => 'paquetes',
                'clasificacion_abc' => 'A',
                'es_perecedero' => false,
                'dias_vida_util_promedio' => 700,
                'costo_unitario' => 5.80,
                'costo_pedido' => 18.00,
                'costo_mantenimiento_anual' => 1.10,
                'tiempo_entrega_dias' => 2.0,
                'consumo_diario_referencia' => 0.08,
                'stock_seguridad_sugerido' => 35.0,
                'costo_escasez_newsvendor' => 16.00,
                'costo_desecho_newsvendor' => 3.50,
            ],
            [
                'codigo' => 'MED-008',
                'nombre' => 'Guantes Quirúrgicos de Látex (Caja x 100)',
                'descripcion' => 'Protección biológica para médicos y enfermeros en curaciones.',
                'categoria' => 'medico',
                'subcategoria' => 'Protección Personal',
                'unidad_medida' => 'cajas',
                'clasificacion_abc' => 'B',
                'es_perecedero' => false,
                'dias_vida_util_promedio' => 800,
                'costo_unitario' => 7.20,
                'costo_pedido' => 15.00,
                'costo_mantenimiento_anual' => 1.00,
                'tiempo_entrega_dias' => 2.0,
                'consumo_diario_referencia' => 0.05,
                'stock_seguridad_sugerido' => 30.0,
                'costo_escasez_newsvendor' => 14.00,
                'costo_desecho_newsvendor' => 2.80,
            ],
            [
                'codigo' => 'MED-009',
                'nombre' => 'Alcohol Antiséptico al 70% (Frasco 1000ml)',
                'descripcion' => 'Desinfección de instrumental, superficies y manos en enfermería.',
                'categoria' => 'medico',
                'subcategoria' => 'Antisépticos',
                'unidad_medida' => 'litros',
                'clasificacion_abc' => 'B',
                'es_perecedero' => false,
                'dias_vida_util_promedio' => 900,
                'costo_unitario' => 3.00,
                'costo_pedido' => 14.00,
                'costo_mantenimiento_anual' => 0.60,
                'tiempo_entrega_dias' => 2.0,
                'consumo_diario_referencia' => 0.04,
                'stock_seguridad_sugerido' => 25.0,
                'costo_escasez_newsvendor' => 8.50,
                'costo_desecho_newsvendor' => 1.50,
            ],
            [
                'codigo' => 'MED-010',
                'nombre' => 'Gasas y Compresas Estériles (Paq x 50)',
                'descripcion' => 'Apósitos para absorción y compresión de hemorragias y llagas.',
                'categoria' => 'medico',
                'subcategoria' => 'Material de Curación',
                'unidad_medida' => 'paquetes',
                'clasificacion_abc' => 'C',
                'es_perecedero' => false,
                'dias_vida_util_promedio' => 1000,
                'costo_unitario' => 2.10,
                'costo_pedido' => 12.00,
                'costo_mantenimiento_anual' => 0.30,
                'tiempo_entrega_dias' => 2.0,
                'consumo_diario_referencia' => 0.06,
                'stock_seguridad_sugerido' => 40.0,
                'costo_escasez_newsvendor' => 5.00,
                'costo_desecho_newsvendor' => 1.00,
            ],
        ];

        $supplies = [];
        foreach ($suppliesData as $sData) {
            $supplies[$sData['codigo']] = Supply::updateOrCreate(['codigo' => $sData['codigo']], $sData);
        }

        // 3. INVENTARIOS COMPLETAMENTE DIFERENCIADOS PARA CADA REFUGIO
        // Cada refugio refleja su realidad y perfil específico:
        $distribucionStock = [
            // REF-01 (Parque Naciones Unidas - Alta población infantil: 120 niños. ESCASEZ CRÍTICA en fórmula y agua)
            'REF-01' => [
                'ALI-001' => 380.0,  // Harina
                'ALI-002' => 210.0,  // Frijol
                'ALI-003' => 6.0,    // Fórmula infantil (¡CRÍTICO URGENTE! 120 niños -> 1.1 días de autonomía)
                'ALI-004' => 45.0,   // Agua potable (¡CRÍTICO! 380 personas -> 1.0 día de autonomía)
                'ALI-005' => 180.0,  // Raciones
                'ALI-006' => 220.0,  // Atún
                'ALI-007' => 310.0,  // Arroz
                'ALI-008' => 55.0,   // Aceite
                'ALI-009' => 35.0,   // Leche en polvo
                'ALI-010' => 110.0,  // Lentejas
                'MED-001' => 160.0,  // Sueros orales
                'MED-002' => 55.0,   // Paracetamol
                'MED-003' => 4.0,    // Amoxicilina (¡CRÍTICO!)
                'MED-004' => 35.0,   // Kits curación
                'MED-005' => 70.0,   // Vendas
                'MED-006' => 20.0,   // Salina
                'MED-007' => 12.0,   // Pañales pediátricos (¡CRÍTICO!)
                'MED-008' => 28.0,   // Guantes
                'MED-009' => 20.0,   // Alcohol
                'MED-010' => 45.0,   // Gasas
            ],

            // REF-02 (Fuerte Tiuna - Tránsito rápido casi saturado: 235 personas. Abundantes raciones MRE y latas)
            'REF-02' => [
                'ALI-001' => 160.0,  // Menos harinas
                'ALI-002' => 90.0,
                'ALI-003' => 22.0,   // Fórmula moderada
                'ALI-004' => 140.0,  // Agua moderada
                'ALI-005' => 310.0,  // Gran cantidad de raciones MRE listas
                'ALI-006' => 340.0,  // Gran cantidad de atún en lata
                'ALI-007' => 180.0,  // Arroz
                'ALI-008' => 35.0,
                'ALI-009' => 45.0,
                'ALI-010' => 80.0,
                'MED-001' => 70.0,
                'MED-002' => 35.0,
                'MED-003' => 12.0,
                'MED-004' => 20.0,
                'MED-005' => 35.0,
                'MED-006' => 14.0,
                'MED-007' => 30.0,
                'MED-008' => 20.0,
                'MED-009' => 18.0,
                'MED-010' => 30.0,
            ],

            // REF-03 (Escuela Rep. Venezuela - Albergue Escolar con Comedor: Enorme stock de granos y víveres)
            'REF-03' => [
                'ALI-001' => 480.0,  // Abundante harina para arepas y pan
                'ALI-002' => 320.0,  // Abundante frijol negro
                'ALI-003' => 30.0,
                'ALI-004' => 190.0,
                'ALI-005' => 130.0,
                'ALI-006' => 160.0,
                'ALI-007' => 460.0,  // Abundante arroz blanco
                'ALI-008' => 95.0,   // Aceite para cocina colectiva
                'ALI-009' => 85.0,   // Leche en polvo para desayunos
                'ALI-010' => 220.0,  // Lentejas
                'MED-001' => 90.0,
                'MED-002' => 40.0,
                'MED-003' => 16.0,
                'MED-004' => 28.0,
                'MED-005' => 50.0,
                'MED-006' => 18.0,
                'MED-007' => 25.0,
                'MED-008' => 22.0,
                'MED-009' => 35.0,
                'MED-010' => 40.0,
            ],

            // REF-04 (Polideportivo Odreman Catia - HUB DONANTE CENTRAL con SUPERÁVIT MASIVO PARA TRANSFERENCIAS)
            'REF-04' => [
                'ALI-001' => 920.0,  // Superávit enorme
                'ALI-002' => 580.0,  // Superávit enorme
                'ALI-003' => 150.0,  // Abundante fórmula (> 40 días)
                'ALI-004' => 420.0,  // Gran reserva de agua
                'ALI-005' => 380.0,
                'ALI-006' => 480.0,
                'ALI-007' => 750.0,  // Gran reserva arroz
                'ALI-008' => 180.0,  // Gran reserva aceite
                'ALI-009' => 200.0,  // Gran reserva leche
                'ALI-010' => 350.0,
                'MED-001' => 280.0,  // Superávit médico
                'MED-002' => 110.0,
                'MED-003' => 85.0,   // Abundante amoxicilina disponible para ceder
                'MED-004' => 75.0,
                'MED-005' => 160.0,
                'MED-006' => 90.0,
                'MED-007' => 110.0,  // Gran stock de pañales
                'MED-008' => 140.0,
                'MED-009' => 100.0,
                'MED-010' => 180.0,
            ],

            // REF-05 (Cruz Roja La Candelaria - CENTRO CLÍNICO Y HOSPITALARIO: 85 pacientes, 75% enfoque médico)
            'REF-05' => [
                'ALI-001' => 95.0,   // Pocos alimentos secos
                'ALI-002' => 60.0,
                'ALI-003' => 18.0,
                'ALI-004' => 110.0,
                'ALI-005' => 90.0,
                'ALI-006' => 130.0,
                'ALI-007' => 110.0,
                'ALI-008' => 25.0,
                'ALI-009' => 30.0,
                'ALI-010' => 50.0,
                'MED-001' => 260.0,  // Máxima prioridad médica
                'MED-002' => 95.0,   // Paracetamol continuo
                'MED-003' => 70.0,   // Antibióticos
                'MED-004' => 130.0,  // Máximo kits de curación
                'MED-005' => 180.0,  // Gran stock de vendas
                'MED-006' => 210.0,  // Enorme stock de soluciones intravenosas
                'MED-007' => 35.0,
                'MED-008' => 160.0,  // Guantes quirúrgicos indispensables
                'MED-009' => 110.0,  // Alcohol antiséptico para curaciones
                'MED-010' => 240.0,  // Apósitos y gasas continuas
            ],
        ];

        // Limpiar inventarios antiguos y recrear con los datos diferenciados
        ShelterInventory::query()->delete();
        InventoryBatch::query()->delete();
        InventoryMovement::query()->delete();

        foreach ($distribucionStock as $shelterCod => $supplyMap) {
            $shelter = $shelters[$shelterCod];

            foreach ($supplyMap as $supCod => $cantidad) {
                $supply = $supplies[$supCod];

                $inventory = ShelterInventory::create([
                    'shelter_id' => $shelter->id,
                    'supply_id' => $supply->id,
                    'stock_actual' => $cantidad,
                    'stock_minimo' => $supply->stock_seguridad_sugerido,
                    'stock_maximo' => $supply->stock_seguridad_sugerido * 5,
                    'punto_reorden' => $supply->stock_seguridad_sugerido * 1.5,
                ]);

                // Crear 2 lotes por insumo con fechas de vencimiento escalonadas (FEFO)
                $cantLote1 = round($cantidad * 0.45, 1);
                $cantLote2 = round($cantidad - $cantLote1, 1);

                // Lote 1: Vencimiento más próximo
                InventoryBatch::create([
                    'shelter_inventory_id' => $inventory->id,
                    'numero_lote' => 'LOT-' . strtoupper(substr(md5($shelterCod . $supCod . 'A'), 0, 6)),
                    'cantidad_inicial' => $cantLote1 + 15,
                    'cantidad_disponible' => $cantLote1,
                    'fecha_ingreso' => now()->subDays(rand(10, 25))->toDateString(),
                    'fecha_vencimiento' => now()->addDays(rand(20, 65))->toDateString(),
                    'estado' => 'vigente',
                ]);

                // Lote 2: Vencimiento a mediano/largo plazo
                InventoryBatch::create([
                    'shelter_inventory_id' => $inventory->id,
                    'numero_lote' => 'LOT-' . strtoupper(substr(md5($shelterCod . $supCod . 'B'), 0, 6)),
                    'cantidad_inicial' => $cantLote2,
                    'cantidad_disponible' => $cantLote2,
                    'fecha_ingreso' => now()->subDays(rand(2, 8))->toDateString(),
                    'fecha_vencimiento' => now()->addDays(rand(140, 380))->toDateString(),
                    'estado' => 'vigente',
                ]);

                // Registrar movimiento inicial de carga
                InventoryMovement::create([
                    'shelter_id' => $shelter->id,
                    'supply_id' => $supply->id,
                    'user_id' => $adminId,
                    'tipo' => 'entrada_donacion',
                    'cantidad' => $cantidad,
                    'stock_anterior' => 0.0,
                    'stock_nuevo' => $cantidad,
                    'documento_referencia' => 'DON-PCVE-' . rand(1000, 9999),
                    'motivo' => "Dotación de reserva humanitaria para {$shelter->nombre}",
                    'fecha_movimiento' => now()->subDays(rand(3, 12)),
                ]);

                // Registrar salida de consumo
                $consumo = round(min($cantidad * 0.12, 30.0), 1);
                InventoryMovement::create([
                    'shelter_id' => $shelter->id,
                    'supply_id' => $supply->id,
                    'user_id' => $adminId,
                    'tipo' => 'salida_consumo',
                    'cantidad' => $consumo,
                    'stock_anterior' => $cantidad + $consumo,
                    'stock_nuevo' => $cantidad,
                    'documento_referencia' => 'SAL-CONS-' . rand(1000, 9999),
                    'motivo' => "Consumo diario registrado para atención de albergados en {$shelter->nombre}",
                    'fecha_movimiento' => now()->subDays(rand(1, 2)),
                ]);
            }
        }

        // 4. TRANSFERENCIAS INICIALES REALISTAS
        SupplyTransfer::query()->delete();
        
        // Rebalanceo sugerido desde REF-04 (Catia con superávit) hacia REF-01 (El Paraíso con escasez de fórmula)
        $ref04 = $shelters['REF-04'];
        $ref01 = $shelters['REF-01'];
        $supFormula = $supplies['ALI-003'];
        $supAgua = $supplies['ALI-004'];

        SupplyTransfer::create([
            'codigo_transferencia' => 'TRF-IO-2026-001',
            'refugio_origen_id' => $ref04->id,
            'refugio_destino_id' => $ref01->id,
            'supply_id' => $supFormula->id,
            'cantidad' => 25.0,
            'prioridad' => 'urgente',
            'estado' => 'en_transito',
            'vehiculo_transporte' => 'Camión Utilitario UN-04 (Convoy Metropolitano)',
            'observaciones' => 'Rebalanceo logístico de emergencia: Superávit en Catia enviado a Naciones Unidas por escasez en población lactante.',
            'solicitado_por' => $adminId,
            'aprobado_por' => $adminId,
            'fecha_salida' => now()->subHours(2),
        ]);

        SupplyTransfer::create([
            'codigo_transferencia' => 'TRF-IO-2026-002',
            'refugio_origen_id' => $ref04->id,
            'refugio_destino_id' => $ref01->id,
            'supply_id' => $supAgua->id,
            'cantidad' => 60.0,
            'prioridad' => 'alta',
            'estado' => 'completada',
            'vehiculo_transporte' => 'Cisterna Humanitaria CH-02',
            'observaciones' => 'Abastecimiento de agua potable purificada para comedor metropolitano.',
            'solicitado_por' => $adminId,
            'aprobado_por' => $adminId,
            'fecha_salida' => now()->subHours(18),
            'fecha_llegada' => now()->subHours(6),
        ]);
    }
}
