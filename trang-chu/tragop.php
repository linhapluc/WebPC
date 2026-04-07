<?php

include 'includes/header.php';


$totalQuantity = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $productId_cart => $quantity_cart) {
        if (is_numeric($quantity_cart) && $quantity_cart > 0) {
            $totalQuantity += (int)$quantity_cart;
        }
    }
}
$cart_page_url = isset($_SESSION['user_id']) ? 'cart.php' : 'dangnhap.php';

?>

<?php
// 4. INCLUDE HEADER CHUNG (TOP-BAR VÀ SITE-HEADER)

include 'includes/main_navigation.php';
?>

<main class="static-page installment-page" style="padding: 30px 0;">
    <div class="container">
        <nav aria-label="breadcrumb" style="margin-bottom: 20px; font-size: 0.9em;">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                <li class="breadcrumb-item active" aria-current="page">Trả góp</li>
            </ol>
        </nav>

        <h1 style="text-align: center; margin-bottom: 30px; color: #333; font-size: 2em;">CHÍNH SÁCH TRẢ GÓP</h1>

        <section class="installment-section" id="tra-gop-tai-chinh">
            <h2 class="section-title">HÌNH THỨC TRẢ GÓP TÀI CHÍNH</h2>
            <div class="table-responsive"> 
                <table class="table table-bordered table-striped installment-table"> 
                    <thead>
                        <tr>
                            <th>Đơn vị</th>
                            <th> HOME CREDIT</th>
                            <th> HD SAISON</th>
                            <th> ACS</th>
                            <th> MIRAE ASSET</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Độ tuổi</strong></td>
                            <td>Trên 20 tuổi</td>
                            <td>Trên 18 tuổi</td>
                            <td>Trên 20 tuổi</td>
                            <td>Trên 20 tuổi</td>
                        </tr>
                        <tr>
                            <td><strong>Kỳ hạn</strong></td>
                            <td>6 - 9 - 12 tháng</td>
                            <td>6 - 36 tháng</td>
                            <td>6 - 9 - 12 tháng</td>
                            <td>6 - 9 - 12 tháng</td>
                        </tr>
                        <tr>
                            <td><strong>Trả trước</strong></td>
                            <td>Từ 40% giá trị sản phẩm</td>
                            <td>Từ 10% giá trị sản phẩm</td>
                            <td>Từ 10% giá trị sản phẩm</td>
                            <td>Từ 50% giá trị sản phẩm</td>
                        </tr>
                        <tr>
                            <td><strong>Số tiền trả trước tối thiểu</strong></td>
                            <td>Tối thiểu 2.000.000đ</td>
                            <td>10% giá trị đơn hàng</td>
                            <td>Tối thiểu 2.000.000đ</td>
                            <td>Tối thiểu 2.000.000đ</td>
                        </tr>
                        <tr>
                            <td><strong>Lãi suất</strong></td>
                            <td>1.91%</td>
                            <td>1.66% - 2.3% (*)</td>
                            <td>1.84%</td>
                            <td>1.08% (**)</td>
                        </tr>
                        <tr>
                            <td><strong>Thủ tục, giấy tờ</strong></td>
                            <td>CMND / CCCD</td>
                            <td>CMND / CCCD / GPLX / Giấy đăng ký kết hôn / cà vẹt xe</td>
                            <td>CMND/CCCD & GPLX / Hộ khẩu</td>
                            <td>CMND/CCCD & hộ khẩu/GPLX & Chứng minh cư trú & Chứng minh thu nhập</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="notes" style="margin-top: 20px; font-size: 0.9em; line-height: 1.7;">
                <p><strong>LƯU Ý:</strong></p>
                <p><strong>(*): Đối với hình thức trả góp qua HD SAISON :</strong></p>
                <ol>
                    <li>Lãi suất sẽ tùy thuộc vào số tiền trả trước sản phẩm (vui lòng liên hệ theo SĐT dưới đây để được tư vấn chi tiết).</li>
                    <li>Khoảng vay dưới 30 triệu chỉ cần cung cấp CMND + GPLX hoặc CMND + Hộ khẩu.</li>
                    <li>Ưu đãi lãi suất thấp khi khách hàng cung cấp đầy đủ hồ sơ bao gồm : CMND, GPLX / Hộ khẩu, HĐ tiền ích, chứng minh thu nhập.</li>
                    <li>Liên hệ: 0987 654 321</li>
                </ol>
                <p><strong>(**): Đối với hình thức trả góp qua Mirae Asset:</strong> Hồ sơ cần thêm CCCD + HK (GPLX) + chứng minh cư trú + chứng minh thu nhập + trả trước 50% giá trị đơn hàng (để có lãi suất 1.08%)</p>
                <p><strong>Liên hệ cửa hàng:</strong></p>
                <ul>
                    <li>0123 000 001 - Tân Bình</li>
                    <li>0456 000 003 - Quận 9</li>
                    <li>0789 000 004 - Bình Thạnh</li>
                </ul>
                <p><strong>Tư vấn Online:</strong></p>
                 <ul>
                    <li>0228 060 0909 (Ms. Hiền)</li>
                    <li>0228 060 0666 (Mr. Đăng)</li>
                    <li>0228 060 1732 (Mr. Linh)</li>
                </ul>
            </div>
        </section>

        <section class="installment-section" id="tra-gop-mpos" style="margin-top: 40px;">
            <h2 class="section-title">HÌNH THỨC TRẢ GÓP QUA THẺ TÍN DỤNG MPOS</h2>
            <div class="content-block">
                <p><strong>Điều kiện trả góp:</strong></p>
                <ul>
                    <li>Kỳ hạn trả góp: 3 - 12 tháng</li>
                    <li>Phí quẹt thẻ Visa/Master/JCB: Online (2.5%), Trực tiếp (1.9%)</li>
                    <li>Sản phẩm áp dụng: Tất cả những sản phẩm, linh kiện NEW và 2ND (Cũ) đang được bán ra tại PC Shop Nasa</li>
                    <li>Giá trị trả góp tối thiểu: từ 3.000.000đ trở lên</li>
                    <li>Địa điểm áp dụng: Trên tất cả Showroom / Chi nhánh của PC Shop Nasa</li>
                    <li>Áp dụng: Áp dụng cho chủ thẻ tín dụng quốc tế (VISA, MASTERCARD, UNION PAY, JCB)</li>
                    <li>Lưu ý: Mỗi khách hàng được tham gia chương trình nhiều lần, với tổng giá trị các đơn hàng không vượt quá hạn mức thẻ tín dụng.</li>
                </ul>
                <p><strong>Ưu điểm hình thức trả góp từ xa (online):</strong></p>
                <ol>
                    <li>Ứng dụng thanh toán mPOS giúp cửa hàng và doanh nghiệp đáp ứng tối đa nhu cầu thanh toán của khách hàng khi đến mua sắm sản phẩm - dịch vụ.</li>
                    <li>Tương thích với hệ điều hành IOS và Android</li>
                    <li>Giao diện dễ sử dụng và tích hợp nhiều tiện ích</li>
                    <li>Xác thực thanh toán trực tiếp trên ứng dụng bằng chữ ký</li>
                    <li>Quản lý và đối soát giao dịch nhanh chóng</li>
                    <li>Thông tin giao dịch và hóa đơn được gửi trực tiếp qua SMS/Email</li>
                </ol>
                <p><strong>Quy trình đăng ký trả góp từ xa (Online):</strong></p>
                <ul>
                    <li><strong>Bước 1:</strong> Tải ứng dụng mPOS.vn (hỗ trợ ngay trên iOS và Android)</li>
                    <li><strong>Bước 2:</strong> Đăng ký tài khoản trên Webiste của mpos.vn (Cung cấp đầy đủ thông tin theo yêu cầu)</li>
                    <li><strong>Bước 3:</strong> Đăng nhập tài khoản đến App mPOS.vn, thực hiện theo chỉ dẫn để lựa chọn hình thức trả góp phù hợp.</li>
                </ul>
                 <p><strong>Đăng ký trả góp trực tiếp tại cửa hàng:</strong></p>
                <ul>
                    <li><strong>Bước 1:</strong> Khách hàng cung cấp thẻ tín dụng hợp lệ của ngân hàng và đăng kí trả góp theo đơn đăng ký (Tại cửa hàng)</li>
                    <li><strong>Bước 2:</strong> Thu ngân xuất phiếu bán (Có ghi chú trả góp trên phiếu) và cà thẻ cho khách số tiền theo nội dung đơn đăng ký.</li>
                    <li><strong>Bước 3:</strong> Khách hàng thanh toán phần tiền còn lại không đăng kí trả góp (nếu có) và nhận hàng sau khi hoàn tất thủ tục.</li>
                </ul>
                <p><strong>Các ngân hàng liên kết hỗ trợ trả góp:</strong></p>
                <p>Sacombank | HSBC | Shinhan | VIB | Eximbank | Maritimebank | TP Bank | Vietcombank | MB Bank | ACB | BIDV | Viet Capital Bank.</p>
                <p>VPBank | Techcombank | Citibank | Seabank | SCB | SHB | Standard Chartered | Nam Á Bank | FeCredit | OCB | Kiên Long Bank | Home Credit.</p>
            </div>
        </section>

       
        <section class="installment-section" id="tra-gop-alepay" style="margin-top: 40px;">
            <h2 class="section-title">HÌNH THỨC TRẢ GÓP QUA THẺ TÍN DỤNG ALEPAY</h2>
             <div class="content-block">
                <p><strong>Những ưu điểm nổi bật:</strong></p>
                 <ul>
                    <li>Cung cấp giải pháp thanh toán trực tuyến bằng thẻ nội địa và thẻ quốc tế vô cùng dễ dàng.</li>
                    <li>Cho phép người mua liên kết thẻ hoặc tài khoản ngân hàng với website bán hàng hoặc ứng dụng di động của người bán để tự động thanh toán các giao dịch</li>
                    <li>Thanh toán định kỳ tự động - chỉ cần đăng ký thanh toán 1 lần duy nhất sẽ được tự động thanh toán cho các chu kỳ tiếp theo.</li>
                    <li>Thanh toán áp dụng trên các loại thẻ quốc tế như : Visa / Master / JCB.</li>
                    <li>Đa dạng ngân hàng liên kết : ANZ Bank, Vietcombank, Citi Bank, Eximbank, HSBC, VietinBank, Maritime Bank, Nam Á Bank, SeA Bank, Sacombank, VP Bank, Standard Chartered, Techcombank, SCB, FE Credit, VIB</li>
                </ul>
                <p><strong>Các bước áp dụng hình thức đặt hàng Online qua thẻ :</strong></p>
                <p><u>Bước 1:</u> Lựa chọn sản phẩm đặt hàng và cung cấp đầy đủ thông tin cá nhân <u>Bước 2:</u> Hình thức giao hàng và thanh toán -> mục hình thức thanh toán -> chọn Alepay Payment -> chúng ta có 3 hình thức thanh toán để lựa chọn.</p>
                <ul>
                    <li>Thanh toán bằng thẻ quốc tế : cung cấp thông tin thẻ bạn đang sử dụng -> tiến hành thanh toán.</li>
                    <li>Thanh toán trả góp : chọn ngân hàng hỗ trợ - loại thẻ - tháng trả (thông tin trả về sẽ là số tiền trả góp hàng tháng) -> tiến hành thanh toán trả góp (yêu cầu xác thực nếu có)</li>
                    <li>Thanh toán bằng thẻ ATM/IB : lựa chọn 1 trong 4 hình thức (VISA-Master-JCB / Thẻ ATM / Internet Banking / QRCode ) chọn ngân hàng -> tiến hành thanh toán.</li>
                </ul>
                <p>**Lưu ý ý: Trong quá trình đặt hàng nếu có những trường hợp giao dịch thất bại hoặc khiếu nại, Khách hàng có thể phản hồi để được hỗ trợ từ Alepay (Hotline : 1900 1155) và CSKH PC Shop Nasa.</p>
            </div>
        </section>

    </div>
</main>

<?php

include 'includes/footer.php';
?>