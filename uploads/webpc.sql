SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Bảng Admin
--
CREATE TABLE `admins` (
  `id_admin` VARCHAR(20) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `otp_code` VARCHAR(6) DEFAULT NULL,
  `otp_expiry` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dữ liệu cho bảng `admins`
--

INSERT INTO `admins` (`id_admin`, `name`, `password`, `email`, `otp_code`, `otp_expiry`) VALUES
('AD001', 'Nguyễn Thị Mỹ Hiền', '12345', 'yhiennguyeny@gmail.com', NULL, NULL),
('AD002', 'admin2', '12345','admin2@gmail.com', NULL, NULL),
('AD003', 'admin3', '12345','admin3@gmail.com', NULL, NULL);

--
-- Bảng Loại sản phẩm
--
CREATE TABLE `loaisanpham` (
  `id_loaisp` VARCHAR(20) NOT NULL,
  `ten_loaisp` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id_loaisp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dữ liệu cho bảng `loaisanpham`
INSERT INTO `loaisanpham` (`id_loaisp`, `ten_loaisp`) VALUES
('LSP001', 'Bàn phím'),
('LSP002', 'Chuột'),
('LSP003', 'Tai nghe'),
('LSP004', 'Bàn'),
('LSP005', 'Ghế'),
('LSP006', 'Màn hình KM'),
('LSP007', 'Màn hình Gaming'),
('LSP008', 'Màn hình VP'),
('LSP009', 'Màn hình Đồ họa'),
('LSP010', 'VGA'),
('LSP011', 'CPU'),
('LSP012', 'Mainboard'),
('LSP013', 'RAM'),
('LSP014', 'SSD');

--
-- Bảng Sản phẩm
--
CREATE TABLE `sanpham` (
  `id_sanpham` VARCHAR(20) NOT NULL,
  `ten_sp` VARCHAR(255) NOT NULL,
  `gia` INT(11) NOT NULL,
  `mo_ta` TEXT,
  `thong_so_ky_thuat` TEXT,
  `sl` INT(11) NOT NULL,
  `id_loai` VARCHAR(20) NOT NULL,
  `hinh_anh` VARCHAR(255),
  PRIMARY KEY (`id_sanpham`),
  FOREIGN KEY (`id_loai`) REFERENCES `loaisanpham`(`id_loaisp`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dữ liệu cho bảng `sanpham`
INSERT INTO `sanpham` (`id_sanpham`, `ten_sp`, `gia`, `mo_ta`, `thong_so_ky_thuat`, `sl`, `id_loai`, `hinh_anh`) VALUES
('SP001', 'Bàn phím cơ Akko 5075B', 1150000,
'Bạn đang tìm kiếm một chiếc bàn phím cơ với thiết kế hiện đại, hiệu suất mạnh mẽ cùng trải nghiệm gõ phím “êm tai” khó quên?
Akko 5075B chính là sự lựa chọn hoàn hảo. Với switch Cream Yellow Pro, LED RGB sống động, và khả năng kết nối linh hoạt, 
sản phẩm này đáp ứng mọi nhu cầu từ công việc văn phòng đến giải trí.

Đặc điểm nổi bật của Akko 5075B
Thiết kế Layout 75% có núm: Tối ưu không gian nhưng vẫn đầy đủ chức năng.
LED RGB sống động: Hiệu ứng nháy theo nhạc và LED viền ấn tượng, tạo nên trải nghiệm thị giác độc đáo.
Gasket mount: Đảm bảo âm gõ đều, êm tai trên tất cả các hàng phím.
Keycap ASA profile, PBT Double-Shot: Độ bền cao, xuyên LED tốt, không phai chữ theo thời gian.
Switch Cream Yellow Pro: Tối ưu cảm giác gõ mượt mà, độ bền cao.', 

'Model:	5075B Plus Horizon (Layout 75%) Chip Beken Plus
Switch:	AKKO Switch v3 (Cream Yellow Pro)
Keycap:	ASA profile, PBT Double-Shot, xuyên LED
LED	RGB: (16 triệu màu) với LED viền
Kết nối:	Dây / Bluetooth 5.0 / 2.4Ghz
Pin:	3000mAh (Tiêu thụ 12ma / giờ ở chế độ không dây)
Kích thước:	335*146*42mm | Nặng ~ 1.1Kg
Phần mềm:	AKKO Cloud Driver
Tương thích:	Windows / MacOS / Linux', 
10, 'LSP001', 'assets/images/banphim1.jpg'),

('SP002', 'Bàn phím cơ Xinmeng M75 RGB', 1290000,
'Bạn đang tìm kiếm một chiếc bàn phím cơ giá tốt, thiết kế thời thượng với đầy đủ tính năng hiện đại và trải nghiệm gõ mượt mà, yên tĩnh?
Xinmeng M75 RGB sẽ là người bạn đồng hành lý tưởng. Với layout nhỏ gọn 75%, kết nối 3 chế độ, đèn LED RGB sống động và thời lượng pin ấn tượng,
sản phẩm phù hợp cho cả học tập, làm việc lẫn giải trí.',

'Trọng lượng: 1300g
Số lượng phím: 75 phím
Tính năng: Có núm xoay
LED: LED RGB và LED viền
Keycap: PBT 2 Shot
Màn hình: TFT tùy chỉnh
Custom switch: Có
Kích thước bàn phím: 37 x 15 x 3,5 cm
Kết nối: 3 kiểu kết nối: USB Type-C, Bluetooth, Wireless
Foam: Lót sẵn foam PCB và foam đáy bằng poron
Dung lượng pin: 5000 mAh
Thiết kế mạch: Mạch xuôi | Gasket mount
Phụ kiện tặng kèm: Dây cáp, nắp che bụi, dụng cụ nhổ switch và keycap, sách hướng dẫn sử dụng, bộ keycap
Hỗ trợ hệ điều hành: Windows XP/Vista/7/8/10/11, Android, Linux, macOS',
14, 'LSP001', 'assets/images/banphim2.jpg'),
('SP003', 'Bàn phím cơ Dare-U EK75 White - Black', 990000, 
'Đặc điểm nổi bật:
Kết nối đa chế độ: Bluetooth, Wireless 2.4GHz và Type-C có dây, linh hoạt sử dụng.
Cảm giác gõ êm ái, ổn định với cấu trúc Gasket Mount và switch DareU Dream.
Thiết kế hiện đại, nhỏ gọn 75% với chất liệu cao cấp, bền bỉ.
Tuỳ chỉnh cao với đèn nền RGB, nút xoay đa chức năng và hỗ trợ hot-swap switch.',
'Thương hiệu: Dare-U
Model: EK75 Grey Black
Loại bàn phím: Cơ học, Gasket-Mount
Switch: DareU Dream (Linear)
Keycaps: PBT Double-shot
Hệ thống LED: Multi LED + RGB 2 cạnh
Số phím: 81 phím
Kết nối: Type-C, dây rời (1.8m)
Kích thước: 333 x 140 x 40mm
Trọng lượng: 650g
N-key rollover: Yes',
22, 'LSP001', 'assets/images/banphim3.webp'),
('SP004', 'Bàn phím Gaming ASUS TUF K3 Gen 2', 759000,
'Đặc điểm nổi bật:

Switch quang học đỏ cho trải nghiệm gõ êm ái, phản hồi nhanh.
Thiết kế 96% nhỏ gọn, tiết kiệm không gian.
Đèn nền RGB cá nhân hóa từng phím, hiệu ứng Aura Sync sống động.
Khả năng chống bóng ma N-Key Rollover, đảm bảo độ chính xác cao trong game.',
'Thương hiệu:	ASUS
Kích thước:	385 x 153 x 38 mm
Trọng lượng:	1008g (With Cable)
Loại bàn phím:	Phím cơ
Loại switch:	Optical-Mechanical RGB Switch (Red)
Layout:	96% (Không có cụm phím số bên phải)
Số phím:	Đang cập nhật
Chất liệu keycaps:	Không được nêu cụ thể trong thông số.
Đèn nền:	RGB Per keys, Aura Sync
Kết nối:	USB 2.0 (Type A)',
5, 'LSP001', 'assets/images/banphim5.webp'),
('SP005', 'Chuột Gaming Không Dây Logitech G304', 729000,
'Đặc điểm nổi bật:

Thiết kế nhỏ gọn, nhẹ, phù hợp với nhiều kích cỡ tay.
Kết nối Lightspeed không dây ổn định, độ trễ thấp.
Cảm biến HERO cho hiệu suất chính xác, độ nhạy cao.
Tuổi thọ pin lên đến 250 giờ, sử dụng thoải mái trong thời gian dài.',
'Thương hiệu: Logitech
Kích thước: 116.6 x 62.15 x 38.2 mm 
Trọng lượng: 99g
Màu sắc: Đen
Tốc độ phản hồi: 1ms
Loại chuột: Chuột gaming 
Cảm biến: HERO 
Độ phân giải: 200 ~ 12000 DPI
Tốc độ tối đa: > 400 IPS
Số nút: 6 nút',
14, 'LSP002', 'assets/images/chuot1.jpg'),
('SP006', 'Chuột Logitech G102 Gen II Lightsync', 388000,
'Đặc điểm nổi bật:

Thiết kế hiện đại, nhỏ gọn, phù hợp với nhiều kích cỡ tay.
Cảm biến quang học cho độ nhạy cao, hiệu suất ổn định.
Độ nhạy lên đến 8.000 DPI, đáp ứng nhu cầu chơi game của người dùng.
Tương thích với hầu hết các hệ điều hành, phù hợp với nhiều đối tượng làm việc lẫn chơi game.',
'Thương hiệu: Logitech
Kích thước: 116.6 x 62.15 x 38.2mm
Trọng lượng: 85g
Màu sắc: Đen
Tần số phản hồi: 1000Hz
Loại chuột: Chuột gaming
Cảm biến: Quang học
DPI: 200 - 8000 DPI
Số nút: 6 nút
Kết nối: USB có dây',
27, 'LSP002', 'assets/images/chuot2.jpg'),
('SP007', 'Chuột văn phòng Logitech MX Anywhere 3S Bluetooth', 1260000,
'Đặc điểm nổi bật:

Cảm biến 8K DPI cho độ chính xác cao trên nhiều bề mặt, kể cả kính.
Công nghệ Magspeed cho cuộn siêu nhanh và chính xác.
Quiet Clicks giảm tiếng ồn click, tạo không gian làm việc yên tĩnh.
Kết nối Bluetooth đa thiết bị, sạc nhanh USB-C, pin lên đến 70 ngày.',
'Thương hiệu: Logitech
Loại chuột: Chuột văn phòng
Kích thước: Chiều cao: 100,5 mm; Chiều rộng: 65 mm; Chiều dày: 34,4 mm
Trọng lượng: 99 g
Màu	Đen: Graphite
Chiều dài cáp: Không áp dụng (chuột không dây)
Cảm biến: Darkfield có độ chính xác cao
Độ nhạy: 200 - 8000 DPI
Đèn LED: Không có
Số nút: 6 nút (Nhấp chuột Trái/Phải, Tiếp theo/Quay lại, Chuyển chế độ nút cuộn, Nhấp chuột giữa)
Kết nối: Bluetooth, Logi Bolt USB (không bao gồm đầu thu)
Tương thích: Windows, macOS, iPadOS, ChromeOS, Linux
Tính năng khác: Cuộn MagSpeed SmartShift, Sạc nhanh qua USB-C, Tùy chỉnh nút với Logi Options+, Khả năng Quiet Clicks',
22, 'LSP002', 'assets/images/chuot3.jpg'),
('SP008', 'Chuột gaming Attack Shark X3', 760000,
'Đặc điểm nổi bật:

Kết nối đa chế độ: Bluetooth, 2.4GHz Wireless và Wired USB.
Cảm biến Pixart 3395: Độ chính xác cao, phản hồi nhanh, DPI lên đến 26000.
Thiết kế siêu nhẹ 50g: Thoải mái sử dụng, giảm mỏi tay.
Switch Kailh Micro: Độ bền cao, lên đến 80 triệu lần nhấn.',
'Thương hiệu: Attack Shark
Loại chuột: Gaming
Kích thước: Đang cập nhật
Trọng lượng: ~50g
Màu: Đen (Black)
Chiều dài cáp: Đang cập nhật
Cảm biến: Pixart 3395DM
Độ nhạy: Đang cập nhật
Tần số phản hồi: Đang cập nhật
Độ bền phím (L/R Click): 80 triệu lần nhấn (Kailh Micro Switches)
DPI: Tối đa 26000
Đèn LED: Không được cung cấp
Số nút: 7
Kết nối: Wireless (Bluetooth/2.4GHz), Wired USB
Tương thích: WinXP/Win7/Win8/Win10/Win11/macOS
Tính năng khác: Lập trình Macro, Pin Lithium 300mAh, Con lăn TTC, Cáp Type-C bọc dù siêu nhẹ, chống nhiễu, đầu cáp mạ vàng, Phần mềm điều khiển',
17, 'LSP002', 'assets/images/chuot4.jpg'),
('SP009', 'Tai nghe Gaming Logitech Lightspeed G435 Black', 1389000, 
'Đặc điểm nổi bật:

Thiết kế nhẹ, thoải mái với chất liệu nhựa tái chế thân thiện môi trường.
Âm thanh chất lượng cao, tương thích với nhiều nền tảng và công nghệ âm thanh vòm.
Micro kép tạo chùm tích hợp cho khả năng thu âm rõ ràng, khử tiếng ồn hiệu quả.
Kết nối không dây đa năng với LIGHTSPEED và Bluetooth, thời lượng pin dài 18 giờ.',
'Thương hiệu: Logitech
Kiểu tai nghe: Over-ear (trùm tai)
Drive: 40 mm
Micro: Hai micro tạo chùm tích hợp
Kết nối: LIGHTSPEED không dây, Bluetooth
Tương thích: Windows® 10 trở lên, macOS X 10.14 trở lên, PlayStation 5, PlayStation 4, Nintendo Switch
Phím điều khiển: Đang cập nhật
Dây cáp: USB-A tới USB-C (dùng để sạc)
Trọng lượng: 165 g
Màu sắc: Đen (Black)
Tính năng khác: Tương thích Dolby Atmos, Tempest 3D AudioTech, Windows Sonic Spatial Sound, Nhựa tái chế ít nhất 22%, Thời lượng pin 18 giờ, Được chứng nhận CarbonNeutral',
15, 'LSP003', 'assets/images/tainghe1.webp'),
('SP010', 'Tai nghe Gaming Dare-U EH930 | Đen', 1190000,
'Đặc điểm nổi bật:

Thiết kế over-ear mới mẻ, tạo cảm giác thoải mái ngay cả khi sử dụng trong thời gian dài.
Chất lượng âm thanh vượt trội với driver 53mm, hỗ trợ âm thanh 7.1 giả lập chân thực.
Tích hợp công nghệ điều khiển âm thanh BBH621 với tần số lấy mẫu 96KHz và bitrate 24-bit.
Hệ thống LED RGB tinh tế, tạo phong cách cá nhân độc đáo khi chơi game.',
'Thương hiệu: DareU
Kiểu tai nghe: Over-ear
Màu sắc: Đen
Driver: Φ53 mm
Tần số lấy mẫu: 96KHz
LED: RGB
Kết nối: USB
Trọng lượng: 360 ± 10 g',
30, 'LSP003', 'assets/images/tainghe2.webp'),
('SP011', 'Tai Nghe Gaming Logitech G Pro X', 450000,
'Đặc điểm nổi bật:

Âm thanh đỉnh cao: Màng loa PRO-G 50mm tái tạo âm thanh chân thực.
Thiết kế chuyên nghiệp: Chất liệu thép và nhôm bền bỉ, kiểu dáng thể thao, ôm sát tai.
Micro chất lượng cao: Blue VO!CE thu âm rõ ràng, hạn chế tạp âm, tắt tiếng tiện lợi.
EQ tinh chỉnh chuyên nghiệp: Tùy chỉnh âm thanh theo sở thích, mang đến trải nghiệm tối ưu.',
'Hãng sản xuất: Logitech G
Thiết kế: Chụp tai
Kết nối: Giắc 3.5mm + USB Sound Card
Độ nhạy: 91,7 dB SPL @ 1 mW & 1 cm
Micro: Có (Tháo rời)
Độ nhạy tần số: 20 Hz-20 KHz
Miếng đệm tai nghe và quai đeo: Gỉa da bằng cao su non',
5, 'LSP003', 'assets/images/tainghe3.webp'),
('SP012', 'Tai nghe không dây Logitech G733 LightSpeed Wireless Gaming (Đen)', 650000,
'Đặc điểm nổi bật:

Trọng lượng siêu nhẹ, mang lại cảm giác thoải mái khi sử dụng trong thời gian dài.
Âm thanh chất lượng cao, sống động, giúp game thủ đắm chìm trong thế giới game.
Kết nối không dây LIGHTSPEED tự do di chuyển mà không lo bị gián đoạn tín hiệu.
Thời lượng pin ấn tượng cho phép chơi game trong thời gian dài mà không cần sạc pin thường xuyên.',
'Thương hiệu: Logitech
Kích thước: 194 x 190 x 83 mm
Trọng lượng: 278 g
Màu sắc: Đen
Loại tai nghe: Tai nghe gaming
Kiểu tai nghe: Over-ear
Độ nhạy tần số: 20 Hz - 20 kHz
Độ nhạy: 87,5 dB SPL/mW
LED: RGB
Kết nối: LIGHTSPEED không dây qua USB - 20m
Micro: Có (có thể tháo rời)
Thời lượng pin: Không chiếu sáng: 29 giờ, Chiếu sáng mặc định: 20 giờ',
37, 'LSP003', 'assets/images/tainghe4.webp'),
('SP013', 'Bàn nâng hạ Cougar E-Star 140 - Electric Gaming Desk', 4000000, 
'Chân đế màn hình độc lập tối đa hóa không gian chức năng.
Cá nhân hóa cài đặt độ cao ưa thích bằng điều chỉnh cơ giới với 2 cài đặt sẵn trong bộ nhớ.
Khay quản lý cáp và móc treo tai nghe giúp việc thiết lập không bị lộn xộn.
Tấm laminate có kết cấu sợi carbon cải thiện độ bền bề mặt.
Bàn di chuột toàn bàn đảm bảo hoàn toàn tự do di chuyển.',
'Tên sản phẩm: Bàn Gaming Điện E-STAR 140
Kích thước mặt bàn: 1400 x 600 x 15 mm
Chiều cao mặt bàn điều chỉnh: 720~1170 mm
Tấm lót chuột toàn mặt bàn: Có
Giá đỡ màn hình có thể tháo rời: Có
Tấm chắn trục truyền động và động cơ: Có
Khay quản lý dây cáp: Có
Móc treo tai nghe: Có
Tổng trọng lượng chịu tải của mặt bàn: 60 kg
Trọng lượng chịu tải độc lập của giá đỡ màn hình (bao gồm trong tổng tải): 15 kg',
9, 'LSP004', 'assets/images/ban1.webp'),
('SP014', 'Bàn văn phòng HyperWork Core Desk', 2000000,
'Đặc điểm nổi bật:

Dễ dàng tháo lắp và điều chỉnh kích thước.
Mặt bàn rộng rãi, thoải mái cho công việc.
Khung thép chắc chắn, sơn tĩnh điện chống gỉ sét.
Tích hợp dễ dàng với các sản phẩm khác của HyperWork.',
'Hãng sản xuất: HyperWork
Kích thước (dài x rộng x cao): Khung bàn: 103.5cm - 135.5cm x 60cm x 72.5cm, Mặt bàn: 120cm / 140cm x 60cm x 2.5cm
Chất liệu: Khung bàn: Thép, sơn tĩnh điện, Mặt bàn: Gỗ MDF
Màu sắc: Đen
Tải trọng tối đa: 80 kg
Khay bàn phím: Không
Khay chuột: Không
Khe cắm tai nghe: Không
Kệ CPU: Không
Tính năng khác: Dễ dàng tháo lắp, điều chỉnh kích thước, Tích hợp với hệ sinh thái sản phẩm HyperWork (ngăn kéo, giá đỡ màn hình, ...)',
3, 'LSP004', 'assets/images/ban2.webp'),
('SP015', 'Bàn nâng hạ Warrior - Paladin Series – WGT605', 3000000,
'Ưu điểm nổi bật của bàn nâng hạ WARRIOR – Paladin Series – WGT605
Thiết kế tinh tế và chất liệu cao cấp
Bàn được trang bị mặt bàn hai mảnh làm từ sợi carbon P2PB dày 13mm, phủ vân carbon màu đen sang trọng. 
Kích thước rộng rãi 1400mm x 600mm, phù hợp với nhiều thiết lập máy tính và thiết bị văn phòng. 
Chân bàn chữ T làm bằng sắt chắc chắn, đảm bảo độ ổn định vượt trội.

Điều chỉnh độ cao linh hoạt và thông minh
Bàn nâng hạ điện cho phép điều chỉnh độ cao từ 72cm đến 117cm chỉ với một nút bấm. 
Chức năng ghi nhớ chiều cao thông minh giúp bạn chuyển đổi nhanh giữa các tư thế làm việc, tiết kiệm thời gian và tăng hiệu quả.

Tính năng tiện ích hỗ trợ tối đa
Mặt bàn được thiết kế với lỗ đi dây và kẹp gọn dây, giữ không gian làm việc gọn gàng. 
Ngoài ra, bàn còn đi kèm giá treo tai nghe và giá để cốc, mang lại sự tiện lợi tối đa cho người dùng.

Lợi ích sức khỏe và hiệu suất làm việc
Bàn nâng hạ WARRIOR không chỉ cải thiện tư thế làm việc mà còn tăng cường lưu thông máu, giảm căng thẳng cơ xương và nguy cơ đau lưng do ngồi lâu. 
Đây là giải pháp lý tưởng giúp bạn làm việc hiệu quả hơn, thoải mái hơn mỗi ngày.',
'Mặt bàn: Hai mảnh, sợi carbon P2PB dày 13mm, phủ vân carbon
Kích thước: 1400mm x 600mm
Chiều cao điều chỉnh: 72cm – 117cm
Tải trọng tối đa: 90kg
Chân bàn: Chữ T, sắt vững chắc
Phụ kiện đi kèm: Giá treo tai nghe, Giá để cốc
Tiện ích: Lỗ đi dây, kẹp gọn dây',
2, 'LSP004', 'assets/images/ban3.webp'),
('SP016', 'Bàn văn phòng HyperWork Core Desk', 2500000, 
'Mặt bàn MDF - 2,5 cm
Khung bàn thép
Tháo lắp nhanh chóng, bảo toàn chất lượng
Đóng gói nhỏ gọn, thông minh
Thiết kế nhiều tiện ích đi kèm: Khe vát cạnh bàn, Lỗ khoan sẵn cho phụ kiện, Chân đế tăng chỉnh, Phù hợp người dùng cá nhân, doanh nghiệp, văn phòng lưu động
Dễ dàng tháo và lắp đặt nhờ phương pháp bắt ốc vít với lỗ khoan có sẵn
Tháo lắp nhiều lần vẫn không ảnh hưởng kết cấu, có thể tái sử dụng liên tục
Mặt bàn gỗ MDF dày dặn, khung bàn thép chắc chắn, sơn tĩnh điện công nghệ cao.',
'Chiều cao khung bàn: 72,5 cm
Chiều dài khung bàn: 103,5 cm ~ 135,5 cm
Mặt bàn gỗ MDF: 120 x 60 x 2,5 cm hoặc 140 x 60 x 2,5 cm
Khung bàn: Thép, sơn tĩnh điện
Tải trọng: 80 kg
Màu sắc: Đen | Trắng',
1, 'LSP004', 'assets/images/ban4.webp'),
('SP017', 'Ghế Gaming SaveM LK-2269T | Đen đỏ', 2000000, 
'Đặc điểm nổi bật:

Thiết kế thể thao, đậm chất gaming
Chất liệu da PU cao cấp, bền bỉ
Góc ngả lưng linh hoạt đến 180 độ
Chân ghế xoay 360 độ mượt mà, êm ái',
'Hãng sản xuất: SaveM (OEM)
Loại ghế: Ghế xoay gaming
Chất liệu: Da cao cấp PU
Tải trọng tối đa: 120 kg
Cao độ ghế: 90 ± 5 mm
Góc ngả lưng: 180 độ
Góc xoay: 360 độ
Tay vịn: Cố định
Kích thước (dài x rộng x cao): 670 x 530 x 1250 mm
Tính năng khác: Khung, chân nhựa, bánh xe được thiết kế không gây tiếng ồn, Có trang bị đệm kê chân.',
19, 'LSP005', 'assets/images/ghe1.webp'),
('SP018', 'Ghế văn phòng 7057 | Màu đen, chân xoay kim loại', 990000,
'Ghế văn phòng 7057 là một sản phẩm được thiết kế tinh tế và kỹ lưỡng, đáp ứng nhu cầu tối đa cho người dùng trong quá trình làm việc hoặc nghỉ ngơi.',
'Chất liệu: Da PU cao cấp, chống thấm và dễ vệ sinh
Chân ghế: Kim loại chắc chắn với bánh xe xoay 360°
Góc ngả lưng: 130° có thể khóa
Khả năng chịu tải: 250 lbs (113 kg)
Kích thước ghế: 21"x20.5"
Kích thước tựa lưng: 21"x27.5"
Trọng lượng: 35 lbs', 
8, 'LSP005', 'assets/images/ghe2.webp'),
('SP019', 'Ghế công thái học TSA07 | Đen', 1650000,
'Ghế công thái học TSA07 là dòng ghế làm việc cao cấp thiết kế theo tiêu chuẩn công thái học, hỗ trợ tư thế ngồi đúng cách, giảm áp lực lên cột sống.
Ghế trang bị lưng lưới thoáng khí, tựa đầu và tay vịn điều chỉnh, đệm ngồi êm ái, phù hợp cho làm việc văn phòng hoặc học tập lâu dài.',
'Kích thước:
Chiều cao mặt ngồi: 44-54cm
Chiều cao từ mặt đất đến đỉnh đầu: 116-126cm
Rộng đệm, sâu đệm: 50cm
Khoảng cách giữa 2 tay: 60cm
Chiều dài tay ghế: 45cm.
Chất liệu:
Khung ghế làm từ nhựa PP cao cấp
Tựa lưng lưới đàn hồi tốt
Đệm mút bọc lưới
Piston thủy lực
Có thêm tựa lưng
Chân sao nhựa đúc nguyên khối
Màu: Đen', 11, 'LSP005', 'assets/images/ghe3.webp'),
('SP020', 'Ghế công thái học Ergonomic TMS08 | Đen', 1300000, 
'Ghế công thái học Ergonomic TMS08 được thiết kế hiện đại theo tiêu chuẩn công thái học, hỗ trợ tư thế ngồi chuẩn, giảm mỏi cổ, lưng và vai khi làm việc lâu. 
Ghế sở hữu lưng lưới thoáng khí, đệm ngồi êm, tay vịn và tựa đầu điều chỉnh linh hoạt, phù hợp cho môi trường văn phòng chuyên nghiệp.',
'Chiều cao mặt ngồi: 44-54cm
Chiều cao từ mặt đất đến đỉnh đầu: 125-135cm
Rộng đệm, sâu đệm: 50cm
Khoảng cách giữa 2 tay: 60cm
Chiều dài tay ghế: 45cm
Khung ghế làm từ nhựa PP cao cấp
Tay ghế cố định
Tựa lưng lưới đàn hồi tốt
Đệm mút bọc lưới
Piston thủy lực
Chân sao nhựa đúc nguyên khối
Kê chân nhựa
Bánh xe cao su
Ngả 150 độ',
5, 'LSP005', 'assets/images/ghe4.webp'),
('SP021', 'Màn hình văn phòng Aivision A222FV', 1195000, 
'Đặc điểm nổi bật:

Màn hình 21.5 inch độ phân giải Full HD (1920x1080) sắc nét, sống động.
Tần số quét 100Hz mượt mà, giảm thiểu hiện tượng giật, lag.
Tấm nền VA cho màu sắc trung thực, góc nhìn rộng và độ tương phản cao.
Tích hợp nhiều tính năng bảo vệ mắt như Flicker-free và Low Blue Light.',
'Hãng sản xuất: Aivision
Kích thước: 21.5 inch
Cân nặng (Sản phẩm / Bao bì + Sản phẩm): 2.5 kg / 3 kg
Độ phân giải: 1920 x 1080
Tỉ lệ màn hình: 16:9
Tần số quét: 100Hz
Thời gian phản hồi: 5ms (GTG)',
17, 'LSP006', 'assets/images/manhinh1.webp'),
('SP022', 'Màn hình AOC 24G4E/74', 2695000,
'Đặc điểm nổi bật:

Màn hình 23.8 inch, Full HD, IPS, 180Hz, 1ms, phẳng.
Công nghệ Fast IPS và tốc độ phản hồi 1ms (GTG): Nâng cao hiệu suất chơi game với chất lượng hình ảnh sắc nét và màu sắc rực rỡ.
Tần số quét 180Hz: Đảm bảo mọi chuyển động đều mượt mà, không bị nhòe hay giật.
Thời gian phản hồi siêu nhanh 0.5ms (MPRT): Tối ưu hóa độ nhạy của màn hình, giảm thiểu độ trễ.
Giải pháp chống xé hình toàn diện: Tận hưởng hình ảnh mượt mà, không gián đoạn nhờ công nghệ Adaptive-Sync.', 
'Hãng sản xuất: AOC
Kích thước: 23.8 inch
Trọng lượng: Có chân đế: 3.49 kg, Không chân đế: 2.71 kg
Độ phân giải: 1920 x 1080 (Full HD)
Tần số quét: 180Hz
Thời gian phản hồi: 0.5ms (MPRT), 1ms (GtG)
Loại tấm nền: IPS
Độ sáng: 300 cd/m²
Tỉ lệ tương phản: 1000 : 1
Số lượng màu hiển thị: 16.7 triệu màu
Góc nhìn: 178° (ngang) / 178° (dọc)
Cổng kết nối: HDMI 2.0 x 1, DisplayPort 1.4 x 1
Nguồn điện: AC-DC nội bộ (Internal) 100 – 240V ~ 1.5A, 50/60Hz
Tính năng khác: HDR10, Adaptive Sync, AOC Low Input Lag, Công nghệ chống nháy (Flicker-Free)',
12, 'LSP006', 'assets/images/manhinh2.webp'),
('SP023', 'Màn hình văn phòng Acer EK241Y G', 2000000,
'Hiển thị sắc nét, chuẩn màu tuyệt vời
Với kích thước 23.8 inch và độ phân giải Full HD (1920x1080), Acer EK241Y G mang đến trải nghiệm hiển thị cực kỳ sắc nét.
Tấm nền IPS hỗ trợ độ phủ màu 99% sRGB, đảm bảo tái hiện màu sắc chân thực, phù hợp cho các công việc liên quan đến đồ họa, chỉnh sửa ảnh, hoặc thiết kế.

Tần số quét 120Hz - Trải nghiệm mượt mà hơn
Acer EK241Y G được trang bị tần số quét 120Hz và thời gian phản hồi 1ms (VRB), giúp mọi thao tác từ công việc đến giải trí đều diễn ra mượt mà, không giật lag.

Kết nối đa dạng, tiện lợi
Sản phẩm hỗ trợ các cổng kết nối phổ biến như VGA, HDMI và Audio, cho phép bạn dễ dàng kết nối với các thiết bị khác. 
Đi kèm theo đó là tính năng VESA Mount (100x100mm), giúp bạn tối ưu không gian làm việc.

Âm thanh tích hợp tiện dụng
Ngoài hình ảnh chất lượng, Acer EK241Y G còn tích hợp loa, mang lại sự tiện lợi cho các nhu cầu hội họp, xem video, hoặc giải trí nhẹ nhàng.',
'Kích thước màn hình: 23.8 inch (IPS)
Độ phân giải: 1920 x 1080 (Full HD)
Tần số quét: 120Hz
Thời gian phản hồi: 1ms (VRB)
Độ phủ màu: 99% sRGB
Kết nối: VGA, HDMI, Audio
Loa: Tích hợp
VESA Mount: 100x100mm', 
32, 'LSP006', 'assets/images/manhinh3.webp'),
('SP024', 'Màn hình ACER EK251Q G', 2090000,
'Màn hình ACER EK251Q G là màn hình 24.5 inch độ phân giải Full HD (1920 x 1080), trang bị tấm nền VA cho hình ảnh rõ nét, màu sắc trung thực và góc nhìn rộng. 
Với tần số quét 75Hz và thời gian phản hồi 5ms, màn hình đáp ứng tốt nhu cầu làm việc, học tập và giải trí cơ bản.
Thiết kế viền mỏng thanh lịch, hỗ trợ các cổng kết nối VGA và HDMI tiện lợi.',
'Màn hình 24.5 inch 16:9, độ phân giải FHD (1920×1080)
Tấm nền IPS 120Hz, 1ms
Chuẩn màu 99% sRGB
Độ sáng 250 nits
Tương thích AdaptiveSync
Cổng kết nối HDMI (1.4)', 
11, 'LSP006', 'assets/images/manhinh4.webp'),
('SP025', 'Màn hình Gaming LG 24GS60F-B.ATVQ', 3250000, 
'Đặc điểm nổi bật:

Màn hình 23.8 inch, Full HD, IPS, 180Hz, 1ms, Phẳng.
Tốc độ làm mới 180Hz siêu mượt: Mang đến trải nghiệm chơi game mượt mà, không xé hình, giật hình, giúp bạn phản ứng nhanh hơn và chiến thắng dễ dàng.
Công nghệ HDR10: Tái tạo màu sắc chân thực, rực rỡ, cho phép bạn chiêm ngưỡng thế giới game một cách sống động và ấn tượng.
Thời gian phản hồi 1ms (GtG): Loại bỏ hiện tượng bóng mờ, cho hình ảnh sắc nét, rõ ràng, không bị nhòe, giúp bạn theo dõi chuyển động của nhân vật một cách chính xác.
Thiết kế tối ưu cho game thủ: Viền màn hình siêu mỏng, chân đế có thể điều chỉnh độ nghiêng, giúp bạn thoải mái trải nghiệm game trong thời gian dài',
'Hãng sản xuất: LG
Kích thước: 23.8 inch
Trọng lượng: Có chân đế: 4 kg, Không chân đế: 3.5 kg
Độ phân giải: 1920 x 1080 (Full HD)
Tỉ lệ màn hình: 16:9
Tần số quét: 180Hz
Thời gian phản hồi: 1ms
Loại tấm nền: IPS
Độ sáng: 300 cd/m²
Tỉ lệ tương phản: 1000:1
Số lượng màu: 16.7 triệu màu
Góc nhìn: 178° (ngang) / 178° (dọc)
Cổng kết nối: 1 x HDMI, 1 x DisplayPort 1.4
Nguồn điện: 100 – 240V ~ 50/60Hz
Tính năng khác: FreeSync, HDR, Công nghệ chống nháy hình (Flicker-Free), Chế độ đọc sách (Reader Mode).',
5, 'LSP007', 'assets/images/manhinh5.webp'),
('SP026', 'Màn hình ASUS TUF Gaming VG249Q3A', 3190000,
'Hiển thị và Hiệu năng
Màn hình VG249Q3A có panel IPS cho góc nhìn rộng 178°/178°, giúp bạn có trải nghiệm hình ảnh chất lượng từ nhiều góc độ khác nhau. 
Độ sáng 250cd/㎡ và tần số làm mới tối đa 180Hz giúp hiển thị mượt mà, không bị cản trở trong các tình huống cần phản ứng nhanh. 
Với thời gian phản hồi 1ms(GTG) và công nghệ Adaptive-Sync, VG249Q3A mang đến trải nghiệm gaming mượt mà và không bị rách hình.

Tính năng và Đa dạng cổng kết nối
Màn hình này đi kèm với nhiều tính năng hấp dẫn như GamePlus, Extreme Low Motion Blur, và Shadow Boost, cung cấp lợi ích thực sự cho game thủ. 
Với công nghệ HDCP 2.2 và tương thích AMD FreeSync Premium, VG249Q3A đảm bảo bạn có trải nghiệm trò chơi liền mạch và không bị rách hình.

Cổng kết nối đa dạng bao gồm DisplayPort 1.2 và 2 cổng HDMI(v2.0), cho phép bạn kết nối nhiều thiết bị cùng lúc.

Âm thanh và Thiết kế
VG249Q3A cung cấp âm thanh sống động với loa tích hợp 2Wx2, mang đến trải nghiệm giải trí đa phương tiện tốt hơn. 
Với thiết kế tỷ lệ khung hình 16:9 và kích thước 23.8 inch, màn hình này vừa vặn trên bàn làm việc hoặc trong không gian giải trí của bạn.

Tổng kết
Màn hình ASUS TUF Gaming VG249Q3A là một lựa chọn tuyệt vời cho game thủ đang tìm kiếm trải nghiệm gaming tốt nhất. 
Với màn hình IPS chất lượng cao, hiệu suất mượt mà, và tích hợp nhiều tính năng tiện ích, VG249Q3A đem lại sự sống động cho mọi trò chơi và nội dung giải trí của bạn.',
'Kiểu dáng màn hình: Phẳng
Tỉ lệ khung hình: 16:9
Kích thước mặc định: 23.8 inch
Công nghệ tấm nền: Fast IPS
Phân giải điểm ảnh: FHD - 1920 x 1080
Độ sáng hiển thị: 250 Nits cd/m2
Tần số quét màn: 180 Hz (Hertz) MAX
Thời gian đáp ứng: 1ms (GTG)
Chỉ số màu sắc: 16.7 triệu màu - 99% sRGB - 8 bits
Hỗ trợ tiêu chuẩn: VESA (100 mm x 100 mm) - ELMB Sync - AMD FreeSync Premium - G-Sync Compatible
Cổng cắm kết nối: 2xHDMI 2.0, 1xDisplayPort 1.2, 1x3.5mm Earphone Jack
Phụ kiện trong hộp: Dây nguồn, Dây HDMI, Dây DP', 
7, 'LSP007', 'assets/images/manhinh6.webp'),
('SP027', 'Màn hình Gaming LG 24GS50F-B.ATVQ', 2650000, 
'Đặc điểm nổi bật:

Màn hình 23.8 inch, Full HD, VA, 180Hz, 1ms, phẳng.
Tần số quét 180Hz, tốc độ phản hồi 1ms mang đến trải nghiệm chơi game mượt mà, không giật lag.
Tấm nền VA cho màu sắc rực rỡ, độ tương phản cao và góc nhìn rộng.
Hỗ trợ HDR10, nâng cao chất lượng hình ảnh với dải màu rộng hơn và độ tương phản tốt hơn.
Thiết kế gọn gàng, viền mỏng, phù hợp với mọi không gian sử dụng.',
'Hãng sản xuất: LG
Loại: Màn hình Gaming
Kích thước: 23.8 inch
Độ phân giải: Full HD (1920 x 1080)
Tỉ lệ màn hình: 16:9
Tần số quét: 180Hz
Thời gian phản hồi: 1ms (GtG)
Tấm nền: VA
Độ sáng: 250 nits
Tỉ lệ tương phản: 3000:1
Màu sắc hiển thị: 16.7 triệu màu
Trọng lượng (có chân đế): 3.55 kg
Cổng kết nối: 2 x HDMI 1.4, 1 x DisplayPort 1.4, 1 x Audio 3.5mm
Nguồn điện: Tối đa 18W
Tính năng nổi bật: HDR10, Flicker Safe (chống nháy hình), Hỗ trợ giá treo tường VESA 75 x 75 mm, Điều chỉnh độ nghiêng: -5° đến 15°',
11, 'LSP007', 'assets/images/manhinh7.webp'),
('SP028', 'Màn hình Gaming MSI MAG 255XF | 25 inch, Full HD, Rapid IPS, 300Hz, 0.5ms, phẳng', 4690000, 
'Màn hình Gaming MSI MAG 255XF | Hiệu năng vượt trội, chinh phục mọi giới hạn

Màn hình Gaming MSI MAG 255XF là sự kết hợp hoàn hảo giữa hiệu năng mạnh mẽ và thiết kế tinh tế, đáp ứng mọi nhu cầu của game thủ chuyên nghiệp. 
Với tốc độ làm mới lên đến 300Hz và thời gian phản hồi 0.5ms, sản phẩm mang lại trải nghiệm chơi game mượt mà, không giật lag, cùng hình ảnh sắc nét, sống động.

Hiện tại, Tin Học Ngôi Sao tự hào là đơn vị phân phối màn hình MSI MAG 255XF chính hãng với mức giá cực kỳ cạnh tranh. 
Đừng bỏ lỡ cơ hội sở hữu sản phẩm công nghệ hàng đầu này!

Thiết kế đỉnh cao, tối ưu hóa trải nghiệm
Màn hình MSI MAG 255XF sở hữu kích thước 25 inch với độ phân giải Full HD (1920x1080), mang đến không gian hiển thị rộng rãi, hình ảnh rõ nét. 
Công nghệ Rapid IPS giúp màu sắc chính xác, góc nhìn rộng, phù hợp cho cả công việc lẫn giải trí.',
'Kích thước màn hình: 24.5" (62.23 cm)
Loại tấm nền: Rapid IPS
Độ phân giải: 1920x1080 (FHD)
Tần số quét: 300Hz
Thời gian phản hồi: 0.5ms (GtG, Min.)
Công nghệ đồng bộ: AMD FreeSync™ Premium
Cổng video: 1 x DisplayPort 1.4a, 2 x HDMI™ 2.0b
Góc nhìn: 178°(H) / 178°(V)', 
17, 'LSP007', 'assets/images/manhinh8.webp'),
('SP029', 'Màn Hình Samsung LS22C310EAEXXV (22 inch/FHD/IPS/75Hz/5ms/FreeSync)', 2000000, 
'Đặc điểm nổi bật:

Màn hình IPS 21.5 inch, độ phân giải Full HD, mang đến hình ảnh sắc nét, màu sắc chân thực.
Tần số quét 75Hz và công nghệ FreeSync, cho trải nghiệm chơi game mượt mà, hạn chế hiện tượng xé hình, giật hình.
Thiết kế tràn viền 3 cạnh, mỏng gọn, phù hợp với mọi không gian.
Tích hợp nhiều tính năng bảo vệ mắt, mang đến sự thoải mái cho người dùng trong thời gian dài sử dụng.',
'Hãng sản xuất: Samsung
Loại: Màn hình máy tính
Kích thước: 21.5 inch
Độ phân giải: Full HD (1920 x 1080)
Tỉ lệ màn hình: 16:9
Tần số quét: 75Hz
Thời gian phản hồi: 5ms (GTG)
Tấm nền: IPS
Độ sáng: 200 cd/m²
Tỉ lệ tương phản: 1000:1 (Typical)
Số màu hiển thị: 16.7 triệu màu
Góc nhìn: 178° (ngang/dọc)
Trọng lượng (kèm chân đế): 2.4 kg
Cổng kết nối: 1 x D-Sub, 1 x HDMI 1.4
Nguồn điện tiêu thụ: 25W
Tính năng nổi bật: Công nghệ Eco Saving Plus tiết kiệm điện, Chế độ bảo vệ mắt và Flicker Free giảm mỏi mắt, Chế độ chơi Game, AMD FreeSync, Tính năng Image Size tùy chỉnh hiển thị', 
6, 'LSP008', 'assets/images/manhinh9.webp'),
('SP030', 'Màn hình Dell S2425H | 24 inch, FHD, IPS, 100Hz, HDMI, phẳng', 3300000,
'Màn hình Dell S2425H mang đến trải nghiệm hình ảnh sắc nét, màu sắc sống động và thiết kế hiện đại. 
Với tần số quét 100Hz và thời gian phản hồi nhanh, sản phẩm phù hợp cho cả công việc văn phòng lẫn giải trí nhẹ nhàng. 
Loa tích hợp và các công nghệ bảo vệ mắt giúp nâng cao trải nghiệm người dùng.',
'Kích thước màn hình: IPS 23.8 inch
Độ phân giải: Full HD
Cổng kết nối: 2x HDMI, Audio line-out
Loa tích hợp: Có
Tình trạng: Hàng mới.', 
5, 'LSP008', 'assets/images/manhinh10.webp'),
('SP031', 'Màn hình LG 27MR400-B | 27 inch, Full HD, IPS, 100Hz, 5ms, phẳng', 2500000, 
'Đặc điểm nổi bật:

Màn hình 27 inch Full HD, tấm nền IPS cho hình ảnh sống động và góc nhìn rộng.
Tần số quét 100Hz, phản hồi 5ms, mượt mà cho game và phim hành động.
Công nghệ Adaptive Sync và AMD FreeSync loại bỏ xé hình và giật hình.
Tích hợp tính năng bảo vệ mắt và nâng cao trải nghiệm người dùng.',
'Hãng sản xuất: Màn hình LG
Loại màn hình: IPS
Kích thước: 27 inch
Trọng lượng: 3.85 kg (gồm chân đế)
Độ phân giải: Full HD (1920x1080)
Tỉ lệ màn hình: 16:9
Tần số quét: 100Hz
Thời gian phản hồi: 5ms (GtG at Faster)
Loại tấm nền: IPS
Độ sáng: (Typ.) 250 cd/m², (Min.) 220 cd/m²
Tỉ lệ tương phản: Typ. 1300:1, Min. 1000:1
Số lượng màu: 16.7 triệu màu
Góc nhìn: 178°/178°
Kết nối: D-Sub, HDMI, Headphone Out
Nguồn điện: Input: 100~240V (50/60Hz)
DC Off (Max): Less than 0.3W
Power Save/Sleep Mode (Max): Less than 0.3W, Type: External Power (Adapter)',
11, 'LSP008', 'assets/images/manhinh11.webp'),
('SP032', 'Màn hình MSI Pro MP251 E2 | 24.5 inch, Full HD, IPS, 120Hz, 1ms, phẳng', 2200000, 
'Đặc điểm nổi bật:

Màn hình 24.5 inch Full HD, tần số quét 120Hz mang đến hình ảnh sắc nét, chuyển động mượt mà.
Tấm nền IPS cho góc nhìn rộng, màu sắc chính xác và độ tương phản cao.
Thời gian phản hồi 1ms (MPRT) loại bỏ hiện tượng bóng mờ, mang đến trải nghiệm chơi game mượt mà.
Kết nối đa dạng với HDMI, DP, D-Sub, hỗ trợ âm thanh tích hợp',
'Hãng sản xuất: MSI
Loại màn hình: Màn hình máy tính
Kích thước: 24.5 inch (62.2cm)
Trọng lượng: 2.7 kg (không chân đế) / 4.9 kg (có chân đế)
Độ phân giải: Full HD (1920 x 1080)
Tỉ lệ màn hình: 16:9
Tần số quét: 120Hz
Thời gian phản hồi: 1ms (MPRT), 4ms (GTG)
Loại tấm nền: IPS
Độ sáng: 250 cd/m² (tối thiểu), 300 cd/m² (điển hình)
Tỉ lệ tương phản: 1500:1 (Typ.)
Số lượng màu: 16.7 triệu màu
Góc nhìn: 178º (R/L), 178º (U/D)
Kết nối: 1 x HDMI (2.0), 1 x DisplayPort (1.4a), 1 x D-Sub (VGA), 1 x Headphone-out, 1 x Line-in
Nguồn điện: Nguồn điện bên ngoài',
3, 'LSP008', 'assets/images/manhinh12.webp'),
('SP033', 'Màn hình đồ hoạ Dell Ultrasharp U2424H | 23.8 inch, Full HD, IPS, 120Hz, 5ms, phẳng', 5900000, 
'Màn hình Dell UltraSharp U2424H 24 inch chuẩn WUXGA, tấm nền IPS cho hình ảnh sắc nét và góc nhìn rộng. 
Độ chính xác màu cao, phù hợp thiết kế đồ họa chuyên nghiệp. Kết nối đa dạng HDMI, DisplayPort, USB-C. 
Thiết kế viền mỏng, bảo vệ mắt khi sử dụng lâu.',
'Màn hình: Dell UltraSharp
Kích Thước: 23.8 Inch
Độ Phân Giải: 1920 x 1080
Tần Số Quét: 120Hz
Độ phủ màu: 100% sRGB, 100% BT.709, 85% DCI-P3, Delta E < 2', 
2, 'LSP009', 'assets/images/manhinh13.webp'),
('SP034', 'Màn hình Đồ Họa Asus ProArt PA248QV | 24 inch, FHD, IPS, 75Hz, 100% sRGB, Phẳng', 5000000, 
'Màn hình IPS 24.1 inch, WUXGA (2560 x 1200) 16:10 với thiết kế không khung viền
Tiêu chuẩn màu quốc tế đạt 100% phổ màu sRGB và 100% phổ màu Rec.709
Đạt chứng nhận Calman Verified nhờ được hiệu chuẩn sẵn trước khi xuất xưởng để mang lại độ chính xác màu tuyệt vời (ΔE < 2)
Các tính năng ProArt Preset và ProArt Palette độc quyền của ASUS cung cấp một số thông số màu sắc và chế độ cài đặt sẵn có thể điều chỉnh
Tốc độ làm tươi 75Hz và công nghệ Adaptive-Sync (48 ~ 75Hz) giúp đáp ứng các pha hành động nhanh và loại bỏ hiện tượng xé hình
Khả năng kết nối mở rộng với DisplayPort, HDMI, D-sub, Audio in, Giắc cắm tai nghe cộng với bốn cổng USB 3.0 mang lại cho bạn sự linh hoạt tối đa
Thiết kế tiện dụng, tương thích với các giá treo tường chuẩn VESA cùng khả năng điều chỉnh độ nghiêng, quay, xoay và chiều cao giúp mang lại trải nghiệm xem thoải mái.',
'Kích thước màn hình: 24″
Độ phân giải: Full HD (1920×1200)
Loa âm thanh nổi: 2W x 2 Stereo RMS
Tỷ lệ màn hình: 16:9
Công nghệ tấm nền: IPS
Tần số quét: 75Hz
Màu sắc hiển thị: 16.7 triệu màu
Độ bão hòa màu: 100% sRGB / 100% Rec. 709
Độ chính xác màu: △ E', 
4, 'LSP009', 'assets/images/manhinh14.webp'),
('SP035', 'Màn hình Samsung ViewFinity S9 LS27C900PAEXXV | 27 inch, 5K, IPS, 60Hz, Thunderbolt 4, phẳng', 20950000, 
'Đặc điểm nổi bật:

Màn hình 5K 27 inch với độ phân giải 5120 x 2880 pixel, mang đến hình ảnh sắc nét và không gian hiển thị rộng lớn.
Dải màu DCI-P3 99% và độ lệch màu ΔE < 2, tái tạo màu sắc chính xác và rực rỡ, phù hợp cho nhu cầu thiết kế và sáng tạo.
Cổng Thunderbolt 4 với tốc độ truyền dữ liệu 40 Gbps, cho phép kết nối và sạc nhanh nhiều thiết bị cùng lúc.
Công nghệ chống chói và tính năng bảo vệ mắt thông minh, mang đến trải nghiệm sử dụng thoải mái và bảo vệ mắt hiệu quả.',
'Hãng sản xuất: Samsung
Kích thước: 27 inch
Trọng lượng: Sản phẩm kèm chân đế: 7.4 kg, Sản phẩm không kèm chân đế: 4.7 kg
Độ phân giải: 5,120 x 2,880
Tỉ lệ màn hình: 16:9
Tần số quét: 60Hz
Thời gian phản hồi: 5ms (GTG)
Loại tấm nền: IPS
Độ sáng: 600 cd/㎡
Tỉ lệ tương phản: 1000:1
Số lượng màu: 1 tỷ màu
Góc nhìn: 178° (H) / 178° (V)
Kết nối: 1 x Mini-DisplayPort, 1 x Thunderbolt 4
Nguồn điện: AC 100~240V',
5, 'LSP009', 'assets/images/manhinh15.webp'),
('SP036', 'Màn hình đồ họa 4k Samsung Viewfinity LS27B800PXEXXV (27 Inch/UHD/Ips/60Hz/5Ms/350Nits/ USB C/ chân CTH', 7400000, 
'Đặc điểm nổi bật:

Độ phân giải UHD sắc nét, tấm nền IPS chất lượng cao.
Tái hiện màu sắc chuyên nghiệp với dải màu DCI-P3 98% và chứng nhận Pantone Validated™.
Công nghệ HDR 400 cho hình ảnh chân thực và sống động.
Kết nối linh hoạt với USB Type-C, hỗ trợ cổng LAN và thiết kế công thái học tiện dụng.',
'Hãng sản xuất: Samsung
Kích thước: 27 inch
Trọng lượng: Sản phẩm kèm chân đế: 6.7 kg, Sản phẩm không kèm chân đế: 4.7 kg
Độ phân giải: 3,840 x 2,160
Tỉ lệ màn hình: 16:9
Tần số quét: 60Hz
Thời gian phản hồi: 5ms
Loại tấm nền: IPS
Độ sáng: 350 cd/㎡
Tỉ lệ tương phản: 1000:1
Số lượng màu: 1.07 tỷ
Góc nhìn: 178° (H) / 178° (V)
Kết nối: 1 x DisplayPort, LAN (RJ45), HDMI, 1 x USB-C
Nguồn điện: AC 100~240V
Tính năng khác: Pantone Validated™, Hiển thị HDR 400, Tấm nền chống phản sáng, Thiết kế công thái học',
7, 'LSP009', 'assets/images/manhinh16.webp'),
('SP037', 'Card màn hình ASUS Dual RX 6600 V3 8GB GDDR6', 5300000, 
'Đặc điểm nổi bật:

Card đồ họa mạnh mẽ, hỗ trợ DirectX 12 Ultimate, mang đến trải nghiệm game chân thực.
Trang bị 8GB bộ nhớ GDDR6 tốc độ cao, xử lý mượt mà các game AAA và đồ họa nặng..
Hệ thống làm mát hiệu quả, đảm bảo hoạt động ổn định và yên tĩnh.
Thiết kế đẹp mắt, phù hợp với nhiều phong cách dàn máy.',
'Thương hiệu: ASUS
Chipset: AMD Radeon™ RX 6600
Bộ nhớ: 8GB
Loại bộ nhớ: GDDR6
Bus: 128-bit
Tốc độ xung nhịp: OC mode: 2491 MHz (Boost) / 2064 MHz (Game)
Default mode: 2491 MHz (Boost) / 2044 MHz (Game)
Cổng kết nối: 1 x HDMI 2.1, 3 x DisplayPort 1.4a
Kích thước: 219.2 x 121.2 x 40.5 mm (8.6 x 4.8 x 1.59 inch)
Công suất: 500W (khuyến nghị PSU)
Giao tiếp PCI: PCI Express 4.0
Tản nhiệt: Quạt kép (Dual fan)
Nguồn đầu vào: 1 x 8-pin',
8, 'LSP010', 'assets/images/VGA1.webp'),
('SP038', 'Card màn hình (Amd) Tw-Rx580 8G D5 Blue Dual', 2300000, 
'Hiệu năng mạnh mẽ cho mọi nhu cầu
Với chipset AMD Radeon RX580 và 2048SP CUDA cores, card màn hình này đáp ứng tốt mọi tác vụ từ chơi game AAA, xử lý đồ họa chuyên nghiệp đến các ứng dụng đa nhiệm nặng.
Hình ảnh sắc nét vượt trội
Hỗ trợ độ phân giải tối đa lên đến 7680×4320, cho trải nghiệm hình ảnh sống động và chân thực trên các màn hình độ phân giải cao.
Công nghệ tiên tiến hỗ trợ tối đa
Tích hợp đầy đủ các công nghệ hiện đại như DirectX 12, OpenGL 4.6, Vulkan và DirectML, giúp tối ưu hiệu năng trong các tác vụ đồ họa và chơi game.
Kết nối đa dạng, dễ dàng mở rộng
Trang bị 1 cổng HDMI 2.0 và 3 cổng DisplayPort 1.4, dễ dàng kết nối với nhiều thiết bị và màn hình cùng lúc.
Tiết kiệm điện năng, hoạt động ổn định
Với công suất tiêu thụ tối đa chỉ 110W và yêu cầu nguồn đề xuất chỉ 400W, sản phẩm mang lại hiệu quả năng lượng cao mà vẫn giữ được hiệu suất vượt trội.',
'Chipset: Radeon RX580
Bộ nhớ: 8GB GDDR5
Tốc độ bộ nhớ/xung nhịp: 7000MHz / 1286MHz
Cổng kết nối: HDMI*1, DP*1, DVI*1
Bus bộ nhớ: 256Bit
Độ phân giải tối đa: 7680×4320
Công nghệ hỗ trợ: DirectX 12, OpenGL 4.6, Vulkan
Kích thước PCB: ATX
Kết nối nguồn: 6pin*1
Quy trình sản xuất: 14nm', 
11, 'LSP010', 'assets/images/VGA2.webp'),
('SP039', 'Card màn hình Gigabyte RTX 3060 12G WINDFORCE OC (rev. 1.0)', 7500000, 
'Đặc điểm nổi bật:

Hiệu năng vượt trội, xử lý nhanh các tựa game hiện đại ở độ phân giải cao.
Hệ thống tản nhiệt WindForce 2x độc quyền, hoạt động êm ái, giữ cho card luôn mát mẻ.
Tích hợp công nghệ Ray Tracing và DLSS, mang đến trải nghiệm hình ảnh chân thực.
Thiết kế nhỏ gọn, dễ dàng lắp đặt trong nhiều loại thùng máy.',
'GPU: GeForce RTX™ 3060
Tần số lõi: 1792 MHz (Reference Card: 1777 MHz)
Số lượng lõi CUDA®: 3584
Tần số bộ nhớ: 15000 MHz
Dung lượng bộ nhớ: 12 GB
Loại bộ nhớ: GDDR6
Băng thông bộ nhớ: 360 GB/s
Kết nối: PCI-E 4.0 x 16
Output: DisplayPort 1.4a x 2, HDMI 2.1 x 2
Độ phân giải tối đa: 7680 x 4320
Kích thước card: Dài: 198 mm, Rộng: 121 mm, Cao: 39 mm
Kiểu PCB: ATX
Hỗ trợ DirectX: 12 Ultimate
Hỗ trợ OpenGL: 4.6
Nguồn cung cấp khuyến nghị: 550W
Đầu nối nguồn: 8-pin x 1',
15, 'LSP010', 'assets/images/VGA3.webp'),
('SP040', 'Card màn hình Inno3D GeForce RTX 3060 Twin X2 12G GDDR6 chính hãng', 7200000, 
'Đặc điểm nổi bật:

Card đồ họa mạnh mẽ, hỗ trợ Ray Tracing và DLSS, mang đến trải nghiệm game chân thực.
Trang bị 12GB bộ nhớ GDDR6 tốc độ cao, xử lý mượt mà các game AAA và ứng dụng đồ họa nặng.
Hệ thống làm mát hiệu quả, đảm bảo hoạt động ổn định và yên tĩnh.
Thiết kế đẹp mắt, phù hợp với nhiều phong cách dàn máy.',
'Thương hiệu: INNO3D
Chipset: NVIDIA GeForce RTX 3060
Bộ nhớ: 12GB
Loại bộ nhớ: GDDR6
Bus: 192-bit
Tốc độ xung nhịp: Boost Clock: 1777 MHz
Cổng kết nối: 1x HDMI 2.1, 3x DisplayPort 1.4a
Kích thước: Dài 240mm, Cao 120mm, Rộng 2 khe
Công suất: Yêu cầu công suất hệ thống tối thiểu 550W
Giao tiếp PCI: PCI Express® Gen 4
Tản nhiệt: Tản nhiệt kép Twin X2
Nguồn đầu vào: 1x đầu nối nguồn bổ sung 8 chân',
13, 'LSP010', 'assets/images/VGA4.webp'),
('SP041', 'CPU Intel Core I3 12100 | LGA1700, Turbo 4.30 GHz, 4C/8T, 12MB, Box Chính Hãng', 2800000, 
'Đặc điểm nổi bật:

CPU Intel thế hệ 12 Alder Lake với kiến trúc hybrid mới, hiệu năng vượt trội so với thế hệ trước.
Tốc độ xử lý cao lên đến 4.30 GHz, cho khả năng xử lý đa nhiệm mượt mà..
Hỗ trợ bộ nhớ DDR4/DDR5 Dual channel, nâng cao hiệu suất hoạt động của hệ thống.
Xử lý nhanh chóng và ổn định, phù hợp với nhu cầu sử dụng đa dạng từ văn phòng, học tập đến giải trí...',
'Model: Intel Core™ i3-12100
Socket: FCLGA1700, FCBGA1700
Tốc độ cơ bản: 3.30 GHz
Tốc độ tối đa (Turbo Boost): 4.30 GHz
Số nhân: 4
Số luồng: 8
Cache: 12 MB
Vi xử lý đồ họa tích hợp: Đồ họa Intel® UHD 730
Bộ nhớ hỗ trợ: Tối đa 128 GB với tốc độ lên tới DDR5 4800 MT/s hoặc DDR4 3200 MT/s
Điện áp tiêu thụ tối đa: Từ 60 W đến 89 W',
9, 'LSP011', 'assets/images/CPU1.webp'),
('SP042', 'CPU AMD RYZEN 3 3200G | 3.6GHz Up to 4.0GHz, AM4, 4 Cores 4 Threads', 1840000, 
'Tính năng nổi bật CPU RYZEN 3 3200G 
CPU Ryzen 3 3200G Dòng vi xử lý đầu tiên RYZEN có đồ họa tích hợp. 
Tích hợp Card Đồ Họa Radeon Vega 8 với xung nhịp tối đa 1100Mhz hiệu năng gần ngang ngửa 1 chiếc VGA GT1030. Điểm nhấn chính là nhân đồ họa tích hợp AMD Radeon VEGA...
Hỗ trợ đầy đủ tính năng
CPU Ryzen 3 3200G gồm có 4 Nhân 4 Luồng hoạt động ở xung nhịp 3.6GHz cao nhất 4.0Ghz ở chế độ OC cho bạn một hiệu năng tuyệt vời trong công việc lẫn chơi game. Dù là Vega tích hợp những dòng vi xử lý mới đều hỗ trợ đầy đủ các tính năng như HDR, FreeSync 2, khả năng trình xuất từ đến 4K, đa màn hình.

Hỗ trợ tốt với các loại RAM Bus 3000 hoặc 3200 Mhz cho bạn tiết kiệm tối đa thời gian trong công việc...',
'Bộ xử lý: Ryzen 3 3200G
Hỗ trợ socket: AM4
Số lõi: 4 
Số luồng: 4
TDP: 65 W
Các loại bộ nhớ: DDR4-2933Mhz
Đồ họa tích hợp: Radeon™ Vega 8', 
8, 'LSP011', 'assets/images/CPU2.webp'),
('SP043', 'CPU AMD Ryzen 5 5600G | AM4, Upto 4.40 GHz, 6C/12T, 16MB, Box Chính Hãng', 3500000, 
'Tính năng nổi bật
Chip chơi game là một phần của AMD dòng máy Ryzen 5000.
Xây dựng trên kiến trúc Zen 3 với tiến trình 7nm tân tiến.
Trang bị nhân đồ họa Radeon RX Vega cực kỳ mạnh mẽ, đa nhiệm tốt.
Trải nghiệm mượt mà các phần mềm thiết kế chuyên nghiệp hoặc những tựa game nặng.',

'Số nhân: 6
Số luồng: 12
Xung cơ bản: 3,9GHz
Xung Max Boost: Lên đến 4.4GHz
Tổng bộ nhớ đệm L2: 3MB
Tổng bộ nhớ đệm L3: 16MB
Khả năng ép xung: Có
CMOS: TSMC 7nm FinFET
Socket: AM4
Phiên bản PCI Express®: PCIe 3.0
Giải pháp nhiệt (PIB): Wraith Stealth
TDP / TDP mặc định: 65W.', 
2, 'LSP011', 'assets/images/CPU3.webp'),
('SP044', 'CPU Intel Core Ultra 7 265K | Up to 5.5GHz, 20 cores 20 threads, 30MB', 8000000, 
'Đặc điểm nổi bật:

Hiệu năng mạnh mẽ với 20 nhân xử lý, 20 luồng, xung nhịp tối đa lên đến 5.5 GHz.
Công nghệ Intel 8 tiên tiến mang đến hiệu suất vượt trội.
Bộ nhớ đệm 30 MB giúp tăng tốc độ truy xuất dữ liệu, tối ưu hiệu năng.
Hỗ trợ DDR5-6400, mang đến khả năng xử lý dữ liệu nhanh chóng, mượt mà.',
'Model: ULTRA 7 265K
Kiến trúc: Intel 8
Số nhân: 20 (8 P-Cores + 12 E-Cores)
Xung nhịp đơn nhân tối đa: Up to 5.5 GHz
Xung nhịp tối đa (P-Cores): 5.4 GHz
Xung nhịp tối đa (E-Cores): 4.6 GHz
Xung nhịp cơ bản (P-Cores): 3.9 GHz
Xung nhịp cơ bản (E-Cores): 3.3 GHz
Bộ nhớ đệm: 30 MB
TDP Cơ bản: 125W
TDP Tối đa: 250W
GPU tích hợp: Intel Graphics
RAM hỗ trợ: DDR5-6400
Socket: FCLGA1851', 
3, 'LSP011', 'assets/images/CPU4.webp'),
('SP045', 'Mainboard MSI PRO B760M-E DDR4 | Intel B760, Socket 1700, mATX, 2 khe DDR4', 2300000, 
'Đặc điểm nổi bật:

Chipset Intel® B760 mạnh mẽ và ổn định, hỗ trợ bộ vi xử lý Intel® thế hệ thứ 12 và 13.
Thiết kế Micro-ATX, tối ưu cho hệ thống nhỏ gọn và phù hợp đa dạng nhu cầu setup.
Khe cắm PCIe 4.0, mang đến hiệu suất cao cho card đồ họa và các thiết bị mở rộng.
Trang bị đầy đủ các cổng kết nối, tính năng bảo vệ và công nghệ tăng cường hiệu năng.',
'Hãng sản xuất: MSI
Chipset: Intel® B760
Socket: LGA 1700
RAM: 2 khe cắm bộ nhớ DDR4, hỗ trợ lên đến 64GB
Khe cắm mở rộng: 1x PCI-E x16 slot, 1x PCI-E x1 slot, PCI_E1: PCIe 4.0 (từ CPU), PCI_E2: PCIe 3.0 (từ Chipset)
USB: 4x USB 2.0 (Rear), 4x USB 2.0 (Front), 2x USB 5Gbps Type A (Rear), 2x USB 5Gbps Type A (Front)
Cổng kết nối I/O bên trong: 1x Power Connector (ATX_PWR), 1x Power Connector (CPU_PWR), 1x CPU Fan
2x System Fan, 2x Front Panel (JFP), 1x Chassis Intrusion (JCI), 1x Front Audio (JAUD), 1x Com Port (JCOM)
1x RGB LED connector (JRGB), 1x TPM pin header (hỗ trợ TPM 2.0), 2x USB 2.0, 1x USB 3.2 Gen1 Type A
Cổng kết nối I/O phía sau: HDMI, VGA, Cổng chuột / bàn phím, LAN port, Kết nối âm thanh, USB 2.0, USB 3.2 Gen 1 5Gbps Type A
Âm thanh: Realtek ALC897 Codec, Âm thanh độ nét cao 7.1 kênh
Mạng LAN: Realtek® RTL8111H Gigabit LAN
Ổ cứng hỗ trợ: 4x cổng SATA 6Gb/s (từ Chipset), 1x khe M.2_1 (hỗ trợ lên đến PCIe 4.0 x4)',
5, 'LSP012', 'assets/images/Mainboard1.webp'),
('SP046', 'Mainboard Gigabyte B760M Gaming Plus Wifi DDR4', 3200000, 
'Đặc điểm nổi bật:

Kết nối tốc độ cao: Bay lượn trong thế giới mạng với Wi-Fi 6 802.11ax, GbE LAN và USB-C® 5Gb/s..
Chipset Intel® B760 Express: Cung cấp nền tảng vững chắc cho hiệu năng đỉnh cao, sánh vai cùng CPU Intel® thế hệ 14, 13 và 12..
Hiệu năng bứt phá: Tăng tốc vượt trội với giải pháp VRM kỹ thuật số Hybrid 4+1+1 Phases và RAM DDR4 lên đến 5333 MHz (OC).
Trải nghiệm chơi game đỉnh cao: Chinh phục mọi thử thách với âm thanh HD, công nghệ Smart Fan 6 và đèn LED RGB rực rỡ.',
'Hãng sản xuất: Gigabyte
Chipset: Intel® B760 Express Chipset
Socket: LGA 1700 – Hỗ trợ bộ vi xử lý Intel® Core™, Pentium® Gold và Celeron® thế hệ 12, 13, 14
L3 Cache: Tùy thuộc vào CPU
RAM: DDR4
Hỗ trợ tối đa RAM: 128 GB (4 x DDR4 DIMM)
Khe cắm PCI-E: 1 x PCI Express x16 slot, 2 x PCI Express x1 slots
Kết nối mạng: Realtek® GbE LAN chip (1 Gbps / 100 Mbps / 10 Mbps)
Khe cắm M.2: 2 khe
Âm thanh: Realtek® Audio CODEC, High Definition Audio, 2/4/5.1/7.1-channel
Hệ điều hành hỗ trợ: Windows 11 64-bit, Windows 10 64-bit
Kết nối I/O bên trong: 1 x 24-pin ATX main power connector, 1 x 8-pin ATX 12V power connector, 1 x CPU fan header
3 x System fan headers, 1 x Addressable LED strip header, 1 x RGB LED strip header, 2 x M.2 Socket 3 connectors
4 x SATA 6Gb/s connectors, 1 x Front panel header, 1 x Front panel audio header, 1 x USB Type-C® header (USB 3.2 Gen 1)
1 x USB 3.2 Gen 1 header, 2 x USB 2.0/1.1 headers, 1 x Trusted Platform Module header, 1 x Serial port header
1 x Parallel port header, 1 x S/PDIF Out header, 1 x Q-Flash Plus button, 1 x Reset jumper, 1 x Clear CMOS jumper
Kết nối bảng phía sau: 2 x USB 2.0/1.1 ports, 1 x PS/2 keyboard/mouse port, 2 x SMA antenna connectors (2T2R)
1 x DisplayPort, 1 x HDMI port, 3 x USB 3.2 Gen 1 ports, 1 x RJ-45 LAN port, 3 x Audio jacks
Kích thước: Micro ATX (24.4 cm x 24.4 cm)',
10, 'LSP012', 'assets/images/Mainboard2.webp'),
('SP047', 'Mainboard Gigabyte H610M H V3 DDR4 (rev. 1.0)', 1700000,
'Đặc điểm nổi bật:

Mainboard Gigabyte H610M H V3 DDR4 là bo mạch chủ sử dụng chipset Intel H610, hỗ trợ các CPU Intel thế hệ 12 và 13 trên socket LGA1700. 
Thiết kế nhỏ gọn chuẩn mATX, hỗ trợ RAM DDR4 và các cổng kết nối cơ bản phù hợp cho các cấu hình phổ thông, văn phòng hoặc học tập.',
'Socket: LGA1700
Kích thước: Micro ATX
Khe cắm RAM: 2 khe DDR4', 
12, 'LSP012', 'assets/images/Mainboard3.webp'),
('SP048', 'Mainboard Asus Prime H510M-K R2.0 | Socket 1200, M-ATX, 2 khe ram', 1500000, 
'Đặc điểm nổi bật:

Khả năng tương thích với chip Intel® thế hệ 10 và 11, đáp ứng đa dạng nhu cầu từ làm việc đến giải trí.
Kiểu dáng Micro-ATX nhỏ gọn, phù hợp với mọi không gian lắp đặt..
Trang bị đầy đủ các cổng kết nối cần thiết, đáp ứng mọi nhu cầu kết nối.
Hệ thống bảo vệ toàn diện, đảm bảo hoạt động ổn định và an toàn cho máy tính.',
'Chipset: Intel® H510
Socket: LGA1200 (hỗ trợ CPU Intel Gen 10, 11)
RAM: 2 khe DDR4, Hỗ trợ tối đa 64GB
Hỗ trợ tốc độ: 3200(OC)/2933/2800/2666/2400/2133 MHz
Khe cắm mở rộng & kết nối: 1 x M.2 (PCIe 3.0 x4 & SATA), 4 x SATA 6Gb/s, 2 x USB 3.2 Gen 1 Type-A (rear), 4 x USB 2.0 Type-A (rear)
1 x USB 3.2 Gen 1 header (hỗ trợ thêm 2 cổng USB 3.2 Gen 1), 1 x USB 2.0 header (hỗ trợ thêm 2 cổng USB 2.0)
Đồ họa tích hợp (tùy CPU): 1 x HDMI, 1 x D-Sub (VGA)
Âm thanh: Realtek ALC897/887, Âm thanh HD 7.1 kênh
LAN: Intel® I219-V Gigabit Ethernet
Cổng I/O phía sau: 2 x USB 3.2 Gen 1, 4 x USB 2.0, 1 x HDMI, 1 x VGA (D-Sub), 3 x Audio jacks, 1 x PS/2 Keyboard/Mouse combo
Kích thước: microATX 22.6cm x 20.3cm',
6, 'LSP012', 'assets/images/Mainboard4.webp'),
('SP049', 'Ram DDR4 Kingston 16GB 3200Mhz Fury Beast (1x 16GB) (KF432C16BB/16)', 850000, 
'Đặc điểm nổi bật:

Tính năng XMP giúp dễ dàng ép xung, tối ưu hiệu năng cho hệ thống, mang đến trải nghiệm mượt mà, hiệu quả.
Thiết kế thanh lịch, tạo điểm nhấn sang trọng cho không gian làm việc của bạn.
RAM DDR4 tốc độ cao 3200Mhz mang đến hiệu suất vượt trội, xử lý đa nhiệm mượt mà, tăng cường khả năng chơi game và thiết kế đồ họa.
Dung lượng 16GB đáp ứng nhu cầu sử dụng đa dạng, phù hợp với các tác vụ nặng.',
'Thương hiệu: Kingston
Dung lượng: 16GB
Loại: DDR4
Tần số: 3200MHz
CAS Latency: CL17
Điện áp hoạt động: 1.35V
Hỗ trợ: XMP (Extreme Memory Profile)
Tính năng: Phù hợp với nhiều loại mainboard, Thiết kế tản nhiệt nhôm đơn giúp tăng hiệu suất làm mát
Kích thước: 133.35 x 34 x 7.2 mm
Khối lượng: Không công bố',
20, 'LSP013', 'assets/images/RAM1.webp'),
('SP050', 'Ram 4 16G Bus 3200 Corsair Ddr4 Vengeance Lpx Black Heat Spreader 1X16G (Cmk16Gx4M1E3200C16)', 725000, 
'Đặc điểm nổi bật:

RAM DDR4 với tốc độ 3200Mhz, mang đến hiệu suất vượt trội, xử lý đa nhiệm mượt mà, tăng cường khả năng chơi game, thiết kế đồ họa.
Dung lượng 16GB đáp ứng nhu cầu sử dụng đa dạng, phù hợp với máy tính để bàn chuyên nghiệp và người dùng muốn tối ưu hiệu năng.
Tính năng XMP 2.0 giúp dễ dàng ép xung, nâng hiệu năng tối đa cho hệ thống.
Thiết kế thanh lịch, hiệu quả tản nhiệt tối ưu, tương thích với nhiều dòng mainboard hiện nay.',
'Dòng RAM: Corsair Vengeance LPX
Dung lượng: 16GB
Loại: DDR4
Cấu hình bộ nhớ: Single Channel
Tốc độ xung nhịp: 3200MHz
Độ trễ: 16-20-20-38
Điện áp: 1.35V
Tản nhiệt: Nhôm anodized
Màu sắc: Đen
Tương thích: Intel 100 Series, Intel 200 Series, Intel 300 Series
Công nghệ hỗ trợ: XMP 2.0',
13, 'LSP013', 'assets/images/RAM2.webp'),
('SP051', 'Ram DDR4 XLR8 PNY 16GB 3200Mhz (MD16GD4320016XR) Có Tản Nhiệt', 650000, 
'THIẾT LẬP KÍCH THƯỚC THẾ GIỚI VỚI NÂNG CẤP BỘ NHỚ PNY XLR8 DDR4
Bạn đưa chiếc PC của mình lên đỉnh cao vì một mục đích: tiêu diệt đối thủ. PNY đã hỗ trợ bạn với bản nâng cấp bộ nhớ máy tính để bàn DDR4 3200MHz CL16 ưu tú. 
Các mô-đun XLR8 cao cấp của PNY kết hợp các thành phần hàng đầu và các IC chọn lọc để có tốc độ nhanh, độ trễ thấp, 
độ tin cậy chống đạn và khả năng ép xung cực cao mà các game thủ nghiêm túc yêu cầu. 
Việc ép xung được thực hiện dễ dàng hơn với khả năng tương thích Intel® XMP. 
Và bộ tản nhiệt XLR8 hoàn toàn phong cách của PNY phân tán sức nóng của trận chiến và trông rất khốc liệt khi làm điều đó.

Trong hơn 30 năm, PNY đã nghiêm ngặt tìm nguồn cung ứng, thử nghiệm và sản xuất các bản nâng cấp bộ nhớ cho hàng nghìn nền tảng PC phổ biến nhất. 
Chuẩn bị cho chiếc PC tùy chỉnh của bạn và sẵn sàng chiến đấu với bản nâng cấp XLR8 DDR4 3200MHz CL16 từ PNY và xem thế giới rực lửa.

Hiệu suất DDR4 3200MHz
Bộ nhớ XLR8 cao cấp của PNY có tốc độ mạnh nhất, băng thông cao nhất, độ trễ và mức tiêu thụ điện năng thấp nhất cũng như hiệu suất nhiệt tiên tiến nhất 
để mang lại khả năng phản hồi và ổn định máy tính tối đa trong quá trình chơi game và sử dụng ứng dụng nhiều bộ nhớ.

Các mô-đun bộ nhớ PNY XLR8 DDR4 được thiết kế và thử nghiệm nghiêm ngặt để đảm bảo hiệu suất cao nhất trong cả những môi trường chơi game khó khăn nhất.',
'Loại sản phẩm: RAM cho PC
Hãng sản xuất: PNY
Model: XLR8 MD16GD4320016XR
Chuẩn Ram: DDR4
Dung lượng: 16GB (1x16GB)
Bus: 3200MHz
Độ trễ: CL16
Điện áp: 1.2V
Tản nhiệt: Có', 
11, 'LSP013', 'assets/images/RAM3.webp'),
('SP052', 'Ram DDR4 16GB 3200Mhz D50 Adata XPG Tungsten Grey RGB(AX4U320016G16A-ST50)', 900000, 
'Đặc điểm nổi bật:

RAM DDR4 với tốc độ 3200MHz, mang đến hiệu suất mạnh mẽ, đa nhiệm mượt mà, tăng cường khả năng chơi game và xử lý đồ họa..
Dung lượng 16GB đáp ứng nhu cầu sử dụng đa dạng, phù hợp với nhiều loại PC, Desktop.
Hỗ trợ XMP, dễ dàng ép xung, nâng hiệu năng tối đa cho hệ thống.
Thiết kế nhỏ gọn, tương thích với hầu hết các bo mạch chủ hiện nay, đảm bảo hiệu quả tản nhiệt tối ưu.',
'Hãng sản xuất: ADATA
Model: AX4U320016G16A-ST50
Loại RAM: DDR4
Dung lượng: 16GB (1x 16GB)
Tốc độ Bus: 3200MHz
Độ trễ (CAS Latency): CL16-20-20
Điện áp: 1.35V
Đèn LED: RGB
Tản nhiệt: Có (tản nhiệt kim loại)',
11, 'LSP013', 'assets/images/RAM4.webp'),
('SP053', 'Ổ cứng SSD Adata SU650 256GB | SATA III, 2.5"', 410000, 
'Ổ cứng SSD ADATA Ultimate SU650 256GB là lựa chọn lý tưởng để nâng cấp hiệu suất máy tính với tốc độ cao và độ tin cậy vượt trội. 
Sử dụng công nghệ 3D NAND Flash kết hợp với bộ điều khiển tốc độ cao, SU650 mang đến hiệu suất đọc/ghi lên đến 520/450MB/s, 
giúp khởi động hệ thống nhanh chóng và truyền tải dữ liệu mượt mà. 
Tính năng SLC caching và công nghệ sửa lỗi tiên tiến đảm bảo dữ liệu được bảo vệ an toàn và kéo dài tuổi thọ ổ đĩa',
'Dung lượng: 256GB
Tốc độ đọc (SSD): 520MB/s
Tốc độ ghi (SSD): 450MB/s
Chuẩn giao tiếp: SATA3
Kích thước: 2.5Inch.', 
3, 'LSP014', 'assets/images/SSD1.webp'),
('SP054', 'Ổ Cứng SSD 512GB Patriot P210 SATA III', 700000, 
'Đặc điểm nổi bật:

Tận hưởng tốc độ đọc/ghi ấn tượng với giao tiếp SATA III 6Gbps.
Thiết kế 2.5 inch phổ biến, dễ dàng lắp đặt trên nhiều loại máy tính
Dung lượng 512GB đáp ứng tốt các nhu cầu lưu trữ hằng ngày
Tuổi thọ cao, đảm bảo hoạt động ổn định và lâu dài',
'Model: P210S512G25
Dung lượng: 512GB
Chuẩn giao tiếp: SATA III 6Gbps
Kích thước: 2.5 inch
Tốc độ đọc: 550MB/s
Tốc độ ghi: 500MB/s
Tốc độ ngẫu nhiên 4K: 80.000 IOPS
Tuổi thọ trung bình (MTBF): 2.000.000 giờ',
1, 'LSP014', 'assets/images/SSD2.webp'),
('SP055', 'Ổ cứng SSD Crucial P310 2TB PCIe Gen 4.0 NVMe M.2 2230 CT2000P310SSD2', 500000, 
'SSD Crucial P310 2TB PCIe Gen 4.0 NVMe M.2 2230 - Tốc độ đỉnh cao, hiệu năng vượt trội
Đối với những người yêu thích công nghệ và đang tìm kiếm một giải pháp lưu trữ tối ưu, SSD Crucial P310 2TB PCIe Gen 4.0 NVMe M.2 2230 chính là sự lựa chọn hoàn hảo. 
Với dung lượng lớn, tốc độ xử lý vượt trội và độ bền đáng tin cậy, sản phẩm này là người bạn đồng hành lý tưởng cho mọi nhu cầu sử dụng từ làm việc đến giải trí.',
'Nhà sản xuất: Crucial (Micron USA)
Model: CT2000P310SSD2
Chuẩn giao tiếp: M.2 NVMe PCIe Gen 4×4
Kích thước: 2230
Dung lượng: 2000GB
Random 4K: 1000K IOPS
Đọc tuần tự: 7.100 MB/s
Ghi tuần tự: 6.000 MB/s
TBW: 440 TBW
NAND Flash: Micron 232L 3D-NAND3 TLC.', 
26, 'LSP014', 'assets/images/SSD3.webp'),
('SP056', 'Ssd Sstc 256Gb E130 Oceanic Whitetip E130 W2100-R1300 Mb/S M.2 2280 Nvme Pcie Gen 3', 470000,
'Đặc điểm nổi bật:

Hiệu năng vượt trội: Tốc độ đọc/ghi nhanh chóng giúp tăng tốc hệ thống, khởi động ứng dụng và tải game mượt mà.
Thiết kế nhỏ gọn: Kích thước M.2 2280 phù hợp với nhiều loại bo mạch chủ hiện đại.
Độ bền cao: Với TBW lên đến 150TB, ổ cứng đảm bảo tuổi thọ lâu dài cho người dùng.
Bảo hành dài hạn: Chính sách bảo hành 36 tháng mang lại sự yên tâm cho người sử dụng.',

'Hãng sản xuất: SSTC Oceanic Whitetip  
Model: SSTC-PHI-E13256  
Dung lượng: 256GB  
Kích thước: M.2 2280  
Chuẩn giao tiếp: PCIe Gen 3  
Tốc độ đọc: 2400 MB/s  
Tốc độ ghi: 1200 MB/s.', 
5, 'LSP014', 'assets/images/SSD4.webp');

--
-- Bảng Khách hàng
--

CREATE TABLE `khachhang` (
  `id_khachhang` VARCHAR(20) NOT NULL,
  `ho_ten` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `mat_khau` VARCHAR(255) NOT NULL,
  `so_dien_thoai` VARCHAR(15),
  `otp_code` VARCHAR(6) DEFAULT NULL,
  `otp_expiry` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_khachhang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dữ liệu bảng khách hàng

INSERT INTO `khachhang` (`id_khachhang`, `ho_ten`, `email`, `mat_khau`, `so_dien_thoai`, `otp_code`, `otp_expiry`) VALUES
('KH001', 'Nguyễn Văn A', 'a@gmail.com', '123', '0909123456', NULL, NULL),
('KH002', 'Trần Thị B', 'b@gmail.com', '456', '0988765432', NULL, NULL);



--
-- Bảng Đơn hàng
--
CREATE TABLE `donhang` (
  `id_donhang` VARCHAR(20) NOT NULL,
  `id_khachhang` VARCHAR(20) NOT NULL,
  `ngay_dat` DATETIME NOT NULL,
  `tong_tien` INT(11) NOT NULL,
  `ten_nguoinhan` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `diachi_giaohang` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `sdt_nguoinhan` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `email` VARCHAR(100) NOT NULL,
  `ghi_chu` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `trang_thai` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'Pending',
  `phuongthuc_thanhtoan` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `xac_nhan_admin` TINYINT(1) DEFAULT 0 COMMENT '0 = Chưa xác nhận, 1 = Đã xác nhận',
  `otp_code` VARCHAR(6) DEFAULT NULL,
  `otp_expiry` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id_donhang`),
  FOREIGN KEY (`id_khachhang`) REFERENCES `khachhang`(`id_khachhang`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Dữ liệu cho bảng `donhang`
--
INSERT INTO `donhang` (`id_donhang`, `id_khachhang`, `ngay_dat`, `tong_tien`, `ten_nguoinhan`, `diachi_giaohang`, `sdt_nguoinhan`, 
`email`, `ghi_chu`, `trang_thai`, `phuongthuc_thanhtoan`, `xac_nhan_admin`,`otp_code`, `otp_expiry`
)
VALUES
  ('DH001', 'KH001', '2025-04-23 14:00:00', 13500000, 'Nguyễn Văn A', '123 Đường A, Quận 1, TP.HCM', '0909123456' , 'a@gmail.com', 
  'Giao trong giờ hành chính', 'Đã xác nhận', 'Thanh toán khi nhận hàng', '0', NULL, NULL),

  ('DH002', 'KH002', '2025-04-23 15:30:00', 3000000, 'Trần Thị B', '456 Đường B, Quận 3, TP.HCM', '0988123456', 'b@gmail.com', '', 
  'Chờ xử lý', 'Chuyển khoản', '0', NULL, NULL),

  ('DH003', 'KH001', '2025-04-24 10:00:00', 9500000, 'Nguyễn Văn A', '123 Đường A, Quận 1, TP.HCM', '0909123456', 'a@gmail.com', 
  'Giao trước 12h trưa', 'Đã giao hàng', 'Thanh toán khi nhận hàng', '1', NULL, NULL),

  ('DH004', 'KH002', '2025-04-24 14:45:00', 4300000, 'Trần Thị B', '456 Đường B, Quận 3, TP.HCM', '0988123456', 'b@gmail.com', '', 
  'Đã huỷ', 'Chuyển khoản', '1', NULL, NULL),
  
  ('DH005', 'KH001', '2025-04-25 09:00:00', 1200000, 'Nguyễn Văn A', '123 Đường A, Quận 1, TP.HCM', '0909123456', 'a@gmail.com', 
  'Giao sớm giúp mình', 'Đang vận chuyển', 'Chuyển khoản', '0', NULL, NULL);




--
-- Bảng Giỏ hàng
--
CREATE TABLE `giohang` (
  `id_giohang` VARCHAR(20) NOT NULL,
  `id_khachhang` VARCHAR(20) NOT NULL,
  `id_sanpham` VARCHAR(20) NOT NULL,
  `so_luong` INT(11) NOT NULL,
  PRIMARY KEY (`id_giohang`),
  UNIQUE KEY `unique_khach_sanpham` (`id_khachhang`, `id_sanpham`),
  FOREIGN KEY (`id_khachhang`) REFERENCES `khachhang`(`id_khachhang`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`id_sanpham`) REFERENCES `sanpham`(`id_sanpham`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



--
-- Dữ liệu cho bảng `giohang`
--
INSERT INTO `giohang` (`id_giohang`, `id_khachhang`, `id_sanpham`, `so_luong`) VALUES
('GH001', 'KH001', 'SP001', 1);



--
-- Bảng Bảo hành
--
CREATE TABLE `baohanh` (
  `id_baohanh` VARCHAR(20) NOT NULL,
  `id_sanpham` VARCHAR(20) NOT NULL,
  `thoi_gian_bh` INT(11) NOT NULL COMMENT 'Số tháng bảo hành',
  `mo_ta_bh` TEXT,
  PRIMARY KEY (`id_baohanh`),
  FOREIGN KEY (`id_sanpham`) REFERENCES `sanpham`(`id_sanpham`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



--
-- Dữ liệu cho bảng `baohanh`
--
INSERT INTO `baohanh` (`id_baohanh`, `id_sanpham`, `thoi_gian_bh`, `mo_ta_bh`) VALUES
('BH001', 'SP001', 12, 'Bảo hành 12 tháng theo chính sách'),
('BH002', 'SP002', 12, 'Bảo hành 12 tháng theo chính sách'),
('BH003', 'SP003', 24, 'Bảo hành 24 tháng theo chính sách'),
('BH004', 'SP004', 24, 'Bảo hành 24 tháng theo chính sách'),
('BH005', 'SP005', 24, 'Bảo hành 24 tháng theo chính sách'),
('BH006', 'SP006', 24, 'Bảo hành 24 tháng theo chính sách'),
('BH007', 'SP007', 12, 'Bảo hành 12 tháng theo chính sách'),
('BH008', 'SP008', 12, 'Bảo hành 12 tháng theo chính sách'),
('BH009', 'SP009', 24, 'Bảo hành 24 tháng theo chính sách'),
('BH010', 'SP010', 12, 'Bảo hành 12 tháng theo chính sách'),
('BH011', 'SP011', 24, 'Bảo hành 24 tháng theo chính sách'),
('BH012', 'SP012', 12, 'Bảo hành 12 tháng theo chính sách'),
('BH013', 'SP013', 12, 'Bảo hành 12 tháng theo chính sách'),
('BH014', 'SP014', 24, 'Bảo hành 24 tháng theo chính sách'),
('BH015', 'SP015', 12, 'Bảo hành 12 tháng theo chính sách'),
('BH016', 'SP016', 12, 'Bảo hành 12 tháng theo chính sách'),
('BH017', 'SP017', 12, 'Bảo hành 12 tháng theo chính sách'),
('BH018', 'SP018', 6,  'Bảo hành 6 tháng theo chính sách'),
('BH019', 'SP019', 12, 'Bảo hành 12 tháng theo chính sách'),
('BH020', 'SP020', 6,  'Bảo hành 6 tháng theo chính sách'),
('BH021', 'SP021', 24, 'Bảo hành 24 tháng theo chính sách'),
('BH022', 'SP022', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH023', 'SP023', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH024', 'SP024', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH025', 'SP025', 24, 'Bảo hành 24 tháng theo chính sách'),
('BH026', 'SP026', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH027', 'SP027', 24, 'Bảo hành 24 tháng theo chính sách'),
('BH028', 'SP028', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH029', 'SP029', 24, 'Bảo hành 24 tháng theo chính sách'),
('BH030', 'SP030', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH031', 'SP031', 24, 'Bảo hành 24 tháng theo chính sách'),
('BH032', 'SP032', 24, 'Bảo hành 24 tháng theo chính sách'),
('BH033', 'SP033', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH034', 'SP034', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH035', 'SP035', 24, 'Bảo hành 24 tháng theo chính sách'),
('BH036', 'SP036', 24, 'Bảo hành 24 tháng theo chính sách'),
('BH037', 'SP037', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH038', 'SP038', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH039', 'SP039', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH040', 'SP040', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH041', 'SP041', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH042', 'SP042', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH043', 'SP043', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH044', 'SP044', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH045', 'SP045', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH046', 'SP046', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH047', 'SP047', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH048', 'SP048', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH049', 'SP049', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH050', 'SP050', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH051', 'SP051', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH052', 'SP052', 60, 'Bảo hành 60 tháng theo chính sách'),
('BH053', 'SP053', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH054', 'SP054', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH055', 'SP055', 36, 'Bảo hành 36 tháng theo chính sách'),
('BH056', 'SP056', 36, 'Bảo hành 36 tháng theo chính sách');


--
-- Bảng Khuyến Mãi
--
CREATE TABLE `khuyenmai` (
  `id_khuyenmai` VARCHAR(20) NOT NULL,
  `id_sanpham` VARCHAR(20) NOT NULL,
  `ten_km` VARCHAR(255) NOT NULL,
  `giam_gia_percent` INT(11) DEFAULT 0,
  `giam_gia_tien` INT(11) DEFAULT 0,
  `ngay_bat_dau` DATETIME NOT NULL,
  `ngay_ket_thuc` DATETIME NOT NULL,
  PRIMARY KEY (`id_khuyenmai`),
  FOREIGN KEY (`id_sanpham`) REFERENCES `sanpham`(`id_sanpham`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Dữ liệu bảng khuyến mãi
--
INSERT INTO `khuyenmai` (`id_khuyenmai`, `id_sanpham`, `ten_km`, `giam_gia_percent`, `giam_gia_tien`, `ngay_bat_dau`, `ngay_ket_thuc`) VALUES
('KM001', 'SP001', 'Giảm giá ngày hè', '10%', NULL, '2025-05-20 12:00:00', '2025-06-10 12:00:00'),
('KM002', 'SP006', 'Flash Sale', '20%', NULL, '2025-06-01 14:00:00', '2025-06-10 14:00:00'),
('KM003', 'SP022', 'Giảm giá ngày hè', '10%', NULL, '2025-05-20 15:00:00', '2025-06-10 15:00:00'),
('KM004', 'SP037', 'Flash Sale', NULL, '300000', '2025-06-01 12:00:00', '2025-06-10 12:00:00'),
('KM005', 'SP041', 'Flash Sale', NULL, '250000', '2025-06-01 9:00:00', '2025-06-10 9:00:00'),
('KM006', 'SP046', 'Giảm giá ngày hè', '20%', NULL, '2025-05-20 10:00:00', '2025-06-10 10:00:00');


--
-- Bảng Đánh giá
--
CREATE TABLE `danhgia` (
  `id_danhgia` VARCHAR(20) NOT NULL,
  `id_sanpham` VARCHAR(20) NOT NULL,
  `id_khachhang` VARCHAR(20) NOT NULL,
  `sao` INT(1) NOT NULL CHECK (`sao` BETWEEN 1 AND 5),
  `binh_luan` TEXT,
  `ngay_danhgia` DATETIME NOT NULL,
  PRIMARY KEY (`id_danhgia`),
  FOREIGN KEY (`id_sanpham`) REFERENCES `sanpham`(`id_sanpham`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`id_khachhang`) REFERENCES `khachhang`(`id_khachhang`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dữ liệu cho bảng `danhgia`
--
INSERT INTO `danhgia` (`id_danhgia`, `id_sanpham`, `id_khachhang`, `sao`, `binh_luan`, `ngay_danhgia`) VALUES
('DG001', 'SP001', 'KH001', 5, 'Rất hài lòng!', '2025-04-22 16:00:00'),
('DG002', 'SP002', 'KH002', 4, 'Dùng tốt, giao hàng nhanh.', '2025-04-21 10:30:00');


CREATE TABLE `chitietdonhang` (
  `id_chitietdh` VARCHAR(20) NOT NULL,
  `id_donhang` VARCHAR(20) NOT NULL,
  `id_sanpham` VARCHAR(20) NOT NULL,
  `so_luong_mua` INT(11) NOT NULL,
  `gia_luc_mua` INT(11) NOT NULL,
  PRIMARY KEY (`id_chitietdh`),
  FOREIGN KEY (`id_donhang`) REFERENCES `donhang`(`id_donhang`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`id_sanpham`) REFERENCES `sanpham`(`id_sanpham`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `khachhang`
ADD COLUMN `active` BOOLEAN NOT NULL DEFAULT 1;
 

CREATE TABLE `thuonghieu` (
  `id_thuonghieu` VARCHAR(20) NOT NULL,
  `ten_thuonghieu` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id_thuonghieu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `thuonghieu` (`id_thuonghieu`, `ten_thuonghieu`) VALUES
('TH001', 'Intel'), ('TH002', 'AMD'), ('TH003', 'NVIDIA'), ('TH004', 'Corsair'),
('TH005', 'ASUS'), ('TH006', 'MSI'), ('TH007', 'Gigabyte'), ('TH008', 'Kingston'),
('TH009', 'Samsung'), ('TH010', 'Western Digital'), ('TH011', 'Seagate'),
('TH012', 'Logitech'), ('TH013', 'Akko'), ('TH014', 'Dare-U'), ('TH015', 'Xinmeng'),
('TH016', 'Adata'), ('TH017', 'PNY'), ('TH018', 'Acer'), ('TH019', 'Dell'),
('TH020', 'AOC'), ('TH021', 'Attack Shark'), ('TH022', 'Cougar'),
('TH023', 'HyperWork'), ('TH024', 'Warrior'), ('TH025', 'Aivision'),
('TH026', 'Inno3D'), ('TH027', 'Patriot'), ('TH028', 'Crucial'), ('TH029', 'SSTC'),
('TH030', 'ViewFinity'); -- Thêm các thương hiệu khác nếu có


ALTER TABLE `sanpham`
ADD COLUMN `id_thuonghieu` VARCHAR(20) NULL DEFAULT NULL AFTER `id_loai`,
ADD CONSTRAINT `fk_sanpham_thuonghieu` FOREIGN KEY (`id_thuonghieu`) REFERENCES `thuonghieu`(`id_thuonghieu`) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE `sanpham` SET `id_thuonghieu` = 'TH013' WHERE `ten_sp` LIKE '%Akko%';
UPDATE `sanpham` SET `id_thuonghieu` = 'TH015' WHERE `ten_sp` LIKE '%Xinmeng%';
UPDATE `sanpham` SET `id_thuonghieu` = 'TH005' WHERE `ten_sp` LIKE '%ASUS%';
UPDATE `sanpham` SET `id_thuonghieu` = 'TH012' WHERE `ten_sp` LIKE '%Logitech%';
UPDATE `sanpham` SET `id_thuonghieu` = 'TH001' WHERE `ten_sp` LIKE '%Intel Core%';
UPDATE `sanpham` SET `id_thuonghieu` = 'TH002' WHERE `ten_sp` LIKE '%AMD RYZEN%' OR `ten_sp` LIKE '%AMD Ryzen%';
-- VÀ TIẾP TỤC CHO CÁC SẢN PHẨM KHÁC...

ALTER TABLE `sanpham` ADD COLUMN `ngay_tao` DATETIME DEFAULT CURRENT_TIMESTAMP AFTER `hinh_anh`;