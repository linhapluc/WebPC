<?php
// build_pc.php

include 'includes/header.php';
require 'includes/connect.php';

// ---- TÍNH TOÁN BIẾN CHO main_navigation.php ----
$totalQuantity = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $quantity_in_cart) {
        if (is_numeric($quantity_in_cart) && $quantity_in_cart > 0) {
            $totalQuantity += (int)$quantity_in_cart;
        }
    }
}
$cart_page_url = isset($_SESSION['user_id']) ? 'cart.php' : 'dangnhap.php';


$pageSpecificTitle = "Xây dựng cấu hình PC - PC Shop Nasa";

// Danh sách các loại linh kiện cần thiết và id_loai tương ứng
$components_to_build = [
    ['type' => 'cpu', 'name' => 'Vi xử lý (CPU)', 'id_loai' => 'LSP011'],
    ['type' => 'mainboard', 'name' => 'Bo mạch chủ (Mainboard)', 'id_loai' => 'LSP012'],
    ['type' => 'ram', 'name' => 'Bộ nhớ trong (RAM)', 'id_loai' => 'LSP013'],
    ['type' => 'ssd', 'name' => 'Ổ cứng SSD', 'id_loai' => 'LSP014'],
    // ['type' => 'hdd', 'name' => 'Ổ cứng HDD (Tùy chọn)', 'id_loai' => 'LSPXXX'], 
    ['type' => 'vga', 'name' => 'Card màn hình (VGA)', 'id_loai' => 'LSP010'],
    ['type' => 'psu', 'name' => 'Nguồn máy tính (PSU)', 'id_loai' => 'LSP015'],
    ['type' => 'case', 'name' => 'Vỏ máy tính (Case)', 'id_loai' => 'LSP016'],

];

?>
<script>
    document.title = <?php echo json_encode($pageSpecificTitle); ?>;
</script>

<?php
include 'includes/main_navigation.php';
?>

<main class="build-pc-page" style="padding: 30px 0;">
    <div class="container">
        <h1>Xây dựng cấu hình PC của bạn</h1>
        <p style="margin-bottom:10px; color: #555;">Tự tay lựa chọn từng linh kiện để tạo nên bộ máy tính ưng ý.</p>
        <hr style="margin: 15px 0 30px 0;">

        <div class="build-pc-area" style="display: flex; flex-wrap: wrap; gap: 30px;">
            <div class="component-selection-area" style="flex: 3; min-width: 600px;">
                <table class="component-table" style="width: 100%; border-collapse: collapse; font-size: 0.95em;">
                    <thead style="background-color: #f0f0f0;">
                        <tr>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Linh kiện</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Lựa chọn của bạn</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 2px solid #ddd;">Giá</th>
                            <th style="padding: 12px; text-align: center; border-bottom: 2px solid #ddd;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $count = 1;
                        foreach ($components_to_build as $component): ?>
                            <tr data-component-type="<?php echo htmlspecialchars($component['type']); ?>"
                                data-id-loai="<?php echo htmlspecialchars($component['id_loai']); ?>"
                                data-component-name="<?php echo htmlspecialchars($component['name']); ?>"
                                style="border-bottom: 1px solid #eee;">
                                <td style="padding: 10px; font-weight: 600;"><?php echo $count++; ?>. <?php echo htmlspecialchars($component['name']); ?></td>
                                <td style="padding: 10px;" class="selected-component-name">
                                    <span class="name-display">Chưa chọn</span>
                                    <img src="" alt="" class="selected-component-image" style="max-width: 40px; max-height: 40px; vertical-align: middle; margin-left: 10px; display:none;">
                                </td>
                                <td style="padding: 10px; text-align: right; font-weight: 500;" class="selected-component-price">0₫</td>
                                <td style="padding: 10px; text-align: center;">
                                    <button class="btn-choose-component" style="padding: 6px 12px; cursor:pointer; background-color: #E53935; color:white; border:none; border-radius:4px; font-size:0.9em;">
                                        <i class="fa-solid fa-plus"></i> Chọn
                                    </button>
                                    <button class="btn-remove-component" style="padding: 6px 12px; cursor:pointer; background-color: #6c757d; color:white; border:none; border-radius:4px; font-size:0.9em; display:none; margin-left:5px;">
                                        <i class="fa-solid fa-trash-can"></i> Xóa
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="summary-sidebar-area" style="flex: 1; min-width: 300px; background-color: #f8f9fa; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); height:fit-content;">
                <h4 style="margin-top:0; margin-bottom:15px; border-bottom:1px solid #ddd; padding-bottom:10px;">Tóm tắt cấu hình</h4>
                <div id="build-summary-list">
                    <p style="color:#777; text-align:center;"><em>Chọn linh kiện để xem tóm tắt.</em></p>
                </div>
                <hr style="margin:20px 0;">
                <div style="font-size: 1.3em; font-weight: bold; display:flex; justify-content:space-between; margin-bottom:20px;">
                    <span>Tổng cộng:</span>
                    <span id="build-total-price" style="color: #E53935;">0₫</span>
                </div>
                <button id="btn-add-build-to-cart" style="width: 100%; padding: 12px; background-color: #28a745; color:white; border:none; border-radius:4px; cursor:pointer; font-size: 1.1em; font-weight:bold;" disabled>
                    <i class="fa-solid fa-cart-plus"></i> Thêm tất cả vào giỏ
                </button>
                <p id="add-to-cart-message" style="font-size:0.85em; margin-top:10px; text-align:center; color:#777;">Vui lòng chọn ít nhất một linh kiện.</p>
            </div>
        </div>

        <!-- Modal/Popup để chọn sản phẩm -->
        <div id="product-selection-modal" class="build-pc-modal" style="display: none;">
            <div class="build-pc-modal-content">
                <span class="build-pc-close-modal">×</span>
                <h3 id="modal-title" style="margin-top:0; margin-bottom:20px;">Chọn sản phẩm</h3>

                <div class="modal-filters" style="margin-bottom:15px; display:flex; gap:10px;">
                    <input type="text" id="modal-search-keyword" placeholder="Tìm theo tên..." style="padding:8px; border:1px solid #ccc; border-radius:4px; flex-grow:1;">

                </div>
                <div id="modal-product-list" style="max-height: 450px; overflow-y: auto;">

                </div>
            </div>
        </div>
    </div>

    <?php include 'chatbox.php'; ?>


</main>
<script>
document.addEventListener("DOMContentLoaded", function() {

    let currentSelectingType = '';
    let currentIdLoai = '';
    let currentAbortController = null;

    // ✅ STATE CHUẨN
    let selected = {
        cpu: '',
        mainboard: '',
        ramType: '' // DDR4 / DDR5
    };

    const modal = document.getElementById('product-selection-modal');
    const modalProductList = document.getElementById('modal-product-list');
    const searchInput = document.getElementById('modal-search-keyword');

    // =============================
    // 1. CLICK CHỌN LINH KIỆN
    // =============================
    document.addEventListener('click', function(e) {
        const button = e.target.closest('.btn-choose-component');
        if (!button) return;

        const tr = button.closest('tr');
        currentSelectingType = tr.getAttribute('data-component-type');
        currentIdLoai = tr.getAttribute('data-id-loai');

        modal.style.display = "block";
        searchInput.value = '';

        let socketToFilter = '';

        if (currentSelectingType === 'mainboard') {
            socketToFilter = selected.cpu;
        } 
        else if (currentSelectingType === 'cpu') {
            socketToFilter = selected.mainboard;
        }
        else if (currentSelectingType === 'ram') {
            socketToFilter = selected.ramType; // ✅ FIX CHÍNH
        }

        console.log("👉 Filter socket:", socketToFilter);

        loadProducts('', socketToFilter);
    });

    // =============================
    // 2. LOAD API
    // =============================
    function loadProducts(keyword = '', socket = '') {
        if (currentAbortController) currentAbortController.abort();
        currentAbortController = new AbortController();

        modalProductList.innerHTML = '<div style="text-align:center;padding:20px;">Đang tải...</div>';

        const url = `api/get_components.php?id_loai=${currentIdLoai}&socket=${encodeURIComponent(socket)}&keyword=${encodeURIComponent(keyword)}`;

        fetch(url, { signal: currentAbortController.signal })
            .then(res => res.json())
            .then(data => renderProductList(data))
            .catch(err => {
                if (err.name !== 'AbortError') {
                    modalProductList.innerHTML = 'Lỗi tải dữ liệu';
                }
            });
    }

    // =============================
    // 3. RENDER LIST
    // =============================
    function renderProductList(products) {
        modalProductList.innerHTML = '';

        if (!products || products.length === 0) {
            modalProductList.innerHTML = `
                <div style="padding:20px; text-align:center; color:orange;">
                    ⚠️ Không có sản phẩm phù hợp
                </div>`;
            return;
        }

        products.forEach(p => {
            const div = document.createElement('div');

            div.style = 'display:flex; align-items:center; padding:10px; border-bottom:1px solid #eee; cursor:pointer;';

            div.innerHTML = `
                <img src="${p.hinh_anh}" style="width:50px; height:50px; margin-right:10px;">
                <div style="flex:1">
                    <div><b>${p.ten_sp}</b></div>
                    <div style="color:red">${formatPrice(p.gia)}</div>
                    <small>${p.socket || ''}</small>
                </div>
            `;

            div.addEventListener('click', function() {
                selectProduct({
                    id: p.id_sanpham,
                    ten_sp: p.ten_sp,
                    gia: p.gia,
                    socket: p.socket
                });
            });

            modalProductList.appendChild(div);
        });
    }

    // =============================
    // 4. CHỌN SẢN PHẨM
    // =============================
    function selectProduct(p) {
        const tr = document.querySelector(`tr[data-component-type="${currentSelectingType}"]`);
        if (!tr) return;

        // ✅ lưu ID
        tr.setAttribute('data-selected-id', p.id);

        // CPU
        if (currentSelectingType === 'cpu') {
            selected.cpu = cleanSocket(p.socket);
        }

        // MAINBOARD
        else if (currentSelectingType === 'mainboard') {
            selected.mainboard = cleanSocket(p.socket);

            // ✅ XÁC ĐỊNH RAM TYPE
            if (p.socket && p.socket.toUpperCase().includes('DDR4')) {
                selected.ramType = 'DDR4';
            } else if (p.socket && p.socket.toUpperCase().includes('DDR5')) {
                selected.ramType = 'DDR5';
            } else {
                selected.ramType = '';
            }
        }

        // RAM
        else if (currentSelectingType === 'ram') {
            // không cần set gì thêm
        }

        // UI
        tr.querySelector('.name-display').innerHTML =
            `<b>${p.ten_sp}</b><br><small>${p.socket || ''}</small>`;

        tr.querySelector('.selected-component-price').textContent =
            formatPrice(p.gia);

        tr.querySelector('.btn-choose-component').style.display = 'none';
        tr.querySelector('.btn-remove-component').style.display = 'inline-block';

        modal.style.display = "none";

        updateTotal();
    }

    // =============================
    // 5. XÓA
    // =============================
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-remove-component');
        if (!btn) return;

        const tr = btn.closest('tr');
        const type = tr.getAttribute('data-component-type');

        selected[type] = '';

        if (type === 'mainboard') {
            selected.ramType = '';
        }

        tr.querySelector('.name-display').textContent = 'Chưa chọn';
        tr.querySelector('.selected-component-price').textContent = '0₫';
        tr.querySelector('.btn-choose-component').style.display = 'inline-block';
        btn.style.display = 'none';

        updateTotal();
    });

    // =============================
    // 6. TOTAL
    // =============================
    function updateTotal() {
    let total = 0;
    let hasItem = false;

    document.querySelectorAll('tr').forEach(tr => {
        const id = tr.getAttribute('data-selected-id');
        if (id) hasItem = true;
    });

    document.querySelectorAll('.selected-component-price').forEach(p => {
        total += parseInt(p.textContent.replace(/\D/g, '')) || 0;
    });

    document.getElementById('build-total-price').textContent = formatPrice(total);

    // ✅ ENABLE BUTTON
    const btn = document.getElementById('btn-add-build-to-cart');
    btn.disabled = !hasItem;
}

    // =============================
    // UTIL
    // =============================
    function formatPrice(price) {
        return new Intl.NumberFormat('vi-VN').format(price) + '₫';
    }

    function cleanSocket(socket) {
        return socket ? socket.replace(/\s/g, '').toUpperCase() : '';
    }

});
document.getElementById('btn-add-build-to-cart').addEventListener('click', function () {

    let items = [];

    document.querySelectorAll('tr').forEach(tr => {
        const id = tr.getAttribute('data-selected-id');
        if (id) {
            items.push({
                product_id: id,
                quantity: 1
            });
        }
    });

    if (items.length === 0) {
        alert("Chưa chọn linh kiện!");
        return;
    }

    fetch('add_multiple_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ items: items })
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);

        if (data.success) {
            location.reload(); // reload để cập nhật giỏ hàng
        }
    })
    .catch(() => {
        alert("Lỗi kết nối server!");
    });
});
</script>
<!-- Thêm CSS cho modal vào cuối file hoặc trong style.css -->
<style>
    .build-pc-modal {
        display: none;
        position: fixed;
        z-index: 1001;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
        padding-top: 60px;
    }

    .build-pc-modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 25px;
        border: 1px solid #ddd;
        width: 80%;
        max-width: 700px;
        border-radius: 8px;
        position: relative;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .build-pc-close-modal {
        color: #aaa;
        position: absolute;
        top: 10px;
        right: 20px;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .build-pc-close-modal:hover,
    .build-pc-close-modal:focus {
        color: black;
        text-decoration: none;
    }

    #modal-product-list .product-item {
        display: flex;
        align-items: center;
        padding: 10px;
        border-bottom: 1px solid #eee;
        gap: 15px;
    }

    #modal-product-list .product-item:last-child {
        border-bottom: none;
    }

    #modal-product-list .product-item img {
        width: 60px;
        height: 60px;
        object-fit: contain;
        border: 1px solid #eee;
        border-radius: 4px;
    }

    #modal-product-list .product-item .info {
        flex-grow: 1;
    }

    #modal-product-list .product-item .name {
        font-weight: 600;
        font-size: 0.95em;
        margin-bottom: 3px;
    }

    #modal-product-list .product-item .price {
        color: #E53935;
        font-weight: bold;
    }

    #modal-product-list .product-item .btn-select-this-product {
        padding: 6px 12px;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9em;
    }
</style>

<?php
if ($conn) {
    $conn = null;
}
include 'includes/footer.php';
?>