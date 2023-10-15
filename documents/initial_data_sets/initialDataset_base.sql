-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Version du serveur :  5.7.28-log
-- Version de PHP : 7.0.33-0+deb9u7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Déchargement des données de la table `category`
--

INSERT INTO `category` (`id`, `name`) VALUES
(1, 'Rotations'),
(2, 'Flips'),
(3, 'Grabs'),
(4, 'Slides');

--
-- Déchargement des données de la table `picture`
--

INSERT INTO `picture` (`id`, `trick_id`, `filename`) VALUES
(1, 1, '10801-5ea1cbd721981.jpg'),
(2, 1, '10802-5ea1cbd721b29.jpg'),
(3, 1, '10803-5ea1cbd721d0d.jpg'),
(4, 2, '1801-5ea1cbd722062.jpg'),
(5, 2, '1802-5ea1cbd72221b.jpg'),
(6, 2, '1803-5ea1cbd722399.jpg'),
(7, 3, '3601-5ea1cbd72261a.jpg'),
(8, 3, '3602-5ea1cbd722830.jpg'),
(9, 3, '3603-5ea1cbd7229a1.jpg'),
(10, 4, 'backflip1-5ea1cbd722b9d.jpg'),
(11, 4, 'backflip2-5ea1cbd722df4.jpg'),
(12, 4, 'backflip3-5ea1cbd722fa0.jpg'),
(13, 5, 'frontflip1-5ea1cbd723206.jpg'),
(14, 5, 'frontflip2-5ea1cbd7233cf.jpg'),
(15, 5, 'frontflip3-5ea1cbd72357a.jpg'),
(16, 6, 'indy1-5ea1cbd7238c1.jpg'),
(17, 6, 'indy2-5ea1cbd723b4c.jpg'),
(18, 6, 'indy3-5ea1cbd72402b.jpg'),
(19, 6, 'indy4-5ea1cbd7241bf.jpg'),
(20, 7, 'mute1-5ea1cbd7243ef.jpg'),
(21, 7, 'mute2-5ea1cbd724580.jpg'),
(22, 7, 'mute3-5ea1cbd7246fc.jpg'),
(23, 7, 'mute4-5ea1cbd72488b.jpg'),
(24, 7, 'mute5-5ea1cbd7249d8.jpg'),
(25, 8, 'sad1-5ea1cbd72506c.jpg'),
(26, 8, 'sad2-5ea1cbd72517e.jpg'),
(27, 8, 'sad3-5ea1cbd7252d4.jpg'),
(28, 9, 'noseslide1-5ea1cbd7253e8.jpg'),
(29, 9, 'noseslide2-5ea1cbd725538.jpg'),
(30, 9, 'noseslide3-5ea1cbd725607.jpg'),
(31, 10, 'tailslide1-5ea1cbd725844.jpg'),
(32, 10, 'tailslide2-5ea1cbd725a6b.jpg'),
(33, 10, 'tailslide3-5ea1cbd725bfb.jpg');

--
-- Déchargement des données de la table `trick`
--

INSERT INTO `trick` (`id`, `name`, `description`, `creation_moment`, `revision_moment`, `user_id`, `category_id`, `main_picture_filename`, `slug`) VALUES
(1, '1080', 'Rotation horizontale de trois tours complets.', '2020-03-24 19:09:43', NULL, 1, 1, NULL, '1080'),
(2, '180', 'Rotation horizontale d\'un demi tour.', '2020-03-24 19:09:43', NULL, 1, 1, NULL, '180'),
(3, '360', 'Rotation horizontale d\'un tours complet.', '2020-03-24 19:09:43', NULL, 1, 1, NULL, '360'),
(4, 'back flip', 'Rotation verticale en arrière.', '2020-03-24 19:09:43', NULL, 1, 2, NULL, 'back-flip'),
(5, 'front flip', 'Rotation verticale en avant.', '2020-03-24 19:09:43', NULL, 1, 2, NULL, 'front-flip'),
(6, 'indy', 'Saisie de la carre frontside de la planche, entre les deux pieds, avec la main arrière.', '2020-03-24 19:09:43', NULL, 1, 3, NULL, 'indy'),
(7, 'mute', 'Saisie de la carre frontside de la planche entre les deux pieds avec la main avant.', '2020-03-24 19:09:43', NULL, 1, 3, NULL, 'mute'),
(8, 'sad', 'Saisie de la carre backside de la planche, entre les deux pieds, avec la main avant.', '2020-03-24 19:09:43', NULL, 1, 3, NULL, 'sad'),
(9, 'nose slide', 'Glissade avec planche perpendiculaire à la barre de slide avec la barre du coté avant de la planche.', '2020-03-24 19:09:43', NULL, 1, 4, NULL, 'nose-slide'),
(10, 'tail slide', 'Glissade avec planche perpendiculaire à la barre de slide avec la barre du coté arrière de la planche.', '2020-03-24 19:09:43', NULL, 1, 4, NULL, 'tail-slide');

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `password`, `confirmed`, `token`, `creation_moment`, `forgot_password_moment`, `picture_filename`, `roles`) VALUES
(1, 'generator', 'generator@snowtricks.fr', '$2y$13$2OrIxro.vzEXmFlEJIWAJOavwn0UMOsMfSSImuugOwcyDhRxJh0Km', 1, NULL, '2020-03-24 19:09:43', NULL, '869250x250-5ea1cbd7216a8.jpg', '[\"ROLE_ADMIN\"]'),

--
-- Déchargement des données de la table `video`
--

INSERT INTO `video` (`id`, `trick_id`, `embed_link`) VALUES
(1, 1, 'https://www.youtube.com/embed/j4NfAsszIOk'),
(2, 1, 'https://www.youtube.com/embed/camHB0Rj4gA'),
(3, 2, 'https://www.youtube.com/embed/ATMiAVTLsuc'),
(4, 2, 'https://www.youtube.com/embed/JMS2PGAFMcE'),
(5, 2, 'https://www.youtube.com/embed/GnYAlEt-s00');
(6, 3, 'https://www.youtube.com/embed/hUddT6FGCws'),
(7, 3, 'https://www.youtube.com/embed/GS9MMT_bNn8'),
(8, 3, 'https://www.youtube.com/embed/_rS2i4-yb6E'),
(9, 4, 'https://www.youtube.com/embed/AMsWP9WJS_0'),
(10, 4, 'https://www.youtube.com/embed/SlhGVnFPTDE'),
(11, 4, 'https://www.youtube.com/embed/vIqaebj-GNw'),
(12, 5, 'https://www.youtube.com/embed/xhvqu2XBvI0'),
(13, 5, 'https://www.youtube.com/embed/eGJ8keB1-JM'),
(14, 5, 'https://www.youtube.com/embed/aTTkQ45DUfk'),
(15, 6, 'https://www.youtube.com/embed/iKkhKekZNQ8'),
(16, 6, 'https://www.youtube.com/embed/6yA3XqjTh_w'),
(17, 7, 'https://www.youtube.com/embed/4sha5smEUHA'),
(18, 7, 'https://www.youtube.com/embed/KXDQv7f8JNs'),
(19, 7, 'https://www.youtube.com/embed/8r_yZfBWCeQ'),
(20, 8, 'https://www.youtube.com/embed/KEdFwJ4SWq4'),
(21, 9, 'https://www.youtube.com/embed/7AB0FZWyrGQ'),
(22, 9, 'https://www.youtube.com/embed/Iw77dvnNSKk'),
(23, 9, 'https://www.youtube.com/embed/oAK9mK7wWvw'),
(24, 10, 'https://www.youtube.com/embed/h_jU7vjmLjU'),
(25, 10, 'https://www.youtube.com/embed/HRNXjMBakwM'),
(26, 10, 'https://www.youtube.com/embed/inAxMRSlGS8'),
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
