<img src="https://cdn.upps.cloud/images/nogal/nogal-iso.svg" width="100" alt="nogal"/><br />

## Versión 4.0.00
Grandes cambios se han incorporado desde la ultima versión publicada, ver [changelog](changelog.md), entre los mas significativos se encuentran:
- La supresión de todas las librerías de terceros en la versión por defecto, las mismas deben instalarse ejecutando ```composer update``` dentro de la carpeta ```NOGAL/grafts/composer```
- Sembrar estructuras [pre-diseñadas](assets/templates/structures/) para el desarrollo de diferentes aplicaciones mediante el objeto **sow**. Consultar en la [Guía del usuario](https://github.com/hytcom/wiki/tree/master/nogal), como sembrar un **nogal.**
- Se extendieron, y mucho, las funcionalidades del interprete [Bee](https://github.com/hytcom/wiki/blob/master/nogal/docs/bee.md), ver ejemplos de la estructura pre-diseñada [bee](assets/templates/structures/bee)
- Declarar rutas para el desarrollo de APIs mediante el objeto **ant** y la configuración **uproot.php**. Se recomienda el uso de la estructura pre-diseñada [api](assets/templates/structures/api)

Queda pendiente la acutalización de toda la documentación y la publicación de la misma en un único repositorio [hytcom.net/nogal/docs](https://hytcom.net/nogal/docs)

&nbsp;

## Acerca de nogal
**nogal** pretende ser un framework de PHP de código abierto pensado para que todos puedan desarrollar aplicaciones sin necesidad de tener grandes conocimientos de programación.

Su estructura está pensada para que todo aquel que tenga conocimientos básicos de programación, como que es una **variable**, un **if** y un **bucle**, pueda desarrollar aplicaciones sin mayores dificultades. Esto es gracias, entre otras cosas, a su poderoso sistema de templates llamado [rind](https://github.com/hytcom/wiki/tree/master/rind) que permite al desarrollador comunicarse con cualquiera de los objetos del framework desde el HTML, utilizando una sintáxis basada en XML, JS y JSON.

A pesar de ser fácil de utilizar y de tener una curva de aprendizaje muy rápida, **nogal** es también una poderosa herramienta para el desarrollador experimentado, tiene una sintáxis muy limpia y cuenta con un interprete denominado [Bee](https://github.com/hytcom/wiki/blob/master/nogal/docs/bee.md) que le permitirá ejecutar cualquier objeto del framework desde una terminal.

Para instalarlo y aprender más, consultá la [Guía del usuario](https://github.com/hytcom/wiki/tree/master/nogal)

&nbsp;

## Licencia
The MIT License (MIT) - Copyright (c)2016 [hytcom.net](https://hytcom.net/nogal)

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
of the Software, and to permit persons to whom the Software is furnished to do
so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

&nbsp;
___
<sub><b>nogal</b> - <em>the most simple PHP Framework</em></sub><br />
<sup>&copy; 2022 by <a href="https://hytcom.net">hytcom.net</a> - <a href="https://github.com/hytcom">@hytcom</a></sup><br />