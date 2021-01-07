# 2.8.3 - 202101071530
## general
- fix sintaxis por actualización a PHP 7.4

## alvin
- incorporación de roles (beta)

## fn
- fix error en **msort** derivado de la version 2.8.0

## pecker
- nuevo argumento **file_charset** en el método **loadfile**

## rind
- reestructuración del método **buildcache**

## tree
- nuevos métodos **get** y **nodePath**
- se el método **children**
 
________________________________________________________________________________
# 2.8.2 - 202012171600
## general
- fix sintaxis por actualización a PHP 7.4

________________________________________________________________________________
# 2.8.1 - 202012160000
## general
- se eliminaron redundancias de **isset** AND **empty**
- fix bugs relacionados con el cambio de sintaxis de la version 2.8.0

## mail
- fix bug

## nest
- cambios relacionados con código de las tablas

## owl
- se incorpora el concepto de código de tabla en **\_\_ngl\_owl\_structure\_\_**
- nuevo método **Imya** para que los **imyas** contengan ahora el código de la tabla
- nuevo método **getByImya**

## rind
- se mejoró la perfonmance de los loops owl

________________________________________________________________________________
# 2.8.0 - 202012030000
## general
- se agregó la *\\* de global namespace a todas las funciones y constantes PHP
- se modificó la estructura de **grafts**. Se instaló **composer** en esa carpeta y se eliminaron librerías obsoletas

## excel
- se reescribió el objeto, por migrar de **phpoffice/phpexcel** a **phpoffice/phpspreadsheet**

________________________________________________________________________________
# 2.7.6 - 202011171600
## pecker
- organización del código

## owl
- fix join level

## tutor
- fix sanitizer

________________________________________________________________________________
# 2.7.5 - 202011161500
## pecker
- BETA finalizado

## owl
- cambio en sintaxis

## shift
- reemplazo de caracteres de control por **?** en el método **textTable**

________________________________________________________________________________
# 2.7.4 - 202011090000
## rind
- fix **userpwd** en readfile

## root
- se mejoró la performance en la carga del nucleo

## validate
- fix error en validación de enteros
 
________________________________________________________________________________
# 2.7.3 - 202011022100
## general
- nuevo método global **dumpc**
- fix firewall-ignore en el archivo alvin.php

## crypt
- nuevo método **chKeys**, que permite verificar y un par de claves RSA se corresponden

## excel
- se añadieron los argumentos *csv_enclosed*, *csv_splitter* y *csv_eol* en el método **write**

## nest
- se añadieron los argumentos *enclosed*, *splitter* y *eol* en el método **write**
- fix error en **normalize**
- fix error en el nombre de los campos al crear
- fix error en los joins al utilizar **rename**

## pecker
- NUEVO OBJETO (beta)

## rind
- se añadió el argumento que determina el modo de validación alvin **alvin_mode**
- se añadió la posibilidad de recuperar un índice de un array en el comando **set**
- el método **vector** del comando **set** ya no requiere especificar el nombre de la columna

## root
- se integró **port** al método **currentpath**
- fix error en **inCurrentPath**

## shift
- reemplazo de tabulaciones por **\t** en el método **textTable**
________________________________________________________________________________
# 2.7.2 - 202009301030
## fn
- nuevo método **emptyToZero**

## nest
- se eliminó el log
- fix error en **alter**

## rind
- **mergefile** admite ahora que la ruta de los JSON en el modo *multiple* sean realtivas al proyecto

## root
- fix error en **errorMessage**

## tutor
- nuevo método **Sanitize**

________________________________________________________________________________
# 2.7.1 - 202009090200
## general
- actualización se samples

## fn
- nuevo método **msort**

## nest
- se añadió el concepto **foreign table** que permite vincular objetos de **owl** con otras tablas

## rind
- ahora el comando **alvin** retorna siempre **TRUE** cuando el nombre del usuario es **admin** y acepta variables $_SET
- cambio de salida en el comando **dump**, ahora utiliza el método global **dump**

________________________________________________________________________________
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