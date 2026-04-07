<?php
// chinh-sach-bao-hanh.php

include 'includes/header.php';
// require 'includes/connect.php'; 


$totalQuantity = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $productId_cart => $quantity_cart) {
        if (is_numeric($quantity_cart) && $quantity_cart > 0) {
            $totalQuantity += (int)$quantity_cart;
        }
    }
}
$cart_page_url = isset($_SESSION['user_id']) ? 'cart.php' : 'dangnhap.php';

include 'includes/main_navigation.php'; 
?>

<main class="static-page warranty-policy-page" style="padding: 30px 0; background-color: #f9f9f9;">
    <div class="container" style="background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
        <nav aria-label="breadcrumb" style="margin-bottom: 25px; font-size: 0.9em;">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Chính sách bảo hành</li>
            </ol>
        </nav>

        <h1 class="page-title">CHÍNH SÁCH BẢO HÀNH</h1>
        <h2 class="page-subtitle">QUY ĐỊNH VỀ CHÍNH SÁCH BẢO HÀNH</h2>

        <div class="warranty-content">
            <p class="highlight-link" style="margin-bottom: 25px;">
                <i class="fas fa-caret-right"></i><i class="fas fa-caret-right"></i><i class="fas fa-caret-right"></i>
                <a href="#tra-cuu-bao-hanh" style="color: #E53935; font-weight: bold; text-decoration: underline;">Tra cứu bảo hành tại đây</a>
                
            </p>

            <section id="dia-diem-bh">
                <h3>I. ĐỊA ĐIỂM BẢO HÀNH</h3>
                <h4>1. NHẬN BẢO HÀNH</h4>
                <p>Tất cả hàng hóa PC Shop Nasa bán ra đều được bảo hành tại hệ thống cửa hàng của chúng tôi. <strong>Trung tâm bảo hành chính:</strong>Phân khu đào tạo E1, Khu Công Nghệ cao TP.HCM, Phường Hiệp Phú, TP. Thủ Đức, TP.HCM</p>
                <ul>
                    <li><strong>Hotline nhận hàng:</strong> 0919 267 015</li>
                    <li><strong>Quy trình:</strong> Nhân viên giao dịch bảo hành nhận, rà biên nhận ngay, và sẽ gửi tin nhắn mời khách hàng mang biên nhận đến nhận hàng tại cửa hàng quý khách đến bảo hành.</li>
                    <li><strong>Hotline nhận hàng (khách gửi bảo hành qua xe/chuyển phát):</strong> 0903 955 507 – Trung tâm bảo hành PC Shop Nasa</li>
                    <li><strong>Quy trình:</strong> Tin Học Ngôi Sao nhận hàng bảo hành , khi xong sẽ đóng gói và vận chuyển tận nhà quý khách hàng.</li>
                </ul>
                <p><strong>Tiếp nhận phản ánh:</strong></p>
                <ul>
                    <li>Email: phananhbaohanh@pcshopnasa.com</li>
                    <li>Hotline: 0931 888 888 - Chăm sóc khách hàng</li>
                </ul>
                <p><strong>Giờ làm việc:</strong> Từ Thứ 2 đến Thứ 7 (giờ hành chính)</p>
                <ul>
                    <li>Sáng: 08h30 - 12h00</li>
                    <li>Chiều: 13h00 - 17h30</li>
                    <li>Nghỉ trưa: 12h00 - 13h00 (60 phút)</li>
                </ul>
            </section>

            <section id="dieu-kien-bh" style="margin-top:30px;">
                <h3>II. ĐIỀU KIỆN BẢO HÀNH</h3>
                <ol type="2.1" style="list-style-type: none; padding-left: 0;">
                    <li><strong>2.1</strong> Sản phẩm còn trong thời hạn bảo hành, có tem bảo hành ( hoặc Serial) của nhà phân phối và tem PC Shop Nasa, với hư hỏng xác định do lỗi kỹ thuật hoặc lỗi của nhà sản xuất.</li>
                    <li><strong>2.2</strong> Tem bảo hành và mã vạch phải phải nguyên vẹn, không rách rời, vỡ nát, biến dạng, không bị tẩy xóa.</li>
                    <li><strong>2.3</strong> Sản phẩm còn nguyên vẹn, không cong, vênh, rạn nứt, trầy xước, sứt mẻ, nứt khe cắm, vỡ.</li>
                    <li><strong>2.4</strong> Sử dụng đúng nguồn điện, không bị mối mọt, côn trùng xâm nhập, không cháy nổ, phồng tụ, không bị oxy hóa do môi trường ẩm ướt.</li>
                    <li><strong>2.5</strong> Không can thiệp vào phần cứng (tự ý tháo dỡ, sửa chữa).</li>
                    <li><strong>2.6</strong> Các điều kiện bảo hành tuân theo tiêu chuẩn bảo hành của nhà sản xuất hoặc nhà phân phối tại Việt Nam.</li>
                    <li><strong>2.7</strong> Quý khách vui lòng kiểm tra serial khi mua hàng để dễ dàng bảo hành. Nếu không có serial trong phần mềm PC Shop Nasa, chúng tôi có quyền từ chối bảo hành.</li>
                    <li><strong>2.8</strong> Quý khách lưu trữ phiếu mua hàng để tiện đổi trả trong 07 ngày.</li>
                    <li><strong>2.9</strong> Với hàng cũ, vui lòng mang đủ phụ kiện ( cáp , chân...) giống lúc mua để tránh việc phải trừ tiền phụ kiện.</li>
                </ol>
            </section>

            <section id="tieu-chuan-bh-hang" style="margin-top:30px;">
                <h3>TIÊU CHUẨN BẢO HÀNH THEO HÃNG</h3>
                <div class="brand-list" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">
                    <span class="brand-tag">DELL</span> <span class="brand-tag">AMD</span> <span class="brand-tag">LG</span>
                    <span class="brand-tag">ASUS</span> <span class="brand-tag">SAMSUNG</span> <span class="brand-tag">HP</span>
                    <span class="brand-tag">WESTERN</span> <span class="brand-tag">LOGITECH</span> <span class="brand-tag">GIGABYTE</span>
                    <span class="brand-tag">ACER</span> <span class="brand-tag">TOSHIBA</span> <span class="brand-tag">AOC</span>
                    <span class="brand-tag">TP-LINK</span> <span class="brand-tag">SEAGATE</span> <span class="brand-tag">LENOVO</span>
                    <span class="brand-tag">MSI</span> <span class="brand-tag">INTEL</span> <span class="brand-tag">VIEWSONIC</span>
                    <span class="brand-tag">ZOTAC</span> <span class="brand-tag">IMOU</span>
                  
                </div>
                
            </section>


            <section id="khong-bh" style="margin-top:30px;">
                <h3>III. ĐIỀU KIỆN KHÔNG BẢO HÀNH</h3>
                 <ol type="3.1" style="list-style-type: none; padding-left: 0;">
                    <li><strong>3.1</strong> Sản phẩm hết thời hạn bảo hành hoặc không do PC Shop Nasa bán ra.</li>
                    <li><strong>3.2</strong> Tem bảo hành, mã vạch, chỉ số dung lượng, số serial number bị rách, mờ hoặc có dấu hiệu sửa chữa.</li>
                    <li><strong>3.3</strong> Thiết bị bị va chạm, rơi rớt, bể mẻ, móp méo, biến dạng, trầy xước, rỉ xét, xì hoặc phù tụ.</li>
                    <li><strong>3.4</strong> Thiết bị có dấu hiệu cháy nổ, côn trùng xâm nhập hoặc đặt trong môi trường ẩm ướt.</li>
                    <li><strong>3.5</strong> Hư hỏng do thiên tai , sử dụng nguồn điện không ổn định hoặc vận chuyển không đúng quy cách.</li>
                    <li><strong>3.6</strong> Không bảo hành các lỗi do phần mềm gây ra và các phụ kiện tiêu hao đi kèm . Riêng adapter của các thiết bị Modem, Access Point, LCD... vẫn bảo hành 1 tháng.</li>
                    <li><strong>3.7</strong> PC Shop Nasa không chịu trách nhiệm về dữ liệu trong ổ cứng , SSD nư.</li>
                </ol>
            </section>

            <section id="thoi-gian-bh" style="margin-top:30px;">
                <h3>IV. THỜI GIAN GIỮ BẢO HÀNH</h3>
                <p><strong>4.1 Đối với hàng thuộc diện bảo hành 1 đổi 1 của PC Shop Nasa:</strong></p>
                <ul>
                    <li><strong>Khách hàng tại TP. HCM:</strong> Trong vòng 02 giờ làm việc, trừ trường hợp hết hàng.</li>
                    <li><strong>Khách hàng tỉnh:</strong> Trong vòng 07 ngày làm việc (Không tính Chủ nhật), tính từ lúc nhận hàng tại công ty <a href="#tra-cuu-bao-hanh" style="color: #E53935; font-weight: bold;">>>> Tra cứu bảo hành tại đây</a></li>
                </ul>
                <p><strong>4.2 Đối với với bảo hành chính hãng:</strong></p>
                 <ul>
                    <li><strong>Thời gian bảo hành phổ biến :</strong> Trong vòng 14 ngày làm việc (Không tính thứ 7 và Chủ nhật) tính từ lúc nhận hàng. Nếu quá thời hạn trên, chúng tôi sẽ thông báo lý do đến khách hàng</li>
                    <li><strong>Thời gian tối đa cho bảo hành linh kiện :</strong> 30 ngày.</li>
                    <li>Trong một số trường hợp không đủ điều kiện bảo hành. PC Shop Nasa vẫn có thể nhận bảo hành nhưng thời gian trả hàng phụ thuộc vào hãng (thường từ 2-6 tháng do đi nước ngoài)</li>
                </ul>
                <p><strong>4.4 Trường hợp hàng bảo hành không còn do đã ngưng sản xuất hoặc không còn kinh doanh:</strong></p>
                <p>Khách hàng có thể lựa chọn một trong các cách sau:</p>
                <ul>
                    <li><strong>4.4.1. Đổi một sản phẩm tương đương:</strong> (nếu có) và không nhất thiết phải là hàng mới 100%.</li>
                    <li><strong>4.4.2. Đổi một sản phẩm cấp cao hơn:</strong> nếu khách hàng đồng ý với giá thương lượng đổi bù. Thời hạn bảo hành sẽ được tính tiếp theo thời hạn bảo hành của sản phẩm cũ.</li>
                    <li><strong>4.4.3. Thương lượng hoàn tiền:</strong> với mức khấu trừ cụ thể như sau:</li>
                    <ul>
                        <li><strong>Hàng mới:</strong> (Chi tiết khấu trừ theo từng loại sản phẩm và thời gian sử dụng)</li>
                        <li><strong>Hàng cũ:</strong> (Chi tiết khấu trừ theo tem bảo hành)</li>
                    </ul>
                    <li><strong>Tối đa khấu trừ:</strong> 70% giá trị sản phẩm. Giá trị khấu trừ được tính theo giá thị trường tại thời điểm khấu trừ. Nếu hàng hóa không còn, sẽ căn cứ vào giá của sản phẩm có hiệu năng tương tự.</li>
                </ul>
                <p><strong>Quý khách lưu ý ý:</strong></p>
                 <ul>
                    <li>Xin vui lòng giữ biên nhận bảo hành cẩn thận để mang theo khi nhận hàng. Nếu mất biên nhận, quý khách vui lòng cung cấp giấy tờ tùy thân ( hoặc VneID ) hoặc giấy giới thiệu (nếu là công ty) và số điện thoại đúng như trên biên nhận.</li>
                    <li>Không giải quyết các trường hợp ngoại lệ. Nếu quá 45 ngày, PC Shop Nasa xin phép từ chối xử lý.</li>
                </ul>
            </section>

          
            <section id="che-do-dac-biet" style="margin-top:30px;">
                <h3>V. CÁC CHẾ ĐỘ BẢO HÀNH ĐẶC BIỆT</h3>
                <p><strong>5.1 Chế độ bảo hành 1 đổi 1:</strong></p>
                <p>Tất cả sản phẩm bán ra có chế độ bảo hành 1 đổi 1 đều được công bố trên website pcshopnasa.com . Xem thêm <a href="#" style="color: #E53935; font-weight: bold;">bảo hành siêu tốc 1h tại đây</a>.</p>
                 <p><strong>5.2 Bảo hành cho sản phẩm cũ</strong></p>
                <p>Tất cả sản phẩm cũ do PC Shop Nasa bán ra đều có chế độ bảo hành 1 đổi 1 trong vòng 01 tháng đầu.</p>
            </section>

            <section id="nhan-hang-bh-tinh" style="margin-top:30px;">
                <h3>VII. NHẬN HÀNG BẢO HÀNH Khu vực tỉnh ( Khác Thành phố Hồ Chí Minh )</h3>
                <p>Quý khách vui lòng chuyển phát hàng hóa đến tận PC Shop Nasa.</p>
                 <ol>
                    <li><strong>Đóng gói hàng hóa:</strong>
                        <ul>
                            <li>Quý khách hãy đóng gói cẩn thận để tránh hư hỏng, va đập, nhằm tránh bị từ chối nhận lại. Đặc biệt, với các mặt hàng điện tử dễ vỡ, xin không gửi qua dịch vụ chuyển phát Viettel (do PC Shop Nasa đã gặp phải tình trạng vỡ màn hình với tỷ lệ trên 40% khi gửi qua dịch vụ này ra miền Bắc).</li>
                        </ul>
                    </li>
                    <li><strong>Thông tin người nhận:</strong>
                        <ul>
                            <li><strong>Phòng Bảo Hành/ Đổi Trả PC Shop Nasa</strong></li>
                            <li><strong>Địa chỉ:</strong> Phân khu đào tạo E1, Khu Công Nghệ cao TP.HCM, Phường Hiệp Phú, TP. Thủ Đức, TP.HCM</li>
                            <li><strong>SĐT:</strong> 0903 955 507</li>
                        </ul>
                    </li>
                    <li><strong>Thông tin người gửi:</strong>
                        <ul>
                            <li>Xin ghi rõ họ tên, địa chỉ người gửi và số điện thoại liên lạc. <strong>PC Shop Nasa</strong> sẽ trả hàng tận nhà, vì vậy thông tin cần chính xác 100%.</li>
                            <li>Xin ưu tiên chuyển hàng qua bưu điện , hoặc các nhà chuyển phát tận nơi khác</li>
                        </ul>
                    </li>
                    <li><strong>Gọi điện thông báo sau khi gửi hàng</strong>
                        <ul>
                            <li>Nếu gửi hàng qua chành xe, sau khi gửi hàng, quý khách vui lòng gọi điện đến số 0903 955 507 để thông báo <strong>PC Shop Nasa</strong> nhận hàng. Các thông tin cần cung cấp bao gồm: ... (bạn tự điền)</li>
                        </ul>
                    </li>
                </ol>
                 <p><strong>Thông tin cập nhật:</strong> Trong vòng 01 ngày, PC Shop Nasa sẽ cập nhật thông tin hàng nhận được hàng ngày để quý khách yên tâm.</p>
                 <p><strong>Kiểm tra sản phẩm:</strong> Sau khi nhận hàng, chúng tôi sẽ kiểm tra sản phẩm để xác định có đủ điều kiện bảo hành hay không, và sẽ thông báo cụ thể đến khách hàng.</p>
                 <p><strong>Thời gian xử lý:</strong> Thời gian mở kiện hàng và lắp biên nhận: tối đa không quá 02 ngày làm việc kể từ lúc nhận hàng. Thời Gian PC Shop Nasa nhận hàng : tối đa 01 ngày làm việc kể từ khi hàng đến HCM</p>
                 <p><strong>Phí gửi trả hàng bảo hành:</strong> PC Shop Nasa đóng gói hàng rất cẩn thận và giao tận nhà với mức phí tiết kiệm (chỉ tương đương với chi phí chành xe) để tránh hư hỏng linh kiện.</p>
                 <p><strong>Phản hồi và khiếu nại:</strong> Mọi thắc mắc hoặc khiếu nại về thái độ phục vụ của nhân viên, quý khách vui lòng liên hệ: ... (SĐT, Email)</p>
            </section>


            <p style="margin-top: 40px; text-align: center; font-style: italic;">
                Chân thành cảm ơn quý khách đã tin tưởng và ủng hộ PC Shop Nasa. Rất hân hạnh và sẵn sàng phục vụ quý khách.
            </p>
        </div>

        <?php include 'chatbox.php'; ?>

    </main>

<?php

include 'includes/footer.php';
?>