-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 20/10/2025 às 01:30
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
-- Banco de dados: `conectared_db`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `detalhes_evento`
--

CREATE TABLE `detalhes_evento` (
  `id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `descricao_detalhada` text DEFAULT NULL,
  `tipo_inscricao` enum('Gratuita','Paga','Convite') DEFAULT 'Gratuita',
  `pagamentos_aceitos` varchar(255) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `ponto_referencia` varchar(255) DEFAULT NULL,
  `link_maps` varchar(255) DEFAULT NULL,
  `imagens_adicionais` text DEFAULT NULL,
  `contato_whatsapp` varchar(50) DEFAULT NULL,
  `contato_email` varchar(100) DEFAULT NULL,
  `redes_sociais` varchar(255) DEFAULT NULL,
  `programacao` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `detalhes_evento`
--

INSERT INTO `detalhes_evento` (`id`, `evento_id`, `descricao_detalhada`, `tipo_inscricao`, `pagamentos_aceitos`, `endereco`, `ponto_referencia`, `link_maps`, `imagens_adicionais`, `contato_whatsapp`, `contato_email`, `redes_sociais`, `programacao`, `criado_em`, `atualizado_em`) VALUES
(5, 60, 'O Redex Geek Fest já tem data marcada e não seria possível sem o apoio e a parceria de marcas incríveis que acreditam nesse sonho. \r\n\r\n Obrigado aos nossos patrocinadores e apoiadores que fazem parte dessa jornada e ajudam a construir uma experiência inesquecível para todos os fãs de cultura geek, nerd e oriental.\r\n\r\nSalve a data e prepare-se para viver esse evento épico!', 'Convite', '', NULL, 'Na faculdade UEPA', 'https://maps.app.goo.gl/1rnZ2aSfAx8LCSw86', '68f5715d9f774_Tips for your First Twin Cities Con.jpg,68f5715d9f9e4_Alice in wonderland.jpg,68f5717ce179a_imagem_2025-10-19_201715120.png,68f5718f7ac8b_imagem_2025-10-19_201734416.png', '94991805789', 'redexgeekfest@gmail.com', '@redexgeekfest', '[{\"data\":\"2025-10-26\",\"hora\":\"09:30\",\"atividade\":\"Abertura do evento\"},{\"data\":\"2025-10-26\",\"hora\":\"15:30\",\"atividade\":\"Competição de Cosplay\"},{\"data\":\"2025-10-26\",\"hora\":\"16:30\",\"atividade\":\"Sorteio de Prêmios\"},{\"data\":\"2025-10-26\",\"hora\":\"18:30\",\"atividade\":\"Encerramento\"}]', '2025-10-19 22:34:58', '2025-10-19 23:19:07');

-- --------------------------------------------------------

--
-- Estrutura para tabela `eventos`
--

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_evento` date NOT NULL,
  `local` varchar(150) DEFAULT NULL,
  `vagas` int(11) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `imagem_capa` varchar(255) DEFAULT NULL,
  `organizador_id` int(11) NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `eventos`
--

INSERT INTO `eventos` (`id`, `nome`, `descricao`, `data_evento`, `local`, `vagas`, `link`, `categoria`, `criado_em`, `imagem_capa`, `organizador_id`, `latitude`, `longitude`, `categoria_id`) VALUES
(49, 'Jorge e Mateus', 'Show da dupla Jorge e Mateus, com estrutura de food trucks.', '2025-11-01', 'Parque de Vaquejada', 2000, NULL, 'Show / Música ao Vivo', '2025-10-19 21:52:34', 'evento_68f55e8a525610.76901114.jpg', 4, -8.0419470, -50.0080500, NULL),
(50, 'Diego e Victor Hugo', 'Apresentação intimista com a dupla sertaneja.', '2025-10-25', 'Izidório Junior', 1225, NULL, 'Show / Música ao Vivo', '2025-10-19 21:56:03', 'evento_68f55e738361e8.51687963.jpg', 4, -8.0444910, -50.0464610, NULL),
(51, 'Olivia Rodrigo', 'Apresentação pop com a cantora Olivia Rodrigo.', '2025-11-08', 'Espaço Garden', 750, NULL, 'Show / Música ao Vivo', '2025-10-19 21:58:46', 'evento_68f55f166c2759.37762323.jpg', 4, -8.0393500, -50.0262890, NULL),
(52, 'AnaVitória', 'Apresentação intimista de mpb com dupla.', '2025-10-31', 'Parque Ambiental', 578, NULL, 'Show / Música ao Vivo', '2025-10-19 21:59:54', 'evento_68f55f5a378f50.44667878.jpg', 4, -8.0328480, -50.0399810, NULL),
(53, 'Oficina de Fotografia com Felipe Morais', 'Técnicas de fotografia profissional e edição de imagens.', '2025-10-24', 'UEPA', 30, NULL, 'Workshop / Oficinas Criativas', '2025-10-19 22:03:20', 'evento_68f56037259233.98556486.jpg', 4, -8.0444490, -50.0133090, NULL),
(54, 'Workshop de Criação de Podcast', 'Aprenda a criar, gravar e divulgar seu podcast do zero.', '2025-10-30', 'Tancredo Neves', 15, NULL, 'Workshop / Oficinas Criativas', '2025-10-19 22:05:17', 'evento_68f5609d482299.21343942.jpg', 4, -8.0352700, -50.0204540, NULL),
(55, 'Inovação e Sustentabilidade', 'Como startups estão revolucionando o mundo sustentável.', '2025-10-23', 'UEPA', 50, NULL, 'Palestra / Talk Inspirador', '2025-10-19 22:06:55', 'evento_68f560ff2edf92.93699479.jpg', 4, -8.0444980, -50.0133760, NULL),
(56, 'Liderança Feminina no Século 21', 'Palestra com executivas sobre empoderamento e liderança.', '2025-10-31', 'UEPA', 50, NULL, 'Palestra / Talk Inspirador', '2025-10-19 22:07:58', 'evento_68f561763ff3a4.48672980.jpg', 4, -8.0437970, -50.0201510, NULL),
(57, 'Maratona Uepa', 'Corrida de 5 km com iluminação especial e música ao vivo.', '2025-11-08', 'UEPA', 125, NULL, 'Esportes / Atividades Físicas', '2025-10-19 22:11:58', 'evento_68f5622e896b05.47583741.png', 4, -8.0445530, -50.0132100, NULL),
(58, 'Torneio de Futsal Universitário', 'Equipes universitárias competem pelo título anual.', '2025-11-01', 'Ginásio Poliesportivo', 75, NULL, 'Esportes / Atividades Físicas', '2025-10-19 22:14:00', 'evento_68f562a83eb825.82083239.jpg', 4, -8.0448740, -50.0170420, NULL),
(59, 'Torneio de Beach Tennis ', 'Competição amadora e profissional de beach tennis.', '2025-11-07', 'Nareia', 26, NULL, 'Esportes / Atividades Físicas', '2025-10-19 22:15:49', 'evento_68f563154a55f1.96815146.jpg', 4, -8.0350580, -50.0219490, NULL),
(60, 'Redex GeekFest', 'Primeiro Evento Geek de Redenção.', '2025-10-26', 'UEPA', 500, NULL, 'Cosplay / Eventos Temáticos', '2025-10-19 22:17:37', 'evento_68f56381cc5660.91856519.png', 4, -8.0446250, -50.0131180, NULL),
(61, 'Cosplay HalloweenNight', 'Competição de cosplay de halloween com categorias individuais e em grupo, premiação para os melhores.', '2025-10-31', 'UEPA', 50, NULL, 'Cosplay / Eventos Temáticos', '2025-10-19 22:20:02', 'evento_68f5648a363af0.31355848.jpg', 4, -8.0451480, -50.0134670, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `inscricoes`
--

CREATE TABLE `inscricoes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `inscrito_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `inscricoes`
--

INSERT INTO `inscricoes` (`id`, `usuario_id`, `evento_id`, `inscrito_em`) VALUES
(24, 1, 52, '2025-10-19 19:30:14'),
(25, 1, 55, '2025-10-19 19:30:16'),
(26, 1, 60, '2025-10-19 19:30:19');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('participante','organizador') NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`, `criado_em`) VALUES
(1, 'Anna Beatriz', 'annabmoutinho22@outlook.com', '$2y$10$D3CuIv0b057y.ltHmuoGJ.tRRUnjB3sQAh4U/q4L/pTLkJx85NnhS', 'participante', '2025-10-09 03:33:42'),
(4, 'lua', 'luaninha@gmail.com', '$2y$10$T.HWfpvlMYf0DdNo40wOUu/n45RJphXZY4tZaExmRXrHpElt4T9s6', 'organizador', '2025-10-09 21:39:40'),
(8, 'Wilker Caminha', 'wilker@gmail.com', '$2y$10$4hpSNG.r92YFp0Gn8N7K5OMmy5ibnOERZeilGBHHJceY.blG6kmT6', 'participante', '2025-10-11 00:39:41');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `detalhes_evento`
--
ALTER TABLE `detalhes_evento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evento_id` (`evento_id`);

--
-- Índices de tabela `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Índices de tabela `inscricoes`
--
ALTER TABLE `inscricoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`,`evento_id`),
  ADD KEY `evento_id` (`evento_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `detalhes_evento`
--
ALTER TABLE `detalhes_evento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT de tabela `inscricoes`
--
ALTER TABLE `inscricoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `detalhes_evento`
--
ALTER TABLE `detalhes_evento`
  ADD CONSTRAINT `detalhes_evento_ibfk_1` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `eventos`
--
ALTER TABLE `eventos`
  ADD CONSTRAINT `eventos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Restrições para tabelas `inscricoes`
--
ALTER TABLE `inscricoes`
  ADD CONSTRAINT `inscricoes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscricoes_ibfk_2` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
