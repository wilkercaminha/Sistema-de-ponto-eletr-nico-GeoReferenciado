-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 05/08/2024 às 19:33
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `ponto`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `ponto`
--

CREATE TABLE `ponto` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `entrada` datetime DEFAULT NULL,
  `saida` datetime DEFAULT NULL,
  `almoco_saida` datetime DEFAULT NULL,
  `almoco_retorno` datetime DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `tipo_evento` enum('entrada','saida','almoco_saida','almoco_retorno') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `ponto`
--

INSERT INTO `ponto` (`id`, `usuario_id`, `entrada`, `saida`, `almoco_saida`, `almoco_retorno`, `latitude`, `longitude`, `tipo_evento`, `created_at`) VALUES
(1, 1, '2024-08-05 13:06:19', '2024-08-05 13:06:26', '2024-08-05 13:08:34', '2024-08-05 13:08:36', -6.10274830, -49.59932670, 'entrada', '2024-08-05 16:06:19'),
(2, 1, '2024-08-05 13:06:28', '2024-08-05 13:06:32', '2024-08-05 13:08:34', '2024-08-05 13:08:36', -6.10274830, -49.59932670, 'entrada', '2024-08-05 16:06:28'),
(3, 1, '2024-08-05 13:08:29', '2024-08-05 13:08:39', '2024-08-05 13:08:34', '2024-08-05 13:08:36', -6.10274830, -49.59932670, 'entrada', '2024-08-05 16:08:29'),
(4, 1, '2024-08-05 13:12:04', '2024-08-05 13:14:39', '2024-08-05 13:12:09', '2024-08-05 13:12:21', -6.10274830, -49.59932670, 'entrada', '2024-08-05 16:12:04'),
(5, 1, '2024-08-05 13:12:06', '2024-08-05 13:14:39', '2024-08-05 13:12:09', '2024-08-05 13:12:21', -6.10274830, -49.59932670, 'entrada', '2024-08-05 16:12:06'),
(6, 1, '2024-08-05 13:14:16', '2024-08-05 13:14:39', '2024-08-05 13:12:13', '2024-08-05 13:12:21', -6.10274830, -49.59932670, 'entrada', '2024-08-05 16:12:13'),
(7, 1, '2024-08-05 13:14:16', '2024-08-05 13:14:39', '2024-08-05 13:14:27', '2024-08-05 13:12:27', -6.10274830, -49.59932670, 'entrada', '2024-08-05 16:12:27'),
(8, 1, '2024-08-05 13:14:24', '2024-08-05 13:14:39', '2024-08-05 13:14:27', '2024-08-05 13:14:34', -6.10274830, -49.59932670, 'entrada', '2024-08-05 16:14:24'),
(9, 1, '2024-08-05 13:17:00', '2024-08-05 13:21:51', '2024-08-05 13:19:54', '2024-08-05 13:20:09', -6.10274830, -49.59932670, 'entrada', '2024-08-05 16:17:00'),
(10, 1, '2024-08-05 13:19:47', '2024-08-05 13:21:51', '2024-08-05 13:19:54', '2024-08-05 13:20:09', -6.10274830, -49.59932670, 'entrada', '2024-08-05 16:19:47'),
(11, 1, '2024-08-05 13:21:53', '2024-08-05 13:22:08', '2024-08-05 13:22:06', '2024-08-05 13:22:11', -6.10274830, -49.59932670, 'entrada', '2024-08-05 16:21:53'),
(12, 2, '2024-08-05 13:54:01', '2024-08-05 13:54:03', '2024-08-05 14:07:23', '2024-08-05 14:07:27', -6.10274830, -49.59932670, 'entrada', '2024-08-05 16:54:01'),
(13, 2, '2024-08-05 13:54:11', '2024-08-05 14:07:29', '2024-08-05 14:07:23', '2024-08-05 14:07:27', -6.10274830, -49.59932670, 'entrada', '2024-08-05 16:54:11'),
(14, 2, '2024-08-05 14:10:37', NULL, NULL, NULL, -6.10274830, -49.59932670, 'entrada', '2024-08-05 17:10:37'),
(15, 1, '2024-08-05 14:14:57', '2024-08-05 14:17:39', '2024-08-05 14:17:35', '2024-08-05 14:13:50', -6.10274830, -49.59932670, 'entrada', '2024-08-05 17:13:50'),
(16, 1, '2024-08-05 14:19:00', '2024-08-05 14:18:00', '2024-08-05 14:17:35', '2024-08-05 14:17:37', -6.10274830, -49.59932670, 'entrada', '2024-08-05 17:17:33');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('usuario','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `senha`, `tipo`) VALUES
(1, 'wilkercaminha20', '$2y$10$9KUsf5C92TDYH8zi1/lh.O2SGlSA7bvjCniOUrJLQveJi3ydUZaOG', 'usuario'),
(2, 'wilkercaminha10', '$2y$10$bmgEYdWFKSVgGuPCQZQNOuZ6QoymH39hvBNqXmLwEz/2WyA6JySDm', 'admin'),
(3, 'wilkercaminha30', '$2y$10$OkGpo7w1apSYXG3dSxyxZuaTz9xnoM3BPwCcKsP1GJwDeDnQx2FAS', 'admin'),
(4, 'wilkercaminha50', '$2y$10$V8L0Py730Uoz2h0uK8C1W.9ybPB4zLUJOE6Holxw/VPyngxmUJN8m', 'admin'),
(5, 'wilkercaminha60', '$2y$10$UhyNoFFKD1jQRNTQx20ByuK9oIYuTHBo2zo/5s00jF.NotpNijTYa', 'usuario'),
(6, 'wilkercaminha80', '$2y$10$fu88HJDA3QpAN9JpVrtjUeao.Gz5ZI3mdEE6ECO21oMwx8CNOf5F6', 'usuario');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `ponto`
--
ALTER TABLE `ponto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `ponto`
--
ALTER TABLE `ponto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `ponto`
--
ALTER TABLE `ponto`
  ADD CONSTRAINT `ponto_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
