-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-05-2026 a las 21:26:26
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tickeepdb`
--
DROP DATABASE IF EXISTS `tickeepdb`;
CREATE DATABASE IF NOT EXISTS `tickeepdb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tickeepdb`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `garantias`
--

CREATE TABLE `garantias` (
  `id_garantia` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre_producto` varchar(150) NOT NULL,
  `tienda` varchar(100) DEFAULT NULL,
  `fecha_compra` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `archivo_ticket` varchar(255) DEFAULT NULL,
  `foto_producto` varchar(255) DEFAULT NULL,
  `comentarios` text DEFAULT NULL,
  `estado` enum('Vigente','Expira pronto','Caducada') DEFAULT 'Vigente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `garantias`
--

INSERT INTO `garantias` (`id_garantia`, `id_usuario`, `nombre_producto`, `tienda`, `fecha_compra`, `fecha_vencimiento`, `archivo_ticket`, `foto_producto`, `comentarios`, `estado`) VALUES
(2, 2, 'Auriculares', 'Mediamarkt', '2000-12-12', '2026-04-24', NULL, NULL, NULL, 'Expira pronto'),
(4, 2, 'CAFE', 'ame CAFE TEATRE', '1200-12-12', '2029-10-10', NULL, NULL, 'Tienda detectada: ame CAFE TEATRE | Producto detectado: ch As', 'Vigente'),
(5, 2, 'a', 'CAFE TEATRE', '2012-09-07', '2027-12-12', 'uploads/tickets/ticket_1776970536_2f2df449.jpg', NULL, NULL, 'Vigente'),
(9, 4, 'a', 'a', '2026-04-23', '2026-05-07', NULL, NULL, NULL, 'Expira pronto'),
(10, 4, 'a', 'a', '2026-04-11', '2026-05-08', NULL, NULL, NULL, 'Expira pronto'),
(11, 4, 'a', 'a', '2026-04-23', '2026-05-07', NULL, NULL, NULL, 'Expira pronto'),
(12, 4, 'a', 'a', '2026-04-17', '2026-04-29', NULL, NULL, NULL, 'Caducada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones_enviadas`
--

CREATE TABLE `notificaciones_enviadas` (
  `id_notificacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_garantia` int(11) DEFAULT NULL,
  `tipo` varchar(50) NOT NULL,
  `fecha_envio` datetime NOT NULL,
  `periodo_clave` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notificaciones_enviadas`
--

INSERT INTO `notificaciones_enviadas` (`id_notificacion`, `id_usuario`, `id_garantia`, `tipo`, `fecha_envio`, `periodo_clave`) VALUES
(1, 4, 9, 'vencimiento', '2026-04-30 14:25:26', NULL),
(2, 4, 11, 'vencimiento', '2026-04-30 14:25:28', NULL),
(3, 4, 12, 'caducada', '2026-04-30 14:25:30', NULL),
(4, 4, NULL, 'resumen_mensual', '2026-04-30 14:25:32', '2026-04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `opciones_configuracion`
--

CREATE TABLE `opciones_configuracion` (
  `id_usuario` int(11) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT 'default_avatar.png',
  `idioma` varchar(5) DEFAULT 'es',
  `tema` enum('claro','oscuro') DEFAULT 'claro',
  `notificaciones_email` tinyint(1) DEFAULT 1,
  `aviso_vencimiento` tinyint(1) DEFAULT 1,
  `notificaciones_app` tinyint(1) DEFAULT 1,
  `dias_aviso` int(11) DEFAULT 30,
  `frecuencia_recordatorio` varchar(20) DEFAULT 'una_vez',
  `hora_recordatorio` varchar(5) DEFAULT '09:00',
  `notificar_caducadas` tinyint(1) DEFAULT 0,
  `resumen_mensual` tinyint(1) DEFAULT 0,
  `color_acento` varchar(20) DEFAULT '#202bbf',
  `formato_fecha` varchar(20) DEFAULT 'd/m/Y',
  `animaciones_ui` tinyint(1) DEFAULT 1,
  `orden_garantias` varchar(30) DEFAULT 'fecha_compra_desc',
  `mostrar_dias_restantes` tinyint(1) DEFAULT 1,
  `confirmar_eliminacion` tinyint(1) DEFAULT 1,
  `modo_compacto` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `opciones_configuracion`
--

INSERT INTO `opciones_configuracion` (`id_usuario`, `foto_perfil`, `idioma`, `tema`, `notificaciones_email`, `aviso_vencimiento`, `notificaciones_app`, `dias_aviso`, `frecuencia_recordatorio`, `hora_recordatorio`, `notificar_caducadas`, `resumen_mensual`, `color_acento`, `formato_fecha`, `animaciones_ui`, `orden_garantias`, `mostrar_dias_restantes`, `confirmar_eliminacion`, `modo_compacto`) VALUES
(2, 'default_avatar.png', 'es', 'claro', 1, 1, 1, 30, 'una_vez', '09:00', 0, 0, '#202bbf', 'd/m/Y', 1, 'fecha_compra_desc', 1, 1, 0),
(3, 'perfil_3_1777545914.jpg', 'Españ', 'claro', 1, 1, 1, 30, 'una_vez', '09:00', 0, 0, '#202bbf', 'd/m/Y', 1, 'fecha_vencimiento_asc', 1, 0, 0),
(4, 'perfil_4_1777550569.png', 'Españ', 'claro', 1, 1, 1, 7, 'una_vez', '14:36', 1, 1, '#202bbf', 'd/m/Y', 1, 'fecha_compra_desc', 1, 1, 0),
(5, 'default_avatar.png', 'es', 'claro', 1, 1, 1, 30, 'una_vez', '09:00', 0, 0, '#202bbf', 'd/m/Y', 1, 'fecha_compra_desc', 1, 1, 0),
(8, 'default_avatar.png', 'Españ', 'claro', 1, 1, 1, 30, 'una_vez', '09:00', 0, 0, '#202bbf', 'd/m/Y', 1, 'fecha_compra_desc', 1, 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expira_en` datetime NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expira_en`, `creado_en`) VALUES
(1, 'rasepal165a@gmail.com', '17075fad830b65f042feb08ee4aa4dd993674b7609692b5948167eed03325540', '2026-05-12 21:59:24', '2026-05-12 18:59:24'),
(2, 'robertotirulo@gmail.com', 'ff1d19435e7d14fef6be2f09e85458229053f4094b90f00e6d4605d5abf70a10', '2026-05-12 22:09:54', '2026-05-12 19:09:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `email_verificado` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `contrasena`, `email`, `fecha_registro`, `email_verificado`) VALUES
(2, 'a', '$2y$10$8Kd0A2DjZlCSEXf5Q2x2s.DgshZkWUMjXB1D0VAq1kMWZHE2lTNua', 'a@a', '2026-04-23 16:52:19', 1),
(3, 'a', '$2y$10$y/NDcEb8ZhoTs64nEnPcKu1YKtOTp1ii.XAGOWr4QEd7L5vMP/0AS', 'a@a.com', '2026-04-30 10:28:38', 1),
(4, 'RRoberto', '$2y$10$Orw75patc6WgP5l1DWCxZewbETkOOjMlw9dZQUggu828mLY.JqG2O', 'roberto.asencio.palacios@iesmaeserodrigo.es', '2026-04-30 12:02:34', 1),
(5, 'Roberto', '$2y$10$bpphB8AfbJVq0ivzlDwH.u.mSRzzTvm2VAo9lvIJ2keISyowI4lwy', 'rasepal165a@gmail.com', '2026-05-12 18:57:52', 1),
(8, 'Roberto', '$2y$10$OAtHkwgLb1Y/OWEreRvhlezSkoEARE3h1AVa22G1zBrT0CIdbAH/a', 'rasepal165@g.educaand.es', '2026-05-12 19:11:10', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `garantias`
--
ALTER TABLE `garantias`
  ADD PRIMARY KEY (`id_garantia`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `notificaciones_enviadas`
--
ALTER TABLE `notificaciones_enviadas`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_garantia` (`id_garantia`),
  ADD KEY `tipo` (`tipo`),
  ADD KEY `periodo_clave` (`periodo_clave`);

--
-- Indices de la tabla `opciones_configuracion`
--
ALTER TABLE `opciones_configuracion`
  ADD PRIMARY KEY (`id_usuario`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `email` (`email`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `garantias`
--
ALTER TABLE `garantias`
  MODIFY `id_garantia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `notificaciones_enviadas`
--
ALTER TABLE `notificaciones_enviadas`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `garantias`
--
ALTER TABLE `garantias`
  ADD CONSTRAINT `garantias_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `opciones_configuracion`
--
ALTER TABLE `opciones_configuracion`
  ADD CONSTRAINT `opciones_configuracion_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
