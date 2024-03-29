<?php

// interprete BEE
$NGL_LIBS["bee"]			= ["nglBee", true];

// base de datos
$NGL_LIBS["dbase"]			= ["nglDBase", false];
$NGL_LIBS["mysql"]			= ["nglDBMySQL", false];
$NGL_LIBS["mysqlq"]			= ["nglDBMySQLQuery", false];
$NGL_LIBS["nest"] 			= ["nglNest", false]; // ORM Manager
$NGL_LIBS["owl"] 			= ["nglOwl", false]; // ORM
$NGL_LIBS["pecker"]			= ["nglPecker", false];
$NGL_LIBS["pgsql"]			= ["nglDBPostgreSQL", false];
$NGL_LIBS["pgsqlq"]			= ["nglDBPostgreSQLQuery", false];
$NGL_LIBS["qparser"]		= ["nglQParser", true]; // Parser SQL
$NGL_LIBS["sqlite"]			= ["nglDBSQLite", false];
$NGL_LIBS["sqliteq"]		= ["nglDBSQLiteQuery", false];

// jsql
$NGL_LIBS["jsql"]			= ["nglJSQL", true];
$NGL_LIBS["jsqlmysql"]		= ["nglJSQLMySQL", true];
$NGL_LIBS["jsqlpgsql"]		= ["nglJSQLPostgreSQL", true];

// fechas
$NGL_LIBS["dates"] 			= ["nglDates", true];

// gestion de imagenes
$NGL_LIBS["image"] 			= ["nglImage", false];

// gestion de rutas, archivos y directorios
$NGL_LIBS["ants"]			= ["nglAnts", false];
$NGL_LIBS["file"] 			= ["nglFile", false];
$NGL_LIBS["files"] 			= ["nglFiles", true];
$NGL_LIBS["ftp"]	 		= ["nglFTP", false];
$NGL_LIBS["sow"] 			= ["nglSow", true];

// grafts
$NGL_LIBS["graft"] 			= ["nglGraft", false];

// mails
$NGL_LIBS["mail"] 			= ["nglMail", false];

// plantillas
$NGL_LIBS["rind"] 			= ["nglRind", false];

// security
$NGL_LIBS["alvin"] 			= ["nglAlvin", true];
$NGL_LIBS["crypt"] 			= ["nglCrypt", false];
$NGL_LIBS["jwt"] 			= ["nglJWT", false];

// sets
$NGL_LIBS["set"] 			= ["nglSet", false];

// tree
$NGL_LIBS["tree"] 			= ["nglTree", false];

// web
$NGL_LIBS["url"] 			= ["nglURL", false];

// zip
$NGL_LIBS["zip"] 			= ["nglZip", false];

?>