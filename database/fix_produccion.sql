-- Ejecuta este archivo en la base de datos del deploy.
-- Corrige columnas demasiado cortas y prepara rutas de imágenes de perfil.

ALTER TABLE opciones_configuracion
    MODIFY foto_perfil VARCHAR(255) DEFAULT NULL,
    MODIFY idioma VARCHAR(20) DEFAULT 'Español',
    MODIFY tema VARCHAR(20) DEFAULT 'claro',
    MODIFY color_acento VARCHAR(20) DEFAULT '#202bbf',
    MODIFY formato_fecha VARCHAR(20) DEFAULT 'd/m/Y',
    MODIFY frecuencia_recordatorio VARCHAR(20) DEFAULT 'una_vez',
    MODIFY orden_garantias VARCHAR(50) DEFAULT 'fecha_compra_desc';

UPDATE opciones_configuracion
SET idioma = 'Español'
WHERE idioma IN ('es', 'Españ');

UPDATE opciones_configuracion
SET foto_perfil = NULL
WHERE foto_perfil IN ('default_avatar.png', 'default-avatar.png', '');
