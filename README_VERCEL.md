# Despliegue en Vercel - Notas Importantes

Este proyecto ha sido configurado para funcionar en Vercel, pero debido a la naturaleza de la plataforma, existen limitaciones importantes que debes conocer:

## 1. Solución al problema de descarga
Vercel no soporta PHP de forma nativa. Por eso, al intentar entrar a la página, el navegador descargaba el archivo `index.php` en lugar de ejecutarlo. He añadido un archivo `vercel.json` que utiliza el runtime `vercel-php` para habilitar la ejecución de archivos PHP.

## 2. Limitaciones de almacenamiento (IMPORTANTE)
Vercel utiliza un sistema de archivos **de solo lectura y efímero**. Esto significa que:
- **No se pueden guardar archivos localmente:** La carpeta `uploads/` no podrá almacenar las imágenes de forma permanente. Cualquier archivo subido desaparecerá poco después.
- **La base de datos JSON no es persistente:** Los archivos `database.json`, `users.json`, etc., se restaurarán a su estado original cada vez que la aplicación se reinicie (lo cual sucede frecuentemente en Vercel).

## Recomendaciones
Para que este sistema de hosting de imágenes sea funcional en producción, te recomiendo:

1.  **Cambiar de Hosting:** Considera usar un hosting tradicional de PHP (como Hostinger, Bluehost, o incluso opciones gratuitas como 000webhost o InfinityFree) que permiten el uso de un sistema de archivos persistente.
2.  **Refactorizar para Vercel:**
    - Usar **Vercel Blob** o **AWS S3** para almacenar las imágenes.
    - Usar una base de datos real (como **Vercel Postgres**, **Supabase** o **PlanetScale**) en lugar de archivos JSON.

Si decides seguir en Vercel, ten en cuenta que las subidas actuales fallarán al intentar escribir en el disco.
