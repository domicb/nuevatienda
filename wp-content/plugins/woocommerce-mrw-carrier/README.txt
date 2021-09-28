// ********************************************************************************* //
//  MODULO de WooCommerce para MRW
// ********************************************************************************* //

1.  El módulo hace el uso de la libreria SOAP de PHP5 para conexion y generacion
    de envios entre WooCommerce y el WebService SAGEC de MRW

// ********************************************************************************* //
//  CHANGELOG - REGISTRO DE CAMBIOS
// ********************************************************************************* //

12/05/2021: 4.3.5 Corrección rutas para instalaciones de Wordpress no estandar. Corrección ids de pedido en pantalla de masivos.

26/01/2021: 4.3.0 Compatilizar con módulo WooCommerce Sequential Order Numbers + fix bugs

01/12/2020: 4.2.0 Permitir cambiar el peso total de la expedición manualmente desde la ficha del pedido

25/09/2020: 4.1.14 Fix tracking

01/09/2020: 4.1.13 Corrección guardar rangos a partir de Wordpress 5.5

25/08/2020: 4.1.12 Corrección guardar tasas a partir de Wordpress 5.5

30/07/2020: 4.1.11 Filtrar por id en pantalla de envíos MRW

07/07/2020: 4.1.10 Corrección filtros ordenación pantalla envíos MRW, nombre empresa "-"

26/06/2020: 4.1.9 Corrección seguimiento

30/10/2019: 4.1.8 Opción de incluir observaciones en etiquetas

19/09/2019: 4.1.7 Compatibilidad con cupones de descuento de WooCommerce

1/07/2019:  4.1.6 Sustituir funciones deprecadas

30/04/2019: 4.1.5 Envío gratuito si se aplica cupón de envío gratuito

26/03/2019: 4.1.4 Cambio de estado correcto en generación e impresión masiva

18/02/2019: 4.1.3 Gestión entrada de provincias de portugal, evitar incorporar si ya existen

08/01/2019: 4.1.2 Gestion de estados para pedidos internacionales a EEUU

25/06/2018: 4.1.0 Bug de diferenciación de Países para calcular la tarifa de envío corregido.

04/06/2018: 4.0.1 Corrección bug para permitir guardar correctamente las tasas de internacional.

27/04/2018: 4.0.0 Incorporación servicios internacionales, se añaden servicios (marítimo Baleares, marítimo Canarias, marítimo Interinsular).

06/02/2018: 3.1.0 Cambiar estado de pedido personalizado al generar la etiqueta.

11/01/2017: 3.0.0 Incorporación provincias de Portugal. Corrección bug para activar y desactivar provincias.

21/09/2017: 2.9.0 Compatibilidad con certificados SSL.

01/09/2017: 2.8.0 Nueva vista para pedidos de MRW.

18/07/2017: 2.7.0 Se añaden acciones masivas para generar e imprimir pedidos de MRW.

11/05/2017: 2.6.1 Se corrige el cálculo de tarifa si no existe rango para esas condiciones.


18/04/2017: 2.6.0 	Se añaden tramos horarios.
					Etiquetas marketplaces opcionales.
					Traducciones completas para ES, PT, EN, CA. 
					Se incluyen los servicios Marítimo baleares, Marítimo canarias y marítimo interinsular.

12/04/2017: 2.5 Corrección para calcular el peso de productos variables correctamente

06/04/2017: 2.4 Módulo compatible con todas las versiones de WooCommerce hasta la versión 3.

30/01/2017: 2.3 Se soluciona el problema para guardar gran cantidad de tasas utilizando un JSON.

18/01/2017: 2.2 Se añade compatibilidad con el módulo de cupones de WooCommerce para realizar envíos gratuitos.

16/01/2017:	2.1 Corregimos excepción no controlada al añadir metaboxes si la variable no está definida.

22/11/2016: 2.0 Número de rangos ampliado a 25

02/11/2016: 1.9   Mejoras para admitir cualquier medida de peso. Se incluye el teléfono obligatorio en los envíos terceras plazas.

04/10/2016: 1.8.4 Se añade el segundo campo de dirección. Se concatenan los dos campos de dirección para generar la etiqueta.

03/10/2016: 1.8.3 Corrección campo contacto y eliminación ALaAtencion de. Eliminación de rango de horas por defecto.

22/09/2016: 1.8.2 Corrección desglose de bultos para servicios en los que es obligatorio. Si no existe teléfono de envío enviar cadena vacía.

23/08/2016: 1.8.1 Se adapta el módulo para productos variables con diferente peso siempre y cuando la variable se llame "peso"

19/07/2016: Se compatibiliza el módulo con la versión de WooCommerce 2.6.2