-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 29/10/2025 às 12:42
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
-- Banco de dados: `plataforma_cursos1`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `auditoria`
--

CREATE TABLE `auditoria` (
  `id` int(11) NOT NULL,
  `tabela_afetada` varchar(100) NOT NULL,
  `acao` enum('INSERCAO','ATUALIZACAO','EXCLUSAO') NOT NULL,
  `registro_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `dados_anteriores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_anteriores`)),
  `dados_novos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`dados_novos`)),
  `data_evento` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `auditoria`
--

INSERT INTO `auditoria` (`id`, `tabela_afetada`, `acao`, `registro_id`, `usuario_id`, `dados_anteriores`, `dados_novos`, `data_evento`) VALUES
(1, 'usuarios', 'INSERCAO', 1, NULL, NULL, '{\"nome\": \"Arroz\", \"email\": \"arroz@gmail.com\"}', '2025-10-29 11:21:23'),
(2, 'usuarios', 'ATUALIZACAO', 1, NULL, '{\"nome\": \"Arroz\", \"email\": \"arroz@gmail.com\", \"ativo\": 1}', '{\"nome\": \"Arroz 1\", \"email\": \"arroz1@gmail.com\", \"ativo\": 1}', '2025-10-29 11:21:54'),
(3, 'usuarios', 'EXCLUSAO', 1, NULL, '{\"nome\": \"Arroz 1\", \"email\": \"arroz1@gmail.com\"}', NULL, '2025-10-29 11:22:00'),
(4, 'usuarios', 'INSERCAO', 2, NULL, NULL, '{\"nome\": \"Márcio Wagner Lourenço da Costa\", \"email\": \"marciowagner326@gmail.com\"}', '2025-10-29 11:38:02'),
(5, 'usuarios', 'ATUALIZACAO', 2, NULL, '{\"nome\": \"Márcio Wagner Lourenço da Costa\", \"email\": \"marciowagner326@gmail.com\", \"ativo\": 1}', '{\"nome\": \"Márcio da Costa\", \"email\": \"marciowagner326@gmail.com\", \"ativo\": 1}', '2025-10-29 11:38:18'),
(7, 'usuarios', 'EXCLUSAO', 2, NULL, '{\"nome\": \"Márcio da Costa\", \"email\": \"marciowagner326@gmail.com\"}', NULL, '2025-10-29 11:41:28'),
(8, 'usuarios', 'INSERCAO', 3, NULL, NULL, '{\"nome\": \"Márcio Wagner Lourenço da Costa\", \"email\": \"marciowagner32@gmail.com\"}', '2025-10-29 11:41:54'),
(9, 'usuarios', 'ATUALIZACAO', 3, NULL, '{\"nome\": \"Márcio Wagner Lourenço da Costa\", \"email\": \"marciowagner32@gmail.com\", \"ativo\": 1}', '{\"nome\": \"Márcio Wagner Lourenço da Costa\", \"email\": \"marciowagner326@gmail.com\", \"ativo\": 1}', '2025-10-29 11:41:59');

-- --------------------------------------------------------

--
-- Estrutura para tabela `auditoria_usuarios`
--

CREATE TABLE `auditoria_usuarios` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `acao` varchar(50) NOT NULL,
  `detalhes` text DEFAULT NULL,
  `usuario_nome` varchar(150) DEFAULT NULL,
  `usuario_email` varchar(150) DEFAULT NULL,
  `data_evento` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `auditoria_usuarios`
--

INSERT INTO `auditoria_usuarios` (`id`, `usuario_id`, `acao`, `detalhes`, `usuario_nome`, `usuario_email`, `data_evento`) VALUES
(1, 2, 'exclusão', 'Usuário removido via AJAX', 'Márcio da Costa', 'marciowagner326@gmail.com', '2025-10-29 11:41:28'),
(2, 3, 'inserção', 'Usuário criado via AJAX', NULL, NULL, '2025-10-29 11:41:54'),
(3, 3, 'edição', 'Usuário atualizado via AJAX', NULL, NULL, '2025-10-29 11:41:59');

-- --------------------------------------------------------

--
-- Estrutura para tabela `aulas`
--

CREATE TABLE `aulas` (
  `id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `tipo_arquivo` enum('video','pdf','texto') DEFAULT 'video',
  `caminho_arquivo` varchar(255) DEFAULT NULL,
  `conteudo` text DEFAULT NULL,
  `posicao` int(11) DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `avaliacoes`
--

CREATE TABLE `avaliacoes` (
  `id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nota` tinyint(4) DEFAULT NULL CHECK (`nota` between 1 and 5),
  `comentario` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cursos`
--

CREATE TABLE `cursos` (
  `id` int(11) NOT NULL,
  `instrutor_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao_curta` varchar(255) DEFAULT NULL,
  `descricao_longa` text DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `preco` decimal(10,2) DEFAULT 0.00,
  `imagem_capa` varchar(255) DEFAULT NULL,
  `status` enum('rascunho','publicado') DEFAULT 'rascunho',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `matriculas`
--

CREATE TABLE `matriculas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `curso_id` int(11) NOT NULL,
  `data_matricula` timestamp NOT NULL DEFAULT current_timestamp(),
  `progresso` float DEFAULT 0,
  `status` enum('ativa','concluida','cancelada') DEFAULT 'ativa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('aluno','instrutor','admin') DEFAULT 'aluno',
  `ativo` tinyint(1) DEFAULT 1,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`, `ativo`, `criado_em`, `atualizado_em`) VALUES
(3, 'Márcio Wagner Lourenço da Costa', 'marciowagner326@gmail.com', '$2y$10$ODSDEMrvDAR/6jMCY0T74O7pYT07NNu4iimgEvJPCi77tlcFLOWkO', 'admin', 1, '2025-10-29 11:41:54', '2025-10-29 11:41:59');

--
-- Acionadores `usuarios`
--
DELIMITER $$
CREATE TRIGGER `trg_audit_usuarios_delete` AFTER DELETE ON `usuarios` FOR EACH ROW INSERT INTO auditoria (tabela_afetada, acao, registro_id, usuario_id, dados_anteriores)
VALUES ('usuarios', 'EXCLUSAO', OLD.id, NULL, JSON_OBJECT('nome', OLD.nome, 'email', OLD.email))
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_audit_usuarios_insert` AFTER INSERT ON `usuarios` FOR EACH ROW INSERT INTO auditoria (tabela_afetada, acao, registro_id, usuario_id, dados_novos)
VALUES ('usuarios', 'INSERCAO', NEW.id, NULL, JSON_OBJECT('nome', NEW.nome, 'email', NEW.email))
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_audit_usuarios_update` AFTER UPDATE ON `usuarios` FOR EACH ROW INSERT INTO auditoria (tabela_afetada, acao, registro_id, usuario_id, dados_anteriores, dados_novos)
VALUES ('usuarios', 'ATUALIZACAO', NEW.id, NULL,
        JSON_OBJECT('nome', OLD.nome, 'email', OLD.email, 'ativo', OLD.ativo),
        JSON_OBJECT('nome', NEW.nome, 'email', NEW.email, 'ativo', NEW.ativo))
$$
DELIMITER ;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `auditoria_usuarios`
--
ALTER TABLE `auditoria_usuarios`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `aulas`
--
ALTER TABLE `aulas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `curso_id` (`curso_id`);

--
-- Índices de tabela `avaliacoes`
--
ALTER TABLE `avaliacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `curso_id` (`curso_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `instrutor_id` (`instrutor_id`);

--
-- Índices de tabela `matriculas`
--
ALTER TABLE `matriculas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`,`curso_id`),
  ADD KEY `curso_id` (`curso_id`);

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
-- AUTO_INCREMENT de tabela `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `auditoria_usuarios`
--
ALTER TABLE `auditoria_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `aulas`
--
ALTER TABLE `aulas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `avaliacoes`
--
ALTER TABLE `avaliacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `matriculas`
--
ALTER TABLE `matriculas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `aulas`
--
ALTER TABLE `aulas`
  ADD CONSTRAINT `aulas_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `avaliacoes`
--
ALTER TABLE `avaliacoes`
  ADD CONSTRAINT `avaliacoes_ibfk_1` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `avaliacoes_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `cursos`
--
ALTER TABLE `cursos`
  ADD CONSTRAINT `cursos_ibfk_1` FOREIGN KEY (`instrutor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `matriculas`
--
ALTER TABLE `matriculas`
  ADD CONSTRAINT `matriculas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `matriculas_ibfk_2` FOREIGN KEY (`curso_id`) REFERENCES `cursos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
