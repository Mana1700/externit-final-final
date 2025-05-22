-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 21, 2025 at 06:41 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `newtest`
--

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `submission_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `issue_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `certificate_number` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `submission_id`, `task_id`, `student_id`, `company_id`, `issue_date`, `certificate_number`) VALUES
(1, 1, 1, 1, 1, '2025-04-24 04:31:12', 'CERT-6809BE90C502F-2025'),
(2, 2, 2, 1, 1, '2025-04-24 04:48:54', 'CERT-6809C2B605E0B-2025'),
(3, 5, 4, 1, 1, '2025-04-26 10:50:28', 'CERT-680CBA74620D5-2025'),
(4, 6, 5, 1, 1, '2025-04-26 13:00:05', 'CERT-680CD8D5A6D4D-2025'),
(5, 7, 5, 4, 1, '2025-04-26 14:31:55', 'CERT-680CEE5B0C639-2025'),
(6, 8, 6, 1, 1, '2025-04-26 14:41:20', 'CERT-680CF090BBBC2-2025'),
(7, 10, 7, 1, 1, '2025-04-26 14:59:57', 'CERT-680CF4ED63052-2025'),
(8, 9, 6, 4, 1, '2025-04-26 15:03:57', 'CERT-680CF5DD68544-2025'),
(9, 11, 8, 1, 1, '2025-04-26 18:05:44', 'CERT-680D2078A0148-2025'),
(10, 12, 9, 5, 1, '2025-04-29 14:40:22', 'CERT-6810E4D689457-2025'),
(11, 13, 9, 6, 1, '2025-04-29 15:03:23', 'CERT-6810EA3B77D5D-2025'),
(12, 15, 10, 3, 1, '2025-05-21 13:52:51', 'CERT-682DDAB3DF84F-2025');

-- --------------------------------------------------------

--
-- Table structure for table `certificate_templates`
--

CREATE TABLE `certificate_templates` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `is_best_submission` tinyint(1) DEFAULT 0,
  `template_html` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificate_templates`
--

INSERT INTO `certificate_templates` (`id`, `task_id`, `is_best_submission`, `template_html`, `created_at`) VALUES
(1, 1, 1, '\n<style>\n    .certificate-special {\n        background: linear-gradient(135deg, rgba(191, 109, 58, 0.1) 0%, rgba(191, 109, 58, 0.05) 100%);\n        border: 2px solid #BF6D3A;\n        padding: 20px;\n        position: relative;\n    }\n    .certificate-special::before {\n        content: \"Best Submission\";\n        position: absolute;\n        top: 10px;\n        right: 10px;\n        background: #BF6D3A;\n        color: white;\n        padding: 5px 15px;\n        border-radius: 20px;\n        font-size: 14px;\n    }\n    .certificate-special h1 { \n        color: #BF6D3A; \n        font-size: 28pt; \n        text-align: center;\n        text-transform: uppercase;\n        letter-spacing: 2px;\n    }\n    .certificate-body { \n        text-align: center; \n        line-height: 1.8;\n        font-size: 14pt;\n    }\n    .award-seal {\n        width: 120px;\n        height: 120px;\n        margin: 20px auto;\n        background: url(\'seals/best_submission.png\') no-repeat center center;\n        background-size: contain;\n    }\n    .signature-area {\n        margin-top: 40px;\n        display: flex;\n        justify-content: space-around;\n    }\n    .signature-line {\n        width: 200px;\n        border-top: 1px solid #666;\n        margin-top: 10px;\n    }\n</style>\n<div class=\"certificate-special\">\n    <div class=\"certificate-header\">\n        <h1>Certificate of Excellence</h1>\n    </div>\n    <div class=\"certificate-body\">\n        <p>This is to certify that</p>\n        <h2>{STUDENT_NAME}</h2>\n        <p>from {UNIVERSITY}</p>\n        <p>has demonstrated exceptional skill and dedication in completing</p>\n        <h3>{TASK_NAME}</h3>\n        <p>This submission was selected as the <strong>Best Submission</strong> by</p>\n        <h3>{COMPANY_NAME}</h3>\n        <p>Awarded on {DATE}</p>\n        <div class=\"award-seal\"></div>\n    </div>\n    <div class=\"signature-area\">\n        <div class=\"signature\">\n            <div class=\"signature-line\"></div>\n            <p>Company Representative</p>\n        </div>\n        <div class=\"signature\">\n            <div class=\"signature-line\"></div>\n            <p>ExternIT Director</p>\n        </div>\n    </div>\n    <div class=\"certificate-footer\">\n        <p>Certificate ID: {CERTIFICATE_ID}</p>\n        <p>Verified by ExternIT | Best Submission Award</p>\n    </div>\n</div>', '2025-04-24 04:27:23'),
(2, 2, 1, '\n<style>\n    .certificate-special {\n        background: linear-gradient(135deg, rgba(191, 109, 58, 0.1) 0%, rgba(191, 109, 58, 0.05) 100%);\n        border: 2px solid #BF6D3A;\n        padding: 20px;\n        position: relative;\n    }\n    .certificate-special::before {\n        content: \"Best Submission\";\n        position: absolute;\n        top: 10px;\n        right: 10px;\n        background: #BF6D3A;\n        color: white;\n        padding: 5px 15px;\n        border-radius: 20px;\n        font-size: 14px;\n    }\n    .certificate-special h1 { \n        color: #BF6D3A; \n        font-size: 28pt; \n        text-align: center;\n        text-transform: uppercase;\n        letter-spacing: 2px;\n    }\n    .certificate-body { \n        text-align: center; \n        line-height: 1.8;\n        font-size: 14pt;\n    }\n    .award-seal {\n        width: 120px;\n        height: 120px;\n        margin: 20px auto;\n        background: url(\'seals/best_submission.png\') no-repeat center center;\n        background-size: contain;\n    }\n    .signature-area {\n        margin-top: 40px;\n        display: flex;\n        justify-content: space-around;\n    }\n    .signature-line {\n        width: 200px;\n        border-top: 1px solid #666;\n        margin-top: 10px;\n    }\n</style>\n<div class=\"certificate-special\">\n    <div class=\"certificate-header\">\n        <h1>Certificate of Excellence</h1>\n    </div>\n    <div class=\"certificate-body\">\n        <p>This is to certify that</p>\n        <h2>{STUDENT_NAME}</h2>\n        <p>from {UNIVERSITY}</p>\n        <p>has demonstrated exceptional skill and dedication in completing</p>\n        <h3>{TASK_NAME}</h3>\n        <p>This submission was selected as the <strong>Best Submission</strong> by</p>\n        <h3>{COMPANY_NAME}</h3>\n        <p>Awarded on {DATE}</p>\n        <div class=\"award-seal\"></div>\n    </div>\n    <div class=\"signature-area\">\n        <div class=\"signature\">\n            <div class=\"signature-line\"></div>\n            <p>Company Representative</p>\n        </div>\n        <div class=\"signature\">\n            <div class=\"signature-line\"></div>\n            <p>ExternIT Director</p>\n        </div>\n    </div>\n    <div class=\"certificate-footer\">\n        <p>Certificate ID: {CERTIFICATE_ID}</p>\n        <p>Verified by ExternIT | Best Submission Award</p>\n    </div>\n</div>', '2025-04-24 04:47:56'),
(3, 3, 1, '\n<style>\n    .certificate-special {\n        background: linear-gradient(135deg, rgba(191, 109, 58, 0.1) 0%, rgba(191, 109, 58, 0.05) 100%);\n        border: 2px solid #BF6D3A;\n        padding: 20px;\n        position: relative;\n    }\n    .certificate-special::before {\n        content: \"Best Submission\";\n        position: absolute;\n        top: 10px;\n        right: 10px;\n        background: #BF6D3A;\n        color: white;\n        padding: 5px 15px;\n        border-radius: 20px;\n        font-size: 14px;\n    }\n    .certificate-special h1 { \n        color: #BF6D3A; \n        font-size: 28pt; \n        text-align: center;\n        text-transform: uppercase;\n        letter-spacing: 2px;\n    }\n    .certificate-body { \n        text-align: center; \n        line-height: 1.8;\n        font-size: 14pt;\n    }\n    .award-seal {\n        width: 120px;\n        height: 120px;\n        margin: 20px auto;\n        background: url(\'seals/best_submission.png\') no-repeat center center;\n        background-size: contain;\n    }\n    .signature-area {\n        margin-top: 40px;\n        display: flex;\n        justify-content: space-around;\n    }\n    .signature-line {\n        width: 200px;\n        border-top: 1px solid #666;\n        margin-top: 10px;\n    }\n</style>\n<div class=\"certificate-special\">\n    <div class=\"certificate-header\">\n        <h1>Certificate of Excellence</h1>\n    </div>\n    <div class=\"certificate-body\">\n        <p>This is to certify that</p>\n        <h2>{STUDENT_NAME}</h2>\n        <p>from {UNIVERSITY}</p>\n        <p>has demonstrated exceptional skill and dedication in completing</p>\n        <h3>{TASK_NAME}</h3>\n        <p>This submission was selected as the <strong>Best Submission</strong> by</p>\n        <h3>{COMPANY_NAME}</h3>\n        <p>Awarded on {DATE}</p>\n        <div class=\"award-seal\"></div>\n    </div>\n    <div class=\"signature-area\">\n        <div class=\"signature\">\n            <div class=\"signature-line\"></div>\n            <p>Company Representative</p>\n        </div>\n        <div class=\"signature\">\n            <div class=\"signature-line\"></div>\n            <p>ExternIT Director</p>\n        </div>\n    </div>\n    <div class=\"certificate-footer\">\n        <p>Certificate ID: {CERTIFICATE_ID}</p>\n        <p>Verified by ExternIT | Best Submission Award</p>\n    </div>\n</div>', '2025-04-24 21:19:11'),
(4, 4, 1, '\n<style>\n    .certificate-special {\n        background: linear-gradient(135deg, rgba(191, 109, 58, 0.1) 0%, rgba(191, 109, 58, 0.05) 100%);\n        border: 2px solid #BF6D3A;\n        padding: 20px;\n        position: relative;\n    }\n    .certificate-special::before {\n        content: \"Best Submission\";\n        position: absolute;\n        top: 10px;\n        right: 10px;\n        background: #BF6D3A;\n        color: white;\n        padding: 5px 15px;\n        border-radius: 20px;\n        font-size: 14px;\n    }\n    .certificate-special h1 { \n        color: #BF6D3A; \n        font-size: 28pt; \n        text-align: center;\n        text-transform: uppercase;\n        letter-spacing: 2px;\n    }\n    .certificate-body { \n        text-align: center; \n        line-height: 1.8;\n        font-size: 14pt;\n    }\n    .award-seal {\n        width: 120px;\n        height: 120px;\n        margin: 20px auto;\n        background: url(\'seals/best_submission.png\') no-repeat center center;\n        background-size: contain;\n    }\n    .signature-area {\n        margin-top: 40px;\n        display: flex;\n        justify-content: space-around;\n    }\n    .signature-line {\n        width: 200px;\n        border-top: 1px solid #666;\n        margin-top: 10px;\n    }\n</style>\n<div class=\"certificate-special\">\n    <div class=\"certificate-header\">\n        <h1>Certificate of Excellence</h1>\n    </div>\n    <div class=\"certificate-body\">\n        <p>This is to certify that</p>\n        <h2>{STUDENT_NAME}</h2>\n        <p>from {UNIVERSITY}</p>\n        <p>has demonstrated exceptional skill and dedication in completing</p>\n        <h3>{TASK_NAME}</h3>\n        <p>This submission was selected as the <strong>Best Submission</strong> by</p>\n        <h3>{COMPANY_NAME}</h3>\n        <p>Awarded on {DATE}</p>\n        <div class=\"award-seal\"></div>\n    </div>\n    <div class=\"signature-area\">\n        <div class=\"signature\">\n            <div class=\"signature-line\"></div>\n            <p>Company Representative</p>\n        </div>\n        <div class=\"signature\">\n            <div class=\"signature-line\"></div>\n            <p>ExternIT Director</p>\n        </div>\n    </div>\n    <div class=\"certificate-footer\">\n        <p>Certificate ID: {CERTIFICATE_ID}</p>\n        <p>Verified by ExternIT | Best Submission Award</p>\n    </div>\n</div>', '2025-04-24 22:44:12'),
(5, 5, 1, '\n<style>\n    .certificate-special {\n        background: linear-gradient(135deg, rgba(191, 109, 58, 0.1) 0%, rgba(191, 109, 58, 0.05) 100%);\n        border: 2px solid #BF6D3A;\n        padding: 20px;\n        position: relative;\n    }\n    .certificate-special::before {\n        content: \"Best Submission\";\n        position: absolute;\n        top: 10px;\n        right: 10px;\n        background: #BF6D3A;\n        color: white;\n        padding: 5px 15px;\n        border-radius: 20px;\n        font-size: 14px;\n    }\n    .certificate-special h1 { \n        color: #BF6D3A; \n        font-size: 28pt; \n        text-align: center;\n        text-transform: uppercase;\n        letter-spacing: 2px;\n    }\n    .certificate-body { \n        text-align: center; \n        line-height: 1.8;\n        font-size: 14pt;\n    }\n    .award-seal {\n        width: 120px;\n        height: 120px;\n        margin: 20px auto;\n        background: url(\'seals/best_submission.png\') no-repeat center center;\n        background-size: contain;\n    }\n    .signature-area {\n        margin-top: 40px;\n        display: flex;\n        justify-content: space-around;\n    }\n    .signature-line {\n        width: 200px;\n        border-top: 1px solid #666;\n        margin-top: 10px;\n    }\n</style>\n<div class=\"certificate-special\">\n    <div class=\"certificate-header\">\n        <h1>Certificate of Excellence</h1>\n    </div>\n    <div class=\"certificate-body\">\n        <p>This is to certify that</p>\n        <h2>{STUDENT_NAME}</h2>\n        <p>from {UNIVERSITY}</p>\n        <p>has demonstrated exceptional skill and dedication in completing</p>\n        <h3>{TASK_NAME}</h3>\n        <p>This submission was selected as the <strong>Best Submission</strong> by</p>\n        <h3>{COMPANY_NAME}</h3>\n        <p>Awarded on {DATE}</p>\n        <div class=\"award-seal\"></div>\n    </div>\n    <div class=\"signature-area\">\n        <div class=\"signature\">\n            <div class=\"signature-line\"></div>\n            <p>Company Representative</p>\n        </div>\n        <div class=\"signature\">\n            <div class=\"signature-line\"></div>\n            <p>ExternIT Director</p>\n        </div>\n    </div>\n    <div class=\"certificate-footer\">\n        <p>Certificate ID: {CERTIFICATE_ID}</p>\n        <p>Verified by ExternIT | Best Submission Award</p>\n    </div>\n</div>', '2025-04-26 12:58:28'),
(6, 6, 1, '\n<style>\n    .certificate-special {\n        background: linear-gradient(135deg, rgba(191, 109, 58, 0.1) 0%, rgba(191, 109, 58, 0.05) 100%);\n        border: 2px solid #BF6D3A;\n        padding: 20px;\n        position: relative;\n    }\n    .certificate-special::before {\n        content: \"Best Submission\";\n        position: absolute;\n        top: 10px;\n        right: 10px;\n        background: #BF6D3A;\n        color: white;\n        padding: 5px 15px;\n        border-radius: 20px;\n        font-size: 14px;\n    }\n    .certificate-special h1 { \n        color: #BF6D3A; \n        font-size: 28pt; \n        text-align: center;\n        text-transform: uppercase;\n        letter-spacing: 2px;\n    }\n    .certificate-body { \n        text-align: center; \n        line-height: 1.8;\n        font-size: 14pt;\n    }\n    .award-seal {\n        width: 120px;\n        height: 120px;\n        margin: 20px auto;\n        background: url(\'seals/best_submission.png\') no-repeat center center;\n        background-size: contain;\n    }\n    .signature-area {\n        margin-top: 40px;\n        display: flex;\n        justify-content: space-around;\n    }\n    .signature-line {\n        width: 200px;\n        border-top: 1px solid #666;\n        margin-top: 10px;\n    }\n</style>\n<div class=\"certificate-special\">\n    <div class=\"certificate-header\">\n        <h1>Certificate of Excellence</h1>\n    </div>\n    <div class=\"certificate-body\">\n        <p>This is to certify that</p>\n        <h2>{STUDENT_NAME}</h2>\n        <p>from {UNIVERSITY}</p>\n        <p>has demonstrated exceptional skill and dedication in completing</p>\n        <h3>{TASK_NAME}</h3>\n        <p>This submission was selected as the <strong>Best Submission</strong> by</p>\n        <h3>{COMPANY_NAME}</h3>\n        <p>Awarded on {DATE}</p>\n        <div class=\"award-seal\"></div>\n    </div>\n    <div class=\"signature-area\">\n        <div class=\"signature\">\n            <div class=\"signature-line\"></div>\n            <p>Company Representative</p>\n        </div>\n        <div class=\"signature\">\n            <div class=\"signature-line\"></div>\n            <p>ExternIT Director</p>\n        </div>\n    </div>\n    <div class=\"certificate-footer\">\n        <p>Certificate ID: {CERTIFICATE_ID}</p>\n        <p>Verified by ExternIT | Best Submission Award</p>\n    </div>\n</div>', '2025-04-26 14:39:22'),
(7, 7, 1, '\n<style>\n    .certificate-special {\n        background: linear-gradient(135deg, rgba(191, 109, 58, 0.1) 0%, rgba(191, 109, 58, 0.05) 100%);\n        border: 2px solid #BF6D3A;\n        padding: 20px;\n        position: relative;\n    }\n    .certificate-special::before {\n        content: \"Best Submission\";\n        position: absolute;\n        top: 10px;\n        right: 10px;\n        background: #BF6D3A;\n        color: white;\n        padding: 5px 15px;\n        border-radius: 20px;\n        font-size: 14px;\n    }\n    .certificate-special h1 { \n        color: #BF6D3A; \n        font-size: 28pt; \n        text-align: center;\n        text-transform: uppercase;\n        letter-spacing: 2px;\n    }\n    .certificate-body { \n        text-align: center; \n        line-height: 1.8;\n        font-size: 14pt;\n    }\n    .award-seal {\n        width: 120px;\n        height: 120px;\n        margin: 20px auto;\n        background: url(\'seals/best_submission.png\') no-repeat center center;\n        background-size: contain;\n    }\n    .signature-area {\n        margin-top: 40px;\n        display: flex;\n        justify-content: space-around;\n    }\n    .signature-line {\n        width: 200px;\n        border-top: 1px solid #666;\n        margin-top: 10px;\n    }\n</style>\n<div class=\"certificate-special\">\n    <div class=\"certificate-header\">\n        <h1>Certificate of Excellence</h1>\n    </div>\n    <div class=\"certificate-body\">\n        <p>This is to certify that</p>\n        <h2>{STUDENT_NAME}</h2>\n        <p>from {UNIVERSITY}</p>\n        <p>has demonstrated exceptional skill and dedication in completing</p>\n        <h3>{TASK_NAME}</h3>\n        <p>This submission was selected as the <strong>Best Submission</strong> by</p>\n        <h3>{COMPANY_NAME}</h3>\n        <p>Awarded on {DATE}</p>\n        <div class=\"award-seal\"></div>\n    </div>\n    <div class=\"signature-area\">\n        <div class=\"signature\">\n            <div class=\"signature-line\"></div>\n            <p>Company Representative</p>\n        </div>\n        <div class=\"signature\">\n            <div class=\"signature-line\"></div>\n            <p>ExternIT Director</p>\n        </div>\n    </div>\n    <div class=\"certificate-footer\">\n        <p>Certificate ID: {CERTIFICATE_ID}</p>\n        <p>Verified by ExternIT | Best Submission Award</p>\n    </div>\n</div>', '2025-04-26 14:48:07'),
(8, 8, 1, '\n<style>\n    .certificate-special {\n        background: linear-gradient(135deg, rgba(191, 109, 58, 0.1) 0%, rgba(191, 109, 58, 0.05) 100%);\n        border: 2px solid #BF6D3A;\n        padding: 20px;\n        position: relative;\n    }\n    .certificate-special::before {\n        content: \"Best Submission\";\n        position: absolute;\n        top: 10px;\n        right: 10px;\n        background: #BF6D3A;\n        color: white;\n        padding: 5px 15px;\n        border-radius: 20px;\n        font-size: 14px;\n    }\n    .certificate-special h1 { \n        color: #BF6D3A; \n        font-size: 28pt; \n        text-align: center;\n        text-transform: uppercase;\n        letter-spacing: 2px;\n    }\n    .certificate-body { \n        text-align: center; \n        line-height: 1.8;\n        font-size: 14pt;\n    }\n    .award-seal {\n        width: 120px;\n        height: 120px;\n        margin: 20px auto;\n        background: url(\'seals/best_submission.png\') no-repeat center center;\n        background-size: contain;\n    }\n    .signature-area {\n        margin-top: 40px;\n        display: flex;\n        justify-content: space-around;\n    }\n    .signature-line {\n        width: 200px;\n        border-top: 1px solid #666;\n        margin-top: 10px;\n    }\n</style>\n<div class=\"certificate-special\">\n    <div class=\"certificate-header\">\n        <h1>Certificate of Excellence</h1>\n    </div>\n    <div class=\"certificate-body\">\n        <p>This is to certify that</p>\n        <h2>{STUDENT_NAME}</h2>\n        <p>from {UNIVERSITY}</p>\n        <p>has demonstrated exceptional skill and dedication in completing</p>\n        <h3>{TASK_NAME}</h3>\n        <p>This submission was selected as the <strong>Best Submission</strong> by</p>\n        <h3>{COMPANY_NAME}</h3>\n        <p>Awarded on {DATE}</p>\n        <div class=\"award-seal\"></div>\n    </div>\n    <div class=\"signature-area\">\n        <div class=\"signature\">\n            <div class=\"signature-line\"></div>\n            <p>Company Representative</p>\n        </div>\n        <div class=\"signature\">\n            <div class=\"signature-line\"></div>\n            <p>ExternIT Director</p>\n        </div>\n    </div>\n    <div class=\"certificate-footer\">\n        <p>Certificate ID: {CERTIFICATE_ID}</p>\n        <p>Verified by ExternIT | Best Submission Award</p>\n    </div>\n</div>', '2025-04-26 18:04:18'),
(9, 9, 1, '\r\n<style>\r\n    .certificate-special {\r\n        background: linear-gradient(135deg, rgba(191, 109, 58, 0.1) 0%, rgba(191, 109, 58, 0.05) 100%);\r\n        border: 2px solid #BF6D3A;\r\n        padding: 20px;\r\n        position: relative;\r\n    }\r\n    .certificate-special::before {\r\n        content: \"Best Submission\";\r\n        position: absolute;\r\n        top: 10px;\r\n        right: 10px;\r\n        background: #BF6D3A;\r\n        color: white;\r\n        padding: 5px 15px;\r\n        border-radius: 20px;\r\n        font-size: 14px;\r\n    }\r\n    .certificate-special h1 { \r\n        color: #BF6D3A; \r\n        font-size: 28pt; \r\n        text-align: center;\r\n        text-transform: uppercase;\r\n        letter-spacing: 2px;\r\n    }\r\n    .certificate-body { \r\n        text-align: center; \r\n        line-height: 1.8;\r\n        font-size: 14pt;\r\n    }\r\n    .award-seal {\r\n        width: 120px;\r\n        height: 120px;\r\n        margin: 20px auto;\r\n        background: url(\'seals/best_submission.png\') no-repeat center center;\r\n        background-size: contain;\r\n    }\r\n    .signature-area {\r\n        margin-top: 40px;\r\n        display: flex;\r\n        justify-content: space-around;\r\n    }\r\n    .signature-line {\r\n        width: 200px;\r\n        border-top: 1px solid #666;\r\n        margin-top: 10px;\r\n    }\r\n</style>\r\n<div class=\"certificate-special\">\r\n    <div class=\"certificate-header\">\r\n        <h1>Certificate of Excellence</h1>\r\n    </div>\r\n    <div class=\"certificate-body\">\r\n        <p>This is to certify that</p>\r\n        <h2>{STUDENT_NAME}</h2>\r\n        <p>from {UNIVERSITY}</p>\r\n        <p>has demonstrated exceptional skill and dedication in completing</p>\r\n        <h3>{TASK_NAME}</h3>\r\n        <p>This submission was selected as the <strong>Best Submission</strong> by</p>\r\n        <h3>{COMPANY_NAME}</h3>\r\n        <p>Awarded on {DATE}</p>\r\n        <div class=\"award-seal\"></div>\r\n    </div>\r\n    <div class=\"signature-area\">\r\n        <div class=\"signature\">\r\n            <div class=\"signature-line\"></div>\r\n            <p>Company Representative</p>\r\n        </div>\r\n        <div class=\"signature\">\r\n            <div class=\"signature-line\"></div>\r\n            <p>ExternIT Director</p>\r\n        </div>\r\n    </div>\r\n    <div class=\"certificate-footer\">\r\n        <p>Certificate ID: {CERTIFICATE_ID}</p>\r\n        <p>Verified by ExternIT | Best Submission Award</p>\r\n    </div>\r\n</div>', '2025-04-29 12:26:25'),
(10, 10, 1, '\r\n<style>\r\n    .certificate-special {\r\n        background: linear-gradient(135deg, rgba(191, 109, 58, 0.1) 0%, rgba(191, 109, 58, 0.05) 100%);\r\n        border: 2px solid #BF6D3A;\r\n        padding: 20px;\r\n        position: relative;\r\n    }\r\n    .certificate-special::before {\r\n        content: \"Best Submission\";\r\n        position: absolute;\r\n        top: 10px;\r\n        right: 10px;\r\n        background: #BF6D3A;\r\n        color: white;\r\n        padding: 5px 15px;\r\n        border-radius: 20px;\r\n        font-size: 14px;\r\n    }\r\n    .certificate-special h1 { \r\n        color: #BF6D3A; \r\n        font-size: 28pt; \r\n        text-align: center;\r\n        text-transform: uppercase;\r\n        letter-spacing: 2px;\r\n    }\r\n    .certificate-body { \r\n        text-align: center; \r\n        line-height: 1.8;\r\n        font-size: 14pt;\r\n    }\r\n    .award-seal {\r\n        width: 120px;\r\n        height: 120px;\r\n        margin: 20px auto;\r\n        background: url(\'seals/best_submission.png\') no-repeat center center;\r\n        background-size: contain;\r\n    }\r\n    .signature-area {\r\n        margin-top: 40px;\r\n        display: flex;\r\n        justify-content: space-around;\r\n    }\r\n    .signature-line {\r\n        width: 200px;\r\n        border-top: 1px solid #666;\r\n        margin-top: 10px;\r\n    }\r\n</style>\r\n<div class=\"certificate-special\">\r\n    <div class=\"certificate-header\">\r\n        <h1>Certificate of Excellence</h1>\r\n    </div>\r\n    <div class=\"certificate-body\">\r\n        <p>This is to certify that</p>\r\n        <h2>{STUDENT_NAME}</h2>\r\n        <p>from {UNIVERSITY}</p>\r\n        <p>has demonstrated exceptional skill and dedication in completing</p>\r\n        <h3>{TASK_NAME}</h3>\r\n        <p>This submission was selected as the <strong>Best Submission</strong> by</p>\r\n        <h3>{COMPANY_NAME}</h3>\r\n        <p>Awarded on {DATE}</p>\r\n        <div class=\"award-seal\"></div>\r\n    </div>\r\n    <div class=\"signature-area\">\r\n        <div class=\"signature\">\r\n            <div class=\"signature-line\"></div>\r\n            <p>Company Representative</p>\r\n        </div>\r\n        <div class=\"signature\">\r\n            <div class=\"signature-line\"></div>\r\n            <p>ExternIT Director</p>\r\n        </div>\r\n    </div>\r\n    <div class=\"certificate-footer\">\r\n        <p>Certificate ID: {CERTIFICATE_ID}</p>\r\n        <p>Verified by ExternIT | Best Submission Award</p>\r\n    </div>\r\n</div>', '2025-05-21 13:43:20');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `industry` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `user_id`, `name`, `industry`, `website`, `phone`, `address`, `created_at`) VALUES
(1, 2, 'Extern It', 'Manufacturing', '', NULL, NULL, '2025-04-24 04:27:00'),
(2, 6, 'abdelrahman mohamed', 'Telecommunications', NULL, NULL, NULL, '2025-04-26 16:24:20');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_type` enum('student','company') NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_type` enum('student','company') NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('certificate','submission','payment') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 'certificate', 'Certificate Issued', 'Congratulations! Your submission for \'task w keda\' has been approved and a certificate has been issued.', 1, '2025-04-26 14:41:20'),
(2, 2, 'submission', 'New Submission Received', 'A new submission has been received for task \'task w keda\' from student abdo nasser', 1, '2025-04-26 14:45:34'),
(3, 2, 'submission', 'New Submission Received', 'A new submission has been received for task \'gaaaggg\' from student abdelrahman mohamed', 1, '2025-04-26 14:50:56'),
(4, 1, 'certificate', 'Certificate Issued', 'Congratulations! Your submission for \'gaaaggg\' has been approved and a certificate has been issued.', 1, '2025-04-26 14:59:57'),
(5, 5, 'certificate', 'Certificate Issued', 'Congratulations! Your submission for \'task w keda\' has been approved and a certificate has been issued.', 1, '2025-04-26 15:03:57'),
(6, 1, 'certificate', 'Certificate Issued', 'Congratulations! Your submission for \'3oobaad\' has been approved and a certificate has been issued.', 1, '2025-04-26 18:05:44'),
(7, 7, 'certificate', 'Certificate Issued', 'Congratulations! Your submission for \'ccc\' has been approved and a certificate has been issued.', 1, '2025-04-29 14:40:22'),
(8, 8, 'certificate', 'Certificate Issued', 'Congratulations! Your submission for \'ccc\' has been approved and a certificate has been issued.', 0, '2025-04-29 15:03:23'),
(9, 4, 'certificate', 'Certificate Issued', 'Congratulations! Your submission for \'abdo2\' has been approved and a certificate has been issued.', 0, '2025-05-21 13:52:51');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `certificate_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
  `payment_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `certificate_id`, `amount`, `status`, `payment_date`, `created_at`) VALUES
(1, 1, 2000.00, 'pending', '2025-04-24 04:31:12', '2025-04-24 04:31:12'),
(2, 3, 500.00, 'pending', '2025-04-26 10:50:28', '2025-04-26 10:50:28'),
(3, 4, 2000.00, 'pending', '2025-04-26 14:59:57', '2025-04-26 14:59:57'),
(4, 5, 2000.00, 'pending', '2025-04-26 15:03:57', '2025-04-26 15:03:57'),
(5, 6, 1000.00, 'pending', '2025-04-26 18:05:44', '2025-04-26 18:05:44'),
(6, 8, 100.00, 'pending', '2025-04-29 15:03:23', '2025-04-29 15:03:23'),
(7, 9, 1000.00, 'pending', '2025-05-21 13:52:51', '2025-05-21 13:52:51');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `university` varchar(255) NOT NULL,
  `major` varchar(255) NOT NULL,
  `graduation_year` year(4) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `iban` varchar(34) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `bio` text DEFAULT NULL COMMENT 'Student biography or personal statement'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `name`, `university`, `major`, `graduation_year`, `phone`, `iban`, `created_at`, `bio`) VALUES
(1, 1, 'abdelrahman mohamed', 'UNIMAS', 'CY', '2028', '01112233475', NULL, '2025-04-24 04:26:23', 'Im mana'),
(2, 3, 'mana', 'UTM', 'DS', '2029', '', NULL, '2025-04-25 11:54:58', ''),
(3, 4, 'abdelrahman mohamed', 'UiTM', 'CY', '2028', NULL, NULL, '2025-04-25 13:13:13', NULL),
(4, 5, 'abdo nasser', 'UMS', 'CY', '2027', '', NULL, '2025-04-26 14:29:41', 'hi im abdo'),
(5, 7, 'body', 'USM', 'CY', '2030', '', NULL, '2025-04-29 14:38:56', 'ana bodddyyyy'),
(6, 8, 'dr hadad', 'UNIMAS', 'CY', '2029', '', NULL, '2025-04-29 15:02:06', ''),
(7, 9, 'abdo awy awy', 'UMS', 'CY', '2029', '01112233475', NULL, '2025-05-21 13:25:36', 'aaaaaaaa');

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `comments` text DEFAULT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `feedback` text DEFAULT NULL,
  `is_best` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`id`, `task_id`, `student_id`, `file_path`, `comments`, `status`, `feedback`, `is_best`, `created_at`) VALUES
(1, 1, 1, '/externit-final/uploads/submissions/submission_6809bddd5ad42_1745468893.pdf', 'jmcvjgvgn', 'accepted', 'asdads', 1, '2025-04-24 04:28:13'),
(2, 2, 1, '/externit-final/uploads/submissions/submission_6809c295ec494_1745470101.pdf', '16351', 'accepted', 'uh', 0, '2025-04-24 04:48:21'),
(3, 3, 1, '/externit-final/uploads/submissions/submission_680aaafad302b_1745529594.pdf', 'its good\r\n', 'rejected', 'asdds', 0, '2025-04-24 21:19:54'),
(4, 3, 2, '/externit-final/uploads/submissions/submission_680b7882bb3a0_1745582210.pdf', 'sgd', 'pending', NULL, 0, '2025-04-25 11:56:50'),
(5, 4, 1, '/externit-final/uploads/submissions/submission_680cba53c5bd5_1745664595.pdf', 'zdsdddfs', 'accepted', 'fdsddfssf', 1, '2025-04-26 10:49:55'),
(6, 5, 1, '/externit-final/uploads/submissions/submission_680cd8b07e587_1745672368.pdf', 'wedffwfwfe', 'accepted', 'ehsta', 0, '2025-04-26 12:59:28'),
(7, 5, 4, '/externit-final/uploads/submissions/submission_680cee1075b40_1745677840.pdf', 'sahdhdahsdhasdhsadhashdas', 'accepted', 'enta kwayes gedan', 0, '2025-04-26 14:30:40'),
(8, 6, 1, '/externit-final/uploads/submissions/submission_680cf05506622_1745678421.pdf', 'mananana', 'accepted', 'ksjabkads', 0, '2025-04-26 14:40:21'),
(9, 6, 4, '/externit-final/uploads/submissions/submission_680cf18e40e7e_1745678734.pdf', 'cxzxzxxzcxc', 'accepted', 'asasdds', 1, '2025-04-26 14:45:34'),
(10, 7, 1, '/externit-final/uploads/submissions/submission_680cf2d0da37b_1745679056.pdf', 'xzxxca', 'accepted', 'nice', 1, '2025-04-26 14:50:56'),
(11, 8, 1, '/externit-final/uploads/submissions/submission_680d2047bb339_1745690695.pdf', 'ana 5alst', 'accepted', 'nice', 1, '2025-04-26 18:04:55'),
(12, 9, 5, '/externit-final/uploads/submissions/submission_6810e4aa589e6_1745937578.docx', 'hiiii', 'accepted', 'm4 hiiii', 0, '2025-04-29 14:39:38'),
(13, 9, 6, '/externit-final/uploads/submissions/submission_6810ea1653360_1745938966.docx', 'dsdsdsdsf', 'accepted', 'hkjhjhjh', 1, '2025-04-29 15:02:46'),
(14, 10, 1, '/externit-final/uploads/submissions/submission_682dd90250922_1747835138.docx', 'gag', 'pending', NULL, 0, '2025-05-21 13:45:38'),
(15, 10, 3, 'uploads/submissions/submission_682dda813b4dd_1747835521.docx', 'mana', 'accepted', 'nice', 1, '2025-05-21 13:52:01');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `difficulty` enum('beginner','intermediate','advanced','expert') NOT NULL DEFAULT 'beginner',
  `deadline` date NOT NULL,
  `budget` decimal(10,2) NOT NULL,
  `estimated_hours` int(11) NOT NULL DEFAULT 1,
  `max_submissions` int(11) NOT NULL DEFAULT 100 COMMENT 'Maximum number of students allowed to submit their contributions for this task',
  `deliverables` text NOT NULL,
  `template_file_path` varchar(255) DEFAULT NULL,
  `status` enum('active','closed','completed') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `company_id`, `title`, `description`, `difficulty`, `deadline`, `budget`, `estimated_hours`, `max_submissions`, `deliverables`, `template_file_path`, `status`, `created_at`) VALUES
(1, 1, 'mana', 'asfasdfs', 'beginner', '2025-10-12', 2000.00, 5, 10, 'sadfsdffd', NULL, 'active', '2025-04-24 04:27:23'),
(2, 1, 'mmmm', '3213', 'intermediate', '2025-12-05', 2000.00, 50, 20, '65416', NULL, 'active', '2025-04-24 04:47:56'),
(3, 1, 'Design a Mascot for a Fictional Space Agency', 'Create a fun and engaging cartoon mascot for a fictional space agency called "Galaxy Explorers United (GEU)." The mascot should be suitable for branding across social media, merchandise, and educational content for kids aged 8–12. Think colorful, creative, and space-themed — maybe an alien, robot, or space critter!', 'beginner', '2025-05-05', 1500.00, 8, 15, 'A digital illustration of the mascot (JPG or PNG)\r\nA short paragraph describing the mascot's personality and story\r\nOptional: a version with the GEU logo integrated', NULL, 'active', '2025-04-24 21:19:11'),
(4, 1, 'Create a Jingle for a Pet Food Brand', 'Write and produce a short (20–30 second) jingle for a fictional pet food brand called "PawPal." It should be catchy, fun, and memorable, appealing to both pet owners and kids. You can use vocals, instruments, or digital tools.', 'intermediate', '2025-05-05', 500.00, 7, 10, 'An audio file (MP3 or WAV)\r\n\r\nLyrics as a text document\r\n\r\nA short note explaining your musical choices', NULL, 'active', '2025-04-24 22:44:12'),
(5, 1, 'agaga', 'asdassad', 'beginner', '2025-05-02', 1000.00, 3, 10, 'asdasadads', NULL, 'active', '2025-04-26 12:58:28'),
(6, 1, 'task w keda', 'asdasdasd', 'beginner', '2025-12-02', 2000.00, 6, 10, 'asdassad', NULL, 'active', '2025-04-26 14:39:22'),
(7, 1, 'gaaaggg', 'asdsasad', 'intermediate', '2025-12-05', 2000.00, 4, 6, 'asdasdads', NULL, 'active', '2025-04-26 14:48:07'),
(8, 1, '3oobaad', 'asddsasdasads', 'intermediate', '2025-06-05', 1000.00, 10, 10, 'asadsds', NULL, 'active', '2025-04-26 18:04:18'),
(9, 1, 'ccc', 'gfdsa', 'advanced', '2025-05-07', 100.00, 120, 11, 'vsa', NULL, 'active', '2025-04-29 12:26:25'),
(10, 1, 'abdo2', 'gag', 'expert', '2025-12-06', 1000.00, 12, 10, 'gagga', 'uploads/templates/template_682dd87817bf7.xlsx', 'active', '2025-05-21 13:43:20');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('student','company','admin') NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `user_type`, `photo`, `created_at`) VALUES
(1, 'Abdelrahmanmana155@gmail.com', '$2y$10$hPeKkHmy.v0WyPod8BdvI.WgTgsPVvkC7BGqciJwEyqwuGIWAq3ES', 'student', 'uploads/profile_photos/profile_6809c28aec5b6.png', '2025-04-24 04:26:23'),
(2, 'mostafa.shrief19@gmail.com', '$2y$10$a8atzfr45..oEmRMwbTFluuKWk5oxTszDR.SUqMzlahpXWtKRQBEC', 'company', 'uploads/profile_photos/profile_6809c2a87e2f4.png', '2025-04-24 04:27:00'),
(3, 'abdelrahmanmana79@gmail.com', '$2y$10$fim0Egt4v.imY2a5G/97fuuQ76jXv6AdDk/3ybDs.ua8TtcBXhCmW', 'student', 'uploads/profile_photos/profile_680b7a3f9f0b8.png', '2025-04-25 11:54:58'),
(4, 'abdelrahmangithub17@gmail.com', '$2y$10$KbInU/CVRmQwR4jkSz43Duqdn/kY4kMmavBhh3WTPdFj/BHG5K5MW', 'student', NULL, '2025-04-25 13:13:13'),
(5, 'ya.wga3elgrad@gmail.com', '$2y$10$gyBDWvxy4wPPDEFNv3dshuiomB/bTFmDc1PW50riKqM3OBXJLiJmC', 'student', 'uploads/profile_photos/profile_680cedeb21879.png', '2025-04-26 14:29:41'),
(6, 'fatimaamohammed80@gmail.com', '$2y$10$Vaym4HLg7Xh5/ENgLybDrOj0zDP0otFNEUXxVw6TceV3wJ8Dy9nUu', 'company', NULL, '2025-04-26 16:24:20'),
(7, 'boody@gmail.com', '$2y$10$yWNnOtbK1onJ7ftb/8gcPeIozAibO2hf5oMS2VPC4MPs/AH/oEXAS', 'student', 'uploads/profile_photos/profile_6810e497b9f1a.jpg', '2025-04-29 14:38:56'),
(8, 'drhadad@gmail.com', '$2y$10$.8Cf6d2X4yc/F.OdJd9jK.9fElWFnyBl22fKlLilFBU7yOT3pVXN.', 'student', 'uploads/profile_photos/profile_6810ea00bb345.png', '2025-04-29 15:02:06'),
(9, 'abdoawy@gmail.com', '$2y$10$JX9C2Kky1EeE03WWgG78quHtvqz5hqvnWE.5eeokUiNB.CbY4p63u', 'student', 'uploads/profile_photos/profile_682dd47bf32cd.png', '2025-05-21 13:25:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certificate_number` (`certificate_number`),
  ADD KEY `submission_id` (`submission_id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `certificate_templates`
--
ALTER TABLE `certificate_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `certificate_id` (`certificate_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `certificate_templates`
--
ALTER TABLE `certificate_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_ibfk_4` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `certificate_templates`
--
ALTER TABLE `certificate_templates`
  ADD CONSTRAINT `certificate_templates_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `companies`
--
ALTER TABLE `companies`
  ADD CONSTRAINT `companies_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `