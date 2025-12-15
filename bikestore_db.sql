-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-12-2025 a las 18:49:44
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
-- Base de datos: `bikestore_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `talla` varchar(10) DEFAULT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios`
--

CREATE TABLE `comentarios` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `calificacion` int(1) NOT NULL CHECK (`calificacion` >= 1 and `calificacion` <= 5),
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `comentarios`
--

INSERT INTO `comentarios` (`id`, `producto_id`, `usuario_id`, `comentario`, `calificacion`, `fecha`) VALUES
(4, 7, 1, 'está muy buena la calidad', 5, '2025-11-12 23:59:19'),
(5, 7, 2, 'me gusta mucho', 4, '2025-11-04 18:09:52'),
(8, 12, 1, 'sshhshshsskssks s s', 1, '2025-12-10 19:57:17'),
(10, 14, 22, 'algo rigida', 3, '2025-12-12 01:32:23'),
(11, 13, 22, 'Algo buena', 3, '2025-12-12 02:12:38'),
(12, 12, 2, 'muy buena', 5, '2025-12-12 16:03:14'),
(13, 23, 23, 'ruda', 5, '2025-12-12 20:12:07'),
(14, 11, 23, 'está bien vrg', 5, '2025-12-15 13:56:21'),
(15, 11, 25, 'muy buena', 4, '2025-12-15 14:52:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direcciones`
--

CREATE TABLE `direcciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `calle` varchar(255) NOT NULL,
  `numero_exterior` varchar(20) NOT NULL,
  `numero_interior` varchar(20) DEFAULT NULL,
  `colonia` varchar(100) NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `estado` varchar(100) NOT NULL,
  `codigo_postal` varchar(10) NOT NULL,
  `referencias` text DEFAULT NULL,
  `es_principal` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `direcciones`
--

INSERT INTO `direcciones` (`id`, `usuario_id`, `nombre_completo`, `telefono`, `calle`, `numero_exterior`, `numero_interior`, `colonia`, `ciudad`, `estado`, `codigo_postal`, `referencias`, `es_principal`, `fecha_creacion`) VALUES
(2, 1, 'Jose Manuel Fragoso Rizo', '5579430382', 'CLL D', '47', 'Ninguno', 'Cumbre Norte', 'Estado de México', 'México', '54769', 'Entre calle E y C', 1, '2025-11-04 23:15:43'),
(3, 2, 'Jose Manuel Fragoso Rizo', '5579430382', 'CLL D', '47', 'Ninguno', 'Cumbre Norte', 'Estado de México', 'México', '54769', 'en x y y', 1, '2025-11-05 04:15:01'),
(9, 20, 'Jose Manuel Fragoso Rizo', '5579430382', 'CLL D 47, Ninguno', 'we', 'we', 'Cumbre Norte', 'Estado de México', 'México', '54769', '', 1, '2025-12-11 15:32:56'),
(10, 22, 'Jose Manuel Fragoso Rizo', '5579430382', 'CLL D 47, Ninguno', 'we', 'we', 'Cumbre Norte', 'Estado de México', 'México', '54769', '', 1, '2025-12-12 01:24:44'),
(11, 1, 'Mauricio Fragoso Rizo', '5579430382', 'calle D casa', '47', NULL, '.FRACC. CUMBRE NORTE', 'Estado de México', 'México', '54769', NULL, 0, '2025-12-12 14:32:01'),
(12, 23, 'Jose Manuel Fragoso Rizo', '5579430382', 'CLL D 47, Ninguno', 'we', NULL, 'Cumbre Norte', 'Estado de México', 'México', '54769', NULL, 0, '2025-12-12 15:55:55'),
(13, 19, 'Jose Manuel Fragoso Rizo', '5579430382', 'CLL D 47, Ninguno', 'we', NULL, 'Cumbre Norte', 'Estado de México', 'México', '54769', NULL, 0, '2025-12-12 19:14:09'),
(14, 25, 'cerrada peña', '5579430382', 'loma bonita', 's/n', NULL, 'san juan', 'Estado de México', 'México', '54769', NULL, 0, '2025-12-15 14:54:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodos_pago`
--

CREATE TABLE `metodos_pago` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` enum('tarjeta_credito','tarjeta_debito','paypal','transferencia') DEFAULT 'tarjeta_credito',
  `nombre_titular` varchar(100) NOT NULL,
  `ultimos_digitos` varchar(4) NOT NULL,
  `fecha_expiracion` varchar(5) NOT NULL,
  `marca` varchar(50) DEFAULT NULL COMMENT 'Visa, Mastercard, American Express, etc.',
  `mes_expiracion` int(2) NOT NULL,
  `ano_expiracion` int(4) NOT NULL,
  `es_principal` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `metodos_pago`
--

INSERT INTO `metodos_pago` (`id`, `usuario_id`, `tipo`, `nombre_titular`, `ultimos_digitos`, `fecha_expiracion`, `marca`, `mes_expiracion`, `ano_expiracion`, `es_principal`, `fecha_creacion`) VALUES
(2, 2, 'tarjeta_debito', 'JOSE MANUEL FRAGOSO RIZO', '7654', '', 'Desconocida', 11, 2029, 1, '2025-11-05 04:15:28'),
(4, 2, 'tarjeta_credito', 'JOSE MANUEL FRAGOSO RIZO', '7373', '', 'Desconocida', 8, 2032, 0, '2025-11-06 00:13:32'),
(17, 2, '', '', '1002', '03/27', NULL, 0, 0, 0, '2025-12-12 15:51:35'),
(18, 23, '', '', '9973', '11/32', NULL, 0, 0, 0, '2025-12-12 15:56:42'),
(20, 19, '', '', '9973', '11/32', NULL, 0, 0, 0, '2025-12-12 19:16:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `total` decimal(10,2) DEFAULT NULL,
  `direccion_envio_id` int(11) DEFAULT NULL,
  `metodo_pago_id` int(11) DEFAULT NULL,
  `estado` enum('pendiente','procesando','enviado','entregado','cancelado') DEFAULT 'pendiente',
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `envio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notas` text DEFAULT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `usuario_id`, `fecha`, `total`, `direccion_envio_id`, `metodo_pago_id`, `estado`, `subtotal`, `envio`, `notas`, `fecha_actualizacion`) VALUES
(1, 1, '2025-11-04 23:16:46', 18000.00, 2, 1, 'entregado', 18000.00, 0.00, NULL, '2025-12-05 02:24:46'),
(2, 1, '2025-11-04 23:22:31', 18000.00, 2, 1, 'entregado', 18000.00, 0.00, NULL, '2025-12-05 02:24:46'),
(3, 2, '2025-11-05 04:15:45', 18000.00, 3, 2, 'entregado', 18000.00, 0.00, NULL, '2025-12-05 02:24:46'),
(5, 2, '2025-11-06 00:14:34', 320000.00, 3, 2, 'entregado', 320000.00, 0.00, NULL, '2025-12-05 02:24:46'),
(6, 1, '2025-11-06 14:42:04', 80000.00, 2, 1, 'entregado', 80000.00, 0.00, NULL, '2025-12-05 02:24:46'),
(7, 1, '2025-11-12 21:54:41', 160000.00, 2, 1, 'entregado', 160000.00, 0.00, NULL, '2025-12-05 02:24:46'),
(8, 1, '2025-11-13 00:00:15', 80000.00, 2, 1, 'entregado', 80000.00, 0.00, NULL, '2025-12-05 02:24:46'),
(9, 1, '2025-11-13 03:36:05', 200000.00, 2, 1, 'entregado', 200000.00, 0.00, NULL, '2025-12-05 02:24:46'),
(10, 2, '2025-11-14 02:54:39', 400000.00, 3, 2, 'entregado', 400000.00, 0.00, NULL, '2025-12-05 02:24:46'),
(11, 2, '2025-11-14 03:42:35', 300000.00, 3, 2, 'entregado', 300000.00, 0.00, NULL, '2025-12-05 02:24:46'),
(12, 1, '2025-11-20 19:13:19', 80000.00, 2, 1, 'entregado', 80000.00, 0.00, NULL, '2025-12-05 02:24:46'),
(13, 1, '2025-11-21 13:41:57', 302243.00, 2, 1, 'entregado', 302243.00, 0.00, NULL, '2025-12-10 20:01:32'),
(17, 1, '2025-12-04 00:28:07', 10350.00, 2, 1, 'entregado', 10350.00, 0.00, NULL, '2025-12-15 13:57:52'),
(18, 1, '2025-12-04 00:38:13', 10350.00, 2, 1, 'entregado', 10350.00, 0.00, NULL, '2025-12-15 13:57:52'),
(19, 1, '2025-12-05 02:01:34', 130350.00, 2, 1, 'enviado', 130350.00, 0.00, NULL, '2025-12-12 15:47:24'),
(20, 1, '2025-12-05 02:07:06', 10350.00, 2, 1, 'enviado', 10350.00, 0.00, NULL, '2025-12-12 15:47:24'),
(21, 1, '2025-12-05 02:12:24', 240000.00, 2, 1, 'enviado', 240000.00, 0.00, NULL, '2025-12-12 15:47:24'),
(23, 1, '2025-12-09 14:41:33', 10350.00, 2, 1, 'enviado', 10350.00, 0.00, NULL, '2025-12-12 15:47:24'),
(26, 20, '2025-12-11 15:33:27', 28000.00, 9, 12, 'enviado', 28000.00, 0.00, NULL, '2025-12-15 13:57:52'),
(27, 1, '2025-12-11 16:15:17', 28000.00, 2, 1, 'cancelado', 28000.00, 0.00, NULL, '2025-12-11 16:57:58'),
(28, 1, '2025-12-11 23:57:54', 283000.00, 2, 1, 'enviado', 283000.00, 0.00, NULL, '2025-12-15 13:57:52'),
(29, 22, '2025-12-12 01:25:17', 48000.00, 10, 13, 'enviado', 48000.00, 0.00, NULL, '2025-12-15 13:57:52'),
(30, 22, '2025-12-12 04:40:46', 300000.00, 10, 13, 'enviado', 300000.00, 0.00, NULL, '2025-12-15 13:57:52'),
(31, 1, '2025-12-12 14:23:48', 200000.00, 2, 1, 'procesando', 200000.00, 0.00, NULL, '2025-12-15 13:57:52'),
(32, 1, '2025-12-12 14:39:40', 283000.00, 11, 14, 'procesando', 283000.00, 0.00, NULL, '2025-12-15 13:57:52'),
(37, 1, '2025-12-12 15:26:50', 28000.00, 11, 14, 'cancelado', 28000.00, 0.00, NULL, '2025-12-12 15:27:19'),
(40, 1, '2025-12-12 15:35:23', 28000.00, 11, 14, 'cancelado', 28000.00, 0.00, NULL, '2025-12-12 15:37:51'),
(41, 1, '2025-12-12 15:38:38', 56000.00, 11, 15, 'procesando', 56000.00, 0.00, NULL, '2025-12-15 13:57:52'),
(42, 1, '2025-12-12 15:46:51', 490000.00, 2, 15, 'procesando', 490000.00, 0.00, NULL, '2025-12-15 13:57:52'),
(43, 2, '2025-12-12 15:51:49', 240000.00, 3, 17, 'cancelado', 240000.00, 0.00, NULL, '2025-12-12 19:31:47'),
(44, 23, '2025-12-12 15:57:49', 302243.00, 12, 18, 'procesando', 302243.00, 0.00, NULL, '2025-12-15 13:57:52'),
(45, 19, '2025-12-12 19:16:51', 1212220.00, 13, 20, 'procesando', 1212220.00, 0.00, NULL, '2025-12-15 13:57:52'),
(46, 23, '2025-12-12 20:12:22', 285000.00, 12, 18, 'procesando', 285000.00, 0.00, NULL, '2025-12-15 13:57:52'),
(47, 23, '2025-12-15 13:54:19', 855000.00, 12, 18, 'pendiente', 855000.00, 0.00, NULL, '2025-12-15 13:54:19'),
(48, 23, '2025-12-15 14:46:43', 570000.00, 12, 18, 'pendiente', 570000.00, 0.00, NULL, '2025-12-15 14:46:43'),
(49, 25, '2025-12-15 14:59:39', 554000.00, 14, 21, 'enviado', 554000.00, 0.00, NULL, '2025-12-15 16:51:38'),
(50, 23, '2025-12-15 15:13:42', 320000.00, 12, 18, 'pendiente', 320000.00, 0.00, NULL, '2025-12-15 15:13:42'),
(51, 23, '2025-12-15 15:45:33', 285000.00, 12, 18, 'pendiente', 285000.00, 0.00, NULL, '2025-12-15 15:45:33'),
(52, 23, '2025-12-15 15:50:19', 290000.00, 12, 18, 'pendiente', 290000.00, 0.00, NULL, '2025-12-15 15:50:19'),
(53, 23, '2025-12-15 15:59:59', 200000.00, 12, 18, 'entregado', 200000.00, 0.00, NULL, '2025-12-15 16:40:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_items`
--

CREATE TABLE `pedido_items` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `nombre_producto` varchar(255) NOT NULL COMMENT 'Guardar nombre por si se elimina el producto',
  `talla` varchar(10) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `imagen_producto` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedido_items`
--

INSERT INTO `pedido_items` (`id`, `pedido_id`, `producto_id`, `nombre_producto`, `talla`, `cantidad`, `precio_unitario`, `subtotal`, `imagen_producto`, `fecha_creacion`) VALUES
(1, 1, 7, 'Roadlite 7', 'S', 1, 18000.00, 18000.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1732184941/2025_FULL_roadlite_7_3874_M156_P01_rnhtgw', '2025-11-04 23:16:46'),
(2, 2, 7, 'Roadlite 7', 'M', 1, 18000.00, 18000.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1732184941/2025_FULL_roadlite_7_3874_M156_P01_rnhtgw', '2025-11-04 23:22:31'),
(3, 3, 7, 'Roadlite 7', 'S', 1, 18000.00, 18000.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1732184941/2025_FULL_roadlite_7_3874_M156_P01_rnhtgw', '2025-11-05 04:15:45'),
(5, 5, 8, 'Aeroad CFR Disc Frame and Brake Kit', 'XS', 4, 80000.00, 320000.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1750671276/2026_FULL_aeroad_cfr-frs_4227_R108_P01_yhrnk6', '2025-11-06 00:14:34'),
(6, 6, 8, 'Aeroad CFR Disc Frame and Brake Kit', 'XXL', 1, 80000.00, 80000.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1750671276/2026_FULL_aeroad_cfr-frs_4227_R108_P01_yhrnk6', '2025-11-06 14:42:04'),
(7, 7, 8, 'Aeroad CFR Disc Frame and Brake Kit', 'XXL', 2, 80000.00, 160000.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1750671276/2026_FULL_aeroad_cfr-frs_4227_R108_P01_yhrnk6', '2025-11-12 21:54:41'),
(8, 8, 8, 'Aeroad CFR Disc Frame and Brake Kit', 'XS', 1, 80000.00, 80000.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1750671276/2026_FULL_aeroad_cfr-frs_4227_R108_P01_yhrnk6', '2025-11-13 00:00:15'),
(9, 9, 6, 'Endurace Young Hero', 'XS', 1, 200000.00, 200000.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1749111376/2026_FULL_endurace_yh_4285_R074_P90_rwrayu', '2025-11-13 03:36:05'),
(10, 10, 6, 'Endurace Young Hero', 'XXL', 2, 200000.00, 400000.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1749111376/2026_FULL_endurace_yh_4285_R074_P90_rwrayu', '2025-11-14 02:54:39'),
(11, 11, 2, 'Madone SLR 9 AXS Gen 8', 'M', 1, 300000.00, 300000.00, 'https://cdn.shopify.com/s/files/1/0828/8980/2039/files/ridotta-ridotta_COLNAGOG4Xbaglioreoro-31_1.jpg?v=1714527082&width=2000&height=1167&crop=center', '2025-11-14 03:42:35'),
(12, 12, 8, 'Aeroad CFR Disc Frame and Brake Kit', 'XXL', 1, 80000.00, 80000.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1750671276/2026_FULL_aeroad_cfr-frs_4227_R108_P01_yhrnk6', '2025-11-20 19:13:19'),
(13, 13, 4, 'Speedmax CFR TT', 'M', 1, 302243.00, 302243.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1754462235/2026_FULL_speedmax_cfr-tt_4254_R090_P01_aikpng', '2025-11-21 13:41:57'),
(17, 17, 17, 'Benotto Bicicleta Ruta 850', 'S', 1, 10350.00, 10350.00, 'https://m.media-amazon.com/images/I/61qXFTevvOL._AC_SL1200_.jpg', '2025-12-04 00:28:07'),
(18, 18, 17, 'Benotto Bicicleta Ruta 850', 'XS', 1, 10350.00, 10350.00, 'https://m.media-amazon.com/images/I/61qXFTevvOL._AC_SL1200_.jpg', '2025-12-04 00:38:13'),
(19, 19, 17, 'Benotto Bicicleta Ruta 850', 'M', 1, 10350.00, 10350.00, 'https://m.media-amazon.com/images/I/61qXFTevvOL._AC_SL1200_.jpg', '2025-12-05 02:01:34'),
(20, 19, 11, 'Spectral:ONfly CF LTD', 'XXL', 1, 120000.00, 120000.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1733933538/AB5288D2-3252-4D75-93DE79BEDB0CBA67', '2025-12-05 02:01:34'),
(21, 20, 17, 'Benotto Bicicleta Ruta 850', 'S', 1, 10350.00, 10350.00, 'https://m.media-amazon.com/images/I/61qXFTevvOL._AC_SL1200_.jpg', '2025-12-05 02:07:06'),
(22, 21, 11, 'Spectral:ONfly CF LTD', 'XS', 2, 120000.00, 240000.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1733933538/AB5288D2-3252-4D75-93DE79BEDB0CBA67', '2025-12-05 02:12:24'),
(24, 23, 17, 'Benotto Bicicleta Ruta 850', 'M', 1, 10350.00, 10350.00, 'https://m.media-amazon.com/images/I/61qXFTevvOL._AC_SL1200_.jpg', '2025-12-09 14:41:33'),
(27, 26, 14, 'Allez', 'M', 1, 28000.00, 28000.00, 'https://assets.specialized.com/i/specialized/90022-70_ALLEZ-E5-DISC-SMK-WHT-SILDST_HERO?$scom-pdp-gallery-image$&fmt=webp', '2025-12-11 15:33:27'),
(28, 27, 14, 'Allez', 'M', 1, 28000.00, 28000.00, 'https://assets.specialized.com/i/specialized/90022-70_ALLEZ-E5-DISC-SMK-WHT-SILDST_HERO?$scom-pdp-gallery-image$&fmt=webp', '2025-12-11 16:15:17'),
(29, 28, 15, 'Colnago T1Rs', 'L', 1, 283000.00, 283000.00, 'https://cdn.shopify.com/s/files/1/0828/8980/2039/files/Colnago_T1Rs_TT_fondonero-laterale_bagliore_oro-pursuit_1.jpg?v=1761887233&width=2000&height=1167&crop=center', '2025-12-11 23:57:54'),
(30, 29, 10, 'Exceed CF 7', 'XS', 1, 48000.00, 48000.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1740411129/2025_FULL_exceed_cf-8_3988_M160_P03_de93r4', '2025-12-12 01:25:17'),
(31, 30, 2, 'Madone SLR 9 AXS Gen 8', 'M', 1, 300000.00, 300000.00, 'https://cdn.shopify.com/s/files/1/0828/8980/2039/files/ridotta-ridotta_COLNAGOG4Xbaglioreoro-31_1.jpg?v=1714527082&width=2000&height=1167&crop=center', '2025-12-12 04:40:46'),
(32, 31, 6, 'Endurace Young Hero', 'XXL', 1, 200000.00, 200000.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1749111376/2026_FULL_endurace_yh_4285_R074_P90_rwrayu', '2025-12-12 14:23:48'),
(33, 32, 15, 'Colnago T1Rs', 'L', 1, 283000.00, 283000.00, 'https://cdn.shopify.com/s/files/1/0828/8980/2039/files/Colnago_T1Rs_TT_fondonero-laterale_bagliore_oro-pursuit_1.jpg?v=1761887233&width=2000&height=1167&crop=center', '2025-12-12 14:39:40'),
(34, 37, 14, 'Allez', 'M', 1, 28000.00, 28000.00, NULL, '2025-12-12 15:26:50'),
(35, 40, 14, 'Allez', 'M', 1, 28000.00, 28000.00, NULL, '2025-12-12 15:35:23'),
(36, 41, 14, 'Allez', 'L', 2, 28000.00, 56000.00, NULL, '2025-12-12 15:38:38'),
(37, 42, 16, 'TT1', 'M', 1, 390000.00, 390000.00, NULL, '2025-12-12 15:46:51'),
(38, 42, 13, 'Turbo Vado 4.0', 'XS', 1, 100000.00, 100000.00, NULL, '2025-12-12 15:46:51'),
(40, 43, 11, 'Spectral:ONfly CF LTD', 'M', 2, 120000.00, 240000.00, NULL, '2025-12-12 15:51:49'),
(41, 44, 4, 'Speedmax CFR TT', 'M', 1, 302243.00, 302243.00, NULL, '2025-12-12 15:57:49'),
(42, 45, 21, 'mariln', 'S', 10, 121222.00, 1212220.00, NULL, '2025-12-12 19:16:51'),
(43, 46, 23, 'S-Works Turbo Creo 3', 'M', 1, 285000.00, 285000.00, NULL, '2025-12-12 20:12:22'),
(44, 47, 23, 'S-Works Turbo Creo 3', 'M', 3, 285000.00, 855000.00, NULL, '2025-12-15 13:54:19'),
(45, 48, 23, 'S-Works Turbo Creo 3', 'M', 2, 285000.00, 570000.00, NULL, '2025-12-15 14:46:43'),
(46, 49, 5, 'Neuron CF 8', 'S', 2, 72000.00, 144000.00, NULL, '2025-12-15 14:59:39'),
(47, 49, 22, 'S-Works Tarmac SL88', 'S', 1, 290000.00, 290000.00, NULL, '2025-12-15 14:59:39'),
(48, 49, 11, 'Spectral:ONfly CF LTD', 'S', 1, 120000.00, 120000.00, NULL, '2025-12-15 14:59:39'),
(49, 50, 12, 'S-Works Tarmac SL8 LTD', 'L', 1, 320000.00, 320000.00, NULL, '2025-12-15 15:13:42'),
(50, 51, 23, 'S-Works Turbo Creo 3', 'M', 1, 285000.00, 285000.00, NULL, '2025-12-15 15:45:33'),
(51, 52, 22, 'S-Works Tarmac SL88', 'S', 1, 290000.00, 290000.00, NULL, '2025-12-15 15:50:19'),
(52, 53, 6, 'Endurace Young Hero', 'M', 1, 200000.00, 200000.00, NULL, '2025-12-15 15:59:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('montaña','ruta','urbana','eléctrica') DEFAULT NULL,
  `talla` varchar(10) DEFAULT NULL,
  `nivel_ciclismo` varchar(20) DEFAULT NULL,
  `peso` decimal(5,2) DEFAULT NULL,
  `velocidades` int(11) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `imagen_principal` varchar(255) DEFAULT NULL,
  `imagen_2` varchar(255) DEFAULT NULL,
  `imagen_3` varchar(255) DEFAULT NULL,
  `imagen_4` varchar(255) DEFAULT NULL,
  `imagen_5` varchar(255) DEFAULT NULL,
  `imagen_6` varchar(255) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `dias_envio` int(11) DEFAULT 7,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `tipo`, `talla`, `nivel_ciclismo`, `peso`, `velocidades`, `precio`, `imagen`, `imagen_principal`, `imagen_2`, `imagen_3`, `imagen_4`, `imagen_5`, `imagen_6`, `stock`, `dias_envio`, `fecha_creacion`, `descripcion`) VALUES
(2, 'Madone SLR 9 AXS Gen 8', 'ruta', 'M', 'intermedio', 8.00, 19, 300000.00, 'https://cdn.shopify.com/s/files/1/0828/8980/2039/files/ridotta-ridotta_COLNAGOG4Xbaglioreoro-31_1.jpg?v=1714527082&width=2000&height=1167&crop=center', 'https://cdn.shopify.com/s/files/1/0828/8980/2039/files/ridotta-ridotta_COLNAGOG4Xbaglioreoro-31_1.jpg?v=1714527082&width=2000&height=1167&crop=center', 'https://cdn.shopify.com/s/files/1/0828/8980/2039/files/ridotta_COLNAGOG4Xbaglioreoro-31_1.jpg?v=1714527082&width=1200&height=1200&crop=center', 'https://cdn.shopify.com/s/files/1/0828/8980/2039/files/ridotta_ColnagoG4Xgiallo-Fondonero-prospettivaant-baglioreoro_1.jpg?v=1714527082&width=1200&height=1200&crop=center', 'https://cdn.shopify.com/s/files/1/0828/8980/2039/files/ridotta_ColnagoG4Xgiallo-Fondonero-prospettivapost-baglioreoro_1.jpg?v=1714527082&width=1200&height=1200&crop=center', 'https://a.storyblok.com/f/263970/7952x5304/45083b0a70/detail-tires.jpg/m/1920x0/filters:quality(75)', 'https://a.storyblok.com/f/263970/7952x5304/5e7bbcfe26/detail-cockpit.jpg/m/1920x0/filters:quality(75)', -1, 7, '2025-10-29 13:24:08', 'La Madone SLR 9 AXS Gen 8 está diseñada para dominar las carreteras con ligereza y velocidad aerodinámicas a partes iguales. Está fabricada con nuestro Carbono OCLV Serie 900 del más alto nivel, que no solo es increíblemente ligero, sino que ofrece formas aerodinámicas para dominar las bajadas y las rectas. Esta bici viene dispuesta a llevarse la victoria gracias a una transmisión SRAM RED AXS E1 de alto nivel con medidor de potencia, ruedas de carbono Bontrager Aeolus RSL 51, un manubrio/poste de manubrio Trek Aero RSL de carbono de una pieza, y Ánforas y Porta Ánforas RSL Aero para una mayor reducción de la resistencia aerodinámica.'),
(4, 'Speedmax CFR TT', 'ruta', 'M', 'avanzado', 9.00, 12, 302243.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1754462235/2026_FULL_speedmax_cfr-tt_4254_R090_P01_aikpng', 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1754462235/2026_FULL_speedmax_cfr-tt_4254_R090_P01_aikpng', 'https://dma.canyon.com/image/upload/t_web-detail/w_2500,h_2500,c_fill/b_rgb:F2F2F2/f_auto/q_auto/v1754462235/2026_FULL_speedmax_cfr-tt_4254_R090_P01_aikpng', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1754401374/2026_TOP-1_speedmax_cfr-tt_4254_R090_P01_cfr_ytqmrh', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1754401375/2026_TOP-2_speedmax_cfr-tt_4254_R090_P01_seatstays_itf1pv', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1754401377/2026_TOP-3_speedmax_cfr-tt_4254_R090_P01_geometry_b2dzf6', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1754401378/2026_TOP-4_speedmax_cfr-tt_4254_R090_P01_adjustable-extension_ieotqx', 7, 6, '2025-10-29 20:04:45', 'Canyon Factory Racing: Cuadro CFR ganador del Campeonato del Mundo\r\n100 % compatible con UCI\r\nLos mejores componentes disponibles: Ruedas de carbono DT Swiss ARC 1100, cambio Dura-Ace Di2'),
(5, 'Neuron CF 8', 'montaña', 'S', 'intermedio', 13.90, 18, 72000.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1725953332/2025_FULL_neuron_cf-8_4006_M119_P10_jwmnny', 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1725953332/2025_FULL_neuron_cf-8_4006_M119_P10_jwmnny', 'https://dma.canyon.com/image/upload/t_web-detail/w_2500,h_2500,c_fill/b_rgb:F2F2F2/f_auto/q_auto/v1725953332/2025_FULL_neuron_cf-8_4006_M119_P10_jwmnny', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1726043584/2025_TOP-1_neuron_cf-8_4006_M119_P10_handling_qurgc1', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1725951564/2025_TOP-2_neuron_cf-8_4006_M119_P10_geo-and-wheels_m5jq5a', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1725975660/2025_TOP-3_neuron_cf-8_4006_M119_P10_suspension_ep8onf', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1725951566/2025_TOP-7_neuron_cf-8_4006_M119_P10_through-axle_yqhwsv', 10, 7, '2025-11-03 21:49:39', 'Elige la Neuron CF 8 para disfrutar de una suspensión RockShox completa que te permite rodar más alto y más lejos que antes. Cuando el sendero es cuesta abajo, la horquilla y el amortiguador suavizan las esteras de raíces y los jardines de rocas.'),
(6, 'Endurace Young Hero', 'urbana', 'XS', 'intermedio', 8.00, 21, 200000.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1749111376/2026_FULL_endurace_yh_4285_R074_P90_rwrayu', 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1749111376/2026_FULL_endurace_yh_4285_R074_P90_rwrayu', 'https://dma.canyon.com/image/upload/t_web-detail/w_2500,h_2500,c_fill/b_rgb:F2F2F2/f_auto/q_auto/v1749111376/2026_FULL_endurace_yh_4285_R074_P90_rwrayu', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1749199764/2026_TOP-1_endurace_yh_4285_R074_P90_cf_sij8mz', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1750999240/2026_TOP-2_endurace_yh_4285_R074_P90_front-triangle_krfbcw', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1749133425/2026_TOP-3_endurace_yh_4285_R074_P90_frame_ovsvro', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1750841487/2026_TOP-4_ultimate_cf-yh_4284_R101_P90_cassette_wqqayn', 15, 7, '2025-11-03 22:35:15', 'Elegante y resistente cuadro de aluminio con horquilla de carbono\r\nElegante transmisión Shimano Tiagra\r\nDesarrollos específicos para jóvenes\r\nResistentes ruedas DT Swiss\r\nEspacio libre para cubiertas 35c, cubiertas anchas Schwalbe All-Road'),
(7, 'Roadlite 7', 'urbana', 'S', 'principiante', 12.00, 12, 18000.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1732184941/2025_FULL_roadlite_7_3874_M156_P01_rnhtgw', 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1732184941/2025_FULL_roadlite_7_3874_M156_P01_rnhtgw', 'https://dma.canyon.com/image/upload/t_web-detail/w_2500,h_2500,c_fill/b_rgb:F2F2F2/f_auto/q_auto/v1732184941/2025_FULL_roadlite_7_3874_M156_P01_rnhtgw', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1732781327/2025_TOP-1_roadlite_7_3874_M156_P01_frame_copy_nmdzed', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1732781340/2025_TOP-2_roadlite_7_3874_M156_P01_cockpit-front-triangle_copy_qbv6go', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1738314286/2025_TOP-3_roadlite_7_3874_M156_P01_rack-fenders_x6cpta', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1738314286/2025_TOP-3_roadlite_7_3874_M156_P01_rack-fenders_x6cpta', 4, 7, '2025-11-03 23:09:31', 'Con un cuadro de aluminio de alta calidad, horquilla de carbono y guiado interno de cables, esta deportiva bicicleta híbrida ofrece una velocidad sin igual en su rango de precio.'),
(8, 'Aeroad CFR Disc Frame and Brake Kit', 'ruta', 'XS', 'intermedio', 8.00, 20, 80000.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1750671276/2026_FULL_aeroad_cfr-frs_4227_R108_P01_yhrnk6', 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1750671276/2026_FULL_aeroad_cfr-frs_4227_R108_P01_yhrnk6', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1750747970/2026_TOP-1_aeroad_cfr-frs_4227_R108_P01_peloton_u4peut', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1750747929/2026_TOP-2_aeroad_cfr-frs_4227_R108_P01_adaptive-cockpit_kvnoac', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1750747955/2026_TOP-3_aeroad_cfr-frs_4227_R108_P01_cockpit_yxoltq', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1750747982/2026_TOP-4_aeroad_cfr-frs_4227_R108_P01_aero-seatpost_cdqxrn', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1753362835/2026_TOP-5_aeroad_cfr-frs_4227_R108_P01_headset-bearings_dznspa', -1, 7, '2025-11-05 14:13:59', 'Nuestro cuadro de carbono tope de gama probado por profesionales en las carreras más importantes del mundo.\r\nManetas de cambio y freno Shimano Dura-Ace de nivel profesional\r\nTija de sillín aerodinámica SP0077\r\nCockpit de carbono regulable'),
(9, 'Ultimate CF 7 Di2', 'ruta', 'XS', 'intermedio', 8.30, 12, 61490.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1744815593/2026_FULL_ultimate_cf-sl-7-di2_4065_R101_P06_lrkym1', 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1744815593/2026_FULL_ultimate_cf-sl-7-di2_4065_R101_P06_lrkym1', 'https://dma.canyon.com/image/upload/t_web-detail/w_2500,h_2500,c_fill/b_rgb:F2F2F2/f_auto/q_auto/v1744815593/2026_FULL_ultimate_cf-sl-7-di2_4065_R101_P06_lrkym1', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1745487832/2026_TOP-1_ultimate_cf-sl-7-di2_4065_R101_P06_perfect-balance_dxncpy', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1745487830/2026_TOP-2_ultimate_cf-sl-7-di2_4065_R101_P06_cockpit_qycq1v', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1745487830/2026_TOP-4_ultimate_cf-sl-7-di2_4065_R101_P06_carbon_orks60', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1745487828/2026_TOP-5_ultimate_cf-sl-7-di2_4065_R101_P06_aero_o8plbq', 23, 7, '2025-11-24 01:33:18', 'Combinando el ADN de diseño legendario ganador de la Ultimate, ruedas de aluminio ligeras y fiables de DT Swiss, nuestro cockpit de carbono ajustable CP0048 y cambios electrónicos Shimano 105 Di2, esta es una bicicleta de carreras con cuadro de carbono lista para devorar puertos de montaña.'),
(10, 'Exceed CF 7', 'montaña', 'XS', 'intermedio', 9.00, 12, 48000.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1740411129/2025_FULL_exceed_cf-8_3988_M160_P03_de93r4', 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1740411129/2025_FULL_exceed_cf-8_3988_M160_P03_de93r4', 'https://dma.canyon.com/image/upload/t_web-detail/w_2500,h_2500,c_fill/b_rgb:F2F2F2/f_auto/q_auto/v1740411129/2025_FULL_exceed_cf-8_3988_M160_P03_de93r4', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1740734597/2025_TOP-1_exceed_cf-8_3988_M160_P03_dt-storage_es0tql', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1740734596/2025_TOP-2_exceed_cf-8_3988_M160_P03_mounts_kxu8oa', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1740734598/2025_TOP-3_exceed_cf-8_3988_M160_P03_frame-protection_elccab', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1740734601/2025_TOP-4_exceed_cf-8_3988_M160_P03_bb_jn3lzv', 6, 7, '2025-11-24 01:37:29', '¿Buscas un rendimiento prémium a un precio inmejorable? Con el ágil y rígido cuadro Exceed CF, el almacenamiento integrado de herramientas, la espectacular suspensión FOX y las sensacionales ruedas de carbono Reynolds, reservadas habitualmente para montajes de gama alta, esta bicicleta marca una nueva referencia en su categoría.'),
(11, 'Spectral:ONfly CF LTD', 'eléctrica', 'XS', 'intermedio', 18.00, 12, 120000.00, 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1733933538/AB5288D2-3252-4D75-93DE79BEDB0CBA67', 'https://dma.canyon.com/image/upload/t_web-p5/w_2500,h_2500,c_fit/b_rgb:F2F2F2/f_auto/q_auto/v1733933538/AB5288D2-3252-4D75-93DE79BEDB0CBA67', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fill/b_rgb:F2F2F2/f_auto/q_auto/v1725538479/346B0733-D7EF-48F4-BABD50179BE6359F', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1721866299/D0200B4E-84C3-4E3F-B95E6C68C2320FC2', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1721866302/D4470594-7018-4AE9-B873EB4157A8DC56', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1721866296/122CC784-BB84-4C0E-87CD4E50F37AB3F0', 'https://dma.canyon.com/image/upload/w_2500,h_2500,c_fit/f_auto/q_auto/v1721866305/F94538D9-BC77-4FC7-A7D38D10D4E4C1D1', 13, 7, '2025-11-24 02:08:05', 'No encontrarás un diseño mejor que este: una hoja técnica optimizada para las exigencias del trail, un motor compacto y un peso total extraordinario. Esta es una eMTB de trail ligera sin límites.'),
(12, 'S-Works Tarmac SL8 LTD', 'ruta', 'XS', 'avanzado', 9.00, 12, 320000.00, 'https://assets.specialized.com/i/specialized/74925-11_TARMAC-SW-LTD-FORWARD-50_HERO-PDP?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/74925-11_TARMAC-SW-LTD-FORWARD-50_HERO-PDP?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/74925-11_TARMAC-SW-LTD-FORWARD-50_FDSQ?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/74925-11_TARMAC-SW-LTD-FORWARD-50_RDSQ?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/74925-11_TARMAC-SW-LTD-FORWARD-50_D1-POV?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/74925-11_TARMAC-SW-LTD-FORWARD-50_D3-HT?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/74925-11_TARMAC-SW-LTD-FORWARD-50_D4-STTT?$scom-pdp-gallery-image$&fmt=webp', 9, 7, '2025-11-24 02:13:49', 'Como Specialized celebra su 50 Aniversario y la innovación que nos ha ayudado a inspirar a ciclistas de todo el mundo, tenemos la vista puesta en los próximos 50 años de innovación. La Tarmac SL8 Forward 50 LTD representa este compromiso de seguir pedaleando por el planeta innovando para los ciclistas. Como la bicicleta de carretera más rápida e innovadora del mundo, la Tarmac SL8 es la plataforma perfecta.'),
(13, 'Turbo Vado 4.0', 'montaña', 'XS', 'intermedio', 22.00, 12, 100000.00, 'https://assets.specialized.com/i/specialized/95026-52_VADO-40-SEA-CMLNLPS-GCLMET_HERO-PDP?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/95026-52_VADO-40-SEA-CMLNLPS-GCLMET_HERO-PDP?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/95026-52_VADO-40-SEA-CMLNLPS-GCLMET_FDSQ?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/95026-52_VADO-40-SEA-CMLNLPS-GCLMET_RDSQ?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/95026-52_VADO-40-SEA-CMLNLPS-GCLMET_D1-POV?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/95026-52_VADO-40-SEA-CMLNLPS-GCLMET_D3-HT?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/95026-52_VADO-40-SEA-CMLNLPS-GCLMET_D4-STTT?$scom-pdp-gallery-image$&fmt=webp', 13, 7, '2025-11-24 02:17:59', '«Considera a la Vado tu bicicleta de transporte de alto rendimiento, llevándote a donde necesites con una combinación inigualable de velocidad, confiabilidad y seguridad. Ya sea que la cargues con tus cosas, la uses para ir al trabajo o para llegar al gimnasio, la potencia total y la gran autonomía de la Vado te llevarán del ‘punto A’ al ‘punto B’ lo más rápido posible.»'),
(14, 'Allez', 'ruta', 'S', 'intermedio', 11.00, 15, 28000.00, 'https://assets.specialized.com/i/specialized/90022-70_ALLEZ-E5-DISC-SMK-WHT-SILDST_HERO?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/90022-70_ALLEZ-E5-DISC-SMK-WHT-SILDST_HERO?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/90022-70_ALLEZ-E5-DISC-SMK-WHT-SILDST_FDSQ?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/90022-70_ALLEZ-E5-DISC-SMK-WHT-SILDST_RDSQ?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/003_ROAD-6189_Allez_LC?$hybris-pdp-photo-carousel$&fmt=webp', 'https://assets.specialized.com/i/specialized/001_ROAD-6189_Allez_LC?$hybris-pdp-photo-carousel$&fmt=webp', 'https://assets.specialized.com/i/specialized/005_ROAD-6189_Allez_LC?$hybris-pdp-photo-carousel$&fmt=webp', 14, 7, '2025-11-24 04:13:11', 'Cuatro décadas después de que la primera Specialized Allez saliera a la carretera, la nueva Allez es la mejor de todas. La más liviana en su categoría,* entrega más confianza, versatilidad y rendimiento que nunca, para más ciclistas que nunca. Ya sea que estés buscando una aleación premium, quieras una bicicleta para paseos de fin de semana y viajes rápidos al trabajo, o si recién comienzas tu camino en el ciclismo de ruta, el rendimiento en la carretera empieza con Allez.'),
(15, 'Colnago T1Rs', 'ruta', 'S', 'avanzado', 8.00, 21, 283000.00, 'https://cdn.shopify.com/s/files/1/0828/8980/2039/files/Colnago_T1Rs_TT_fondonero-laterale_bagliore_oro-pursuit_1.jpg?v=1761887233&width=2000&height=1167&crop=center', 'https://cdn.shopify.com/s/files/1/0828/8980/2039/files/Colnago_T1Rs_TT_fondonero-laterale_bagliore_oro-pursuit_1.jpg?v=1761887233&width=2000&height=1167&crop=center', 'https://cdn.shopify.com/s/files/1/0828/8980/2039/files/Colnago_T1Rs_fondonero-10.jpg?v=1761887426&width=1200&height=1200&crop=center', 'https://cdn.shopify.com/s/files/1/0828/8980/2039/files/Colnago_T1Rs_fondonero-11_1.jpg?v=1761887871&width=1200&height=1200&crop=center', 'https://cdn.shopify.com/s/files/1/0828/8980/2039/files/Colnago_T1Rs_fondonero-55_1.jpg?v=1761887871&width=1200&height=1200&crop=center', 'https://a.storyblok.com/f/263970/8796x6509/ac8f8e2fe3/colnago_t1rs_fondobianco-70.jpg/m/1920x0/filters:quality(75)', 'https://a.storyblok.com/f/263970/7784x5192/b9ab779f88/colnago_t1rs_fondonero-11.jpg/m/1920x0/filters:quality(75)', 7, 7, '2025-11-24 04:20:16', 'La configuración de contrarreloj/persecución incorpora una unidad monocoque de carbono compuesta por potencia y barra base, diseñada para ofrecer la máxima rigidez y una integración aerodinámica perfecta.'),
(16, 'TT1', 'ruta', 'XS', 'avanzado', 8.00, 21, 390000.00, 'https://cdn.shopify.com/s/files/1/0828/8980/2039/files/ColnagoTT1-cover_resized.jpg?v=1709161398&width=2000&height=1167&crop=center', 'https://cdn.shopify.com/s/files/1/0828/8980/2039/files/ColnagoTT1-cover_resized.jpg?v=1709161398&width=2000&height=1167&crop=center', 'https://cdn.shopify.com/s/files/1/0828/8980/2039/files/ColnagoTT1-cover2_resized.jpg?v=1709161398&width=1200&height=1200&crop=center', 'https://cdn.shopify.com/s/files/1/0828/8980/2039/files/ColnagoTT1-cover3_resized.jpg?v=1709161399&width=1200&height=1200&crop=center', 'https://a.storyblok.com/f/263970/7952x5304/f030332edf/1-revolutionary-shapes.jpg/m/1920x0/filters:quality(75)', 'https://a.storyblok.com/f/263970/7952x5304/2293e9b6d3/2-chainstay-alti.jpg/m/1920x0/filters:quality(75)', 'https://a.storyblok.com/f/263970/7952x5304/bbfc704855/4-colnago-basebar.jpg/m/1920x0/filters:quality(75)', 6, 7, '2025-11-24 04:23:27', 'Con la Colnago TT1 puedes disfrutar de lo último en tecnología aerodinámica aplicada a una bicicleta. La Colnago TT1 demuestra sistemáticamente su nivel superior, tanto en el prólogo contrarreloj del Tour de Francia como en una etapa de triathlón.'),
(17, 'Benotto Bicicleta Ruta 850', 'ruta', 'XS', 'principiante', 15.00, 14, 10350.00, 'https://m.media-amazon.com/images/I/61qXFTevvOL._AC_SL1200_.jpg', 'https://m.media-amazon.com/images/I/61qXFTevvOL._AC_SL1200_.jpg', 'https://m.media-amazon.com/images/I/61hqiXaHkeL._AC_SL1200_.jpg', 'https://m.media-amazon.com/images/I/61XFGGLceBL._AC_SL1200_.jpg', 'https://m.media-amazon.com/images/I/61ta96HeoRL._AC_SL1200_.jpg', 'https://m.media-amazon.com/images/I/61hqiXaHkeL._AC_SL1200_.jpg', 'https://m.media-amazon.com/images/I/61ta96HeoRL._AC_SL1200_.jpg', -1, 7, '2025-11-24 04:29:50', 'Bicicleta Ruta 850 R700 14V con Cuadro de Aluminio. La Bici Perfecta Para Recorrer Largas Distancias. Sal a Rodar ya sea en Montaña o Ciudad con tu Bici Benotto.'),
(18, 'Rockhopper Expert', 'montaña', 'XS', '0', 12.00, 9, 30000.00, 'https://assets.specialized.com/i/specialized/91524-34_ROCKHOPPER-EXPERT-KH-EGRN-DKMOS-XS--_HERO?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/91524-34_ROCKHOPPER-EXPERT-KH-EGRN-DKMOS-XS--_HERO?$scom-pdp-gallery-image$&fmt=webp', '', '', '', '', '', -1, 7, '2025-12-11 15:24:05', 'Comienza con un cuadro de Aluminio Premium A1 cuidadosamente diseñado, agrega una geometría moderna que pone un ojo en la eficiencia y el otro en la capacidad segura, y tendrás la base sólida de nuestra mejor Rockhopper hasta el momento.\\\\r\\\\n\\\\r\\\\nSi a eso le sumamos una lista de piezas que no termina (¿podemos mencionar la horquilla Judy SoloAir de RockShox y los frenos de disco hidráulicos MT-200 siempre en carga de Shimanos?), obtendremos una bicicleta lista para usar sin cámara. Rockhopper equipada con Shimano Deore M5100 1x11 y absolutamente preparada para volar.'),
(19, 'C68 Road Ti', 'ruta', 'XS', 'intermedio', 10.00, 18, 260000.00, 'https://a.storyblok.com/f/263970/x/5953e847f2/colnago-c68-510r-ti-top-to-side.mp4', 'https://a.storyblok.com/f/263970/x/5953e847f2/colnago-c68-510r-ti-top-to-side.mp4', 'https://a.storyblok.com/f/263970/3840x2400/aa44ea6a91/colnago-c68-510-ti-3quarter.jpg/m/1920x0/filters:quality(75)', 'https://a.storyblok.com/f/263970/7952x5304/aa889b987d/1-giunzione-in-titanio.jpg/m/1920x0/filters:quality(75)', 'https://a.storyblok.com/f/263970/7952x5304/b805ff9d37/1-gomme-32.jpg/m/1920x0/filters:quality(75)', 'https://a.storyblok.com/f/263970/7952x5304/12318b6b35/2-t47.jpg/m/1920x0/filters:quality(75)', 'https://a.storyblok.com/f/263970/8215x5315/64c61edf46/7-geometrie-aggressive.jpg/m/1920x0/filters:quality(75)', -1, 7, '2025-12-12 17:25:50', 'Nuestra bicicleta más longeva, inoxidable como cualquier diamante que se precie. Un proceso de mejora constante y minuciosa, trabajando a menudo en detalles invisibles, capaz de combinar la innovación orientada al futuro y el legado de más de 45 años de Basso.'),
(20, 'sdasdsads', 'urbana', 'S', 'intermedio', 11.00, 12, 100000.00, 'https://a.storyblok.com/f/263970/4660x3306/2df4a5610f/1-banner-alto.jpg/m/1920x0/filters:quality(75)', 'https://a.storyblok.com/f/263970/4660x3306/2df4a5610f/1-banner-alto.jpg/m/1920x0/filters:quality(75)', '', '', '', '', '', -1, 7, '2025-12-12 17:43:25', 'sasaasasajddddddddddddddddddddddddddddddddddddddddddddddddnckscnkdcds'),
(21, 'mariln', 'ruta', 'XS', 'intermedio', 12.00, 22, 121222.00, 'https://assets.specialized.com/i/specialized/94925-01_TARMAC-SL8-SW-DI2-SLDMET-REDPRL-METWHTSIL_HERO-PDP?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/94925-01_TARMAC-SL8-SW-DI2-SLDMET-REDPRL-METWHTSIL_HERO-PDP?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/94925-01_TARMAC-SL8-SW-DI2-SLDMET-REDPRL-METWHTSIL_FDSQ?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/94925-01_TARMAC-SL8-SW-DI2-SLDMET-REDPRL-METWHTSIL_RDSQ?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/94925-01_TARMAC-SL8-SW-DI2-SLDMET-REDPRL-METWHTSIL_D4-STTT?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/94925-01_TARMAC-SL8-SW-DI2-SLDMET-REDPRL-METWHTSIL_D3-HT?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/94925-01_TARMAC-SL8-SW-DI2-SLDMET-REDPRL-METWHTSIL_D1-POV?$scom-pdp-gallery-image$&fmt=webp', -1, 5, '2025-12-12 18:50:21', 'lsssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss ssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss'),
(22, 'S-Works Tarmac SL88', 'ruta', 'XS', 'intermedio', 12.00, 12, 290000.00, 'https://assets.specialized.com/i/specialized/94924-00_TARMAC-SL8-SW-DI2-FOGTNT-GRNGSTPRL-REDGSTPRL_HERO?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/94924-00_TARMAC-SL8-SW-DI2-FOGTNT-GRNGSTPRL-REDGSTPRL_HERO?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/94924-00_TARMAC-SL8-SW-DI2-FOGTNT-GRNGSTPRL-REDGSTPRL_FDSQ?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/94924-00_TARMAC-SL8-SW-DI2-FOGTNT-GRNGSTPRL-REDGSTPRL_RDSQ?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/94924-00_TARMAC-SL8-SW-DI2-FOGTNT-GRNGSTPRL-REDGSTPRL_D1-POV?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/94924-00_TARMAC-SL8-SW-DI2-FOGTNT-GRNGSTPRL-REDGSTPRL_D3-HT?$scom-pdp-gallery-image$&fmt=webp', 'https://assets.specialized.com/i/specialized/94924-00_TARMAC-SL8-SW-DI2-FOGTNT-GRNGSTPRL-REDGSTPRL_D4-STTT?$scom-pdp-gallery-image$&fmt=webp', 1, 7, '2025-12-12 19:30:19', 'Nada es más rápido que la Tarmac SL8 gracias a una combinación de aerodinámica, ligereza y calidad de conducción que antes se creía imposible. Tras ocho generaciones y más de dos décadas de desarrollo, es más que la Tarmac más rápida de la historia: es la bicicleta de carreras más rápida del mundo.'),
(23, 'S-Works Turbo Creo 3', 'eléctrica', 'XS', 'intermedio', 22.00, 12, 285000.00, 'https://assets.specialized.com/i/specialized/98126-00_CREO-SL-SW-CARBON-BDXMET-GLDPRL-SILDST_HERO-PDP_DARK?$scom-pdp-gallery-image-premium$&fmt=webp', 'https://assets.specialized.com/i/specialized/98126-00_CREO-SL-SW-CARBON-BDXMET-GLDPRL-SILDST_HERO-PDP_DARK?$scom-pdp-gallery-image-premium$&fmt=webp', 'https://assets.specialized.com/i/specialized/98126-00_CREO-SL-SW-CARBON-BDXMET-GLDPRL-SILDST_FDSQ_DARK?$scom-pdp-gallery-image-premium$&fmt=webp', 'https://assets.specialized.com/i/specialized/98126-00_CREO-SL-SW-CARBON-BDXMET-GLDPRL-SILDST_RDSQ_DARK?$scom-pdp-gallery-image-premium$&fmt=webp', 'https://assets.specialized.com/i/specialized/98126-00_CREO-SL-SW-CARBON-BDXMET-GLDPRL-SILDST_D1-POV_DARK?$scom-pdp-gallery-image-premium$&fmt=webp', 'https://assets.specialized.com/i/specialized/98126-00_CREO-SL-SW-CARBON-BDXMET-GLDPRL-SILDST_D3-HT_DARK?$scom-pdp-gallery-image-premium$&fmt=webp', 'https://assets.specialized.com/i/specialized/98126-00_CREO-SL-SW-CARBON-BDXMET-GLDPRL-SILDST_D4-STTT_DARK?$scom-pdp-gallery-image-premium$&fmt=webp', 11, 7, '2025-12-12 20:10:33', '¿Carretera o gravel? ¿5 millas o 5 horas? ¿Subir o bajar? ¿Sufrir o sonreír? ¿Más potencia o menos peso? La Creo 2 no se trata de tener que elegir. Se trata de tenerlo todo. Con más potencia, gran autonomía, peso ligero, enorme espacio para neumáticos y Future Shock 3.0, rompe las categorías, haciendo posibles rutas que antes eran imposibles.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_tallas`
--

CREATE TABLE `producto_tallas` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `talla` varchar(10) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producto_tallas`
--

INSERT INTO `producto_tallas` (`id`, `producto_id`, `talla`, `stock`, `activo`, `fecha_creacion`) VALUES
(1, 2, 'M', 7, 0, '2025-11-03 22:21:33'),
(2, 4, 'M', 6, 1, '2025-11-03 22:21:33'),
(3, 5, 'S', 10, 1, '2025-11-03 22:21:33'),
(4, 6, 'XS', 3, 1, '2025-11-03 22:35:15'),
(5, 6, 'S', 1, 1, '2025-11-03 22:35:15'),
(6, 6, 'L', 2, 1, '2025-11-03 22:35:15'),
(7, 6, 'XL', 1, 1, '2025-11-03 22:35:15'),
(8, 6, 'XXL', 2, 1, '2025-11-03 22:35:15'),
(11, 6, 'M', 2, 1, '2025-11-03 22:35:36'),
(15, 7, 'S', 0, 0, '2025-11-03 23:09:31'),
(17, 7, 'M', 2, 1, '2025-11-03 23:09:49'),
(18, 8, 'XS', 0, 0, '2025-11-05 14:13:59'),
(19, 8, 'XXL', 0, 0, '2025-11-05 14:13:59'),
(20, 9, 'XS', 8, 1, '2025-11-24 01:33:18'),
(21, 9, 'S', 7, 1, '2025-11-24 01:33:18'),
(22, 9, 'M', 3, 1, '2025-11-24 01:33:18'),
(23, 9, 'L', 2, 1, '2025-11-24 01:33:18'),
(24, 9, 'XL', 1, 1, '2025-11-24 01:33:18'),
(25, 9, 'XXL', 2, 1, '2025-11-24 01:33:18'),
(26, 10, 'XS', 2, 1, '2025-11-24 01:37:29'),
(27, 10, 'S', 1, 1, '2025-11-24 01:37:29'),
(28, 10, 'M', 2, 1, '2025-11-24 01:37:29'),
(29, 11, 'XS', 0, 1, '2025-11-24 02:08:05'),
(30, 11, 'S', 0, 1, '2025-11-24 02:08:05'),
(31, 11, 'M', 7, 1, '2025-11-24 02:08:05'),
(32, 11, 'XL', 1, 1, '2025-11-24 02:08:05'),
(33, 11, 'XXL', 2, 1, '2025-11-24 02:08:05'),
(34, 12, 'XS', 1, 1, '2025-11-24 02:13:49'),
(35, 12, 'S', 1, 1, '2025-11-24 02:13:49'),
(36, 12, 'M', 1, 1, '2025-11-24 02:13:49'),
(37, 12, 'L', 3, 1, '2025-11-24 02:13:49'),
(38, 12, 'XXL', 1, 1, '2025-11-24 02:13:49'),
(39, 13, 'XS', 1, 1, '2025-11-24 02:17:59'),
(40, 13, 'S', 2, 1, '2025-11-24 02:17:59'),
(41, 13, 'M', 4, 1, '2025-11-24 02:17:59'),
(42, 13, 'L', 6, 1, '2025-11-24 02:17:59'),
(43, 14, 'S', 2, 1, '2025-11-24 04:13:11'),
(44, 14, 'M', 4, 1, '2025-11-24 04:13:11'),
(45, 14, 'L', 8, 1, '2025-11-24 04:13:11'),
(49, 15, 'S', 2, 1, '2025-11-24 04:20:16'),
(50, 15, 'M', 1, 1, '2025-11-24 04:20:16'),
(51, 15, 'L', 3, 1, '2025-11-24 04:20:16'),
(52, 16, 'XS', 1, 1, '2025-11-24 04:23:27'),
(53, 16, 'S', 1, 1, '2025-11-24 04:23:27'),
(54, 16, 'M', 1, 1, '2025-11-24 04:23:27'),
(55, 16, 'L', 1, 1, '2025-11-24 04:23:27'),
(56, 17, 'XS', 0, 0, '2025-11-24 04:29:50'),
(57, 17, 'S', 0, 0, '2025-11-24 04:29:50'),
(58, 17, 'M', 0, 0, '2025-11-24 04:29:50'),
(59, 17, 'L', 0, 0, '2025-11-24 04:29:50'),
(60, 17, 'XL', 0, 0, '2025-11-24 04:29:50'),
(61, 17, 'XXL', 0, 0, '2025-11-24 04:29:50'),
(68, 18, 'XS', 1, 0, '2025-12-11 15:24:05'),
(69, 18, 'S', 2, 0, '2025-12-11 15:24:05'),
(70, 18, 'XXL', 2, 0, '2025-12-11 15:24:05'),
(73, 18, 'M', 2, 0, '2025-12-11 15:25:30'),
(75, 16, 'XL', 2, 1, '2025-12-11 16:31:57'),
(76, 19, 'XS', 2, 0, '2025-12-12 17:25:50'),
(77, 19, 'S', 2, 0, '2025-12-12 17:25:50'),
(78, 19, 'M', 222, 0, '2025-12-12 17:25:50'),
(79, 19, 'XL', 2, 0, '2025-12-12 17:25:50'),
(80, 19, 'XXL', 1, 0, '2025-12-12 17:25:50'),
(81, 20, 'S', 7, 0, '2025-12-12 17:43:25'),
(82, 21, 'XS', 3, 0, '2025-12-12 18:50:21'),
(83, 21, 'S', 2, 0, '2025-12-12 18:50:21'),
(84, 21, 'M', 2, 0, '2025-12-12 18:50:21'),
(85, 21, 'L', 0, 0, '2025-12-12 18:50:21'),
(86, 21, 'XL', 0, 0, '2025-12-12 18:50:21'),
(87, 21, 'XXL', 0, 0, '2025-12-12 18:50:21'),
(88, 22, 'XS', 0, 0, '2025-12-12 19:30:19'),
(89, 22, 'S', 0, 1, '2025-12-12 19:30:19'),
(90, 22, 'M', 1, 1, '2025-12-12 19:30:19'),
(91, 22, 'L', 0, 0, '2025-12-12 19:30:19'),
(92, 22, 'XL', 0, 0, '2025-12-12 19:30:19'),
(93, 22, 'XXL', 0, 0, '2025-12-12 19:30:19'),
(94, 23, 'XS', 1, 1, '2025-12-12 20:10:33'),
(95, 23, 'S', 0, 0, '2025-12-12 20:10:33'),
(96, 23, 'M', 2, 1, '2025-12-12 20:10:33'),
(97, 23, 'L', 8, 1, '2025-12-12 20:10:33'),
(98, 23, 'XL', 0, 0, '2025-12-12 20:10:33'),
(99, 23, 'XXL', 0, 0, '2025-12-12 20:10:33'),
(100, 7, 'L', 2, 1, '2025-12-15 16:34:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones`
--

CREATE TABLE `sesiones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_expiracion` datetime NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sesiones`
--

INSERT INTO `sesiones` (`id`, `usuario_id`, `token`, `fecha_creacion`, `fecha_expiracion`, `ip_address`, `user_agent`, `activa`) VALUES
(2, 2, '98d531b03ad5182bd9322da05fceec55f42c14f5dab5d38205222c58ed6e3bbb', '2025-11-05 21:34:56', '2025-11-06 22:34:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 0),
(3, 2, '78136ea9141730f96bc12e2f954bfda4cc3e7cf4f1cd34ab3a4b72df72d04020', '2025-11-06 00:11:22', '2025-11-07 01:11:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 0),
(6, 2, '2b0b3340c1ea1c9d889781711dfa7cd615747fdf6864141b554b5ade761871b8', '2025-11-06 14:44:15', '2025-11-07 15:44:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 0),
(11, 2, '74d3229377f6d76f2b76953865c9e9b4b8e066fb1846134b6aed726933639f70', '2025-11-11 21:26:14', '2025-11-12 22:26:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 0),
(14, 2, '169c3bcd9827332afd7ab35ffeec1ec158c5c1b8b47b7acf02f007f1c1ce2358', '2025-11-13 00:00:47', '2025-11-14 01:00:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 0),
(17, 2, 'dde9ece493b19d5b3ce85701d11fab62e968aefc79bffe247c2e85b6546603ec', '2025-11-13 03:37:21', '2025-11-14 04:37:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 0),
(19, 2, '24108532e68720ce0f005f0058aa40f75e2b145c5c9966f6d4cc963b81a3d652', '2025-11-14 02:53:17', '2025-11-15 03:53:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 0),
(20, 2, 'eb4919bf7ecd5d45d87ba8ae218c9dd5a67f49a737bdb58b03275f9e71cb4a7f', '2025-11-14 05:28:40', '2025-11-15 06:28:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 0),
(25, 2, 'f84587a25f82d10f6fa854b9947da427055e50890e955a64d1b32290e55568eb', '2025-11-22 04:20:45', '2025-11-23 05:20:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 0),
(26, 13, '50920df090d453bca1e61c03eb82bf32fef63e943e03530797524dc3b0d884fc', '2025-11-22 04:23:49', '2025-11-23 05:23:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 0),
(30, 2, 'c07917ef61b2482ab5e0c92c3e37dd727727bf4a6e12c5b9bbfdfe8c308e3c27', '2025-11-24 02:49:19', '2025-11-25 03:49:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 0),
(33, 14, '1bb61ed4650ab5b866e4d4f408695e3d788f3e38a6e44566ec668009caa4ed61', '2025-11-24 04:40:11', '2025-11-25 05:40:11', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 1),
(39, 2, '5e6a0e7a1f3e68f57d84971f1eb4c868304876dad51653f8ff780bf2ff30d616', '2025-12-05 02:24:46', '2025-12-06 03:24:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', 0),
(45, 2, '774136842321f0e1616df8b58c55ab93d3d4a43241d79fa172f071fc222049ea', '2025-12-10 20:01:32', '2025-12-11 21:01:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(46, 19, 'a305c9964c1991e347522ddbcfb35498730462de753b7694a6ba407c8ad030ef', '2025-12-10 20:09:25', '2025-12-11 21:09:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(51, 19, '998f254583d399e4bc4de3222c37957d1e8888f1640151c2c1c7d9d6a5437cd4', '2025-12-11 15:20:56', '2025-12-12 16:20:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(53, 19, '8185d1ad410fe570c632c2a6f37ba28d476ee5aaa3092f316e5f85cb6575708e', '2025-12-11 15:34:39', '2025-12-12 16:34:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(54, 2, '655c5d99b535360e5ecdf8703b35101f4f98289089c8de8b961721164ac464a5', '2025-12-11 15:35:57', '2025-12-12 16:35:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(55, 2, '35688e7828fbd6c1a9abcc42440fcf23a66c81967079d3e2b6e1e7e4b991320a', '2025-12-11 15:59:15', '2025-12-12 16:59:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(57, 2, '49162fd27759a5cdb8c8022bfa508713cb14daf66ea4c0e0e6a227fdc84b7f0f', '2025-12-11 16:15:50', '2025-12-12 17:15:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(58, 19, '300d13ae1f258953e4590fa00637842e7f93ac40e0ff2a7a0117cb0dc2261aa5', '2025-12-11 16:26:33', '2025-12-12 17:26:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(59, 2, 'c7589fc985ee5a3998c052322be9894c33438437a38ee7b287270058bfecf282', '2025-12-11 16:51:36', '2025-12-12 17:51:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(61, 2, 'fe02d7fde7bd48536f703853994aaaa5160f3a7c9b705b9bf3fa162f1998163b', '2025-12-11 16:58:38', '2025-12-12 17:58:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(65, 2, 'b517328da4badc45fba69bfc5f73395e595f9f83d8d4aeb5109459ad45f79e53', '2025-12-12 00:15:49', '2025-12-13 01:15:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(67, 19, '563ed10f9863dec0629ea392f5ea81e4d78181e54a9c7450f6302186b822d04a', '2025-12-12 02:59:26', '2025-12-13 03:59:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(68, 2, 'ffb15d99c0e1fe423072264ff32cebc05f3e033314e41d06a6ba7d7f3f2bd372', '2025-12-12 04:18:10', '2025-12-13 05:18:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(70, 2, 'bc7e3c51979978e3ac35bcfd9b214aa5297bb82f56ac2e1f465da0d663d86ca8', '2025-12-12 04:41:23', '2025-12-13 05:41:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(72, 2, '5c708193cc126cf9b272656c17a86a9ae7071c50bb9cd377e731ed2b88a302dd', '2025-12-12 15:47:24', '2025-12-13 16:47:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(73, 23, 'a366f03deb10f1ecaa592ee1314706e4772a7b2c1d5f4ed6ec7be7b18eec0011', '2025-12-12 15:53:41', '2025-12-13 16:53:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(74, 2, 'ccc49f76f4d73c3bb7771471e6b6a8c3a8ef4cd76af2162ea9f97f687fb69950', '2025-12-12 15:58:21', '2025-12-13 16:58:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(75, 19, '798082432e8c63596ecb8ba7ec2f302cd0c2bbaa9471ce08bdf6fd59f748cfd3', '2025-12-12 17:13:22', '2025-12-13 18:13:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(76, 19, 'a6ad272f31284cf3995e4abb47c83a42ee9b0cc2d755f430b099c263f8ea9e7d', '2025-12-12 18:16:09', '2025-12-13 19:16:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(77, 2, '4681c5c86117b7970110a3946845019da7868fd02f6af7d251504c0cda4ac0d7', '2025-12-12 19:17:35', '2025-12-13 20:17:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(78, 2, '8f76671f4c202dd600a5962ca6da30a9b7f0ba5b62215c2f46b3cd7a58d090d6', '2025-12-12 20:07:55', '2025-12-13 21:07:55', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(79, 23, 'd6c90451a116656bf7f5ab0fcdce47fcca57bee263bdcb61c264ecb347b3fc7d', '2025-12-12 20:11:46', '2025-12-13 21:11:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(80, 23, 'c79c6ae6727b8cd5dc838a93ba7244d70f1160207b575e02c7db115ec6630364', '2025-12-15 13:53:30', '2025-12-16 14:53:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(81, 2, 'bcc9598e7a10df5b6f228b75dfc1e67d8a552047f31edfedef3b2d2a0696c04b', '2025-12-15 13:57:52', '2025-12-16 14:57:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(82, 23, '1346df15a488c0be1adce7844cd25a43b9ae927cdd02f74084bab435fe86c984', '2025-12-15 14:45:41', '2025-12-16 15:45:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(84, 2, '86fd79ee5de6fb194af0fb7108a6cf7b3e572841bd45e012535b87293eb85e91', '2025-12-15 15:00:33', '2025-12-16 16:00:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(85, 23, '5d4bd45791698b2ad7b3d0e870abbd96079bf538a4b4d69ded5c71f7e100f8bc', '2025-12-15 15:12:30', '2025-12-16 16:12:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(86, 23, '16adfdd8c73564dac75f8a2e1864ef1da7e9dd17095f0bd36ccac9f6c3da55aa', '2025-12-15 15:14:21', '2025-12-16 16:14:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(87, 23, '93277e96832490afd0856f801c6a3fbf83542b34a41a7287472b80b44a98931b', '2025-12-15 15:23:57', '2025-12-16 16:23:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(88, 23, 'c32f8d1644d0ce2f12cc195cfa51a1c56219e628bf248778659a6cd7956c2ee1', '2025-12-15 15:27:20', '2025-12-16 16:27:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(89, 23, '6022cbebe52cb472c1dd13b826bc45a6111256fd395e5b37d5e1e3471144d0a7', '2025-12-15 15:36:16', '2025-12-16 16:36:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(90, 19, 'cc11decf77f2b24452f1b47c7fcc41a23296ce4815d716e160d64f3d8a1392da', '2025-12-15 16:28:45', '2025-12-16 17:28:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(91, 2, '8430e1a4468682029f6c5a3bb7bea2951e91afd1ad411c03da7f08c8b4cba02f', '2025-12-15 16:39:02', '2025-12-16 17:39:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0),
(92, 23, '0117c2ec4abbe2273465c0828b3b4e4373bec6fd216e4a539a81951eddbe4fe3', '2025-12-15 16:51:54', '2025-12-16 17:51:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('cliente','operador','admin') DEFAULT 'cliente',
  `nivel_ciclismo` enum('principiante','intermedio','avanzado') NOT NULL,
  `edad` int(11) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_exp` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `telefono`, `password`, `rol`, `nivel_ciclismo`, `edad`, `fecha_registro`, `reset_token`, `reset_token_exp`) VALUES
(1, 'Usuario Eliminado (Histórico)', 'eliminado_1765554522_1@bikestore.anonymized', NULL, '$2y$10$NM41ia7HTHy072e0e82A7.ADGrrm7ZapnwNd27e9g4xNpYgBpZDY2', '', 'avanzado', 20, '2025-09-29 21:25:29', NULL, NULL),
(2, 'Administrador Admin', 'admin@bikestore.com', NULL, '$2y$10$OpZ6ftjiPmJ3Z19Je9m2K.KXKefqJhAHpWhf3X2H6hRo6frnfiSoO', 'admin', 'avanzado', 20, '2025-09-29 21:27:23', NULL, NULL),
(13, 'Magaly Chavez Fonseca', 'magalyfm@gmail.com', NULL, '$2y$10$0tZ60BJ76v6hxAx8I6HnPOWdiB7qOTP8ETN1Bp5il1xSZliEBYa2e', 'cliente', 'principiante', 21, '2025-11-22 04:23:27', NULL, NULL),
(14, 'JOSE MANUEL Fragoso Rizo', 'fragoso.rizo.jose.manuel.m5@gmail.com', NULL, '$2y$10$0chOX.57zs0FRMyAWrhp6e3irymcu996o.XMiXBfLBDy/KnOB/MlS', 'cliente', 'avanzado', 21, '2025-11-24 04:39:59', NULL, NULL),
(19, 'Operador', 'operador@bikestore.com', NULL, '$2y$10$9r68GjlBbcHYdmgm4UA.euNp.FogRTjsGEwQF..UDG7yEnppybYM.', 'operador', 'avanzado', 20, '2025-12-10 20:03:12', NULL, NULL),
(20, 'Usuario Eliminado (Histórico)', 'eliminado_1765467289_20@bikestore.anonymized', NULL, '$2y$10$7dHDpJQ0KAanI.AvyNvjNuejT8WXFJfBtV/tvOX0lVPGRNuFAfCjC', '', 'intermedio', 21, '2025-12-11 15:19:36', NULL, NULL),
(21, 'Miguel Torres Prestons', 'miguel@gmail.com', NULL, '$2y$10$iqzQ8Z1Un9.5olMy8sa4Su518.4OtLw6fInE461RTHp0sDDLOvVCe', 'cliente', 'principiante', 13, '2025-12-11 15:42:31', NULL, NULL),
(22, 'Usuario Eliminado (Histórico)', 'eliminado_1765514546_22@bikestore.anonymized', NULL, '$2y$10$KfaIBNmJG.WzOqeBqyRGNeeYxasiWUMqkx76x7sFzL29FiZXfEZWO', '', 'avanzado', 23, '2025-12-12 01:12:37', NULL, NULL),
(23, 'Jose Manuel Fragoso Rizo', 'jfragosorizo@gmail.com', NULL, '$2y$10$d7kEzEG8cUPfr2wb9T3mGOo/9GoXLPFyRHCtwE7RH7HbspEXIjIkG', 'cliente', 'intermedio', 20, '2025-12-12 15:53:30', NULL, NULL),
(24, 'juan', 'juan123@gmail.com', NULL, '$2y$10$UdJFx2XgW0BCKS/hdSJVr.z4BN6Xpq5PhS6jNO8VGnWbeeVnS555S', 'cliente', 'intermedio', 23, '2025-12-15 13:59:49', NULL, NULL),
(25, 'Usuario Eliminado (Histórico)', 'eliminado_1765810842_25@bikestore.anonymized', NULL, '$2y$10$wn36zupOqTZNH7tQbT5LJ.mvcJNueUqk8B6fayKFp4/G2JoJrnsi6', '', 'intermedio', 21, '2025-12-15 14:50:18', NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_item` (`usuario_id`,`producto_id`,`talla`),
  ADD KEY `idx_usuario_carrito` (`usuario_id`),
  ADD KEY `idx_producto_carrito` (`producto_id`);

--
-- Indices de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_principal` (`usuario_id`,`es_principal`);

--
-- Indices de la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_principal` (`usuario_id`,`es_principal`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pedido` (`pedido_id`),
  ADD KEY `idx_producto` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `producto_tallas`
--
ALTER TABLE `producto_tallas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_producto_talla` (`producto_id`,`talla`);

--
-- Indices de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_usuario_activa` (`usuario_id`,`activa`),
  ADD KEY `idx_expiracion` (`fecha_expiracion`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `producto_tallas`
--
ALTER TABLE `producto_tallas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD CONSTRAINT `comentarios_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `comentarios_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD CONSTRAINT `direcciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  ADD CONSTRAINT `metodos_pago_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  ADD CONSTRAINT `pedido_items_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedido_items_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `producto_tallas`
--
ALTER TABLE `producto_tallas`
  ADD CONSTRAINT `producto_tallas_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD CONSTRAINT `sesiones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
