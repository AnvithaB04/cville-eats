-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 14, 2026 at 04:49 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `CvilleEats`
--

-- --------------------------------------------------------

--
-- Table structure for table `Cuisine`
--

CREATE TABLE `Cuisine` (
  `Cuisine_ID` int(11) NOT NULL,
  `Cuisine_Name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Cuisine`
--

INSERT INTO `Cuisine` (`Cuisine_ID`, `Cuisine_Name`) VALUES
(1, 'American'),
(2, 'Asian'),
(3, 'Bagels'),
(4, 'Bakery'),
(5, 'Chinese'),
(6, 'Coffee'),
(7, 'Indian'),
(8, 'Italian'),
(9, 'Korean'),
(10, 'Latin American'),
(11, 'Mediterranean'),
(12, 'Mexican'),
(13, 'Pizza'),
(14, 'Salvadoran'),
(15, 'Thai'),
(16, 'Vegetarian');

-- --------------------------------------------------------

--
-- Table structure for table `Favorite`
--

CREATE TABLE `Favorite` (
  `User_ID` int(11) NOT NULL,
  `Restaurant_ID` int(11) NOT NULL,
  `Date_Saved` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Favorite`
--

INSERT INTO `Favorite` (`User_ID`, `Restaurant_ID`, `Date_Saved`) VALUES
(1, 14, '2026-04-13 21:59:24'),
(1, 19, '2026-04-13 21:59:53'),
(1, 21, '2026-04-13 21:59:58'),
(1, 22, '2026-04-13 21:58:19'),
(2, 1, '2026-03-17 22:57:28'),
(2, 15, '2026-04-13 22:31:54'),
(3, 8, '2026-03-17 22:57:28'),
(4, 4, '2026-03-17 22:57:28');

-- --------------------------------------------------------

--
-- Table structure for table `Has_Cuisine`
--

CREATE TABLE `Has_Cuisine` (
  `Restaurant_ID` int(11) NOT NULL,
  `Cuisine_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Has_Cuisine`
--

INSERT INTO `Has_Cuisine` (`Restaurant_ID`, `Cuisine_ID`) VALUES
(1, 3),
(2, 1),
(3, 13),
(3, 16),
(4, 11),
(4, 16),
(6, 2),
(6, 16),
(7, 7),
(7, 16),
(8, 6),
(9, 2),
(10, 5),
(10, 9),
(11, 2),
(12, 15),
(13, 15),
(14, 4),
(15, 4),
(16, 4),
(17, 4),
(18, 4),
(19, 10),
(20, 12),
(21, 12),
(22, 12),
(22, 14),
(23, 12),
(24, 12);

-- --------------------------------------------------------

--
-- Table structure for table `Location`
--

CREATE TABLE `Location` (
  `Zip_Code` char(5) NOT NULL,
  `City` varchar(50) NOT NULL,
  `State` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Location`
--

INSERT INTO `Location` (`Zip_Code`, `City`, `State`) VALUES
('22901', 'Charlottesville', 'Virginia'),
('22902', 'Charlottesville', 'Virginia'),
('22903', 'Charlottesville', 'Virginia'),
('22911', 'Charlottesville', 'Virginia'),
('22932', 'Crozet', 'Virginia');

-- --------------------------------------------------------

--
-- Table structure for table `Opening_Hours`
--

CREATE TABLE `Opening_Hours` (
  `Restaurant_ID` int(11) NOT NULL,
  `Day_Of_Week` varchar(10) NOT NULL,
  `Open_Time` time DEFAULT NULL,
  `Close_Time` time DEFAULT NULL
) ;

--
-- Dumping data for table `Opening_Hours`
--

INSERT INTO `Opening_Hours` (`Restaurant_ID`, `Day_Of_Week`, `Open_Time`, `Close_Time`) VALUES
(1, 'Monday', '07:00:00', '15:00:00'),
(1, 'Tuesday', '07:00:00', '15:00:00'),
(2, 'Monday', '11:00:00', '22:30:00'),
(2, 'Tuesday', '11:00:00', '22:00:00'),
(3, 'Monday', '11:00:00', '22:00:00'),
(4, 'Monday', '10:30:00', '21:00:00'),
(5, 'Monday', '11:00:00', '21:30:00'),
(6, 'Monday', '11:00:00', '21:00:00'),
(7, 'Monday', '12:00:00', '21:30:00'),
(8, 'Monday', '08:00:00', '18:00:00'),
(9, 'Monday', '11:00:00', '20:00:00'),
(10, 'Monday', '11:00:00', '21:00:00'),
(11, 'Monday', '17:00:00', '22:00:00'),
(12, 'Monday', '11:00:00', '21:00:00'),
(13, 'Monday', '11:00:00', '21:00:00'),
(14, 'Monday', '07:00:00', '17:00:00'),
(15, 'Monday', '07:00:00', '17:00:00'),
(16, 'Monday', '07:00:00', '17:00:00'),
(17, 'Monday', '08:00:00', '16:00:00'),
(18, 'Monday', '08:00:00', '17:00:00'),
(19, 'Monday', '11:00:00', '21:00:00'),
(20, 'Monday', '11:00:00', '21:00:00'),
(21, 'Monday', '11:00:00', '22:00:00'),
(22, 'Monday', '11:00:00', '21:00:00'),
(23, 'Monday', '11:00:00', '20:00:00'),
(24, 'Monday', '08:00:00', '20:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `Restaurant`
--

CREATE TABLE `Restaurant` (
  `Restaurant_ID` int(11) NOT NULL,
  `Restaurant_Name` varchar(100) NOT NULL,
  `Price_Level` varchar(5) NOT NULL,
  `Street` varchar(100) NOT NULL,
  `Zip_Code` char(5) NOT NULL,
  `Vegetarian_Options` tinyint(1) DEFAULT 0,
  `Vegan_Options` tinyint(1) DEFAULT 0,
  `GlutenFree_Options` tinyint(1) DEFAULT 0
) ;

--
-- Dumping data for table `Restaurant`
--

INSERT INTO `Restaurant` (`Restaurant_ID`, `Restaurant_Name`, `Price_Level`, `Street`, `Zip_Code`, `Vegetarian_Options`, `Vegan_Options`, `GlutenFree_Options`) VALUES
(1, 'Bodo\'s Bagels', '$', '1418 University Ave', '22903', 1, 0, 0),
(2, 'The Virginian', '$$', '1521 University Ave', '22903', 1, 0, 1),
(3, 'Mellow Mushroom', '$$', '1321 W Main St', '22903', 1, 1, 1),
(4, 'Cava', '$$', '1114 Emmet St N', '22903', 1, 1, 1),
(5, 'Guadalajara', '$$', '817 W Main St', '22903', 1, 1, 1),
(6, 'Vu Noodles', '$$', '420 W Main St', '22903', 1, 1, 1),
(7, 'Kanak', '$$$', '1001 W Main St', '22903', 1, 1, 1),
(8, 'Mudhouse Coffee', '$', '213 W Main St', '22902', 1, 1, 1),
(9, 'Asian Express', '$', '909 W Main St', '22903', 0, 0, 0),
(10, 'Bamboo House', '$$', '4831 Seminole Trail', '22901', 0, 0, 0),
(11, 'Bang!', '$', '213 Second St', '22902', 0, 0, 0),
(12, 'Bangkok 99 Crozet', '$$', '540 Radford Ln #700', '22932', 0, 0, 0),
(13, 'Bangkok 99 Charlottesville', '$$', '2005 Commonwealth Dr', '22901', 0, 0, 0),
(14, 'Albemarle Baking Company', '$', '418 W Main St', '22903', 0, 0, 0),
(15, 'BreadWorks Preston Plaza', '$', 'Preston Plaza', '22903', 0, 0, 0),
(16, 'BreadWorks Ivy Rd', '$', '2955 Ivy Rd', '22903', 0, 0, 0),
(17, 'Carpe Donut', '$', 'McIntire Plaza', '22902', 0, 0, 0),
(18, 'Chandler\'s Bakery', '$', 'Rio Hill Shopping Center', '22901', 0, 0, 0),
(19, 'Al Carbon', '$', '1871 Seminole Trail', '22901', 0, 0, 0),
(20, 'Armando\'s Mexican Restaurant', '$', '105 14th St NW', '22903', 0, 0, 0),
(21, 'Asado Wing & Taco Company', '$', '1327 W Main St', '22903', 0, 0, 0),
(22, 'Aqui es Mexico', '$', '221 Carlton Rd Ste 12', '22902', 0, 0, 0),
(23, 'Barbie\'s Burrito Barn', '$', '201 Avon St', '22902', 0, 0, 0),
(24, 'Brazos Tacos', '$', '925 Second St SE', '22902', 0, 0, 0);



-- --------------------------------------------------------

--
-- Table structure for table `Pending_Restaurant`
--

CREATE TABLE `Pending_Restaurant` (
  `Pending_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) DEFAULT NULL,
  `Price_Level` varchar(5) DEFAULT NULL,
  `Street` varchar(100) DEFAULT NULL,
  `City` varchar(50) DEFAULT NULL,
  `State` varchar(50) DEFAULT NULL,
  `Zip_Code` char(5) DEFAULT NULL,
  `Vegetarian` tinyint(1) DEFAULT 0,
  `Vegan` tinyint(1) DEFAULT 0,
  `GlutenFree` tinyint(1) DEFAULT 0,
  `Phones` text DEFAULT NULL,
  `Cuisines` text DEFAULT NULL,
  `Hours` text DEFAULT NULL,
  PRIMARY KEY (`Pending_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Restaurant_Phone`
--

CREATE TABLE `Restaurant_Phone` (
  `Restaurant_ID` int(11) NOT NULL,
  `Phone_Number` char(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Restaurant_Phone`
--

INSERT INTO `Restaurant_Phone` (`Restaurant_ID`, `Phone_Number`) VALUES
(1, '4345551001'),
(2, '4345551002'),
(3, '4345551003'),
(4, '4345551004'),
(5, '4345551005'),
(6, '4345551006'),
(7, '4345551007'),
(9, '4349791888'),
(10, '4349739211'),
(11, '4349842264'),
(12, '4348235881'),
(13, '4349741326'),
(14, '4342936456'),
(15, '4342964663'),
(16, '4342204575'),
(17, '4342022918'),
(18, '4349752253'),
(19, '4349641052'),
(20, '4342021980'),
(21, '4342343486'),
(22, '4342954748'),
(23, '4343288020'),
(24, '4349841163');

-- --------------------------------------------------------

--
-- Table structure for table `Restaurant_Rating`
--

CREATE TABLE `Restaurant_Rating` (
  `Rating_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Restaurant_ID` int(11) NOT NULL,
  `Rating` tinyint(4) NOT NULL,
  `Review` text DEFAULT NULL,
  `Created_At` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Restaurant_Rating`
--

INSERT INTO `Restaurant_Rating` (`Rating_ID`, `User_ID`, `Restaurant_ID`, `Rating`, `Review`, `Created_At`) VALUES
(5, 1, 4, 2, 'amazing', '2026-04-13 21:08:16'),
(8, 2, 4, 1, 'hello.', '2026-04-13 21:11:56'),
(10, 2, 11, 5, 'amazing!', '2026-04-13 21:12:26'),
(11, 1, 11, 1, 'ok', '2026-04-13 21:13:13');

-- --------------------------------------------------------

--
-- Table structure for table `Review`
--

CREATE TABLE `Review` (
  `Review_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Restaurant_ID` int(11) NOT NULL,
  `Rating` int(11) NOT NULL,
  `Comment` text DEFAULT NULL,
  `Review_Date` datetime DEFAULT current_timestamp()
  CONSTRAINT check_rating_range CHECK (`Rating` >= 1 AND `Rating` <= 5)
) ;

--
-- Dumping data for table `Review`
--

INSERT INTO `Review` (`Review_ID`, `User_ID`, `Restaurant_ID`, `Rating`, `Comment`, `Review_Date`) VALUES
(1, 1, 1, 4, 'Updated review after another visit.', '2026-03-10 10:00:00'),
(3, 3, 3, 5, 'Lots of vegetarian options and good pizza.', '2026-03-12 18:00:00'),
(4, 4, 4, 4, 'Fresh bowls and quick service.', '2026-03-13 12:30:00'),
(5, 5, 5, 3, 'Solid Mexican food near UVA.', '2026-03-14 19:00:00'),
(6, 1, 6, 5, 'Loved the noodles and vegan choices.', '2026-03-15 17:45:00'),
(7, 2, 7, 4, 'Tasty Indian food and nice portions.', '2026-03-16 20:10:00'),
(8, 3, 8, 5, 'Great coffee and study spot.', '2026-03-17 09:15:00'),
(9, 4, 9, 4, 'Cheap and quick lunch option.', '2026-03-17 12:00:00'),
(10, 5, 10, 4, 'Good Korean and Chinese dishes.', '2026-03-17 18:30:00'),
(11, 1, 2, 5, 'Really enjoyed this place.', '2026-03-17 22:57:28'),
(12, 1, 7, 5, '', '2026-04-13 21:35:53'),
(13, 1, 3, 3, 'yes', '2026-04-13 21:44:49'),
(14, 1, 17, 3, '', '2026-04-13 21:54:06'),
(15, 1, 19, 1, '', '2026-04-13 21:54:21'),
(16, 2, 17, 2, 'amazing!', '2026-04-13 22:34:12'),
(17, 2, 10, 3, 'yesss', '2026-04-13 22:39:00');

--
-- Triggers `Review`
--
DELIMITER $$
CREATE TRIGGER `before_review_insert_set_date` BEFORE INSERT ON `Review` FOR EACH ROW SET NEW.Review_Date = COALESCE(NEW.Review_Date, CURRENT_TIMESTAMP())
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `Review_Like`
--

CREATE TABLE `Review_Like` (
  `User_ID` int(11) NOT NULL,
  `Review_ID` int(11) NOT NULL,
  `Liked_At` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Review_Like`
--

INSERT INTO `Review_Like` (`User_ID`, `Review_ID`, `Liked_At`) VALUES
(1, 3, '2026-03-17 22:57:28'),
(1, 8, '2026-04-13 21:28:24'),
(1, 9, '2026-04-13 21:44:19'),
(2, 1, '2026-03-17 22:57:28'),
(2, 3, '2026-04-13 22:31:15'),
(3, 1, '2026-03-17 22:57:28'),
(4, 5, '2026-03-17 22:57:28');

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `User` (
  `User_ID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password_Hash` varchar(255) NOT NULL,
  `Is_Admin` tinyint(1) DEFAULT 0,
  `Date_Created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `User`
--

INSERT INTO `User` (`User_ID`, `Username`, `Email`, `Password_Hash`, `Date_Created`) VALUES
(1, 'anvitha', 'updated_anvitha@example.com', 'hash_anvitha', '2026-03-17 22:57:28'),
(2, 'krishna', 'krishna@example.com', 'hash_krishna', '2026-03-17 22:57:28'),
(3, 'aleesha', 'aleesha@example.com', 'hash_aleesha', '2026-03-17 22:57:28'),
(4, 'uvafoodie', 'uvafoodie@example.com', 'hash_foodie', '2026-03-17 22:57:28'),
(5, 'cvillelocal', 'cvillelocal@example.com', 'hash_local', '2026-03-17 22:57:28'),
(6, 'newuser', 'newuser@example.com', 'hash_newuser', '2026-03-17 22:57:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Cuisine`
--
ALTER TABLE `Cuisine`
  ADD PRIMARY KEY (`Cuisine_ID`),
  ADD UNIQUE KEY `Cuisine_Name` (`Cuisine_Name`);

--
-- Indexes for table `Favorite`
--
ALTER TABLE `Favorite`
  ADD PRIMARY KEY (`User_ID`,`Restaurant_ID`),
  ADD KEY `Restaurant_ID` (`Restaurant_ID`);

--
-- Indexes for table `Has_Cuisine`
--
ALTER TABLE `Has_Cuisine`
  ADD PRIMARY KEY (`Restaurant_ID`,`Cuisine_ID`),
  ADD KEY `Cuisine_ID` (`Cuisine_ID`);

--
-- Indexes for table `Location`
--
ALTER TABLE `Location`
  ADD PRIMARY KEY (`Zip_Code`);

--
-- Indexes for table `Opening_Hours`
--
ALTER TABLE `Opening_Hours`
  ADD PRIMARY KEY (`Restaurant_ID`,`Day_Of_Week`);

--
-- Indexes for table `Restaurant`
--
ALTER TABLE `Restaurant`
  ADD PRIMARY KEY (`Restaurant_ID`),
  ADD KEY `Zip_Code` (`Zip_Code`);

--
-- Indexes for table `Restaurant_Phone`
--
ALTER TABLE `Restaurant_Phone`
  ADD PRIMARY KEY (`Restaurant_ID`,`Phone_Number`);

--
-- Indexes for table `Restaurant_Rating`
--
ALTER TABLE `Restaurant_Rating`
  ADD PRIMARY KEY (`Rating_ID`),
  ADD UNIQUE KEY `user_restaurant` (`User_ID`,`Restaurant_ID`);

--
-- Indexes for table `Review`
--
ALTER TABLE `Review`
  ADD PRIMARY KEY (`Review_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `Restaurant_ID` (`Restaurant_ID`);

--
-- Indexes for table `Review_Like`
--
ALTER TABLE `Review_Like`
  ADD PRIMARY KEY (`User_ID`,`Review_ID`),
  ADD KEY `Review_ID` (`Review_ID`);

--
-- Indexes for table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`User_ID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Cuisine`
--
ALTER TABLE `Cuisine`
  MODIFY `Cuisine_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `Restaurant`
--
ALTER TABLE `Restaurant`
  MODIFY `Restaurant_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Restaurant_Rating`
--
ALTER TABLE `Restaurant_Rating`
  MODIFY `Rating_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `Review`
--
ALTER TABLE `Review`
  MODIFY `Review_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Favorite`
--
ALTER TABLE `Favorite`
  ADD CONSTRAINT `favorite_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `User` (`User_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorite_ibfk_2` FOREIGN KEY (`Restaurant_ID`) REFERENCES `Restaurant` (`Restaurant_ID`) ON DELETE CASCADE;

--
-- Constraints for table `Has_Cuisine`
--
ALTER TABLE `Has_Cuisine`
  ADD CONSTRAINT `has_cuisine_ibfk_1` FOREIGN KEY (`Restaurant_ID`) REFERENCES `Restaurant` (`Restaurant_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `has_cuisine_ibfk_2` FOREIGN KEY (`Cuisine_ID`) REFERENCES `Cuisine` (`Cuisine_ID`) ON DELETE CASCADE;

--
-- Constraints for table `Opening_Hours`
--
ALTER TABLE `Opening_Hours`
  ADD CONSTRAINT `opening_hours_ibfk_1` FOREIGN KEY (`Restaurant_ID`) REFERENCES `Restaurant` (`Restaurant_ID`) ON DELETE CASCADE;

--
-- Constraints for table `Restaurant`
--
ALTER TABLE `Restaurant`
  ADD CONSTRAINT `restaurant_ibfk_1` FOREIGN KEY (`Zip_Code`) REFERENCES `Location` (`Zip_Code`);

--
-- Constraints for table `Restaurant_Phone`
--
ALTER TABLE `Restaurant_Phone`
  ADD CONSTRAINT `restaurant_phone_ibfk_1` FOREIGN KEY (`Restaurant_ID`) REFERENCES `Restaurant` (`Restaurant_ID`) ON DELETE CASCADE;

--
-- Constraints for table `Review`
--
ALTER TABLE `Review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `User` (`User_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`Restaurant_ID`) REFERENCES `Restaurant` (`Restaurant_ID`) ON DELETE CASCADE;

--
-- Constraints for table `Review_Like`
--
ALTER TABLE `Review_Like`
  ADD CONSTRAINT `review_like_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `User` (`User_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_like_ibfk_2` FOREIGN KEY (`Review_ID`) REFERENCES `Review` (`Review_ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
