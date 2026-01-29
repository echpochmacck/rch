-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Хост: MySQL-8.0
-- Время создания: Янв 29 2026 г., 07:37
-- Версия сервера: 8.0.41
-- Версия PHP: 8.2.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `rch`
--

-- --------------------------------------------------------

--
-- Структура таблицы `course`
--

CREATE TABLE `course` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `is_public` tinyint(1) NOT NULL DEFAULT '1',
  `owner_id` int UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `structure` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `course`
--

INSERT INTO `course` (`id`, `title`, `description`, `is_public`, `owner_id`, `created_at`, `updated_at`, `structure`) VALUES
(1, 'Введение в программирование', 'Базовые концепции: алгоритмы, переменные, условия, циклы', 1, 1, '2026-01-27 10:00:57', '2026-01-27 10:00:57', 'null'),
(2, 'Backend-разработка на PHP', 'REST API, архитектура приложений, безопасность', 1, 1, '2026-01-27 10:00:57', '2026-01-27 10:00:57', 'null'),
(3, 'SPA + WebSocket', 'Single Page Application и real-time взаимодействие', 0, 1, '2026-01-27 10:00:57', '2026-01-27 10:00:57', 'null'),
(4, 'Backend-разработка на PHP', 'REST API, архитектура приложений, безопасность', 1, 1, '2026-01-28 13:49:48', '2026-01-28 13:49:48', 'null'),
(5, 'Backend-разработка на PHP', 'REST API, архитектура приложений, безопасность', 1, 1, '2026-01-28 13:49:50', '2026-01-28 13:49:50', 'null'),
(6, 'WebSocket курс', 'Создан через Ratchet', 1, 1, '2026-01-28 16:55:41', '2026-01-28 16:55:41', 'null'),
(7, 'WebSocket курс', 'Создан через Ratchet', 1, 1, '2026-01-28 16:56:28', '2026-01-28 16:56:28', 'null'),
(8, 'WebSocket курс', 'Создан через Ratchet', 1, 1, '2026-01-28 17:06:52', '2026-01-28 17:06:52', 'null'),
(9, 'WebSocket курс', 'Создан через Ratchet', 1, 1, '2026-01-28 22:13:12', '2026-01-28 22:13:12', 'null'),
(10, 'test 123', 'Создан через Ratchet', 1, 1, '2026-01-28 22:13:24', '2026-01-28 22:13:24', 'null');

-- --------------------------------------------------------

--
-- Структура таблицы `course_access`
--

CREATE TABLE `course_access` (
  `id` int UNSIGNED NOT NULL,
  `course_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `access_type` enum('viewer','editor') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'viewer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `course_access`
--

INSERT INTO `course_access` (`id`, `course_id`, `user_id`, `access_type`) VALUES
(1, 2, 1, 'viewer'),
(2, 3, 1, 'viewer');

-- --------------------------------------------------------

--
-- Структура таблицы `course_element`
--

CREATE TABLE `course_element` (
  `id` int UNSIGNED NOT NULL,
  `course_id` int UNSIGNED NOT NULL,
  `content_url` text COLLATE utf8mb4_general_ci,
  `file_url` text COLLATE utf8mb4_general_ci,
  `structure` json NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `course_element`
--

INSERT INTO `course_element` (`id`, `course_id`, `content_url`, `file_url`, `structure`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'mdkdayduajJr9PdA-i7BQW8VC1KZB0pk.pdf', '\"{\\n  \\\"position\\\": { \\\"x\\\": 120, \\\"y\\\": 200 },\\n  \\\"size\\\": { \\\"width\\\": 260, \\\"height\\\": 140 },\\n  \\\"style\\\": {\\n    \\\"backgroundColor\\\": \\\"#F4F6FA\\\",\\n    \\\"borderColor\\\": \\\"#3B82F6\\\"\\n  }\\n}\"', '2026-01-28 22:07:52', '2026-01-28 22:07:52'),
(2, 1, NULL, 'WUyUp67JSZIpDcgyIZJbVqLO4jzmCsKR.pdf', '\"{\\\"position\\\":{\\\"x\\\":120,\\\"y\\\":200},\\\"size\\\":{\\\"width\\\":260,\\\"height\\\":140},\\\"style\\\":{\\\"backgroundColor\\\":\\\"#F4F6FA\\\",\\\"borderColor\\\":\\\"#3B82F6\\\"}}\"', '2026-01-28 22:27:17', '2026-01-28 22:27:17');

-- --------------------------------------------------------

--
-- Структура таблицы `role`
--

CREATE TABLE `role` (
  `id` int UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `role`
--

INSERT INTO `role` (`id`, `title`) VALUES
(1, 'user'),
(2, 'admin');

-- --------------------------------------------------------

--
-- Структура таблицы `user`
--

CREATE TABLE `user` (
  `id` int UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role_id` int UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `user`
--

INSERT INTO `user` (`id`, `email`, `password`, `role_id`, `created_at`, `token`) VALUES
(1, 'student@example.com', '$2y$13$GrnkZ1Z.v7Xq21aKVmKr3Og4XktFbtXTr7j3zgZh1QxFWVSOmxmrm', 1, '2026-01-27 09:48:30', 'kcxgjo4F4p-kzU5q-sDkEPwOiUBbYNoM');

-- --------------------------------------------------------

--
-- Структура таблицы `user_element_progress`
--

CREATE TABLE `user_element_progress` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `element_id` int UNSIGNED NOT NULL,
  `is_viewed` tinyint(1) NOT NULL DEFAULT '0',
  `viewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_course_owner` (`owner_id`);

--
-- Индексы таблицы `course_access`
--
ALTER TABLE `course_access`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_course_user` (`course_id`,`user_id`),
  ADD KEY `fk_access_user` (`user_id`);

--
-- Индексы таблицы `course_element`
--
ALTER TABLE `course_element`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_element_course` (`course_id`);

--
-- Индексы таблицы `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role` (`role_id`);

--
-- Индексы таблицы `user_element_progress`
--
ALTER TABLE `user_element_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_element` (`user_id`,`element_id`),
  ADD KEY `idx_progress_user` (`user_id`),
  ADD KEY `fk_progress_element` (`element_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `course`
--
ALTER TABLE `course`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `course_access`
--
ALTER TABLE `course_access`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `course_element`
--
ALTER TABLE `course_element`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `role`
--
ALTER TABLE `role`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `user`
--
ALTER TABLE `user`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `user_element_progress`
--
ALTER TABLE `user_element_progress`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `course`
--
ALTER TABLE `course`
  ADD CONSTRAINT `fk_course_owner` FOREIGN KEY (`owner_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `course_access`
--
ALTER TABLE `course_access`
  ADD CONSTRAINT `fk_access_course` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_access_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `course_element`
--
ALTER TABLE `course_element`
  ADD CONSTRAINT `fk_element_course` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_element_progress`
--
ALTER TABLE `user_element_progress`
  ADD CONSTRAINT `fk_progress_element` FOREIGN KEY (`element_id`) REFERENCES `course_element` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_progress_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
