# three.js

`three.module.min.js` es la compilación oficial de [three.js](https://threejs.org/) **0.170.0** (paquete npm `three`, archivo `build/three.module.min.js`), sin cambios. Licencia MIT: ver `LICENSE`.

Se sirve desde el tema para no depender de un CDN (que además recibiría la IP de cada visitante). Solo la descarga `assets/js/pista.js`, y únicamente cuando la sección `mmpadel-pista` se acerca a la pantalla.

Para actualizarla: sustituye el archivo por el de la versión nueva y cambia `THREE_VERSION` en `assets/js/pista.js`, que se usa para saltarse la caché del navegador.
