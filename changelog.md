# 2.7.0 - 202009021200
## general
- cambios en la salida de errores, ahora es independiente para cada tipo de objeto
- nuevos métodos globales **dump** e **is**

## alvin
- cambios en la estructura y métodos para administrar y chequear los permisos, grupos y permisos [ACTUALIZACION CRITICA]
- se eliminó el método **GetGrant**
- nuevo método **profile**

## nest
- el método **collapse** fué reemplazado por **objectvar**
- fix error por campo *dependencies* en el método **generate**

## root
- nuevo método **is**
- cambio en **parseConfigString**, ahora se aceptan **\\** en las claves y **-** en las secciones

## shift
- se añadió el formato YAML al método **convert**

## trunk
- nuevo método **__errorMode__**, que establece el tipo de salida de error para el objeto

## unicode
- **is** es ahora **ischr**

## validate
- cambio de **is** por **ischr** en el método **ClearCharacters**

________________________________________________________________________________
# 2.6.0 - 202008181200
## dbase
- cambió la manera de leer los registros en el método **Fetch**
- nuevo método **handler**, que retorna el puntero de la conexión

## fn
- nuevo método **strToArray**

## mysql
- actualización de **mquery** y **mexec** por el cambio de **strToArray**
- nuevo método **handler**, que retorna el puntero de la conexión

## nest
- vuelta a MyISAM del motor en las tablas __ngl

## pgsql
- nuevo controlador para PostgreSQL

## pgsqlq
- nuevo controlador de resultados de PostgreSQL

## qparser
- se quitaron los slash para indicar el namespace global

## shift
- actualización de **fixedExplode** por el cambio de **strToArray**

## sqlite
- actualización de **mquery** y **mexec** por el cambio de **strToArray**
- nuevo método **handler**, que retorna el puntero de la conexión

## validate
- cambio en **RequestFrom** de **strToArray** por **explodeTrim**

________________________________________________________________________________
# 2.5.4 - 202008131630
## alvin
- se añadió Passhrase al encriptado del fuente de los permisos
- se redefinieron errores

## fn
- sandbox en **apacheMimeTypes**

## msqyl
- fix error en **mquery**

## nest
- cambio de motor InnoDB por MyISAM en las tablas __ngl

## rind
- fix error en el submetodo **ifempty**

## root
- cambio en el manejo de errores

________________________________________________________________________________
# 2.5.3 - 202008081430
## bee(cmd)
- se incorporó el argumento **-m** que permite ejecutar multiples comandos en el modo directo
- se incorporó el argumento **-s** que ejecuta los comandos en modo silecioso, sin generar output

## mysql
- cambios en el retorno de errores
- se mejoró el método **mquery**

## nest
- cambio en la validación de los argumentos **der** y **core**
- se añadió la funcionalidad de normalización automática al momento de crear un nuevo objeto
- se modificó la salida del log

## root
- fix error al escribir logs

## sqlite
- se aplicó **sandboxPath** a la ruta de la base de datos

________________________________________________________________________________
# 2.5.2 - 202008032030
## mysql
- mejora en **import**, cuando la tabla no existe, la primer línea del **CSV** es utilizada como nombres de las columnas y eliminada del contenido

## nest
- nuevo campo **dependencies** en la tabla **\_\_ngl_sentences\_\_**
  
## shift
- mejora en la conversión de *CSV*. Si el argumento **colnames** es **true** escribe en la primer línea las claves del array
  
________________________________________________________________________________
# 2.5.1 - 202008021930
## general
- variables de identificación en algunos objetos **feeder** del núcleo

## sysvar
- cambio de release por version

________________________________________________________________________________
# START
v2.5-20200730

## generalgit 
- Primera versión pública