<?php
include 'includes/header.php';
require 'includes/connect.php';
include 'includes/main_navigation.php';
?>

<!-- Nhúng thư viện Biểu đồ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* CSS để xử lý phần ảnh mô phỏng không bị vỡ và hiển thị đẹp */
#pc-visual-preview {
    position: relative;
    width: 100%;
    padding-top: 75%;
    background: #f8f9fa;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #dee2e6;
}

#pc-visual-preview img {
    position: absolute;
    transition: all 0.5s ease;
    object-fit: contain;
    background: white;
    padding: 5px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    border: 1px solid #eee;
}

/* Định vị từng linh kiện trên khung hình cho đẹp */
#view-mainboard { top: 10%; left: 10%; width: 45%; height: 45%; z-index: 1; }
#view-cpu { top: 15%; left: 60%; width: 25%; height: 25%; z-index: 2; }
#view-vga { top: 55%; left: 10%; width: 45%; height: 35%; z-index: 4; }
#view-ram { top: 45%; left: 60%; width: 25%; height: 20%; z-index: 3; }
#view-psu { top: 75%; left: 60%; width: 30%; height: 20%; z-index: 5; }
#view-ssd { top: 5%; left: 60%; width: 20%; height: 10%; z-index: 0; }
</style>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- CỘT 1: MÔ PHỎNG LẮP RÁP & BIỂU ĐỒ -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white text-center">Mô phỏng lắp ráp (Ảnh thật)</div>
                <div class="card-body">
                    <div id="pc-visual-preview">
                        <!-- Các ảnh này sẽ lấy trực tiếp từ p.hinh_anh -->
                        <img src="" id="view-mainboard" style="display: none;">
                        <img src="" id="view-cpu" style="display: none;">
                        <img src="" id="view-vga" style="display: none;">
                        <img src="" id="view-ram" style="display: none;">
                        <img src="" id="view-psu" style="display: none;">
                        <img src="" id="view-ssd" style="display: none;">
                        
                        <div id="no-image-hint" style="position: absolute; top: 45%; width: 100%; text-align: center; color: #ccc;">
                            Chưa có linh kiện nào được chọn
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center">Sức mạnh Benchmark</div>
                <div class="card-body">
                    <canvas id="performanceChart" height="200"></canvas>
                    <div class="text-center mt-3">
                        <h2 id="total-benchmark-score" class="text-warning mb-0">0</h2>
                        <small class="text-muted">Tổng điểm hiệu năng</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- CỘT 2: BẢNG CHỌN LINH KIỆN -->
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Linh kiện</th>
                            <th>Lựa chọn</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $types = [
                            ['cpu', 'LSP011', 'Vi xử lý (CPU)'],
                            ['mainboard', 'LSP012', 'Bo mạch chủ'],
                            ['ram', 'LSP013', 'Bộ nhớ RAM'],
                            ['vga', 'LSP010', 'Card đồ họa'],
                            ['ssd', 'LSP014', 'Ổ cứng SSD'],
                            ['psu', 'LSP015', 'Nguồn (PSU)']
                        ];
                        foreach($types as $t): ?>
                        <tr data-component-type="<?= $t[0] ?>" data-id-loai="<?= $t[1] ?>" data-component-name="<?= $t[2] ?>">
                            <td class="align-middle"><strong><?= $t[2] ?></strong></td>
                            <td class="name-display align-middle text-muted">Chưa chọn</td>
                            <td class="align-middle text-right" style="min-width: 120px;">
                                <button class="btn btn-outline-primary btn-sm btn-choose-component">CHỌN</button>
                                <button class="btn btn-danger btn-sm btn-remove-component" style="display:none;">XÓA</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CỘT 3: TỔNG KẾT -->
        <div class="col-lg-3">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h5 class="card-title">Tóm tắt cấu hình</h5>
                    <div id="build-summary-list" class="small text-muted mb-3"></div>
                    
                    <div id="power-check-area" class="p-2 border rounded mb-3 bg-light">
                        <div class="d-flex justify-content-between small">
                            <span>Tiêu thụ: <strong id="total-wattage">0W</strong></span>
                            <span>Nguồn: <strong id="psu-wattage-display">0W</strong></span>
                        </div>
                        <div id="power-warning" class="mt-1 small font-weight-bold"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="h6 mb-0">Tổng cộng:</span>
                        <span id="build-total-price" class="h4 text-danger mb-0">0₫</span>
                    </div>
                    
                    <button id="btn-add-build-to-cart" class="btn btn-success btn-block btn-lg" disabled>THÊM VÀO GIỎ</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CHỌN SẢN PHẨM (Giữ nguyên logic cũ) -->
<div id="product-selection-modal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1050;">
    <div class="modal-dialog modal-lg mt-5">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 id="modal-title">Chọn linh kiện</h5>
                <button type="button" class="close build-pc-close-modal" style="font-size: 2rem;">&times;</button>
            </div>
            <div class="p-3">
                <input type="text" id="modal-search-keyword" class="form-control" placeholder="Nhập tên linh kiện để tìm...">
            </div>
            <div id="modal-product-list" style="max-height: 450px; overflow-y: auto;"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let selectedComponents = {};
    let currentType = '';
    let currentLoaiId = '';
    let performanceChart;

    // 1. KHỞI TẠO BIỂU ĐỒ
    const ctx = document.getElementById('performanceChart').getContext('2d');
    performanceChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['CPU', 'GPU', 'Khác', 'Tổng'],
            datasets: [{
                label: 'Điểm hiệu năng',
                data: [0, 0, 0, 0],
                backgroundColor: ['#007bff', '#28a745', '#17a2b8', '#ffc107']
            }]
        },
        options: { scales: { y: { beginAtZero: true, max: 50000 } } }
    });

    // 2. MỞ MODAL
    document.querySelectorAll('.btn-choose-component').forEach(btn => {
        btn.onclick = function() {
            const tr = this.closest('tr');
            currentType = tr.dataset.componentType;
            currentLoaiId = tr.dataset.idLoai;
            document.getElementById('modal-title').textContent = "Chọn " + tr.dataset.componentName;
            document.getElementById('product-selection-modal').style.display = 'block';
            loadProducts();
        };
    });

    function loadProducts(keyword = '') {
        let socket = '';
        if (currentType === 'mainboard' && selectedComponents['cpu']) socket = selectedComponents['cpu'].socket;
        else if (currentType === 'cpu' && selectedComponents['mainboard']) socket = selectedComponents['mainboard'].socket;
        else if (currentType === 'ram' && selectedComponents['mainboard']) {
        const mbName = selectedComponents['mainboard'].ten_sp.toUpperCase();
        socket = mbName.includes('DDR5') ? 'DDR5' : 'DDR4';
        console.log("Hệ thống tự động lọc RAM chuẩn:", socket);
    }
        const url = `api/get_components.php?id_loai=${currentLoaiId}&socket=${encodeURIComponent(socket)}&keyword=${encodeURIComponent(keyword)}`;
        
        fetch(url).then(res => res.json()).then(data => {
            const list = document.getElementById('modal-product-list');
            list.innerHTML = '';
            data.forEach(p => {
                list.insertAdjacentHTML('beforeend', `
                    <div class="d-flex align-items-center p-3 border-bottom">
                        <img src="${p.hinh_anh}" style="width:60px; height:60px; object-fit:contain; margin-right:15px; border: 1px solid #eee;">
                        <div style="flex-grow:1;">
                            <div class="font-weight-bold text-primary">${p.ten_sp}</div>
                            <small class="text-muted">Socket: ${p.socket || 'N/A'} | Công suất: ${p.wattage}W | Điểm: ${p.benchmark_score || 0}</small>
                        </div>
                        <div class="text-danger font-weight-bold mr-3">${new Intl.NumberFormat('vi-VN').format(p.gia)}₫</div>
                        <button class="btn btn-primary btn-sm btn-select-item" data-p='${JSON.stringify(p)}'>CHỌN</button>
                    </div>
                `);
            });
        });
    }

    // 3. XỬ LÝ CHỌN SẢN PHẨM
    document.getElementById('modal-product-list').onclick = function(e) {
        if (e.target.classList.contains('btn-select-item')) {
            const p = JSON.parse(e.target.dataset.p);
            selectedComponents[currentType] = p;
            updateUI();
            document.getElementById('product-selection-modal').style.display = 'none';
        }
    };

    function updateUI() {
        let totalP = 0; let totalW = 50; let totalS = 0;
        let cpuS = 0; let vgaS = 0; let otherS = 0;
        let psuW = 0;

        const summary = document.getElementById('build-summary-list');
        summary.innerHTML = '';
        document.getElementById('no-image-hint').style.display = 'none';

        for (const type in selectedComponents) {
            const p = selectedComponents[type];
            totalP += parseInt(p.gia);
            
            // Cập nhật dòng bảng
            const tr = document.querySelector(`tr[data-component-type="${type}"]`);
            tr.querySelector('.name-display').innerHTML = `<span class="text-dark">${p.ten_sp}</span>`;
            tr.querySelector('.btn-choose-component').style.display = 'none';
            tr.querySelector('.btn-remove-component').style.display = 'inline-block';

            // CẬP NHẬT ẢNH MÔ PHỎNG (Lấy ảnh thật)
            const viewImg = document.getElementById(`view-${type}`);
            if (viewImg) {
                viewImg.src = p.hinh_anh;
                viewImg.style.display = 'block';
            }

            // Tính toán công suất & điểm
            if (type === 'psu') psuW = parseInt(p.wattage);
            else totalW += parseInt(p.wattage);

            let score = parseInt(p.benchmark_score || 0);
            if (type === 'cpu') cpuS = score;
            else if (type === 'vga') vgaS = score;
            else otherS += score;
            totalS += score;

            summary.insertAdjacentHTML('beforeend', `<div class="mb-1">• ${p.ten_sp}</div>`);
        }

        // 4. CẬP NHẬT BIỂU ĐỒ
        performanceChart.data.datasets[0].data = [cpuS, vgaS, otherS, totalS];
        performanceChart.update();
        document.getElementById('total-benchmark-score').textContent = totalS.toLocaleString();

        // 5. CẬP NHẬT TIỀN & NGUỒN
        document.getElementById('build-total-price').textContent = new Intl.NumberFormat('vi-VN').format(totalP) + '₫';
        document.getElementById('total-wattage').textContent = totalW + 'W';
        document.getElementById('psu-wattage-display').textContent = psuW + 'W';
        
        const warning = document.getElementById('power-warning');
if (psuW > 0) {
    const usageRatio = totalW / psuW; // Tính tỷ lệ sử dụng

    if (totalW > psuW) { 
        // 1. MÀU ĐỎ: Vượt quá công suất
        warning.textContent = "⚠️ Nguy hiểm: Nguồn quá yếu!"; 
        warning.className = "text-danger font-weight-bold"; 
    } 
    else if (usageRatio >= 0.8) { 
        // 2. MÀU VÀNG: Sử dụng trên 80% (Trường hợp 435W/450W sẽ rơi vào đây)
        warning.textContent = "⚠️ Cảnh báo: Nguồn sát tải (90-100%)"; 
        warning.style.color = "#ffc107"; // Màu vàng chuẩn cảnh báo
        warning.className = "font-weight-bold"; 
    } 
    else { 
        // 3. MÀU XANH: Dưới 80% công suất
        warning.textContent = "✅ Nguồn an toàn"; 
        warning.style.color = ""; // Reset về mặc định
        warning.className = "text-success font-weight-bold"; 
    }
}

        document.getElementById('btn-add-build-to-cart').disabled = (Object.keys(selectedComponents).length === 0);
    }

    // 6. XỬ LÝ NÚT XÓA
    document.querySelectorAll('.btn-remove-component').forEach(btn => {
        btn.onclick = function() {
            const type = this.closest('tr').dataset.componentType;
            const viewImg = document.getElementById(`view-${type}`);
            if (viewImg) { viewImg.style.display = 'none'; viewImg.src = ''; }
            
            delete selectedComponents[type];
            const tr = this.closest('tr');
            tr.querySelector('.name-display').textContent = 'Chưa chọn';
            tr.querySelector('.btn-choose-component').style.display = 'inline-block';
            this.style.display = 'none';
            
            if (Object.keys(selectedComponents).length === 0) 
                document.getElementById('no-image-hint').style.display = 'block';
            
            updateUI();
        };
    });

    // 7. THÊM VÀO GIỎ
    document.getElementById('btn-add-build-to-cart').onclick = function() {
        const ids = Object.values(selectedComponents).map(p => p.id_sanpham);
        const fd = new FormData();
        fd.append('product_ids', JSON.stringify(ids));
        fetch('add_multiple_to_cart.php', { method: 'POST', body: fd })
        .then(res => res.json()).then(data => {
            if(data.success) { 
                alert("Đã thêm toàn bộ cấu hình vào giỏ!"); 
                window.location.href = 'cart.php'; 
            }
        });
    };

    document.querySelector('.build-pc-close-modal').onclick = () => document.getElementById('product-selection-modal').style.display = 'none';
    document.getElementById('modal-search-keyword').oninput = (e) => loadProducts(e.target.value);
});
</script>

<?php include 'includes/footer.php'; ?>