-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Apr 16, 2025 alle 19:28
-- Versione del server: 10.4.27-MariaDB
-- Versione PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `reflexio`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `goals`
--

CREATE TABLE `goals` (
  `goal_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `deadline` date DEFAULT NULL,
  `progress` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `priority` varchar(10) DEFAULT 'medium',
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `goals`
--

INSERT INTO `goals` (`goal_id`, `title`, `deadline`, `progress`, `description`, `priority`, `user_id`) VALUES
(5, 'Loose weight', '2025-06-01', 70, 'Loose weight before summer', 'high', 5),
(31, 'Study ITSM', '2004-04-20', 10, 'Study for the exam of ITSM', 'low', 2),
(34, 'Bake', '2026-05-05', 40, 'Learn to bake an apple pie', 'low', 6),
(35, 'coding', '2025-06-15', 30, 'Code the project', 'high', 2),
(39, 'Learn PHP', '2025-05-02', 20, 'I want to learn php', 'high', 8),
(40, 'coding', '2026-01-01', 10, 'Code stands for', 'high', 2),
(41, 'coding', '2025-05-05', 10, 'code', 'low', 2);

-- --------------------------------------------------------

--
-- Struttura della tabella `habits`
--

CREATE TABLE `habits` (
  `habit_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `times_monthly` int(11) NOT NULL,
  `completed_days` text NOT NULL DEFAULT '{}',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `habits`
--

INSERT INTO `habits` (`habit_id`, `title`, `times_monthly`, `completed_days`, `created_at`, `user_id`) VALUES
(36, 'Go on walk', 10, '{\"2025-03\":[5,6,7,4,3,15,16,17,18,19,20,21,22,23,24,25,26,29,11,12,10],\"2025-04\":[5,6,7]}', '2025-03-22 06:31:28', 6),
(37, 'Call Emi', 3, '{\"2025-03\":[8,9,5],\"2025-04\":[9,10,5]}', '2025-03-22 06:31:35', 2),
(39, 'Clean the room', 30, '{\"2025-03\":{\"0\":4,\"1\":5,\"2\":6,\"4\":8,\"5\":9,\"6\":10,\"7\":3,\"8\":2,\"9\":1,\"10\":7,\"11\":12,\"12\":11,\"13\":13,\"14\":14,\"15\":16,\"16\":15,\"17\":22,\"18\":23,\"19\":24,\"20\":25,\"21\":26,\"22\":27,\"23\":28,\"24\":29,\"25\":30,\"26\":31,\"27\":21,\"28\":20,\"29\":19,\"30\":18,\"31\":17},\"2025-04\":[5,3,1,2,4,6,7,8,9,10]}', '2025-03-22 07:17:50', 5),
(57, 'Treadmill', 5, '{\"2025-04\":[7,6,5,3,4,10,9,8]}', '2025-04-14 12:11:53', 6),
(58, 'Treadmill', 6, '{\"2025-04\":[8,9,10,11,12]}', '2025-04-15 09:27:52', 2),
(64, 'Study', 5, '{\"2025-04\":[5,6,7,8]}', '2025-04-16 11:29:19', 6),
(66, 'Treadmill', 4, '{\"2025-04\":[16,15,14,13]}', '2025-04-16 14:23:04', 8),
(67, 'Treadmill', 4, '{\"2025-04\":[9,10,11]}', '2025-04-16 17:16:53', 2),
(68, 'study', 3, '{\"2025-04\":[9,10]}', '2025-04-16 17:24:47', 2);

-- --------------------------------------------------------

--
-- Struttura della tabella `journal_entries`
--

CREATE TABLE `journal_entries` (
  `journal_id` int(11) NOT NULL,
  `entry_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `journal_entries`
--

INSERT INTO `journal_entries` (`journal_id`, `entry_date`, `title`, `content`, `created_at`, `updated_at`, `user_id`) VALUES
(6, '2025-04-01', 'Do the goal page', 'Today my goal was to create the goals page. And finally I was able to finish it', '2025-04-04 10:32:27', '2025-04-14 11:35:24', 2),
(7, '2025-04-05', 'pizza Day', 'me my husband my sister cooked pizza', '2025-04-04 14:31:25', '2025-04-16 16:54:03', 2),
(15, '2025-04-03', 'Meem se mohabbat', 'Beqarar Yeh Dil Tera\nPaagal Hai Samjhe Na\nBeruhki To Bahana Hai\nYeh Baat Kahi Uljhe Na\n\nIshq Mein Raahi Manzil Ko\nNa Pata Hai Na Khota Hai\nMann Chahton Ka Anjaam Toh\nBas Yahi Hota Hain\n\nMuskura Ke Na Dekhiye\nAisa Na Ho Dil Sambhle Na\nBeruhki Toh Bahana Hai\nBaat Kahin Uljhe Na\n\nBekarar Yeh Dil Tera\nPaagal Hai Samjhe Na\n\nKyun Mohabbat Par\nYaqeen Nahi Tumko\nKya Lagti Nahi\nHaseen Main Tumko\n\nParvaah Zamane Ki\nKyun Kar Rahe Ho\nKya Dikhti Hai\nMujhme Kami Tumko\n\nBekarar Yeh Dil Tera\nHai Paagal Hai Samjhe Na\n\nTu Jidhar Jaye\nDekhe Nazar Tujhko\nRoz Chhup Chhup Kar\nMain Dhoondta Tujhko\n\nKhud Se Ijazat Kyun\nMain Chahta Hoon\nJaane Khuda Se Main\nKya Mangta Hoon\n\nIshq Mein Aashiqon Ka\nBas Yahi Kaam Hota Hai\nMuskurahat Par Yeh\nKissa Tamaam Hota Hai\n\nMuskura Ke Na Dekhiye\nAisa Na Ho Dil Sambhale Na\nBerukhi Toh Bahana Hai\nBaat Kahin Uljhe Na\n\nBekarar Yeh Dil Tera\nPaagal Hai Samjhe Na', '2025-04-14 11:34:00', '2025-04-16 12:06:20', 2),
(16, '2025-04-08', 'Iqtidar', 'Ae Ishq Awalda Daku Maahi\nDo Nain Milake Lut Lenda\nAe Ishq Na Tera Mera Mahi\nAe Baldi Agg Wich Sut Deda\nAe Baldi Agg Vich Sut Denda\n\nDil Mein Hai Kya Aankhon Mein Hai Kya\nTum Ho Wahi Ya Waham Hai Koi\nMera Dil Bata\n\nIs Aag Mein Kyun Jalate Ho Tum\nDushman Mera Lagta Jese Koi Tabeeb Sa\n\nMayal Hone Laga Hai Ye Man\nKhone Laga Hai Ye Dil\nRito’n Riwajo’n Ko Tod Do Ya\nAa Ke Kabhi Yaar Mil\n\nMera Hi Ye Dil Ab Na Mera Raha\n\nIshq Rulaave Ishq Hasaave\nIshq Piyaave Dar-Dar Kamla\nIshq Nachaave Ishq Gavaave\nIshq Banaave Jag Vich Chhala\n\nIshq Rulaave Ishq Hasaave\nIshq Piyaave Dar-Dar Kamla\nIshq Nachaave Ishq Gavaave\nIshq Banaave Jag Vich Chhala\n\nVaar Dilon Pe Karte Nahi\nZakhm Judaai Wale Bharte Nahi\nKhoon Jigar Maaf Kaise Karoon\nMain Bhool Jaoon Yeh Bhi Mumkin Nahi\n\nEk Dil Jo Dard Ka Maara Hai\nJo Paas Tha Woh Sab Hara Hai\nIs Baat Ko Koi Jaane Na\nEk Dushman Jaan Se Pyaara Hai\n\nKhwabon Ka Jahan Barbaad Hua\n\nIshq Rulaave Ishq Hasaave\nIshq Piyaave Dar-Dar Kamla\nIshq Nachaave Ishq Gavaave\nIshq Banaave Jag Vich Chhala\n\nIshq Rulaave Ishq Hasaave\nIshq Piyaave Dar-Dar Kamla\nIshq Nachaave Ishq Gavaave\nIshq Banaave Jag Vich Chhala', '2025-04-14 11:34:43', '2025-04-14 11:34:43', 5),
(17, '2025-04-14', 'bake a cake', 'Today I baked a cake', '2025-04-14 11:42:06', '2025-04-14 11:42:06', 6),
(18, '2025-04-15', 'Try', 'Today I recorded the screen', '2025-04-15 09:17:41', '2025-04-15 09:23:35', 6),
(23, '2025-04-16', 'input', 'Today I\'m showing my website dasd', '2025-04-16 14:19:07', '2025-04-16 14:19:13', 8),
(24, '2025-04-16', 'Try', 'Today I went....', '2025-04-16 17:16:22', '2025-04-16 17:16:22', 2),
(25, '2025-04-17', 'Try', '....', '2025-04-16 17:24:12', '2025-04-16 17:24:12', 2);

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `surname` varchar(30) NOT NULL,
  `name` varchar(30) NOT NULL,
  `birthdate` date NOT NULL,
  `email` varchar(30) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`user_id`, `surname`, `name`, `birthdate`, `email`, `username`, `password`) VALUES
(2, 'park', 'ruby', '2004-04-04', 'rubs@gmail.com', 'Rub', '$2y$10$jcEQ7w.X9Ab9Pau8Vj/2QuV4O4u1UsDjGqNafS0q3sYk47QZ4/soS'),
(5, 'Kelly', 'Luke', '1996-05-01', 'luk3@yahoo.com', 'luk3', '$2y$10$A2vcd.O.hTbKomxc1n6PLectm0X3JIscr2H0lcMrPpvQW8HfuPdjC'),
(6, 'Rossi', 'Stefano', '1990-05-01', 'Stef@gmail.com', 'stef', '$2y$10$jdeiYhhoy0t9IcwLlpL7.OopDPpfYIlg8c/XNIl6ElB7QJvyAtQHm'),
(8, 'maybe', 'someone', '2002-05-04', 'loc@gmail.com', 'maybe', '$2y$10$T2P27UDCU7Ao5C4YUOo1geFN2C.1SbT0qTzzusNwBLRijNrmXeH9y');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `goals`
--
ALTER TABLE `goals`
  ADD PRIMARY KEY (`goal_id`),
  ADD KEY `user_goals` (`user_id`);

--
-- Indici per le tabelle `habits`
--
ALTER TABLE `habits`
  ADD PRIMARY KEY (`habit_id`),
  ADD KEY `user_habit` (`user_id`);

--
-- Indici per le tabelle `journal_entries`
--
ALTER TABLE `journal_entries`
  ADD PRIMARY KEY (`journal_id`),
  ADD KEY `user_journal` (`user_id`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `goals`
--
ALTER TABLE `goals`
  MODIFY `goal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT per la tabella `habits`
--
ALTER TABLE `habits`
  MODIFY `habit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT per la tabella `journal_entries`
--
ALTER TABLE `journal_entries`
  MODIFY `journal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `goals`
--
ALTER TABLE `goals`
  ADD CONSTRAINT `user_goals` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `habits`
--
ALTER TABLE `habits`
  ADD CONSTRAINT `user_habit` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `journal_entries`
--
ALTER TABLE `journal_entries`
  ADD CONSTRAINT `user_journal` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
